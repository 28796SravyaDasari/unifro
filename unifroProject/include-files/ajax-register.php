<?php

    $response['status'] = 'error';

    if(VerifyFormToken('RegistrationForm'))
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
            $error['EmailID'] = 'Enter a valid Email Address';
        }
        else
        {
            // Check if Email is already registered with us
            if(mysqli_num_rows(MysqlQuery("SELECT MemberID FROM customers WHERE EmailID = '".$_POST['EmailID']."' LIMIT 1")) == 1)
            {
                echo json_encode(array( 'status'            => 'error',
                                        'response_message'  => 'Email Address is already registered with us. <br>
                                                                If you have forgotten your password, kindly reset your password on Login page.<br> <a href="/login/">Login Now</a>'
                                        ));
                exit;
            }
        }
        if(strlen($_POST['Password']) < 4)
		{
			$error['Password'] = 'Password must be at least 4 characters long';
		}
		if(!ValidatePassword($_POST['Password']))
		{
			$error['Password'] = 'Spaces are not allowed in password';
		}
        if(!ValidatePassword($_POST['ConfirmPassword']))
		{
			$error['ConfirmPassword'] = 'Spaces are not allowed in password';
		}
        if($_POST['Password'] != $_POST['ConfirmPassword'])
        {
            $error['ConfirmPassword'] = 'Confirm Password does not match';
        }

        if(!isset($error))
        {
            $Password = GeneratePwd(stripslashes($_POST['Password']));

            $res = MysqlQuery("INSERT INTO customers (  FirstName, LastName, EmailID, Password, RegisteredOn, RegistrationIP, RegistrationSource, LastLoginTimestamp, LastLoginIP,
                                                        CurrentLoginTimestamp, CurrentLoginIP, SID
                                                     )
            VALUES ('".$_POST['FirstName']."', '".$_POST['LastName']."', '".$_POST['EmailID']."', '".$Password."', '".time()."', '".GetIP()."', 'Web', '0', '', '0', '', '')");

            if(MysqlAffectedRows() == 1)
            {
                $ID = MysqlInsertID();

                $EmailBody = '<tr>
                                <td>
                                    <table style="font-size: 14px;">
                                        <tr>
                                            <td style="font-weight: 300; font-size: 21px; text-align:center">Welcome to Unifro.com</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-top: 15px">Thank you for creating an account with us.</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>';
                $EmailMessage = FormatEmail($EmailBody);

                SendMailHTML($_POST['EmailID'], 'Welcome to Unifro.com', $EmailMessage);

                RecordMemberActivity('Customer Account Created', 'customers', $ID, '', $ID);

            	$response['status'] = 'success';
                $response['redirect'] = '/login/';
                $response['response_message'] = 'Congratulations! Your account is ready to use.';
            }
            else
            {
            	$response['status']             = 'error';
                $response['response_message']   = 'Error occurred! LN 90'.$res;
            }
        }
        else
        {
            $response['error'] = $error;
            $response['status'] = 'validation';
        }
    }
    else
    {
        echo json_encode(array('status' => 'error', 'message' => 'Something went wrong! Try after some time. [LN 100]'));
    }

    echo json_encode($response);
?>