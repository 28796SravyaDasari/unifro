<?php

    if(!isset($_COOKIE['asid']))
    {
        echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
        exit();
    }

    $response['status'] = 'error';

    /*-----------------------------------------
        REMOVE PRODUCT FROM THE ORDER
    ------------------------------------------*/

    if($_POST['option'] == 'remove')
    {
        if($_POST['OrderDetailsID'] > 0)
        {
            $OrderID = GetOrderProducts($_POST['OrderDetailsID'], 'OrderID');

            $res = MysqlQuery("DELETE FROM client_order_details WHERE OrderDetailsID = '".$_POST['OrderDetailsID']."' AND ProductID = '".$_POST['ProductID']."' LIMIT 1");

            if(MysqlAffectedRows() == 1)
            {
                // Let's get the total cost of all the products in this order
                $GetTotalCost = MysqlQuery("SELECT TotalCost FROM client_order_details WHERE OrderID = '".$OrderID."'");
                $GetTotalCost = MysqlFetchAll($GetTotalCost);
                $OrderTotal = 0;

                foreach($GetTotalCost as $row)
                {
                    $OrderTotal = $OrderTotal + $row['TotalCost'];
                }

                // Let's check if discount is offered
                $DiscountPercent = mysqli_fetch_assoc(MysqlQuery("SELECT DiscountPercent FROM client_orders WHERE OrderID = '".$OrderID."' LIMIT 1"))['DiscountPercent'];

                $OrderTotal = round($OrderTotal);
                $TotalDiscount = round($OrderTotal * $DiscountPercent / 100);
                $FinalTotal = $OrderTotal - $TotalDiscount;

                // Let's update the discount amount and final total in client orders table
                $res = MysqlQuery("UPDATE client_orders SET TotalCost = '".$OrderTotal."', DiscountAmount = '".$TotalDiscount."', FinalTotal = '".$FinalTotal."', UpdatedOn = '".time()."',
                                    UpdatedBy = '".$AID."' WHERE OrderID = '".$OrderID."' LIMIT 1");

                $activity = 'Order Product Deleted';
                $table = 'client_order_details';
                $Desc = 'Product removed from Order ID: '.$OrderID.' and Product: '.$_POST['ProductID'];

                RecordAdminActivity($activity, $table, $_POST['OrderDetailsID'], $Desc);
                $response['status']     = 'success';
            }
            else
            {
                $response['response_message']    = 'Error occurred! [LN 10]';
            }
        }
        else
        {
            $response['response_message'] = 'Something went wrong! Please refresh the page and try again. [LN 20]';
        }
    }

    /*-------------------------------------------------------------------
        UPDATE QTY & PRICE OF THE PRODUCT
    --------------------------------------------------------------------*/
    elseif($_POST['OrderDetailsID'] > 0 && isset($_POST['Quantity']))
    {
        // LETS VALIDATE THE ID AND GET THE DETAILS
        $InvalidAccess = 'y';
        $OrderTotal = 0;

        $CartDetails = MysqlQuery("SELECT * FROM client_order_details WHERE OrderDetailsID = '".$_POST['OrderDetailsID']."' AND OrderID = '".$_POST['OrderID']."' LIMIT 1");
        if(mysqli_num_rows($CartDetails) == 1)
        {
            $CartDetails = mysqli_fetch_assoc($CartDetails);

            foreach($_POST['Quantity'] as $size => $qty)
            {
                if($qty == '')
                {
                    $response['response_message']   = 'Quantity for '. $size.' size cannot be blank. Set as "0" if do not want any quantity';
                    echo json_encode($response);
                    exit;
                }
                else
                {
                    $UpdateQty[$size] = $qty;
                }
            }

            $TotalQty = array_sum($UpdateQty);
            $TotalCost = $TotalQty * $CartDetails['FinalPrice'];
            $TotalDiscount = $CartDetails['Discount'] * $TotalQty;
            $UpdateQty = JSONEncode($UpdateQty);

            $res = MysqlQuery("UPDATE client_order_details SET Size = '".$UpdateQty."', TotalCost = '".$TotalCost."', TotalDiscount = '".$TotalDiscount."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE OrderDetailsID = '".$_POST['OrderDetailsID']."' AND OrderID = '".$_POST['OrderID']."' LIMIT 1");

            if(MysqlAffectedRows() == 1)
            {
                // Let's get the total cost of all the products in this order
                $GetTotalCost = MysqlQuery("SELECT TotalCost FROM client_order_details WHERE OrderID = '".$_POST['OrderID']."'");
                $GetTotalCost = MysqlFetchAll($GetTotalCost);
                $OrderTotal = 0;

                foreach($GetTotalCost as $row)
                {
                    $OrderTotal = $OrderTotal + $row['TotalCost'];
                }

                // Let's check if discount is offered
                $DiscountPercent = mysqli_fetch_assoc(MysqlQuery("SELECT DiscountPercent FROM client_orders WHERE OrderID = '".$_POST['OrderID']."' LIMIT 1"))['DiscountPercent'];

                $OrderTotal = round($OrderTotal);
                $TotalDiscount = round($OrderTotal * $DiscountPercent / 100);
                $FinalTotal = $OrderTotal - $TotalDiscount;

                // Let's update the discount amount and final total in client orders table
                $res = MysqlQuery("UPDATE client_orders SET TotalCost = '".$OrderTotal."', DiscountAmount = '".$TotalDiscount."', FinalTotal = '".$FinalTotal."', UpdatedOn = '".time()."',
                                    UpdatedBy = '".$AID."' WHERE OrderID = '".$_POST['OrderID']."' LIMIT 1");

                if(MysqlAffectedRows() == 1)
                {
                    $Desc = 'Quantity for '.$CartDetails['ProductName'].' has been updated for Order ID: '.$_POST['OrderID'].'. Quantity: '.$UpdateQty.', Total Cost: '.$TotalCost;

                    RecordAdminActivity('Order Quantity Updated', 'client_order_details', $_POST['OrderDetailsID'], $Desc);
                	$response['status'] = 'success';
                }
                else
                {
                    // Roll back the client_order_details updated query
                    MysqlQuery("UPDATE client_order_details SET Size = '".$CartDetails['Size']."', TotalCost = '".$CartDetails['TotalCost']."', TotalDiscount = '".$CartDetails['TotalDiscount']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE OrderDetailsID = '".$_POST['OrderDetailsID']."' AND OrderID = '".$_POST['OrderID']."' LIMIT 1");

                    $response['response_message']   = 'Error occurred! LN 10';
                }
            }
            else
            {
                $response['response_message']   = 'Error occurred! LN 10';
            }

            /*
            if($CartDetails['CustomData'] != '')
            {
                $Percentage = GetCategoryDetails($CartDetails['ProductID'], 'Size');
            }
            else
            {
                // GET THE SIZE OBJECT FROM THE CATEGORY TABLE
                $Percentage = MysqlQuery("SELECT c.Size FROM master_categories c LEFT JOIN product_categories pc ON pc.CategoryID = c.CategoryID
                                          WHERE pc.ProductID = '".$CartDetails['ProductID']."' LIMIT 1");
                $Percentage = mysqli_fetch_assoc($Percentage)['Size'];
            }

            $Percentage = json_decode($Percentage, true);

            */
            //$TotalCost = array_sum($TotalCost);
        }
        else
        {
            $response['response_message'] = 'Invalid Access!';
        }
    }
    elseif($_POST['option'] == 'update-shipping' && $_POST['pk'] > 0)
    {
        // Validate the order ID
        if(mysqli_num_rows(MysqlQuery("SELECT OrderID FROM client_orders WHERE OrderID = '".$_POST['pk']."' LIMIT 1")) == 1)
        {
            $res = MysqlQuery("UPDATE client_orders SET ".$_POST['name']." = '".$_POST['value']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE OrderID = '".$_POST['pk']."' LIMIT 1");

            if(MysqlAffectedRows() == 1)
            {
                $desc = $_POST['name'].' updated as '.$_POST['value'];

                RecordAdminActivity($_POST['name'].' Updated', 'client_orders', $_POST['pk'], $desc);
            	$response['status'] = 'success';
            }
            else
            {
                $response['response_message']   = 'Error occurred! [LN 10]';
            }
        }
        else
        {
            $response['response_message'] = 'Invalid Access! [LN 50]';
        }
    }
    elseif($_POST['name'] == 'OrderStatus' && is_numeric($_POST['pk']))
    {
        $res = MysqlQuery("UPDATE client_order_status SET Status = '".$_POST['value']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE OrderID = '".$_POST['pk']."' LIMIT 1");

        if(MysqlAffectedRows() == 1)
        {
            $desc = 'Order status updated as '.$_POST['value'];

            RecordAdminActivity('Order Status Updated', 'client_order_status', $_POST['pk'], $desc);
        	$response['status'] = 'success';
        }
        else
        {
            $response['response_message']   = 'Error occurred! [LN 10]';
        }
    }
    elseif(($_POST['name'] == 'PaymentMode' || $_POST['name'] == 'PaymentStatus') && is_numeric($_POST['pk']))
    {
        $res = MysqlQuery("UPDATE client_orders SET ".$_POST['name']." = '".$_POST['value']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE OrderID = '".$_POST['pk']."' LIMIT 1");

        if(MysqlAffectedRows() == 1)
        {
            $desc = $_POST['name'].' updated as '.$_POST['value'].' for Order ID: '.$_POST['pk'];

            RecordAdminActivity('Order Status Updated', 'client_orders', $_POST['pk'], $desc);
        	$response['status'] = 'success';
        }
        else
        {
            $response['response_message']   = 'Error occurred! [LN 10]';
        }
    }
    else
    {
        $response['response_message'] = 'Invalid Access!';
    }

    echo json_encode($response);


?>