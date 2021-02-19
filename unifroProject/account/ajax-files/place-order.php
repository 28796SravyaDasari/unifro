<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

    if(!isset($_SESSION['CartDetails']['AddressID']))
    {
        $response['response_message'] = 'Please select your Delivery Address';
    }
    else
    {
        // GET THE CART DETAILS
        $CartDetails = MysqlQuery("SELECT * FROM customer_shopping_cart WHERE CustomerID = '".$MemberID."'");
        if(mysqli_num_rows($CartDetails) > 0)
        {
            $TotalCost = $ID = 0;

            /*--------------------------------------------
            Store the discount percentage in a variable
            --------------------------------------------*/

            for(;$row = mysqli_fetch_assoc($CartDetails);)
            {
                if($row['UpdatePrice'] == 1)
                {
                    $response['status'] = 'update';
                    $response['response_message'] = 'Product Price has been changed! Please check for the updated price.';
                    $response['redirect'] = '/shopping-bag/';
                    echo json_encode($response);
                    exit;
                }
                else
                {
                    /*---------------------------------------------------
                    Sum up the total cost of all the products in the cart
                    ----------------------------------------------------*/
                    $TotalCost = $TotalCost + $row['TotalCost'];

                    /*----------------------------------------------------------------------------------
                    Lets store the cart records in array for bulk insert into customer_order_details table
                    -----------------------------------------------------------------------------------*/
                    $Values[] = "'[%ORDERID%]', '".$row['ProductID']."', '".$row['ProductName']."', '".$row['CustomData']."', '".$row['Size']."', '".$row['Measurement']."',
                                '".$row['AdditionalDetails']."', '".$row['GrossPrice']."', '".$row['Discount']."', '".$row['FinalPrice']."', '".$row['TotalCost']."',
                                '".$row['TotalDiscount']."', '".$row['TaxRate']."', '".time()."'";
                }
            }   // END OF FOR LOOP

            $TotalCost = round($TotalCost);

            if($_SESSION['CartDetails']['CouponDiscount'] > 0)
            {
                // Total Payable Amount = Total Cost - Coupon Discount - Shipping Cost
                $FinalTotal = ($TotalCost - $_SESSION['CartDetails']['CouponDiscount']) + $_SESSION['CartDetails']['ShippingCost'];
            }
            else
            {
                // Total Payable Amount = Total Cost - Shipping Cost
                $FinalTotal = $TotalCost + $_SESSION['CartDetails']['ShippingCost'];
            }

            $OrderAdditionalDetails = $_SESSION['CartDetails']['AdditionalDetails'];

             /*--------------------------------------------
                Lets fetch the delivery address
             --------------------------------------------*/
            $DeliveryAddress = mysqli_fetch_assoc(MysqlQuery("SELECT a.*, s.Name, c.CityName FROM customer_delivery_addresses a
                                                                LEFT JOIN cities c ON c.CityID = a.City
                                                                LEFT JOIN states s ON s.StateID = a.State
                                                                WHERE a.AddressID = '".$_SESSION['CartDetails']['AddressID']."' AND a.MemberID = '".$MemberID."' LIMIT 1"));

            /*--------------------------------------------
             Insert record into the client_orders table
            ---------------------------------------------*/
            $_SESSION['CartDetails']['CouponID']        = $_SESSION['CartDetails']['CouponID'] > 0 ? $_SESSION['CartDetails']['CouponID'] : 0;
            $_SESSION['CartDetails']['CouponDiscount']  = $_SESSION['CartDetails']['CouponDiscount'] > 0 ? $_SESSION['CartDetails']['CouponDiscount'] : 0;

            $res = MysqlQuery("INSERT INTO customer_orders (
                                                    MemberID, TotalCost, CouponID, DiscountAmount, ShippingCharges, FinalTotal, OrderAdditionalDetails, ShippingName, ShippingEmail,
                                                    ShippingPhone, ShippingAddress, ShippingLandmark, ShippingPincode, ShippingCountry, ShippingState, ShippingCity, OrderDate, IPAddress
                                                  )
            VALUE (
                    '".$MemberID."', '".$TotalCost."', '".$_SESSION['CartDetails']['CouponID']."', '".$_SESSION['CartDetails']['CouponDiscount']."',
                    '".$_SESSION['CartDetails']['ShippingCost']."', '".$FinalTotal."', '".$OrderAdditionalDetails."', '".$DeliveryAddress['ContactName']."',
                    '".$MemberDetails['EmailID']."', '".$DeliveryAddress['Mobile']."', '".$DeliveryAddress['Address']."', '', '".$DeliveryAddress['Pincode']."', 'India',
                    '".$DeliveryAddress['Name']."', '".$DeliveryAddress['CityName']."', '".time()."', '".GetIP()."'
                  )"
                      );

            if(MysqlAffectedRows() == 1)
            {
                $ID = MysqlInsertID();

                foreach ($Values as &$str)
                {
                    $ValuesToInsert[] = str_replace('[%ORDERID%]', $ID, $str);
                }

                /*-----------------------------------------------------------------------------
                 On successful insert, add the cart products to the customer_order_details table
                ------------------------------------------------------------------------------*/
                $TargetTable = 'customer_order_details';
                $fields = 'OrderID,ProductID,ProductName,CustomData,Size,Measurement,AdditionalDetails,GrossPrice,Discount,FinalPrice,TotalCost,TotalDiscount,TaxRate,AddedOn';
                $count = BulkInsert($TargetTable, $fields, $ValuesToInsert);
                if($count > 0)
                {
                    /*-----------------------------------------------------------------
                      Lets update the order status in the customer_order_status table
                    ------------------------------------------------------------------*/
                    $res = MysqlQuery("INSERT INTO customer_order_status (OrderID, Status) VALUES ('".$ID."', 'Pending')");

                    if(MysqlAffectedRows() < 1)
                    {
                        $description    = 'Failed to insert record in customer_order_status table. Kindly update the order status for Order ID: '.$ID.' as Pending';
                        $flag           = 'INSERT_FAILED';
                        GenerateLog($flag, $description);
                    }

                    /*---------------------------------------------------------------------------------------------------
                        As we have successfully added the products in the orders table, delete the products from the cart
                    ----------------------------------------------------------------------------------------------------*/
                    MysqlQuery("DELETE FROM customer_shopping_cart WHERE CustomerID = '".$MemberID."'");

                    unset($_SESSION['CartDetails']);

                    $_SESSION['CartDetails']['OrderID'] = $ID;

                    $response['status']             = 'success';
                    $response['redirect']           = '/ccavenue/pg-process.php';

                    RecordMemberActivity('Order Placed', 'customer_orders', $ID);

                    SendOrderConfirmationEmail($ID);
                }
                else
                {
                    /*-----------------------------------------------------------------
                    If bulk insert fails, then delete a record from customer_orders table
                    ------------------------------------------------------------------*/
                    MysqlQuery("DELETE FROM customer_orders WHERE OrderID = '".$ID."' LIMIT 1");
                    $response['response_message']   = 'Error occurred! LN 50'.$count;
                }
            }
            else
            {
                $response['response_message']   = 'Error occurred! LN 60';
            }

        }
        else
        {
            $response['response_message'] = 'Something went wrong! Please refresh the page and try again. [LN 20]';
        }
    }


    echo json_encode($response);


?>