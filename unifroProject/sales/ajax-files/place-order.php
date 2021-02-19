<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

    if($_SESSION['ClientID'] > 0 && $_POST['AddressID'] > 0)
    {
        foreach($_SESSION['CartDetails'][$_SESSION['ClientID']]['Products'] as $CartID => $Arr)
        {
            $CartIDs[] = $CartID;
        }

        // LETS VALIDATE THE CLIENT ID AND GET THE CART DETAILS
        $CartDetails = MysqlQuery("SELECT * FROM client_shopping_cart WHERE CartID IN(".implode(',', $CartIDs).") AND SalesID = '".$MemberID."' AND ClientID = '".$_SESSION['ClientID']."'");
        if(mysqli_num_rows($CartDetails) > 0)
        {
            $TotalCost = $TotalDiscount = $ID = 0;

            /*--------------------------------------------
            Store the discount percentage in a variable
            --------------------------------------------*/
            $CorporateDiscountPercent = $_SESSION['CartDetails']['CorporateDiscount'] > 0 ? $_SESSION['CartDetails']['CorporateDiscount'] : 0;

            for(;$row = mysqli_fetch_assoc($CartDetails);)
            {
                if($row['UpdatePrice'] == 1)
                {
                    $response['status'] = 'update';
                    $response['response_message'] = 'Product Price has been changed! Please check for the updated price.';
                    $response['redirect'] = '/sales/clients/'.$_SESSION['ClientID'].'/';
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
                    Lets store the cart records in array for bulk insert into client_order_details table
                    -----------------------------------------------------------------------------------*/
                    $Values[] = "'[%ORDERID%]', '".$row['ProductID']."', '".$row['ProductName']."', '".$row['CustomData']."', '".$row['Size']."', '".$row['GrossPrice']."', '".$row['Discount']."', '".$row['FinalPrice']."', '".$row['TotalCost']."', '".$row['TotalDiscount']."', '".$row['TaxRate']."',  '".$row['AdditionalDetails']."',  '".$row['MeasurementFile']."', '".time()."'";
                }
            }   // END OF FOR LOOP

            $TotalCost = round($TotalCost);
            $TotalDiscount = round($TotalCost * $CorporateDiscountPercent / 100);
            $FinalTotal = $TotalCost - $TotalDiscount;

            $OrderAdditionalDetails = $_SESSION['CartDetails'][$_SESSION['ClientID']]['AdditionalDetails'];

            /*-----------------------------------------------
                Fetch the client details for billing address
             -----------------------------------------------*/
            $ClientDetails = GetClientDetails($_SESSION['ClientID']);

             /*--------------------------------------------
                Lets fetch the delivery address
             --------------------------------------------*/
            $DeliveryAddress = mysqli_fetch_assoc(MysqlQuery("SELECT a.*, s.Name, c.CityName FROM client_delivery_addresses a LEFT JOIN cities c ON c.CityID = a.City LEFT JOIN states s ON s.StateID = a.State WHERE a.AddressID = '".$_POST['AddressID']."' AND a.ClientID = '".$_SESSION['ClientID']."' LIMIT 1"));

            /*--------------------------------------------
             Insert record into the client_orders table
            ---------------------------------------------*/
            $res = MysqlQuery("INSERT INTO client_orders (
                                                    ClientID, TotalCost, DiscountPercent, DiscountAmount, ShippingCharges, FinalTotal, OrderAdditionalDetails, BillingName, BillingEmail, BillingPhone, BillingAddress,
                                                    BillingLandmark, BillingPincode, BillingCountry, BillingState, BillingCity, ShippingName, ShippingEmail, ShippingPhone, ShippingAddress,
                                                    ShippingLandmark, ShippingPincode, ShippingCountry, ShippingState, ShippingCity, OrderDate, IPAddress
                                                  )
            VALUE (
                    '".$_SESSION['ClientID']."', '".$TotalCost."', '".$CorporateDiscountPercent."', '".$TotalDiscount."', '0', '".$FinalTotal."', '".$OrderAdditionalDetails."', '".$ClientDetails['ClientName']."',
                    '".$ClientDetails['EmailID']."', '".$ClientDetails['Mobile']."', '".$ClientDetails['Address']."', '', '".$ClientDetails['Pincode']."', 'India',
                    '".$ClientDetails['Name']."', '".$ClientDetails['CityName']."', '".$DeliveryAddress['ContactName']."', '".$ClientDetails['EmailID']."', '".$DeliveryAddress['Mobile']."',
                    '".$DeliveryAddress['Address']."', '', '".$DeliveryAddress['Pincode']."', 'India', '".$DeliveryAddress['Name']."', '".$DeliveryAddress['CityName']."',
                    '".time()."', '".GetIP()."'
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
                 On successful insert, add the cart products to the client_order_details table
                ------------------------------------------------------------------------------*/
                $TargetTable = 'client_order_details';
                $fields = 'OrderID,ProductID,ProductName,CustomData,Size,GrossPrice,Discount,FinalPrice,TotalCost,TotalDiscount,TaxRate,AdditionalDetails,MeasurementFile,OrderDate';
                $count = BulkInsert($TargetTable, $fields, $ValuesToInsert);
                if($count > 0)
                {
                    /*-----------------------------------------------------------------
                      Lets update the order status in the client_order_status table
                    ------------------------------------------------------------------*/
                    $res = MysqlQuery("INSERT INTO client_order_status (OrderID, Status) VALUES ('".$ID."', '".$MasterOrderStatus[2]."')");

                    if(MysqlAffectedRows() < 1)
                    {
                        $description    = 'Failed to insert record in client_order_status table. Kindly update the order status for Order ID: '.$ID.' as '.$MasterOrderStatus[1];
                        $flag           = 'INSERT_FAILED';
                        GenerateLog($flag, $description);
                    }

                    /*---------------------------------------------------------------------------------------------------
                        As we have successfully added the products in the orders table, delete the products from the cart
                    ----------------------------------------------------------------------------------------------------*/
                    MysqlQuery("DELETE FROM client_shopping_cart WHERE CartID IN(".implode(',', $CartIDs).") AND ClientID = '".$_SESSION['ClientID']."'");

                    $response['status']             = 'success';
                    $response['response_message']   = 'Order Placed Successfully!';
                    $response['redirect']           = '/sales/clients/'.$_SESSION['ClientID'].'/';

                    unset($_SESSION['CartDetails']);

                    RecordSalesActivity('order_placed', 'client_orders', $ID);
                }
                else
                {
                    /*-----------------------------------------------------------------
                    If bulk insert fails, then delete a record from client_orders table
                    ------------------------------------------------------------------*/
                    MysqlQuery("DELETE FROM client_orders WHERE OrderID = '".$ID."' LIMIT 1");
                    $response['response_message']   = 'Error occurred! LN 50';
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
    else
    {
        $response['response_message'] = 'Invalid Access!';
    }

    echo json_encode($response);


?>