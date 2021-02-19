<?php

    if($LoggedIn)
    {
        $response['status'] = 'error';

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
            $error['EmailID'] = 'Enter a valid Email ID';
        }
        if(!is_numeric($_POST['SalesID']) && mysqli_num_rows(MysqlQuery("SELECT SalesID FROM sales where EmailID = '".$_POST['EmailID']."' LIMIT 1")) == 1)
        {
            $error['EmailID'] = 'Email ID already registered! Please use another Email ID';
        }
        if(is_numeric($_POST['SalesID']))
        {
            $getSalesEmail =  MysqlQuery("SELECT EmailID FROM sales WHERE SalesID = '".$_POST['SalesID']."' LIMIT 1");
            $getSalesEmail =  mysqli_fetch_assoc($getSalesEmail);
            $SalesEmail =  $getSalesEmail['EmailID'];
            if($SalesEmail != $_POST['EmailID'] && mysqli_num_rows(MysqlQuery("SELECT SalesID FROM sales where EmailID = '".$_POST['EmailID']."' LIMIT 1")) == 1)
            {
                $error['EmailID'] = 'Email ID already registered! Please use another Email ID';
            }
        }
        if($_POST['Mobile'] == '' || !is_numeric($_POST['Mobile']))
        {
            $error['Mobile'] = 'Enter a valid Mobile Number';
        }
        if(!is_numeric($_POST['State']))
        {
            $error['State'] = 'Select State';
        }
        if(!is_numeric($_POST['City']))
        {
            $error['City'] = 'Select City';
        }
        if(!is_numeric($_POST['Pincode']))
        {
            $error['Pincode'] = 'Enter a valid Pincode';
        }

        if(!isset($error))
        {
            if(is_numeric($_POST['SalesID']))
            {
                $res = MysqlQuery("UPDATE sales SET FirstName = '".$_POST['FirstName']."', LastName = '".$_POST['LastName']."', EmailID = '".$_POST['EmailID']."', Mobile = '".$_POST['Mobile']."', State = '".$_POST['State']."', City = '".$_POST['City']."', Address = '".$_POST['Address']."', Pincode = '".$_POST['Pincode']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE SalesID = '".$ID."' LIMIT 1");

                $EditMode = true;
                $ID = $_POST['SalesID'];
                $Activity = 'Sales Details Updated!';
            }
            else
            {
                $SystemPassword = AlphaNumericCode(6);
                $Password = GeneratePwd($SystemPassword);

                $res = MysqlQuery("INSERT INTO sales (FirstName, LastName, EmailID, Mobile, Password, State, City, Address, Pincode, RegistrationTimestamp, RegistrationIP)
            VALUES ('".$_POST['FirstName']."', '".$_POST['LastName']."', '".$_POST['EmailID']."', '".$_POST['Mobile']."', '".$Password."', '".$_POST['State']."', '".$_POST['City']."', '".$_POST['Address']."', '".$_POST['Pincode']."', '".time()."', '".GetIP()."')");

                $EditMode = false;
                $ID = MysqlInsertID();
                $Activity = 'Sales Account Created!';
            }

            if(MysqlAffectedRows() >= 0)
            {
                if(!$EditMode)
                {
                    $EmailBody = '<tr>
                                    <td>
                                        <table style="font-size: 14px;">
                                            <tr>
                                                <td>Dear '.$_POST['FirstName'].'</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 15px">Please find below the login credentials for Unifro Sales Account:</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 15px">
                                                Email: '.$_POST['EmailID'].'<br>
                                                Password: '.$SystemPassword.'
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 15px">This is a system generated password. Kindly change your password as soon as you login.</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 15px"><b><a href="'._HOST.'/sales/">Login Now</a></b></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>';
                    $EmailMessage = FormatEmail($EmailBody);

                    if(!SendMailHTML($_POST['EmailID'], 'Unifro Login Credentials', $EmailMessage))
                    {
                        $EmailError = ' But failed to email password';
                    }
                }

                RecordAdminActivity($Activity.' ('.$_POST['FirstName'].' '.$_POST['LastName'].' - Sales ID: '.$ID.')', 'sales', $ID);

            	$response['status'] = 'success';
                $response['redirect'] = '/admin/sales/';
                $response['response_message'] = $Activity.$EmailError;
            }
            else
            {
            	$response['status']             = 'error';
                $response['response_message']   = 'Error occurred! LN 90'.$res;
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