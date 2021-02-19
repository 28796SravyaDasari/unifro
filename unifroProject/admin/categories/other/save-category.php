<?php
	if(!isset($_COOKIE['asid']))
	{
		echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
		exit();
	}

    $response['status'] = 'error';

    if(isset($_POST['CategoryTitle']))
	{
	    if($_POST['CategoryTitle'] == '')
        {
            $error['CategoryTitle'] = 'Please enter Category Title';
        }
        if(!is_numeric($_POST['CategoryID']) && mysqli_num_rows(MysqlQuery("SELECT CategoryID FROM master_categories WHERE CategoryTitle = '".CleanText($_POST['CategoryTitle'])."' LIMIT 1")) == 1)
        {
            $error['CategoryTitle'] = 'Category already exists';
        }
        if(!is_numeric($_POST['ParentID']))
        {
            $error['ParentID'] = 'Please select Parent Category';
        }
        if($_POST['Size'] == '')
        {
            $error['Size'] = 'Please enter Sizes';
        }
        else
        {
            $_POST['Size']  = strtoupper($_POST['Size']);
            $Sizes          = json_encode(explode(',', $_POST['Size']));
        }

        if($_POST['SortOrder'] != '' && !is_numeric($_POST['SortOrder']))
        {
            $error['SortOrder'] = 'Only numeric values are allowed';
        }

        if($_FILES['WidgetImage']['name'] != '')
        {
            $WidgetImage = $_FILES['WidgetImage']['tmp_name'];
            $WidgetImageSize = $_FILES['WidgetImage']['size'];
            $WidgetImageName = $_FILES['WidgetImage']['name'];
            $WidgetImageExt = strtolower(substr(strrchr($WidgetImageName,'.'),1));

            if(array_search(strtolower($WidgetImageExt), $AllowedImageTypes) === false)
            {
                $error['WidgetImage'] = 'Invalid File type. Only '.implode(', ', $AllowedImageTypes).' are allowed';
            }
        }

        if(!isset($error))
        {
            $_POST['CategoryTitle'] = ucwords($_POST['CategoryTitle']);
            $_POST['SortOrder'] = is_numeric($_POST['SortOrder']) ? $_POST['SortOrder'] : '0';

            $CategoryURL = GenerateCategoryURL($_POST['ParentID']);
            $CategoryURL = MakeFriendlyURL($CategoryURL.'/'.$_POST['CategoryTitle'].'/');

            if(is_numeric($_POST['CategoryID']))
            {
                $activity = 'Category Updated. ('.$_POST['CategoryTitle'].')';
                $EditMode = true;
                $ID = $_POST['CategoryID'];

                MysqlQuery("UPDATE master_categories SET CategoryTitle = '".$_POST['CategoryTitle']."', CategoryURL = '".$CategoryURL."',
                                    ParentID = '".$_POST['ParentID']."', Size = '".$Sizes."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."', SortOrder = '".$_POST['SortOrder']."'
                                    WHERE CategoryID = '".$ID."' LIMIT 1");
            }
            else
            {
                $res = MysqlQuery("INSERT INTO master_categories (CategoryTitle, CategoryURL, ParentID, WidgetImage, Size, AddedOn, UpdatedOn, UpdatedBy, SortOrder)
                VALUES ('".$_POST['CategoryTitle']."', '".$CategoryURL."', '".$_POST['ParentID']."', '', '".$Sizes."', '".time()."', '0', '0', '".$_POST['SortOrder']."')");

                $ID = MysqlInsertID();
                $activity = 'Category Added. ('.$_POST['CategoryTitle'].')';
            }

    		if(MysqlAffectedRows() >= 0)
    		{
    		    if($_FILES['WidgetImage']['name'] != '')
                {
                    // SAVE CATEGORY PICTURE
                    $FileName = $ID.'_cat_'.time().'.'.$WidgetImageExt;
                    $ImageDir = _ROOT._CategoryPicDir;

                    list($currwidth, $currheight, $type, $attr) = getimagesize($WidgetImage);

                    if($currwidth <= _CategoryPicWidth)
                        $SetImageWidth = $currwidth;
                    else
                        $SetImageWidth = _CategoryPicWidth;

                    if(move_uploaded_file($WidgetImage, $ImageDir.$FileName))
					{
                        //	CREATE STANDARD SIZED IMAGE
    					if(CreateThumbnail($ImageDir.$FileName, $ImageDir, $SetImageWidth))
    					{
                            MysqlQuery("UPDATE categories SET WidgetImage = '".$FileName."' WHERE CategoryID = '".$ID."' LIMIT 1");

                            if(MysqlAffectedRows() < 0)
                            {
                                @unlink($ImageDir.$FileName);
                                $UploadError = '<br><span class="text-danger"> But error occurred while uploading Category Picture. [LN 10]</span>';
                            }
                        }
                        else
                        {
                            $UploadError = '<br><span class="text-danger"> But error occurred while uploading Category Picture. [LN 50]</span>';
                        }
                    }
                    else
                    {
                        $UploadError = '<br><span class="text-danger"> But error occurred while uploading Category Picture. [LN 60]</span>';
                    }
                }

                RecordAdminActivity($activity, 'master_categories', $ID);

                $response['status'] = 'success';
                $response['message'] = 'Category Details Saved!'.$UploadError;
                $response['redirect'] = GoToLastPage();
    		}
    		else
    		{
    		    $response['message'] = 'Something went wrong! Please try again. [LN 100]';
    		}
        }
        else
        {
            $response['error'] = $error;
            $response['status'] = 'validation';
        }
	}
	else
	{
	    $response['message'] = 'Invalid Access!';
	}

    echo json_encode($response);
?>