<?php

    if(!isset($_COOKIE['asid']))
    {
        echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
        exit();
    }

    $response['status'] = 'error';

    if($_POST['id'] > 0)
    {
        MysqlQuery("DELETE FROM product_reviews WHERE ID = '".$_POST['id']."' LIMIT 1");

        if(MysqlAffectedRows() == 1)
        {
            $activity = 'Review Deleted';
            $table = 'product_reviews';
            $Desc = 'Product Review Deleted. Review ID: '.$_POST['id'];

            RecordAdminActivity($activity, $table, $_POST['id'], $Desc);
            $response['status']             = 'success';
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