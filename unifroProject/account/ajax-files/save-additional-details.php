<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

    if($_POST['Option'] == 'CustomProduct' || $_POST['Option'] == 'All')
    {
        if($_POST['Option'] == 'All')
        {
            if(!isset($_POST['CustomerMonogram']) && CleanText($_POST['MonogramText']) == '')
            {
                $error['Monogram'] = 'Please choose Monogram or Type the brand name';
            }
            if(!isset($_POST['MonogramType']))
            {
                $error['MonogramType'] = 'Please choose the appropriate option';
            }
        }

        if(!isset($_POST['BookATailor']))
        {
            $error['BookATailor'] = 'Please choose the appropriate option';
        }
        if($_POST['BookATailor'] == 'y' && !strtotime($_POST['AppointmentDate']))
        {
            $error['AppointmentDate'] = 'Please choose the Tailor visit date';
        }
        if(!isset($_POST['UnifroLabel']))
        {
            $error['UnifroLabel'] = 'Please choose the appropriate option';
        }

        if(!isset($error))
        {
            /*
            if($_POST['Option'] == 'All')
            {
                if(isset($_POST['CustomerMonogram']))
                {
                    $AdditionalDetails['CustomerMonogram'] = $_POST['CustomerMonogram'];
                }
                else
                {
                    $AdditionalDetails['MonogramText'] = $_POST['MonogramText'];
                }
                $AdditionalDetails['MonogramType'] = $_POST['MonogramType'];
            }

            $AdditionalDetails['Comments'] = $_POST['Comments'];
            */
            $AdditionalDetails = $_POST;
            unset($AdditionalDetails['Option']);
            unset($AdditionalDetails['opt']);

            $_SESSION['CartDetails']['AdditionalDetails'] = json_encode($AdditionalDetails);

            $response['status'] = 'success';
            $response['redirect'] = '/checkout/addresses/';
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

            MysqlQuery("UPDATE customer_shopping_cart SET AdditionalDetails = '".json_encode($AdditionalDetails)."'
                        WHERE CartID = '".$_POST['CartID']."' AND CustomerID = '".$MemberID."' LIMIT 1");

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

            MysqlQuery("UPDATE customer_shopping_cart SET AdditionalDetails = '".json_encode($AdditionalDetails)."'
                        WHERE CartID = '".$_POST['CartID']."' AND CustomerID = '".$MemberID."' LIMIT 1");

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

    echo json_encode($response);

?>