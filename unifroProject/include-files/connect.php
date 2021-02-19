<?php

    //	Database authentications & Time zone
	$DBCon = $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? mysqli_connect("localhost", "root", "1379", "unifro_db") : mysqli_connect("localhost", "unifro_user", "U#18032007@Y", "unifro_com");
	if (!$DBCon)
	{
		die('Could not connect: ' . mysqli_connect_error());
	}

	date_default_timezone_set("Asia/Calcutta");		//Set time zone
?>