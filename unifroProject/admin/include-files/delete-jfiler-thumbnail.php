<?php
	if(!isset($_COOKIE['asid']))
	{
		echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
		exit();
	}

    $response['status'] = 'error';

    if(is_numeric($_POST['id']))
	{
        if($_POST['option'] == 'category')
        {
            // LETS FETCH THE FILE NAME
            $FileName = MysqlQuery("SELECT WidgetImage FROM categories WHERE CategoryID = '".$_POST['id']."' LIMIT 1");
            if(mysqli_num_rows($FileName) == 1)
            {
                $FileName = mysqli_fetch_assoc($FileName)['WidgetImage'];
                $FileName = _ROOT._CategoryPicDir.$FileName;
                $activity = 'Category Picture Deleted';
                $TableName = 'categories';
                $DeleteElement = 'li';

                MysqlQuery("UPDATE categories SET WidgetImage = '' WHERE CategoryID = '".$_POST['id']."' LIMIT 1");
            }
            else
            {
                $response['message'] = 'Invalid Access!';
            }
        }
        elseif($_POST['option'] == 'product_image')
        {
            // DO NOT ALLOW TO DELETE THE DEFAULT PICTURE
            if(mysqli_num_rows(MysqlQuery("SELECT ImageID FROM product_images WHERE ImageID = '".$_POST['id']."' AND DefaultImage = '1' LIMIT 1")))
            {
                $response['message'] = 'You cannot delete the Default Image.<br>Please set another image as Default then delete this';
                echo json_encode($response);
                exit;
            }

            // LETS FETCH THE FILE NAME
            $FileName = MysqlQuery("SELECT FileName FROM product_images WHERE ImageID = '".$_POST['id']."' LIMIT 1");
            if(mysqli_num_rows($FileName) == 1)
            {
                $FileName = mysqli_fetch_assoc($FileName)['FileName'];
                $FileName = _ROOT._ProductImageDir.$FileName;
                $FileNameThumb = _ROOT._ProductImageThumbDir.$FileName;
                $activity = 'Product Image Deleted';
                $TableName = 'product_images';
                $DeleteElement = 'div.product';

                MysqlQuery("DELETE FROM product_images WHERE ImageID = '".$_POST['id']."' LIMIT 1");
            }
            else
            {
                $response['message'] = 'Invalid Access!';
            }
        }

        if(MysqlAffectedRows() == 1)
        {
            @unlink($FileName);
            if(isset($FileNameThumb))
            {
                @unlink($FileNameThumb);
            }
            RecordAdminActivity($activity, $TableName, $_POST['id']);
            $response['status'] = 'success';
            $response['element'] = $DeleteElement;
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