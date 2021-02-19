<?php

    if(!is_numeric($_POST['pin']) || strlen($_POST['pin']) < 6)
    {
        $response['response_message'] = 'Enter a valid pincode';
    }
    else
    {
        $q = MysqlQuery("SELECT ID FROM pincodes_cod WHERE Pincode = '".$_POST['pin']."' AND Status = 'y' LIMIT 1");

        if(mysqli_num_rows($q) == 1)
        {
            $response['status'] = 'success';
        }
        else
        {
            $response['status'] = 'error';
        }
    }

    echo json_encode($response);
?>