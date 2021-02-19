<?php

    if(!isset($_COOKIE['asid']))
    {
        echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
        exit();
    }

    $response['status'] = 'error';

    if($_POST['id'] > 0)
    {
        // Get the order details
        $OrderDetails = MysqlQuery("SELECT c.FirstName, c.LastName, o.FinalTotal FROM customer_orders o LEFT JOIN customers c ON c.MemberID = o.MemberID
                                    WHERE o.OrderID = '".$_POST['id']."' LIMIT 1");
        $OrderDetails = mysqli_fetch_assoc($OrderDetails);

        $Desc = 'Order of Customer ('.$OrderDetails['FirstName'].' '.$OrderDetails['LastName'].') amount Rs.'.$OrderDetails['FinalTotal'].' has been deleted. Order ID: '.$_POST['id'];

        MysqlQuery("DELETE FROM customer_orders WHERE OrderID = '".$_POST['id']."' LIMIT 1");

        if(MysqlAffectedRows() == 1)
        {
            MysqlQuery("DELETE FROM customer_order_details WHERE OrderID = '".$_POST['id']."'");
            MysqlQuery("DELETE FROM customer_order_status WHERE OrderID = '".$_POST['id']."' LIMIT 1");

            $activity = 'Order Deleted';
            $table = 'customer_orders';

            RecordAdminActivity($activity, $table, $_POST['id'], $Desc);
            $response['status']             = 'success';
            $response['response_message']   = 'Order Deleted Successfully!';
            $response['redirect']           = '/admin/customers/orders/';
        }
        else
        {
            $response['response_message']    = 'Error occurred! [LN 10]';
        }
    }
    else
    {
        $response['response_message'] = 'Invalid Access!';
    }

    echo json_encode($response);

?>