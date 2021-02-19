<?php
    $response['status'] = 'error';

    if($_POST['id'] > 0 && isset($_FILES['SVGImage']))
	{
        if($_FILES['SVGImage']['name'] != '')
        {
            $SVGImage = $_FILES['SVGImage']['tmp_name'];
            $SVGImageSize = $_FILES['SVGImage']['size'];
            $SVGImageName = $_FILES['SVGImage']['name'];
            $SVGImageExt = strtolower(substr(strrchr($SVGImageName,'.'),1));

            if(strtolower($SVGImageExt) != 'svg')
            {
                $error['SVGImage'] = 'Invalid File type! Only SVG files are allowed to be uploaded';
            }
        }

        if(!isset($error))
        {
            $FileName = $_POST['id'].'_svg_'.time().'.'.$SVGImageExt;
            $ImageDir = _ROOT._SVGDir;

            // GET THE OLD SVG NAME
            $OldFile = mysqli_fetch_assoc(MysqlQuery("SELECT SVGImage FROM master_categories WHERE CategoryID = '".$_POST['id']."' LIMIT 1"))['SVGImage'];

            // LETS UPDATE THE SVG FILENAME IN THE DATABASE
            MysqlQuery("UPDATE master_categories SET SVGImage = '".$FileName."' WHERE CategoryID = '".$_POST['id']."' LIMIT 1");
            if(MysqlAffectedRows() >= 0)
            {
                if(move_uploaded_file($SVGImage, $ImageDir.$FileName))
                {
                    @unlink($ImageDir.$OldFile);

                    RecordAdminActivity('SVG Image Uploaded', 'master_categories', $_POST['id']);

                    $response['status'] = 'success';
                    $response['message'] = 'SVG Uploaded Successfully!';
                }
                else
                {
                    $response['message'] = 'Error occurred while uploading SVG. [LN 20]';
                }
            }
            else
            {
                $response['message'] = 'Error occurred while uploading SVG. [LN 10]';
            }
        }
        else
        {
            $response['message'] = $error['SVGImage'];
        }
	}
	else
	{
	    $response['message'] = 'Invalid Access!';
	}

    echo json_encode($response);
?>