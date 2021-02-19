<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

    if($_SESSION['ClientID'] > 0 && $_POST['CartID'] > 0)
    {
        if($_FILES['AddMeasurement']['name'] != '')
        {
            $Monogram = $_FILES['AddMeasurement']['tmp_name'];
            $MonogramName = $_FILES['AddMeasurement']['name'];
            $MonogramExt = strtolower(substr(strrchr($MonogramName,'.'),1));

            if(array_search(strtolower($MonogramExt), array('xls','xlsx')) === false)
            {
                $response['response_message'] = 'Invalid File type. Only XLS OR XLSX files are allowed';
            }
            else
            {
                $FileName = $_SESSION['ClientID'].'_'.$_POST['CartID'].'_'.time().'.'.$MonogramExt;
                $ImageDir = _ROOT._ClientMeasurementsDir;

                if(move_uploaded_file($Monogram, $ImageDir.$FileName))
                {
                    // Before updating the new file, fetch the old file if any
                    $OldFile = MysqlQuery("SELECT MeasurementFile FROM client_shopping_cart WHERE CartID = '".$_POST['CartID']."' AND ClientID = '".$_SESSION['ClientID']."' LIMIT 1");
                    $OldFile = mysqli_fetch_assoc($OldFile)['MeasurementFile'];

                    MysqlQuery("UPDATE client_shopping_cart SET MeasurementFile = '".$FileName."' WHERE CartID = '".$_POST['CartID']."' AND ClientID = '".$_SESSION['ClientID']."' LIMIT 1");

                    if(MysqlAffectedRows() >= 0)
                    {
                        if($OldFile != '')
                        {
                            unlink(_ROOT._ClientMeasurementsDir.$OldFile);
                        }

                        $response['status'] = 'success';
                        RecordSalesActivity('measurement_file_upload', 'client_shopping_cart', $_POST['CartID'], 'Measurement file updated for client '.GetClientDetails($_SESSION['ClientID'], 'ClientName'));
                    }
                    else
                    {
                        @unlink($ImageDir.$FileName);
                        $response['response_message'] = 'Error occurred while uploading the file! [LN 50]';
                    }
                }
                else
                {
                    $response['response_message'] = 'Error occurred while uploading the file! [LN 100]';
                }
            }
        }
        else
        {
            $response['response_message'] = 'Please choose the file';
        }
    }
    else
    {
        $response['response_message'] = 'Invalid Access! [LN 90]';
    }

    echo json_encode($response);


?>