<?php

    include_once("../include-files/autoload-server-files.php");
    include_once('Crypto.php');

    error_reporting(0);

    $merchant_data='';
    //$working_key='3ABF310AF3FE4ADDEA1091813C117A6C';
    //$access_code='AVBM74EK34BJ91MBJB';

    foreach ($_POST as $key => $value){
        $merchant_data.=$key.'='.$value.'&';
    }

    $encrypted_data=encrypt($merchant_data,$working_key); // Method for encrypting the data.

?>

<html>
<head>
<title> Secure Payment Gateway - Unifro</title>
</head>
<body>
<center>

<form method="post" name="redirect" action="https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction">
<?php
echo "<input type=hidden name=encRequest value=$encrypted_data>";
echo "<input type=hidden name=access_code value=$access_code>";
?>
</form>
</center>
<script language='javascript'>document.redirect.submit();</script>
</body>
</html>

