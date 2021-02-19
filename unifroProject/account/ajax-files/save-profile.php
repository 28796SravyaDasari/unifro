<?php

    if(!$LoggedIn)
    {
        echo json_encode( array('status' => 'login') );
        exit;
    }

    $response['status'] = 'error';

    if(isset($_POST['FirstName']))
    {
        if($_POST['FirstName'] == '')
        {
            $error['FirstName'] = 'Enter First Name';
        }
        if($_POST['LastName'] == '')
        {
            $error['LastName'] = 'Enter Last Name';
        }
        if(!preg_match( "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $_POST['EmailID']))
        {
            $error['EmailID'] = 'Invalid Email ID!'.$_POST['EmailID'];
        }
        else
        {
            // Check if user has changed the email id, if yes then validate for duplication
            if(mysqli_num_rows(MysqlQuery("SELECT MemberID FROM customers WHERE MemberID <> '".$MemberID."' AND EmailID = '".$_POST['EmailID']."' LIMIT 1")) == 1)
            {
                $error['EmailID'] = 'Email Address is already registered with us';
            }
        }
        if($_POST['Mobile'] != '' && strlen($_POST['Mobile']) < 10)
        {
            $error['Mobile'] = 'Enter a valid Mobile Number';
        }
        if(!is_numeric($_POST['Mobile']))
        {
            $error['Mobile'] = 'Enter a valid Mobile Number';
        }

        if(!isset($error))
        {
            $_POST['State'] = is_numeric($_POST['State']) ? $_POST['State'] : 0;
            $_POST['City']  = is_numeric($_POST['City']) ? $_POST['City'] : 0;

            $res = MysqlQuery("UPDATE customers SET FirstName = '".$_POST['FirstName']."', LastName = '".$_POST['LastName']."', EmailID = '".$_POST['EmailID']."',
                                Mobile = '".$_POST['Mobile']."', State = '".$_POST['State']."', City = '".$_POST['City']."', Newsletter = '".$_POST['Newsletter']."', UpdatedOn = '".time()."' WHERE MemberID = '".$MemberID."' LIMIT 1");

            if(MysqlAffectedRows() >= 0)
            {
                RecordMemberActivity('Profile Updated', 'customers', $MemberID);

            	$response['status'] = 'success';
                $response['response_message'] = 'Profile Details Saved!';
            }
            else
            {
            	$response['status']             = 'error';
                $response['response_message']   = 'Error occurred! LN 90 '.$res;
            }
        }
        else
        {
            $response['status'] = 'validation';
            $response['error']  = $error;
        }
    }

    echo json_encode($response);


?>