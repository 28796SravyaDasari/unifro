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

            if($OrderStatus != 'Cancelled By Customer' && $OrderStatus != 'Cancelled' && $OrderStatus != 'Declined')
            {
                $_SESSION['CartDetails']['OrderID'] = $_POST['id'];

                $response['status']             = 'success';
                $response['redirect']           = '/ccavenue/pg-process.php';

                RecordMemberActivity('Order Retry', 'customer_orders', $_POST['id']);
            }
            else
            {
                $response['response_message'] = 'Sorry! the order has been '.$OrderStatus.'. You cannot retry payment.';
            }
        }
        else
        {
            $response['response_message'] = 'Invalid Access! [LN 10]';
        }
    }
    else
    {
        $response['response_message'] = 'Invalid Access! [LN 10]';
    }

    echo json_encode($response);


?>