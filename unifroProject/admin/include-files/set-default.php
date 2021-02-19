<?php

    $response['status'] = 'error';

	if(is_numeric($_POST['id']) && is_numeric($_POST['pk']) && $_POST['option'] != '')
	{
		if($_POST['option'] == 'element_style')
		{
			$activity = 'Marked Default';
            $table = 'element_styles';
            $Desc = 'Style ID: '.$_POST['pk'].' marked as Default Style for Heading ID: '.$_POST['id'];

			MysqlQuery("UPDATE element_styles SET DefaultStyle = '0' WHERE HeadingID = '".$_POST['id']."'");
            if(MysqlAffectedRows() >= 0)
            {
                MysqlQuery("UPDATE element_styles SET DefaultStyle = '1' WHERE StyleID = '".$_POST['pk']."' AND HeadingID = '".$_POST['id']."'");
                if(MysqlAffectedRows() == 1)
                {
                    // DO NOTHING
                }
                else
                {
                    $response['message'] = 'Failed to Set Default! [LN 20]';
                }
            }
            else
            {
                $response['message'] = 'Failed to Set Default! [LN 30]';
            }
		}
        elseif($_POST['option'] == 'product_image')
		{
			$activity = 'Marked Default';
            $table = 'product_images';
            $Desc = 'Image ID: '.$_POST['pk'].' marked as Default Picture for Product ID: '.$_POST['id'];

			MysqlQuery("UPDATE product_images SET DefaultImage = '0' WHERE ProductID = '".$_POST['id']."'");
            if(MysqlAffectedRows() >= 0)
            {
                MysqlQuery("UPDATE product_images SET DefaultImage = '1' WHERE ImageID = '".$_POST['pk']."' AND ProductID = '".$_POST['id']."'");
                if(MysqlAffectedRows() == 1)
                {
                    // DO NOTHING
                }
                else
                {
                    $response['message'] = 'Failed to Set Default! [LN 20]';
                }
            }
            else
            {
                $response['message'] = 'Failed to Set Default! [LN 30]';
            }
		}

		if(MysqlAffectedRows() >= 0)
		{
            RecordAdminActivity($activity, $table, $_POST['pk'], $Desc);
            $response['status'] = 'success';
            $response['message'] = 'Marked as Default!';
		}
		else
		{
		    $response['message'] = 'Something went wrong! Please try again.';
		}
	}
	else
	{
	    $response['message'] = 'Invalid Access!';
	}

    echo json_encode($response);
?>