<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

    if($_SESSION['ClientID'] > 0 && $_POST['id'] > 0)
    {
        if($_FILES['Monogram']['name'] != '')
        {
            $Monogram = $_FILES['Monogram']['tmp_name'];
            $MonogramSize = $_FILES['Monogram']['size'];
            $MonogramName = $_FILES['Monogram']['name'];
            $MonogramExt = strtolower(substr(strrchr($MonogramName,'.'),1));

            if(array_search(strtolower($MonogramExt), $AllowedImageTypes) === false)
            {
                $response['response_message'] = 'Invalid File type. Only '.implode(', ', $AllowedImageTypes).' are allowed';
            }
            else
            {
                $FileName = $_SESSION['ClientID'].'_'.time().'.'.$MonogramExt;
                $ImageDir = _ROOT._MonogramDir;

                if(move_uploaded_file($Monogram, $ImageDir.$FileName))
                {
                    MysqlQuery("INSERT INTO client_monograms (ClientID, FileName, AddedOn) VALUES ('".$_SESSION['ClientID']."', '".$FileName."', '".time()."')");

                    if(MysqlAffectedRows() == 1)
                    {
                        $response['status'] = 'success';
                        RecordSalesActivity('monogram_upload', 'client_monograms', MysqlInsertID(), 'New Monogram added for client '.GetClientDetails($_SESSION['ClientID'], 'ClientName'));
                    }
                    else
                    {
                        @unlink($ImageDir.$FileName);
                        $response['response_message'] = 'Error occurred while uploading Monogram!';
                    }
                }
            }
        }
        else
        {
            $response['response_message'] = 'Please choose the Monogram';
        }
    }
    else
    {
        $response['response_message'] = 'Invalid Access! [LN 90]';
    }

    echo json_encode($response);


?>