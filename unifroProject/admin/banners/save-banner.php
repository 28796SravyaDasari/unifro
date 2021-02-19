<?php
	if(!isset($_COOKIE['asid']))
	{
		echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
		exit();
	}

    $response['status'] = 'error';

    if(isset($_POST['BannerID']))
	{
	    if(!is_numeric($_POST['BannerID']) && $_POST['ImageName'] == '')
        {
            $error['AddImage'] = 'Please attach banner image.';
        }
        if($_POST['ImageName'] != '')
        {
            $file = $_POST['ImageName'];

            $data = explode(',', $file);
            list($type, $file) = explode(';', $file);
            list(,$extension) = explode('/',$type);

            if(!isset($AllowedImageTypes[strtolower($extension)]))
            {
                $error['AddImage'] = 'Invalid file type. Only '.implode(', ', $AllowedImageTypes).' are allowed.';
            }
        }
        if(!preg_match("/\b(?:(?:https?|ftp):\/\/|www|\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $_POST['TargetLink']) && $_POST['TargetLink'] != '')
        {
            $error['TargetLink'] = 'Enter a valid URL';
        }
        else
        {
            if($_POST['TargetLink'] != '')
            {
                $TargetLink = strpos($_POST['TargetLink'], 'http', 0) !== false ? $_POST['TargetLink'] : _PROTO.$_POST['TargetLink'];
            }
        }
        if(!is_numeric($_POST['SortOrder']) && $_POST['SortOrder'] != '')
        {
            $error['SortOrder'] = 'Only numeric values are allowed';
        }
        elseif($_POST['SortOrder'] != '' && $_POST['SortOrder'] < 0)
        {
            $error['SortOrder'] = 'Negative values are not allowed';
        }
        if(!is_numeric($_POST['Status']) || $_POST['Status'] < 0 || $_POST['Status'] > 1)
        {
            $error['Status'] = 'Invalid Status!';
        }


        if(!isset($error))
        {
            if(is_numeric($_POST['BannerID']))
            {
                $res = MysqlQuery("UPDATE banners SET TargetLink = '".$TargetLink."', SortOrder = '".$_POST['SortOrder']."', Status = '".$_POST['Status']."',
                                    UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE BannerID = '".$_POST['BannerID']."' LIMIT 1");

                $activity = 'Banner Updated';
                $EditMode = true;
                $ID = $_POST['BannerID'];
            }
            else
            {
                $res = MysqlQuery("INSERT INTO banners (Page, ImageName, TargetLink, SortOrder, Status, AddedOn, AddedBy)
                            VALUES ('Homepage', '', '".$TargetLink."', '".$_POST['SortOrder']."', '".$_POST['Status']."', '".time()."', '".$AID."')");

                $ID = MysqlInsertID();
                $activity = 'New Banner Added. ('.$ID.')';
            }

    		if(MysqlAffectedRows() >= 0)
    		{
    		    if($_POST['ImageName'] != '')
                {
                    if(is_numeric($_POST['BannerID']))
                    {
                        //FETCH THE CURRENT BANNER NAME BEFORE UPDATING THE NEW ONE
                        $CurrentPic = mysqli_fetch_assoc(MysqlQuery("SELECT ImageName FROM banners WHERE BannerID = '".$ID."' LIMIT 1"))['ImageName'];
                    }

                    $FileName = $ID.'_'.time().'.'.$extension;
                    $ifp = fopen(_ROOT._BannerDir.$FileName, "wb");
                    fwrite($ifp, base64_decode($data[1]));
                    fclose($ifp);

                    MysqlQuery("UPDATE banners SET ImageName = '".$FileName."' WHERE BannerID = '".$ID."' LIMIT 1");

                    if(MysqlAffectedRows() < 1)
        			{
        			    @unlink(_ROOT._BannerDir.$FileName);
                        $UploadError = ' But, failed upload the banner image.';
        			}
                    else
                    {
                        if($CurrentPic != '')
                        {
                            @unlink(_ROOT._BannerDir.$CurrentPic);
                        }
                    }
                }

    		    RecordAdminActivity($activity, 'admins', $ID);

                $response['status'] = 'success';
                $response['message'] = 'Banner Successfully Saved!'.$UploadError;
                $response['redirect'] = '/admin/banners/';
    		}
    		else
    		{
    		    $response['message'] = 'Something went wrong! Please try again. [LN 100]'.$res;
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