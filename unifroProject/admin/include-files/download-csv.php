<?php

    require _ROOT."/excel-reader/PHPExcel.php";
    require_once _ROOT."/excel-reader/PHPExcel/IOFactory.php";

	if($Option == 'Fabric')
	{
        //Set column headers
		$colheads[] = array("Product", "Company Name", "Fabric Name", "Fabric Code", "Fabric Color", "Fabric Pattern", "Fabric Price", "Fabric Blend", "Fabric Composition", "Fabric Count", "Fabric GSM", "Fabric Image", "Fabric Category");

		$RequiredFields = array("CategoryTitle", "CompanyName", "FabricName", "FabricCode", "FabricColor", "FabricPattern", "FabricPrice", "FabricBlend", "FabricComposition", "FabricCount", "FabricGSM", "FabricImage", "FabricCategory");

		$filename = 'fabric_data.csv';
		$SheetTitle = 'Fabric Data';
		$funcs = array('AddedOn' => "FormatDateTime('d', ###)", 'DOB' => "FormatDateTime('d', ###)");
        $Query = "SELECT f.*, c.CategoryTitle FROM fabrics f LEFT JOIN master_categories c ON c.CategoryID = f.CategoryID ORDER BY f.FabricID";

		ExportToExcel($Query, $colheads, $RequiredFields, $filename, $SheetTitle, $funcs);
	}
?>
