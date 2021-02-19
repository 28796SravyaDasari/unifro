<?php
	if(!isset($_COOKIE['asid']))
	{
		echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
		exit();
	}

    $response['status'] = 'error';

    if(isset($_POST['StyleName']) && $_POST['ElementID'] > 0 && $_POST['CategoryID'] > 0)
	{
	    $_POST['StyleName'] = CleanText($_POST['StyleName']);

	    if($_POST['StyleName'] == '')
        {
            $error['StyleName'] = 'Please enter the Name';
        }
        if(!is_numeric($_POST['StyleID']) && mysqli_num_rows(MysqlQuery("SELECT StyleID FROM element_styles WHERE StyleName = '".$_POST['StyleName']."' LIMIT 1")) == 1)
        {
            $error['StyleName'] = 'Name already exists';
        }
        if($_POST['SymbolID'] == '')
        {
            $error['SymbolID'] = 'Enter Symbol ID';
        }
        // Symbol ID must be unique for each category
        if(!is_numeric($_POST['StyleID']) && mysqli_num_rows(MysqlQuery("SELECT o.StyleID FROM element_styles o LEFT JOIN master_elements e ON e.ElementID = o.ElementID WHERE o.SymbolID = '".$_POST['SymbolID']."' AND e.CategoryID = '".$_POST['CategoryID']."' LIMIT 1")))
        {
            $error['SymbolID'] = 'Duplicate Symbol ID! Symbol ID must be unique for each Category';
        }
        elseif(mysqli_num_rows(MysqlQuery("SELECT o.StyleID FROM element_styles o LEFT JOIN master_elements e ON e.ElementID = o.ElementID WHERE o.StyleID <> '".$_POST['StyleID']."' AND o.SymbolID = '".$_POST['SymbolID']."' AND e.CategoryID = '".$_POST['CategoryID']."' LIMIT 1")))
        {
            $error['SymbolID'] = 'Duplicate Symbol ID! Symbol ID must be unique for each Category';
        }
        if($_POST['StyleStatus'] != 0 && $_POST['StyleStatus'] != 1)
        {
            $error['StyleStatus'] = 'Invalid Status!';
        }
        if(!is_numeric($_POST['StyleID']) && $_FILES['ImageName']['name'] == '')
        {
            $error['ImageName'] = 'Please upload the Picture';
        }

        if($_FILES['ImageName']['name'] != '')
        {
            $ImageName = $_FILES['ImageName']['tmp_name'];
            $ImageNameSize = $_FILES['ImageName']['size'];
            $ImageNameName = $_FILES['ImageName']['name'];
            $ImageNameExt = strtolower(substr(strrchr($ImageNameName,'.'),1));

            if(array_search(strtolower($ImageNameExt), $AllowedImageTypes) === false)
            {
                $error['ImageName'] = 'Invalid File type. Only JPG & PNG files are allowed';
            }
        }

        if(!isset($error))
        {
            $_POST['StyleName'] = ucwords($_POST['StyleName']);

            if(is_numeric($_POST['StyleID']))
            {
                $activity = 'Element Style Updated';
                $EditMode = true;
                $ID = $_POST['StyleID'];

                MysqlQuery("UPDATE element_styles SET StyleName = '".$_POST['StyleName']."', SymbolID = '".$_POST['SymbolID']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."'
                            WHERE StyleID = '".$ID."' LIMIT 1");
            }
            else
            {
                MysqlQuery("INSERT INTO element_styles (HeadingID, ElementID, StyleName, SymbolID, ImageName, StyleStatus, AddedOn, AddedBy)
                VALUES ('".$_POST['HeadingID']."', '".$_POST['ElementID']."', '".$_POST['StyleName']."', '".$_POST['SymbolID']."', '', 'y', '".time()."', '".$AID."')");

                $ID = MysqlInsertID();
                $activity = 'Element Style Added. ('.$_POST['StyleName'].')';
            }

    		if(MysqlAffectedRows() >= 0)
    		{
    		    if($_FILES['ImageName']['name'] != '')
                {
                    // SAVE STYLE PICTURE
                    $FileName = $ID.'_sty_'.time().'.'.$ImageNameExt;
                    $ImageDir = _ROOT._StyleIconsDir;

                    list($currwidth, $currheight, $type, $attr) = getimagesize($ImageName);

                    if($currwidth <= _StyleIconWidth)
                        $SetImageWidth = $currwidth;
                    else
                        $SetImageWidth = _StyleIconWidth;

                    if(move_uploaded_file($ImageName, $ImageDir.$FileName))
					{
                        //	CREATE STANDARD SIZED IMAGE
    					if(CreateThumbnail($ImageDir.$FileName, $ImageDir, $SetImageWidth))
    					{
    					    if($EditMode)
                            {
                                // GET THE OLD FILE NAME
                                $OldFile = mysqli_fetch_assoc(MysqlQuery("SELECT ImageName FROM element_styles WHERE StyleID = '".$ID."' LIMIT 1"))['ImageName'];
                            }
                            MysqlQuery("UPDATE element_styles SET ImageName = '".$FileName."' WHERE StyleID = '".$ID."' LIMIT 1");

                            if(MysqlAffectedRows() < 0)
                            {
                                @unlink($ImageDir.$FileName);
                                $error['ImageName'] = 'Error occurred while uploading Image. [LN 10]';
                            }
                            else
                            {
                                if($EditMode)
                                {
                                    @unlink($ImageDir.$OldFile);
                                }
                            }
                        }
                        else
                        {
                            $error['ImageName'] = 'Error occurred while uploading Image. [LN 20]';
                        }
                    }
                    else
                    {
                        $error['ImageName'] = 'Error occurred while uploading Image. [LN 30]';
                    }
                }

                if(!$EditMode && isset($error))
                {
                    MysqlQuery("DELETE FROM element_styles WHERE StyleID = '".$ID."' LIMIT 1");
                    $response['error'] = $error;
                    $response['status'] = 'validation';
                }
                else
                {
                    RecordAdminActivity($activity, 'element_styles', $ID);

                    $response['status'] = 'success';
                    $response['message'] = 'Details Saved Successfully!';
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