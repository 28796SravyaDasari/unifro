<?php

    if($LoggedIn)
    {
        $response['status'] = 'error';

        if($_POST['ClientName'] == '')
        {
            $error['ClientName'] = 'Enter Client Name';
        }
        if($_POST['ContactFirstName'] == '')
        {
            $error['ContactFirstName'] = 'Enter First Name';
        }
        if($_POST['ContactLastName'] == '')
        {
            $error['ContactLastName'] = 'Enter Last Name';
        }
        if(!preg_match( "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $_POST['EmailID']))
        {
            $error['EmailID'] = 'Enter a valid Email ID';
        }
        if(!is_numeric($_POST['ClientID']) && mysqli_num_rows(MysqlQuery("SELECT ClientID FROM clients where EmailID = '".$_POST['EmailID']."' LIMIT 1")) == 1)
        {
            $error['EmailID'] = 'Email ID already registered! Please use another Email ID';
        }
        if(is_numeric($_POST['ClientID']))
        {
            $getClientEmail =  MysqlQuery("SELECT EmailID FROM clients WHERE ClientID = '".$_POST['ClientID']."' LIMIT 1");
            $getClientEmail =  mysqli_fetch_assoc($getClientEmail);
            $ClientEmail =  $getClientEmail['EmailID'];
            if($ClientEmail != $_POST['EmailID'] && mysqli_num_rows(MysqlQuery("SELECT ClientID FROM clients where EmailID = '".$_POST['EmailID']."' LIMIT 1")) == 1)
            {
                $error['EmailID'] = 'Email ID already registered! Please use another Email ID';
            }
        }
        if($_POST['Mobile'] == '')
        {
            $error['Mobile'] = 'Enter Mobile Number';
        }
        if(!is_numeric($_POST['State']))
        {
            $error['State'] = 'Select State';
        }
        if(!is_numeric($_POST['City']))
        {
            $error['City'] = 'Select City';
        }
        if($_POST['Address'] == '')
        {
            $error['Address'] = 'Enter Address';
        }
        if(!is_numeric($_POST['Pincode']))
        {
            $error['Pincode'] = 'Enter a valid Pincode';
        }

        if(!isset($error))
        {
            if(is_numeric($_POST['ClientID']))
            {
                $EditMode = true;
                $ID = $_POST['ClientID'];

                $res = MysqlQuery("UPDATE clients SET ClientName = '".$_POST['ClientName']."', ContactFirstName = '".$_POST['ContactFirstName']."', ContactLastName = '".$_POST['ContactLastName']."', EmailID = '".$_POST['EmailID']."', Mobile = '".$_POST['Mobile']."', Landline = '".$_POST['Landline']."', State = '".$_POST['State']."', City = '".$_POST['City']."', Address = '".$_POST['Address']."', Pincode = '".$_POST['Pincode']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE ClientID = '".$ID."' LIMIT 1");

                $Activity = 'Client Details Updated ('.$_POST['ClientName'].' - Client ID: '.$ID.')';
            }
            else
            {
                $res = MysqlQuery("INSERT INTO clients (SalesID, ClientName, ContactFirstName, ContactLastName, Password, ResetPasswordLink, EmailID, Mobile, Landline, State, City, Address, Pincode, Website, Remark, Newsletter, NewsletterSent, RegisteredOn, RegistrationIP, RegistrationSource, LastLoginTimestamp, LastLoginIP, CurrentLoginTimestamp, CurrentLoginIP, SID, Status)
            VALUES ('".$MemberID."', '".$_POST['ClientName']."', '".$_POST['ContactFirstName']."', '".$_POST['ContactLastName']."', '', '', '".$_POST['EmailID']."', '".$_POST['Mobile']."', '', '".$_POST['State']."', '".$_POST['City']."', '".$_POST['Address']."', '".$_POST['Pincode']."', '', '', 'y', '0', '".time()."', '".GetIP()."', 'Web', '0', '', '0', '', '', 'y')");

                $EditMode = false;
                $ID = MysqlInsertID();
                $Activity = 'Client Account Created! ('.$_POST['ClientName'].' - Client ID: '.$ID.')';
            }

            if(MysqlAffectedRows() >= 0)
            {
                if(!$EditMode)
                {
                    MysqlQuery("INSERT INTO sales_clients (SalesID, ClientID) VALUES ('".$MemberID."', '".$ID."')");
                }

                RecordSalesActivity($Activity, 'clients', $ID);

            	$response['status'] = 'success';
                $response['redirect'] = '/sales/';
                $response['response_message'] = $Activity;
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