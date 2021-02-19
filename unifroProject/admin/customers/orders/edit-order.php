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
            $OrderID = MysqlQuery("SELECT OrderID FROM customer_order_details WHERE OrderDetailsID = '".$ID."' LIMIT 1");
            $OrderID = mysqli_fetch_assoc($OrderID)['OrderID'];

            MysqlQuery("DELETE FROM customer_order_details WHERE OrderDetailsID = '".$_POST['OrderDetailsID']."' AND ProductID = '".$_POST['ProductID']."' LIMIT 1");

            if(MysqlAffectedRows() == 1)
            {
                // Let's get the total cost of all the products in this order
                $GetTotalCost = MysqlQuery("SELECT TotalCost FROM customer_order_details WHERE OrderID = '".$OrderID."'");
                $GetTotalCost = MysqlFetchAll($GetTotalCost);
                $OrderTotal = 0;

                foreach($GetTotalCost as $row)
                {
                    $OrderTotal = $OrderTotal + $row['TotalCost'];
                }

                // Let's check if discount is offered
                $DiscountPercent = mysqli_fetch_assoc(MysqlQuery("SELECT DiscountPercent FROM customer_orders WHERE OrderID = '".$OrderID."' LIMIT 1"))['DiscountPercent'];

                $OrderTotal = round($OrderTotal);
                $TotalDiscount = round($OrderTotal * $DiscountPercent / 100);
                $FinalTotal = $OrderTotal - $TotalDiscount;

                // Let's update the discount amount and final total in client orders table
                $res = MysqlQuery("UPDATE client_orders SET TotalCost = '".$OrderTotal."', DiscountAmount = '".$TotalDiscount."', FinalTotal = '".$FinalTotal."', UpdatedOn = '".time()."',
                                    UpdatedBy = '".$AID."' WHERE OrderID = '".$OrderID."' LIMIT 1");

                $activity = 'Order Product Deleted';
                $table = 'customer_order_details';
                $Desc = 'Product removed from Customer Order ID: '.$OrderID.' and Product: '.$_POST['ProductID'];

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
    elseif($_POST['option'] == 'update-shipping' && $_POST['pk'] > 0)
    {
        // Validate the order ID
        if(mysqli_num_rows(MysqlQuery("SELECT OrderID FROM customer_orders WHERE OrderID = '".$_POST['pk']."' LIMIT 1")) == 1)
        {
            $res = MysqlQuery("UPDATE customer_orders SET ".$_POST['name']." = '".$_POST['value']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE OrderID = '".$_POST['pk']."' LIMIT 1");

            if(MysqlAffectedRows() == 1)
            {
                $desc = $_POST['name'].' updated as '.$_POST['value'];

                RecordAdminActivity($_POST['name'].' Updated', 'customer_orders', $_POST['pk'], $desc);
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
        $res = MysqlQuery("UPDATE customer_order_status SET Status = '".$_POST['value']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE OrderID = '".$_POST['pk']."' LIMIT 1");

        if(MysqlAffectedRows() == 1)
        {
            $desc = 'Order status updated as '.$_POST['value'];

            RecordAdminActivity('Order Status Updated', 'customer_order_status', $_POST['pk'], $desc);
        	$response['status'] = 'success';
        }
        else
        {
            $response['response_message']   = 'Error occurred! [LN 10]';
        }
    }
    elseif(($_POST['name'] == 'PaymentMode' || $_POST['name'] == 'PaymentStatus') && is_numeric($_POST['pk']))
    {
        $res = MysqlQuery("UPDATE customer_orders SET ".$_POST['name']." = '".$_POST['value']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE OrderID = '".$_POST['pk']."' LIMIT 1");

        if(MysqlAffectedRows() == 1)
        {
            $desc = $_POST['name'].' updated as '.$_POST['value'].' for Order ID: '.$_POST['pk'];

            RecordAdminActivity('Order Status Updated', 'customer_orders', $_POST['pk'], $desc);
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