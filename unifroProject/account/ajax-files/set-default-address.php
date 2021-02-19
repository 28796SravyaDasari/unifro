<?php

    if(!$LoggedIn)
    {
        echo json_encode( array('status' => 'login') );
        exit;
    }
    
    $response['status'] = 'error';

    if($_POST['pk'] > 0)
    {
        // LETS VALIDATE THE CLIENT ID AND GET THE ADDRESS DETAILS
        $AddressDetails = MysqlQuery("SELECT * FROM customer_delivery_addresses WHERE AddressID = '".$_POST['pk']."' AND MemberID = '".$MemberID."'");
        if(mysqli_num_rows($AddressDetails) == 1)
        {
            $Desc = 'Address ID: '.$_POST['pk'].' marked as Default Address';

            MysqlQuery("UPDATE customer_delivery_addresses SET DefaultAddress = '0' WHERE MemberID = '".$MemberID."'");
            if(MysqlAffectedRows() >= 0)
            {
                $res = MysqlQuery("UPDATE customer_delivery_addresses SET DefaultAddress = '1' WHERE AddressID = '".$_POST['pk']."' AND MemberID = '".$MemberID."'");
                if(MysqlAffectedRows() == 1)
                {
                    RecordMemberActivity('Marked Default Address', 'customer_delivery_addresses', $_POST['pk'], $Desc);
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