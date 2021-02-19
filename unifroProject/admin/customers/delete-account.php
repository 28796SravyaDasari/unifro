<?php

    if(!isset($_COOKIE['asid']))
    {
        echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
        exit();
    }

    $response['status'] = 'error';

    if($_POST['id'] > 0)
    {
        $Name = GetMemberDetails($_POST['id'], true);
        $Name = $Name['FirstName'].' '.$Name['LastName'];

        MysqlQuery("DELETE FROM customers WHERE MemberID = '".$_POST['id']."' LIMIT 1");

        if(MysqlAffectedRows() == 1)
        {
            // Get all the orders placed by the customers
            $CustomerOrders = MysqlQuery("SELECT OrderID FROM customer_orders WHERE MemberID = '".$_POST['id']."'");
            if(mysqli_num_rows($CustomerOrders) > 0)
            {
                for(;$row = mysqli_fetch_assoc;)
                {
                    $Orders[] = $row['OrderID'];
                }

                MysqlQuery("DELETE FROM customer_orders WHERE MemberID = '".$_POST['id']."'");
                MysqlQuery("DELETE FROM customer_order_details WHERE OrderID IN (".implode(',',$Orders).")");
                MysqlQuery("DELETE FROM customer_order_status WHERE OrderID IN (".implode(',',$Orders).")");
            }

            MysqlQuery("DELETE FROM product_ratings WHERE MemberID = '".$_POST['id']."'");
            MysqlQuery("DELETE FROM product_reviews WHERE ReviewBy = '".$_POST['id']."'");

            $activity = 'Account Deleted';
            $table = 'customers';
            $Desc = 'Customer Account Deleted. Customer ID: '.$_POST['id'].' Customer Name: '.$Name;

            RecordAdminActivity($activity, $table, $_POST['id'], $Desc);
            $response['status']             = 'success';
            $response['response_message']   = 'Account Deleted Successfully!';
            $response['redirect']           = '/admin/customers/';
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