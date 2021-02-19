<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

    if($_POST['OrderDetailsID'] > 0)
    {
        if($_POST['Option'] == 'Full Pant')
        {
            if(!isset($_POST['FullPantAttributes']))
            {
                $error['FullPantAttributes'] = 'Please choose the appropriate option';
            }

            if(!isset($error))
            {
                $AdditionalDetails['FullPantAttributes'] = $_POST['FullPantAttributes'];
                $AdditionalDetails['Comments'] = $_POST['Comments'];

                MysqlQuery("UPDATE client_order_details SET AdditionalDetails = '".json_encode($AdditionalDetails)."' WHERE OrderDetailsID = '".$_POST['OrderDetailsID']."' LIMIT 1");

                if(MysqlAffectedRows() >= 0)
                {
                    $activity = 'Order Additional Details Updated';
                    $table = 'client_order_details';
                    $Desc = $_POST['Option'].' additional details updated for Order ID: '.GetOrderProducts($_POST['OrderDetailsID'], 'OrderID').' and Product ID: '.GetOrderProducts($_POST['OrderDetailsID'], 'ProductID');

                    RecordAdminActivity($activity, $table, $_POST['OrderDetailsID'], $Desc);

                    $response['status'] = 'success';
                    $response['response_message'] = '<div class="text-success font15 bold mg-t-15">Details Saved Successfully!</div>';
                }
                else
                {
                    $response['response_message'] = '<div class="text-danger font15 bold mg-t-15">Error Occurred! Please try again.</div>';
                }
            }
            else
            {
                $response['error'] = $error;
                $response['status'] = 'validation';
            }
        }
        elseif($_POST['Option'] == 'Pinafores' || $_POST['Option'] == 'Skirt')
        {
            if(!isset($_POST['Attributes']))
            {
                $error['Attributes'] = 'Please choose the appropriate option';
            }

            if(!isset($error))
            {
                $AdditionalDetails['Attributes'] = $_POST['Attributes'];
                $AdditionalDetails['Comments'] = $_POST['Comments'];

                MysqlQuery("UPDATE client_order_details SET AdditionalDetails = '".json_encode($AdditionalDetails)."' WHERE OrderDetailsID = '".$_POST['OrderDetailsID']."' LIMIT 1");

                if(MysqlAffectedRows() >= 0)
                {
                    $activity = 'Order Additional Details Updated';
                    $table = 'client_order_details';
                    $Desc = $_POST['Option'].' additional details updated for Order ID: '.GetOrderProducts($_POST['OrderDetailsID'], 'OrderID').' and Product ID: '.GetOrderProducts($_POST['OrderDetailsID'], 'ProductID');

                    RecordAdminActivity($activity, $table, $_POST['OrderDetailsID'], $Desc);

                    $response['status'] = 'success';
                    $response['response_message'] = '<div class="text-success font15 bold mg-t-15">Details Saved Successfully!</div>';
                }
                else
                {
                    $response['response_message'] = '<div class="text-danger font15 bold mg-t-15">Error Occurred! Please try again.</div>';
                }
            }
            else
            {
                $response['error'] = $error;
                $response['status'] = 'validation';
            }
        }
    }
    else
    {
        $response['response_message'] = 'Invalid Access! [LN 90]';
    }

    echo json_encode($response);


?>