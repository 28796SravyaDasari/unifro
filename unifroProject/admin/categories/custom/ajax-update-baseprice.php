<?php
    $response['status'] = 'error';

    if($LoggedIn)
    {
        if($_POST['pk'] > 0 && $_POST['value'] > 0)
        {
            if($_POST['name'] == 'BasePrice')
            {
                $res = MysqlQuery("UPDATE master_categories SET BasePrice = '".$_POST['value']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."'
                                    WHERE CategoryID = '".$_POST['pk']."' LIMIT 1");

                $activity = 'Category Updated';
                $Desc = 'Base price for Category '.GetCategoryDetails($_POST['pk'], 'CategoryTitle').' updated as '.$_POST['value'];
            }
            elseif($_POST['name'] == 'Weight')
            {
                $res = MysqlQuery("UPDATE master_categories SET Weight = '".$_POST['value']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."'
                                    WHERE CategoryID = '".$_POST['pk']."' LIMIT 1");

                $activity = 'Category Updated';
                $Desc = 'Weight for Category '.GetCategoryDetails($_POST['pk'], 'CategoryTitle').' updated as '.$_POST['value'];
            }

            if(MysqlAffectedRows() == 1)
            {
                RecordAdminActivity($activity, 'master_categories', $_POST['pk'], $Desc);
                $response['status'] = 'success';
            }
            else
            {
                $response['message'] = 'Something went wrong! Please try again. [LN 100]'.$res;
            }
        }
        else
        {
            $response['message'] = 'Invalid Values!';
        }
    }
    else
    {
        $response['status'] = 'login';
    }


    echo json_encode($response);
?>