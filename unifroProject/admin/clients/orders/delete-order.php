<?php

    if(!isset($_COOKIE['asid']))
    {
        echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
        exit();
    }

    $response['status'] = 'error';

    if($_POST['id'] > 0)
    {
        MysqlQuery("DELETE FROM client_orders WHERE OrderID = '".$_POST['id']."' LIMIT 1");

        if(MysqlAffectedRows() == 1)
        {
            MysqlQuery("DELETE FROM client_order_details WHERE OrderID = '".$_POST['id']."'");
            MysqlQuery("DELETE FROM client_order_status WHERE OrderID = '".$_POST['id']."' LIMIT 1");

            $activity = 'Order Deleted';
            $table = 'client_orders';
            $Desc = 'Order Deleted. Order ID: '.$_POST['id'];

            RecordAdminActivity($activity, $table, $_POST['id'], $Desc);
            $response['status']             = 'success';
            $response['response_message']   = 'Order Deleted Successfully!';
            $response['redirect']           = '/admin/clients/orders/';
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