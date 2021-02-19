<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

    if($_SESSION['ClientID'] > 0)
    {
        if($_POST['Option'] == 'Monogram')
        {
            if(!isset($_POST['ClientMonogram']) && CleanText($_POST['MonogramText']) == '')
            {
                $error['Monogram'] = 'Please choose Monogram or Type the brand name';
            }
            if(!isset($_POST['MonogramType']))
            {
                $error['MonogramType'] = 'Please choose the appropriate option';
            }

            if(!isset($error))
            {
                if(isset($_POST['ClientMonogram']))
                {
                    $AdditionalDetails['ClientMonogram'] = $_POST['ClientMonogram'];
                }
                else
                {
                    $AdditionalDetails['MonogramText'] = $_POST['MonogramText'];
                }
                $AdditionalDetails['MonogramType'] = $_POST['MonogramType'];
                $AdditionalDetails['Comments'] = $_POST['Comments'];

                $_SESSION['CartDetails'][$_SESSION['ClientID']]['AdditionalDetails'] = json_encode($AdditionalDetails);

                $response['status'] = 'success';
                $response['redirect'] = '/sales/clients/addresses/'.$_SESSION['ClientID'].'/';
                $response['response_message'] = '<div class="text-success font15 bold">Details Saved Successfully!</div>';
            }
            else
            {
                $response['error'] = $error;
                $response['status'] = 'validation';
            }
        }
        elseif($_POST['Option'] == 'Full Pant')
        {
            if(!isset($_POST['FullPantAttributes']))
            {
                $error['FullPantAttributes'] = 'Please choose the appropriate option';
            }

            if(!isset($error))
            {
                $AdditionalDetails['FullPantAttributes'] = $_POST['FullPantAttributes'];
                $AdditionalDetails['Comments'] = $_POST['Comments'];

                MysqlQuery("UPDATE client_shopping_cart SET AdditionalDetails = '".json_encode($AdditionalDetails)."' WHERE CartID = '".$_POST['CartID']."' AND ClientID = '".$_SESSION['ClientID']."' LIMIT 1");

                if(MysqlAffectedRows() >= 0)
                {
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

                MysqlQuery("UPDATE client_shopping_cart SET AdditionalDetails = '".json_encode($AdditionalDetails)."' WHERE CartID = '".$_POST['CartID']."' AND ClientID = '".$_SESSION['ClientID']."' LIMIT 1");

                if(MysqlAffectedRows() >= 0)
                {
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