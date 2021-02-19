<?php

    require _ROOT."/excel-reader/PHPExcel.php";
    require_once _ROOT."/excel-reader/PHPExcel/IOFactory.php";

	if(!isset($_COOKIE['asid']))
	{
		echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
		exit();
	}

    $response['status'] = 'error';

    if($_FILES['FabricFile']['name'] != '')
	{
	    $InsertLimit = 500;
        $ext = strtolower(substr(strrchr($_FILES['FabricFile']['name'],'.'),1));

        if($ext == '')
        {
            $response['message'] = 'Select XLS or CSV file to upload!';
        }
        elseif(!is_numeric($_POST['CategoryID']))
        {
            $response['message'] = 'Choose Category';
        }
        elseif(isset($AllowedExcelFiles[$ext]))
        {
            $InsertQuery = "INSERT INTO fabrics (CategoryID, CompanyName, FabricName, FabricCode, FabricColor, FabricPattern, FabricPrice, FabricBlend, KnitType, FabricComposition, FabricCount, FabricGSM, FabricImage, Industry, Season, AddedOn, AddedBy) VALUES ";

            $file = $_FILES['FabricFile']['tmp_name'];
            $objPHPExcel = PHPExcel_IOFactory::load($file);

            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet)
            {
                $worksheetTitle     = $worksheet->getTitle();
                $highestRow         = $worksheet->getHighestRow(); // e.g. 10
                $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
                $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
                $nrColumns = ord($highestColumn) - 64;

                for ($row = 2; $row <= $highestRow; ++ $row)
                {
                    for ($col = 0, $val = array(); $col < $highestColumnIndex; ++ $col)
                    {
                        $cell = $worksheet->getCellByColumnAndRow($col, $row);
                        $val[] = $cell->getCalculatedValue();
                    }
                    $InsertValues[] = "('".$_POST['CategoryID']."', '".$val[0]."', '".$val[1]."', '".$val[2]."', '".$val[3]."', '".$val[4]."', '".number_format($val[5], 2, '.','')."', '".$val[6]."', '".$val[7]."', '".$val[8]."', '".$val[9]."', '".$val[10]."', '".$val[11]."', '".$val[12]."', '".$val[13]."', '".time()."', '".$AID."')";

                    if(count($InsertValues) > $InsertLimit || $highestRow == $row)
                    {
                        $res = MysqlQuery($InsertQuery.implode(',', $InsertValues));
                        $AffectedRows = MysqlAffectedRows();
                        if($AffectedRows > 0)
                        {
                            $RowsAdded = $RowsAdded + $AffectedRows;
                            $InsertValues = array();
                        }
                        else
                        {
                            $response['message'] = 'Error Occurred! Please try again. [LN 40]';
                        }
                    }

                }
            }

            if($RowsAdded)
            {
                RecordAdminActivity('Bulk Upload', 'master_fabrics', $_POST['CategoryID']);

                $response['status'] = 'success';
                $response['message'] = $RowsAdded.' Records Added';
                $response['redirect'] = '/admin/fabrics/';
            }
        }
        else
        {
            $response['message'] = 'Invalid file type!';
        }
	}
	else
	{
	    $response['message'] = 'Invalid Access!';
	}

    echo json_encode($response);
?>