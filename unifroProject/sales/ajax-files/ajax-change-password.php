<?php

    if($LoggedIn)
    {
        $response['status'] = 'error';

        $_POST['CurrentPassword'] = stripslashes($_POST['CurrentPassword']);

        if(!VerifyPwd($_POST['CurrentPassword'], $MemberDetails['Password']))
        {
            $error['CurrentPassword'] = 'Incorrect Current Password'.$_POST['CurrentPassword'];
        }
        if(strlen($_POST['NewPassword']) < 4)
        {
            $error['NewPassword'] = 'New Password must be at least 4 characters long';
        }
        if(!ValidatePassword($_POST['NewPassword']) || !ValidatePassword($_POST['ReNewPassword']))
        {
            $error['NewPassword'] = 'Spaces are not allowed in password';
        }
        if(strtolower($_POST['CurrentPassword']) == strtolower($_POST['NewPassword']))
        {
            $error['NewPassword'] = 'New password cannot be same as current password';
        }
        if($_POST['NewPassword'] !== $_POST['ReNewPassword'])
        {
            $error['ReNewPassword'] = 'Re-entered password does not match with new password!';
        }

        if(!isset($error))
        {
            $NewPass = GeneratePwd(stripslashes($_POST['NewPassword']));

            MysqlQuery("UPDATE sales SET Password = '".$NewPass."' WHERE SalesID = '".$MemberID."'");

            if(MysqlAffectedRows() == 1)
            {
                RecordSalesActivity('Password Changed', 'sales', $MemberID);

                $response['status'] = 'success';
                $response['redirect'] = '/sales/';
                $response['response_message'] = 'Password Changed Successfully!';
            }
            else
            {
                $response['response_message'] = 'Something went wrong! Try after some time. [LN 50]';
            }
        }
        else
        {
            $response['status'] = 'validation';
            $response['error']  = $error;
        }
    }
    else
    {
        $response['status']             = 'login';
        $response['redirect']           = '/login/';
    }

    echo json_encode($response);


?>