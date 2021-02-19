<?php
	include_once("include-files/autoload-server-files.php");

    if(isset($_COOKIE['ssid']))
    {
        $q = MysqlQuery("UPDATE sales SET SID = 'logout' WHERE SID = '".$_COOKIE['ssid']."' LIMIT 1");
        RecordSalesActivity('Logged Out', 'sales', $MemberID);
    	setcookie('ssid', 0, time()- 60 * 5, '/');
        $LogoutURL = _HOST.'/sales-login/';
    }
    if(isset($_COOKIE['sid']))
    {
        $q = MysqlQuery("UPDATE customers SET SID = 'logout' WHERE SID = '".$_COOKIE['sid']."' LIMIT 1");
        RecordMemberActivity('Logged Out', 'customers', $MemberID);
    	setcookie('sid', 0, time()- 60 * 5, '/');
        $LogoutURL = _HOST;
    }

    // UNSET ALL SESSIONS
	session_unset();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex,nofollow" />
<title>logging out...</title>
<?php include(_ROOT._IncludesDir.'common-css.php'); ?>
</head>
<body>

<div align="center" style="padding:50px 0 200px 0">
	<span class="font21" id="loadingText">Logging you out...</span><br><br>
	<div class="pre-loader">
        <div class="cssload-container">
            <div class="cssload-loading"><i></i><i></i><i></i><i></i></div>
        </div>
    </div>
</div>
<script>
	setTimeout(function(){ window.location = '<?=$LogoutURL?>' }, 500);
</script>
</body>
</html>