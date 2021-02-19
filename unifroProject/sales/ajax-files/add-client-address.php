<?php

    $response['status'] = 'error';

    if($_POST['opt'] == 'delete')
    {
        if($_POST['cid'] > 0)
        {
            // LETS VALIDATE THE ID
            $AddressDetails = MysqlQuery("DELETE FROM client_delivery_addresses WHERE AddressID = '".$_POST['pk']."' AND ClientID = '".$_POST['cid']."'");
            if(MysqlAffectedRows() == 1)
            {
                $activity = 'Delivery Address Deleted';

                $response['status'] = 'success';
                RecordSalesActivity($activity, 'client_delivery_addresses', $_POST['pk']);
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
        if($_POST['ClientID'] > 0)
        {
            // LETS VALIDATE THE CLIENT ID
            $ClientDetails = MysqlQuery("SELECT ClientName FROM clients WHERE ClientID = '".$_POST['ClientID']."' LIMIT 1");
            if(mysqli_num_rows($ClientDetails) == 1)
            {
                $ClientDetails = mysqli_fetch_assoc($ClientDetails);

                if($_POST['ContactName'] == '')
                {
                    $error['ContactName'] = 'Enter Contact Person Name';
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
                    if(is_numeric($_POST['AddressID']))
                    {
                        $EditMode = true;
                        $ID = $_POST['AddressID'];

                        $res = MysqlQuery("UPDATE client_delivery_addresses SET ContactName = '".$_POST['ContactName']."', Mobile = '".$_POST['Mobile']."', State = '".$_POST['State']."', City = '".$_POST['City']."', Address = '".$_POST['Address']."', Pincode = '".$_POST['Pincode']."' WHERE AddressID = '".$ID."' AND ClientID = '".$_POST['ClientID']."' LIMIT 1");

                        $Activity = 'Delivery Address Details Updated ('.$ClientDetails['ClientName'].' - Address ID: '.$ID.')';
                    }
                    else
                    {
                        $res = MysqlQuery("INSERT INTO client_delivery_addresses (ClientID, ContactName, Mobile, Address, State, City, Pincode, DefaultAddress, AddedOn)
                        VALUES ('".$_POST['ClientID']."', '".$_POST['ContactName']."', '".$_POST['Mobile']."', '".$_POST['Address']."', '".$_POST['State']."', '".$_POST['City']."', '".$_POST['Pincode']."', '0', '".time()."')");

                        $EditMode = false;
                        $ID = MysqlInsertID();
                        $Activity = 'New Delivery Address Added for ('.$ClientDetails['ClientName'].' - Address ID: '.$ID.')';
                    }

                    if(MysqlAffectedRows() >= 0)
                    {
                        RecordSalesActivity($Activity, 'client_delivery_addresses', $ID);

                    	$response['status'] = 'success';
                        $response['redirect'] = '/sales/clients/addresses/'.$_POST['ClientID'].'/';
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

    echo json_encode($response);


?>