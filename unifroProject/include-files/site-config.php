<?php

	$SiteSettings['AllowedIPs'] = '127.0.0.1';
	$DownForMaintenance = 0;	// MINS.	SET IT TO THE NO OF MINS THE SITE MAY BE TAKEN DOWN FOR MAINTENANCE
	$AllowIPDuringMaintenance = explode(',', $SiteSettings['AllowedIPs']);	//	ALLOWED IP ADDRESSES DURING DOWN TIME

	if($DownForMaintenance > 0 && !in_array($_SERVER['REMOTE_ADDR'], $AllowIPDuringMaintenance) && stripos($_SERVER['REQUEST_URI'], '/admin/', 0) === false)
	{
		header('HTTP/1.1 503 Service Temporarily Unavailable');		//	INFORM robots ABOUT SITE BEING TEMPORARILY DOWN AND RETRY
		header('Retry-After: '.($DownForMaintenance * 60));
		include(_ROOT.'/maintenance.php');
		exit();
	}

?>