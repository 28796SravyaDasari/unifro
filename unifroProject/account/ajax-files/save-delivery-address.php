<?php

    if(!$LoggedIn)
    {
        echo json_encode( array('status' => 'login') );
        exit;
    }

    $response['status'] = 'error';

    if($_POST['opt'] == 'delete')
    {
        if($_POST['cid'] > 0)
        {
            // LETS VALIDATE THE ID
            $AddressDetails = MysqlQuery("DELETE FROM customer_delivery_addresses WHERE AddressID = '".$_POST['pk']."' AND MemberID = '".$MemberID."'");
            if(MysqlAffectedRows() == 1)
            {
                $activity = 'Delivery Address Deleted';

                $response['status'] = 'success';
                RecordMemberActivity($activity, 'customer_delivery_addresses', $_POST['pk']);
            }
            else
            {
                $response['response_message'] = 'Something went wrong! Please refresh the page and try again. [LN 20]';
            }
        }
        else
        {
            $response['response_message'] = 'Invalid Access!';
        }
    }
    else
    {
        if($_POST['ContactName'] == '')
        {
            $error['ContactName'] = 'Enter Contact Person Name';
        }
        if(strlen($_POST['Mobile']) < 10)
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
            if(is_numeric($_POST['AddressID']))
            {
                $EditMode = true;
                $ID = $_POST['AddressID'];

                $res = MysqlQuery("UPDATE customer_delivery_addresses SET ContactName = '".$_POST['ContactName']."', Mobile = '".$_POST['Mobile']."', State = '".$_POST['State']."', City = '".$_POST['City']."', Address = '".$_POST['Address']."', Pincode = '".$_POST['Pincode']."' WHERE AddressID = '".$ID."' AND MemberID = '".$MemberID."' LIMIT 1");

                $Activity = 'Delivery Address Details Updated ('.$MemberDetails['FirstName'].' '.$MemberDetails['LastName'].' - Address ID: '.$ID.')';
            }
            else
            {
                $res = MysqlQuery("INSERT INTO customer_delivery_addresses (MemberID, ContactName, Mobile, Address, State, City, Pincode, DefaultAddress, AddedOn)
                VALUES ('".$MemberID."', '".$_POST['ContactName']."', '".$_POST['Mobile']."', '".$_POST['Address']."', '".$_POST['State']."', '".$_POST['City']."', '".$_POST['Pincode']."', '0', '".time()."')");

                $EditMode = false;
                $ID = MysqlInsertID();
                $Activity = 'New Delivery Address Added for ('.$MemberDetails['FirstName'].' '.$MemberDetails['LastName'].' - Address ID: '.$ID.')';
            }

            if(MysqlAffectedRows() >= 0)
            {
                RecordSalesActivity($Activity, 'customer_delivery_addresses', $ID);

            	$response['status'] = 'success';
                $response['redirect'] = $_POST['ReturnURL'] == 'Account' ? '/account/addresses/' : '/checkout/addresses/';
                $response['response_message'] = 'Address Saved!';
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