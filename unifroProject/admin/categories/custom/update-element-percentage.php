<?php

    if(!isset($_COOKIE['asid']))
    {
        echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
        exit();
    }

    $response['status'] = 'error';

    if($_POST['pk'] > 0 && is_numeric($_POST['value']))
    {
        $res = MysqlQuery("UPDATE element_styles SET Percentage = '".$_POST['value']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE StyleID = '".$_POST['pk']."' LIMIT 1");

        if(MysqlAffectedRows() == 1)
        {
            $activity = 'Element Style Percentage Updated';
            $Desc = 'Percentage for Element Style '.GetStyleNameByID($_POST['id']).' updated as '.$_POST['value'];

            RecordAdminActivity($activity, 'element_styles', $_POST['pk'], $Desc);

            $response['status'] = 'success';
        }
        else
        {
            $response['response_message'] = 'Something went wrong! Please try again. [LN 100]';
        }
    }
    else
    {
        $response['response_message'] = 'Invalid Access!';
    }

    echo json_encode($response);
?>