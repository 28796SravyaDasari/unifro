<?php

    include_once('../include-files/autoload-server-files.php');
    include_once('Crypto.php');

	error_reporting(0);

	//$workingKey     = '3ABF310AF3FE4ADDEA1091813C117A6C';		//Working Key should be provided here.
	$encResponse    = $_POST["encResp"];			            //This is the response sent by the CCAvenue Server
	$rcvdString     = decrypt($encResponse,$working_key);		//Crypto Decryption used as per the specified working key.
	$order_status   = "";
	$decryptValues  = explode('&', $rcvdString);
	$dataSize       = sizeof($decryptValues);

	echo "<center>";

	for($i = 0; $i < $dataSize; $i++)
	{
		$information=explode('=',$decryptValues[$i]);
        $ResponseData[$information[0]] = $information[1];
	}

    $Order_Id       = $ResponseData['order_id'];
    $Amount         = $ResponseData['amount'];
    $OrderStatus    = $ResponseData['order_status'];
    $BankRefNum     = $ResponseData['bank_ref_no'];	//	Bank reference number
    $PayMode        = $ResponseData['payment_mode'];	//	Payment Mode
    $BankName       = $ResponseData['card_name'];	//	Bank name

    $error = '';

    if($Amount > 0)
    {
        if($OrderStatus == 'Success')
        {
            MysqlQuery("UPDATE customer_orders SET PaymentMode = '".$PayMode."', PaymentStatus = 'Successful', PGPaymentID = '', TransID = '".$BankRefNum."',
                        BankName = '".$BankName."', PaymentDate = '".strtotime('today')."' WHERE OrderID = '".$Order_Id."' LIMIT 1");

            if(MysqlAffectedRows() >= 0)
            {
                MysqlQuery("UPDATE customer_order_status SET Status = '".$MasterOrderStatus[2]."', UpdatedOn = '".time()."' WHERE OrderID = '".$Order_Id."' LIMIT 1");

                //	LETS SEND AN SMS FOR ORDER CONFIRMATION
                $SMS = 'Thank you for shopping with Unifro.com. Your order id is '.$Order_Id.' and shipment is under process. Please check your email for order details.';
                //SendSMS($SMS, $_SESSION['BillingDetails']['BillingPhone']);

                $_SESSION['payment'] = array('status' => 'success', 'message' => '<div class="font21 mg-b-20 text-primary text-left">Thank you! We have received your payment.</div>', 'OrderID' => $Order_Id, 'amount' => $Amount, 'bank' => $BankName, 'mode' => $PayMode, 'reference' => $BankRefNum);
                header('Location: ccavResponseHandler.php');
                exit();
            }
            else
            {
                echo 'Error occurred! [LN 10]. Contact us at '._SupportEmail.' for reporting this error.';
                SendMailHTML(_AdminEmail, 'Error during Payment Status Update', 'Order ID : '.$Order_Id.'<BR>Payment Status : Successful<BR>'.'Amount : '.$Amount.'<BR><BR>MySQL Error:'.mysqli_error());
                exit();
            }
        }
        else
        {
            MysqlQuery("UPDATE customer_orders SET PaymentStatus = 'Failed', PGPaymentID = '', TransID = '".$BankRefNum."', BankName = '".$BankName."' WHERE OrderID = '".$Order_Id."' LIMIT 1");
            if(MysqlAffectedRows() >= 0)
            {
                $_SESSION['payment'] = array('status' => 'failed', 'message' => '<div class="font21 mg-b-20 text-danger text-left">Oops! The transaction has failed</div>', 'OrderID' => $Order_Id, 'amount' => $Amount, 'bank' => $BankName, 'mode' => $PayMode, 'reference' => $BankRefNum);
                header('Location: ccavResponseHandler.php');
                exit();
            }
            else
            {
                echo 'Error occurred! [LN 50]. Contact us at '._SupportEmail.' for reporting this error.<br>';
                SendMailHTML(_AdminEmail, 'Error during Payment Status Update', 'Order ID : '.$Order_Id.'<BR>Payment Status : Failed<BR>'.'Amount : '.$Amount.'<BR><BR>MySQL Error:'.mysqli_error());
                exit();
            }
        }
    }

    if(!isset($_SESSION['payment']))
    {
        $_SESSION['AlertMessage'] = 'Invalid Access!';
        header('Location: /account/');
        exit();
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">

        <title>Order Status - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

    </head>


    <body class="bg-lightgray">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card-box text-left">
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                                <li class="breadcrumb-item"><a href="/account/">My Account</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Order Status</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card-box text-left">
                        <h2 class="mg-b-20">ORDER STATUS</h2>
                        <?=$_SESSION['payment']['message']?>
                        <table class="table" style="max-width: 500px">
                            <tr>
                                <td>Order ID</td>
                                <td><?=$_SESSION['payment']['OrderID']?></td>
                            </tr>
                            <tr>
                                <td>Transaction ID</td>
                                <td><?=$_SESSION['payment']['reference'] == 'null' ? 'NA' : $_SESSION['payment']['reference']?></td>
                            </tr>
                            <tr>
                                <td>Amount</td>
                                <td><?=FormatAmount($_SESSION['payment']['amount'])?></td>
                            </tr>
                            <tr>
                                <td>Bank</td>
                                <td><?=$_SESSION['payment']['bank'] == 'null' ? 'NA' : $_SESSION['payment']['bank']?></td>
                            </tr>
                            <tr>
                                <td>Payment Mode</td>
                                <td><?=$_SESSION['payment']['mode'] == 'null' ? 'NA' : $_SESSION['payment']['mode']?></td>
                            </tr>
                        </table>

                        <?=$_SESSION['payment']['status'] != 'success' ?
                        '<button class="btn btn-warning" data-id="'.$_SESSION['payment']['OrderID'].'" onclick="CustomerRetryOrder(this)">Retry Payment</button>&nbsp;&nbsp;&nbsp;' : ''?>
                        <a class="btn btn-primary" href="/account/">Back to My Account</a>
                    </div>
                </div>
            </div>

        </div>  <!-- END OF CONTAINER -->

    </body>
</html>