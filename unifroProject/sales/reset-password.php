<?php

    if(isset($_POST['NewPassword']))
    {
    	if(VerifyFormToken('SalesResetPasswordForm'))
    	{
    	    if(strlen($_POST['NewPassword']) < 4)
    		{
    			$_SESSION['AlertMessage'] = '<i class="fa fa-exclamation-triangle"></i> New Password must be at least 4 characters long';
    		}
    		elseif(!ValidatePassword($_POST['NewPassword']) || !ValidatePassword($_POST['ReNewPassword']))
    		{
    			$_SESSION['AlertMessage'] = '<i class="fa fa-exclamation-triangle"></i> Spaces are not allowed in password';
    		}
    		elseif($_POST['NewPassword'] !== $_POST['ReNewPassword'])
    		{
    			$_SESSION['AlertMessage'] = '<i class="fa fa-exclamation-triangle"></i> Confirm Password does not match with New Password!';
    		}
    		else
    		{
    			$NewPass = GeneratePwd(stripslashes($_POST['NewPassword']));

    			MysqlQuery("UPDATE sales SET Password = '".$NewPass."', ForgotPasswordLink = '' WHERE ForgotPasswordLink = '".$PasswordHash."'");

    			if(MysqlAffectedRows() == 1)
    			{
                    RecordSalesActivity('Sales Password Reset', 'sales', $SalesDetails['SalesID']);
    				$_SESSION['AlertMessage'] = '<SPAN class="text-success">Password changed successfully!</SPAN>';
    				header('Location: /sales-login/');
    				exit;
    			}
    			else
    			{
    				$_SESSION['AlertMessage'] = '<i class="fa fa-exclamation-triangle"></i> Something went wrong! Try after some time.';
    			}
    		}
    	}
        else
        {
            $_SESSION['AlertMessage'] = '<i class="fa fa-exclamation-triangle"></i> Invalid Access!';
        }
    }

    $SelectedTab = "Change Password";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="robots" content="noindex,nofollow" />
    <title>Reset Password - <?=_WEBSITENAME?></title>

    <?php include(_ROOT._IncludesDir.'common-css.php'); ?>
    <?php include(_ROOT._IncludesDir.'common-js.php'); ?>

</head>
<body>
	<?php include(_ROOT._IncludesDir.'common-scripts.php'); ?>

    <div class="container">
        <div class="row">

            <div class="center-box" style="border: 1px solid #ccc">
                <div id="logo-container">
                    <img class="logo-icon" src="<?=_LOGO?>" />
                </div>
                <div class="col-sm-12">
                    <form id="loginform" method="post" onsubmit="ShowProcessing('Resetting password...')">
                        <input type="hidden" name="token" value="<?=GenerateFormToken('SalesResetPasswordForm')?>">
                    	<p class="input mg-t-20">
                    		<label>
                    		<input type="password" name="NewPassword" placeholder="New Password"></label>
                        	<i class="fa fa-lock"></i>
                        </p>
                    	<p class="input">
                    		<label>
                    		<input type="password" name="ReNewPassword" placeholder="Confirm Password"></label>
                    	    <i class="fa fa-lock"></i>
                        </p>

                    	<p class="submit danger">
                    	    <button class="lighter btn" id="submit-btn" style="width: 100%">Reset Password</button>
                    	</p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    

</body>
</html>