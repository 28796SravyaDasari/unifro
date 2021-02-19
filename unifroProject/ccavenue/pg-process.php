<?php

	include_once("../include-files/autoload-server-files.php");

    $OrderID = $_SESSION['CartDetails']['OrderID'];
	$InvalidAccess = 'y';

	if($LoggedIn && $OrderID > 0)
	{
	    $GetLastOrder = MysqlQuery("SELECT * FROM customer_orders WHERE MemberID = '".$MemberID."' ORDER BY OrderID DESC LIMIT 1");
        if(mysqli_num_rows($GetLastOrder) == 1)
        {
            $BillingCountry = 'IND';
            $BillingDetails = mysqli_fetch_assoc($GetLastOrder);
            $LastOrderID = $BillingDetails['OrderID'];

            if($LastOrderID == $OrderID)
            {
        	    unset($_SESSION['CartDetails']['OrderID']);
                $InvalidAccess = 'n';

                /*----------------------------------------------------
                    VARIABLES FOR 'PAYMENT GATEWAY' PROCESS
                ------------------------------------------------------*/

                $BillingDetails     = array_map('htmlentities', $BillingDetails);

                $BillingName        = $BillingDetails['ShippingName'];
        		$BillingEmail       = $MemberDetails['EmailID'];
        		$BillingPhone       = $BillingDetails['ShippingPhone'];
        		$BillingAddress     = $BillingDetails['ShippingAddress'];
        		$BillingPincode     = $BillingDetails['ShippingPincode'];
        		$BillingCountry     = 'IND';
        		$BillingState       = $BillingDetails['ShippingState'];
        		$BillingCity        = $BillingDetails['ShippingCity'];
        		$Amount             = $BillingDetails['FinalTotal'];
                $Amount = 1;
            }
            else
            {
                $InvalidAccess = 'y';
            }
        }
        else
        {
            $InvalidAccess = 'y';
        }
	}

	if($InvalidAccess == 'y')
	{
	    $_SESSION['AlertMessage'] = 'You can only make the payment of last failed order.';
		header('Location: /account/orders/');
		exit();
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
    <META charset="UTF-8">
    <title>Redirecting to secure payment gateway - Unifro</title>
    <META name="robots" content="noindex,nofollow" />

    <?php include_once(_ROOT."/include-files/common-css.php"); ?>
    <?php include_once(_ROOT."/include-files/common-js.php"); ?>

<SCRIPT type="text/javascript">
    window.onload = function() {
		var d = new Date().getTime();
		document.getElementById("tid").value = d;
        document.getElementById('pg').submit();
	};
</SCRIPT>
</HEAD>

<BODY style="margin-top:50px">
<TABLE align="center" cellpadding="10" cellspacing="0">
<TR>
	<TD align="center" class="font17" style="font-family:'Century Gothic';padding:10px">
	Please wait while you are being redirected to a Secure Payment Gateway....<BR><BR>
	<IMG src="/images/loader.gif">
	</TD>
</TR>
<TR>
	<TD>
		<FORM id="pg" method="post" name="customerData" action="ccavRequestHandler.php">
		<INPUT type="text" name="tid" id="tid" readonly />
		<INPUT type="text" name="merchant_id" value="<?=$PGMerchantID?>">
		<INPUT type="text" name="order_id" value="<?=$OrderID?>"/>
		<INPUT type="text" name="amount" value="<?=$Amount?>"/>
		<INPUT type="text" name="currency" value="INR"/>
		<INPUT type="text" name="redirect_url" value="<?=$PGRedirectURL?>"/>
		<INPUT type="text" name="cancel_url" value="<?=$PGCancelURL?>"/>
		<INPUT type="text" name="language" value="EN"/>
		<INPUT type="text" name="billing_name" value="<?=$BillingName?>"/>
		<INPUT type="text" name="billing_address" value="<?=$BillingAddress?>"/>
		<INPUT type="text" name="billing_city" value="<?=$BillingCity?>"/>
		<INPUT type="text" name="billing_state" value="<?=$BillingState?>"/>
		<INPUT type="text" name="billing_zip" value="<?=$BillingPincode?>"/>
		<INPUT type="text" name="billing_country" value="India"/>
		<INPUT type="text" name="billing_tel" value="<?=$BillingPhone?>"/>
		<INPUT type="text" name="billing_email" value="<?=$BillingEmail?>"/>
		</FORM>
	</TD>
</TR>
</TABLE>
</BODY>
</HTML>