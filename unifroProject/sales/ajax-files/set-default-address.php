<?php

    $response['status'] = 'error';

    if($_POST['cid'] > 0)
    {
        // LETS VALIDATE THE CLIENT ID AND GET THE ADDRESS DETAILS
        $AddressDetails = MysqlQuery("SELECT * FROM client_delivery_addresses WHERE AddressID = '".$_POST['pk']."' AND ClientID = '".$_POST['cid']."'");
        if(mysqli_num_rows($AddressDetails) == 1)
        {
            $activity = 'Marked Default Address';
            $table = 'client_delivery_addresses';
            $Desc = 'Address ID: '.$_POST['pk'].' marked as Default Address for Client ID: '.$_POST['cid'];

            MysqlQuery("UPDATE client_delivery_addresses SET DefaultAddress = '0' WHERE ClientID = '".$_POST['cid']."'");
            if(MysqlAffectedRows() >= 0)
            {
                $res = MysqlQuery("UPDATE client_delivery_addresses SET DefaultAddress = '1' WHERE AddressID = '".$_POST['pk']."' AND ClientID = '".$_POST['cid']."'");
                if(MysqlAffectedRows() == 1)
                {
                    // DO NOTHING
                    $response['status'] = 'success';
                }
                else
                {
                    $response['response_message'] = 'Failed to Set as Default! [LN 10]';
                }
            }
            else
            {
                $response['response_message'] = 'Failed to Set as Default! [LN 30]';
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

    echo json_encode($response);


?>