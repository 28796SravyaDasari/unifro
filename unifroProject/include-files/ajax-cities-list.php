<?php
	include_once("vars.php");
	include_once("connect.php");
	include_once("funcs.php");

	$q = MysqlQuery("SELECT CityID, CityName FROM cities WHERE StateID = '".$_GET['id']."' ORDER BY CityName");

	if(mysqli_num_rows($q) > 0)
	{
	    $CityList = '<option value="">Select City</option>';
	    for( ; $row = mysqli_fetch_assoc($q); )
        {
            $data[] = array('text' => $row['CityName'], 'value' => $row['CityID']);

            $CityList .= '<option'.($_GET['selectedCity'] == $row['CityID'] ? ' selected' : '').' value="'.$row['CityID'].'">'.$row['CityName'].'</option>';
        }
		echo $CityList;
	}
?>