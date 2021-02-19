<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

    if($_POST['id'] > 0)
    {
        $ValidateOrderID = MysqlQuery(" SELECT s.Status FROM customer_orders o
                                        LEFT JOIN customer_order_status s ON s.OrderID = o.OrderID
                                        WHERE o.OrderID = '".$_POST['id']."' AND o.MemberID = '".$MemberID."' LIMIT 1");
        if(mysqli_num_rows($ValidateOrderID) == 1)
        {
            $OrderStatus = mysqli_fetch_assoc($ValidateOrderID)['Status'];

            // If Order is Shipped or Completed then do not allow to cancel the order
            if( $OrderStatus != 'Awaiting Fulfillment' &&
                $OrderStatus != 'Awaiting Shipment' &&
                $OrderStatus != 'Pending' &&
                $OrderStatus != 'Awaiting Pickup')
            {
                $response['response_message'] = 'Sorry! the order has been processed. You cannot cancel this order.';
            }
            else
            {
                // Let's update the order status
                MysqlQuery("UPDATE customer_order_status SET Status = 'Cancelled By Customer', UpdatedOn = '".time()."' WHERE OrderID = '".$_POST['id']."' LIMIT 1");

                if(MysqlAffectedRows() >= 0)
                {
                    $response['status'] = 'success';
                    RecordMemberActivity('Order Cancelled', 'customer_orders', $_POST['id']);
                }
                else
                {
                    $response['response_message'] = 'Something went wrong! Please try again. [LN 40]';
                }
            }
        }
        else
        {
            $response['response_message'] = 'Invalid Access! [LN 20]';
        }
    }
    else
    {
        $response['response_message'] = 'Invalid Access! [LN 10]';
    }

    echo json_encode($response);


?>