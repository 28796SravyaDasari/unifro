<?php
	if(!isset($_COOKIE['asid']))
	{
		echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
		exit();
	}

    $response['status'] = 'error';

    if(isset($_POST['FabricName']))
	{
	    if(!is_numeric($_POST['CategoryID']))
        {
            $error['CategoryID'] = 'Choose Category';
        }
	    if($_POST['CompanyName'] == '')
        {
            $error['CompanyName'] = 'Please enter Company Name';
        }
        if($_POST['Industry'] == '')
        {
            $error['Industry'] = 'Please choose Industry';
        }
        if($_POST['FabricName'] == '')
        {
            $error['FabricName'] = 'Please enter Fabric Name';
        }
        if(!is_numeric($_POST['FabricID']) && mysqli_num_rows(MysqlQuery("SELECT FabricID FROM fabrics WHERE CategoryID = '".$_POST['CategoryID']."' AND FabricName = '".CleanText($_POST['FabricName'])."' LIMIT 1")) == 1)
        {
            $error['FabricName'] = 'Fabric with this name already exists';
        }
        if($_POST['FabricCode'] == '')
        {
            $error['FabricCode'] = 'Please enter Fabric Code';
        }
        if($_POST['FabricColor'] == '')
        {
            $error['FabricColor'] = 'Please enter Fabric Color';
        }
        if($_POST['FabricPattern'] == '')
        {
            $error['FabricPattern'] = 'Please enter Fabric Pattern';
        }
        if(!is_numeric($_POST['FabricPrice']))
        {
            $error['FabricPrice'] = 'Only numeric values are allowed';
        }
        if(!isset($_POST['FabricWash']))
        {
            //$error['FabricWash'] = 'Please choose the Fabric Wash';
        }
        else
        {
            $_POST['FabricWash'] = JSONEncode($_POST['FabricWash']);
        }

        if(!is_numeric($_POST['FabricID']) && $_FILES['FabricImage']['name'] == '')
        {
            $error['FabricImage'] = 'Please upload the Fabric Swatch';
        }

        if(isset($_POST['DefaultFabric']) && $_POST['DefaultFabric'] != 1)
        {
            $error['DefaultFabric'] = 'Invalid value for Default Fabric';
        }

        if($_FILES['FabricImage']['name'] != '')
        {
            $FabricImage = $_FILES['FabricImage']['tmp_name'];
            $FabricImageSize = $_FILES['FabricImage']['size'];
            $FabricImageName = $_FILES['FabricImage']['name'];
            $FabricImageExt = strtolower(substr(strrchr($FabricImageName,'.'),1));

            if(array_search(strtolower($FabricImageExt), $AllowedImageTypes) === false)
            {
                $error['FabricImage'] = 'Invalid File type. Only '.implode(', ', $AllowedImageTypes).' are allowed';
            }
        }

        if(!isset($error))
        {
            $_POST['FabricName'] = ucwords($_POST['FabricName']);
            $_POST['FabricCode'] = strtolower( str_replace(' ', '_', $_POST['FabricCode']) );

            if(is_numeric($_POST['FabricID']))
            {
                $activity = 'Fabric Details Updated';
                $EditMode = true;
                $ID = $_POST['FabricID'];

                $res = MysqlQuery("UPDATE fabrics SET CategoryID = '".$_POST['CategoryID']."', CompanyName = '".$_POST['CompanyName']."', FabricName = '".$_POST['FabricName']."',
                                    FabricCode = '".$_POST['FabricCode']."', FabricColor = '".$_POST['FabricColor']."', FabricPattern = '".$_POST['FabricPattern']."',
                                    FabricPrice = '".$_POST['FabricPrice']."', FabricBlend = '".$_POST['FabricBlend']."', FabricComposition = '".$_POST['FabricComposition']."',
                                    FabricCount = '".$_POST['FabricCount']."', FabricGSM = '".$_POST['FabricGSM']."', FabricWeave = '".$_POST['FabricWeave']."',
                                    KnitType = '".$_POST['KnitType']."', FabricWash = '".$_POST['FabricWash']."', Industry = '".$_POST['Industry']."', Status = '".$_POST['Status']."',
                                    UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE FabricID = '".$ID."' LIMIT 1");
            }
            else
            {
                MysqlQuery("INSERT INTO fabrics (   CategoryID, CompanyName, FabricName, FabricCode, FabricColor, FabricPattern, FabricPrice, FabricBlend, FabricComposition,
                                                    FabricCount, FabricGSM, FabricWeave, FabricWash, FabricImage, Industry, KnitType, DefaultFabric, Status,
                                                    AddedOn, AddedBy, UpdatedOn, UpdatedBy )
                VALUES (    '".$_POST['CategoryID']."', '".$_POST['CompanyName']."', '".$_POST['FabricName']."', '".$_POST['FabricCode']."', '".$_POST['FabricColor']."',
                            '".$_POST['FabricPattern']."', '".$_POST['FabricPrice']."', '".$_POST['FabricBlend']."', '".$_POST['FabricComposition']."', '".$_POST['FabricCount']."',
                            '".$_POST['FabricGSM']."', '".$_POST['FabricWeave']."', '".$_POST['FabricWash']."', '', '".$_POST['Industry']."', '".$_POST['KnitType']."', '0',
                            '".$_POST['Status']."', '".time()."', '".$AID."', '0', '0')");

                $ID = MysqlInsertID();
                $activity = 'Fabric Added. ('.$_POST['FabricName'].')';
            }

    		if(MysqlAffectedRows() >= 0)
    		{
    		    if($_POST['DefaultFabric'] == 1)
                {
                    /*
                        If Fabric is mark as default, then first set all Fabrics from this category to 0 and Update the Default Fabric
                    */
                    MysqlQuery("UPDATE fabrics SET DefaultFabric = '0' WHERE CategoryID = '".$_POST['CategoryID']."'");
                    if(MysqlAffectedRows() >= 0)
                    {
                        MysqlQuery("UPDATE fabrics SET DefaultFabric = '1' WHERE FabricID = '".$ID."' AND CategoryID = '".$_POST['CategoryID']."'");
                        if(MysqlAffectedRows() == 1)
                        {
                            // DO NOTHING
                        }
                        else
                        {
                            $FabricUpdate = '<br><span class="text-danger">Failed to Set the Default Fabric! [LN 120]</span>';
                        }
                    }
                    else
                    {
                        $FabricUpdate = '<br><span class="text-danger">Failed to Set the Default Fabric! [LN 130]</span>';
                    }
                }

    		    if($_FILES['FabricImage']['name'] != '')
                {
                    // SAVE FABRIC SWATCH
                    $FileName = $_POST['FabricCode'].'_'.$ID.'_'.time().'.'.$FabricImageExt;
                    $ImageDir = _ROOT._FabricImageDir;
                    $ImageThumbDir = _ROOT._FabricImageThumbDir;

                    list($currwidth, $currheight, $type, $attr) = getimagesize($FabricImage);

                    if($currwidth <= _FabricImageWidth)
                        $SetImageWidth = $currwidth;
                    else
                        $SetImageWidth = _FabricImageWidth;

                    if($currwidth <= _FabricImageThumbWidth)
                        $SetImageThumbWidth = $currwidth;
                    else
                        $SetImageThumbWidth = _FabricImageThumbWidth;

                    if(move_uploaded_file($FabricImage, $ImageDir.$FileName))
					{
                        //	GENERATE STANDARD SIZED IMAGE
    					if(CreateThumbnail($ImageDir.$FileName, $ImageDir, $SetImageWidth))
    					{
    					    //	GENERATE THUMB IMAGE
        					if(CreateThumbnail($ImageDir.$FileName, $ImageThumbDir, $SetImageThumbWidth))
        					{
        					    if($EditMode)
                                {
                                    // GET THE OLD FILE NAME
                                    $OldFile = mysqli_fetch_assoc(MysqlQuery("SELECT FabricImage FROM fabrics WHERE FabricID = '".$ID."' LIMIT 1"))['FabricImage'];
                                }
                                MysqlQuery("UPDATE fabrics SET FabricImage = '".$FileName."' WHERE FabricID = '".$ID."' LIMIT 1");

                                if(MysqlAffectedRows() < 0)
                                {
                                    @unlink($ImageDir.$FileName);
                                    @unlink($ImageThumbDir.$FileName);
                                    $error['FabricImage'] = 'Error occurred while uploading Fabric Swatch. [LN 10]';
                                }
                                else
                                {
                                    if($EditMode)
                                    {
                                        @unlink($ImageDir.$OldFile);
                                        @unlink($ImageThumbDir.$OldFile);
                                    }
                                }
                            }
                            else
                            {
                                $error['FabricImage'] = 'Error occurred while uploading Fabric Swatch. [LN 40]';
                            }
                        }
                        else
                        {
                            $error['FabricImage'] = 'Error occurred while uploading Fabric Swatch. [LN 50]';
                        }
                    }
                    else
                    {
                        $error['FabricImage'] = 'Error occurred while uploading Fabric Swatch. [LN 60]';
                    }
                }

                if(!$EditMode && isset($error))
                {
                    MysqlQuery("DELETE FROM fabrics WHERE FabricID = '".$ID."' LIMIT 1");
                    $response['error'] = $error;
                    $response['status'] = 'validation';
                }
                else
                {
                    RecordAdminActivity($activity, 'fabrics', $ID);

                    $response['status'] = 'success';
                    $response['message'] = 'Fabric Details Saved!'.$FabricUpdate;
                    $response['redirect'] = GoToLastPage();
                }
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