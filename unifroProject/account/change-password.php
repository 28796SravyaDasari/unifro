<?php

    CheckLogin();

    $SelectedTab = "Change Password";
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="robots" content="noindex,nofollow" />

    <title>Change Password | <?=_WebsiteName?></title>

    <?php include(_ROOT._IncludesDir.'common-css.php'); ?>
    <?php include(_ROOT._IncludesDir.'common-js.php'); ?>

    <script>
        $(document).ready(function()
        {
            $("#ChangePasswordForm").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();

                AjaxResponse = AjaxFormSubmit(this,'', false);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        AlertBox(response.response_message, 'success', function(){ location.href = response.redirect });
                    }
                    else if(response.status == 'login')
                    {
                        AlertBox('Oops! Your session has expired! Please login again.', 'error', function(){ location.href = response.redirect } );
                    }
                    else if(response.status == 'validation')
                	{
                	    ThrowError(response.error, true);
                	}
                    else
                    {
                        AlertBox(response.response_message);
                    }
                });

            });
        });
        </script>

</head>
<body>

	<?php include(_ROOT._IncludesDir.'common-scripts.php'); ?>
    <?php include_once(_ROOT."/include-files/header.php"); ?>

    <div class="container">
        <div class="row">
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="/account/">My Account</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                </ol>
            </nav>
        </div>

        <div class="row mg-t-20">
            <div class="col-sm-9">
                <h2 class="page-title">Change Password</h2>
                <form action="/ajax/customer/change-password/" id="ChangePasswordForm">
                <div class="mg-t-20">
                    <div class="form-group">
                        <LABEL for="Password">Current Password</LABEL><br>
                        <input type="password" class="form-control" maxlength="30" name="CurrentPassword" required style="width:100%" value="<?=$_POST['CurrentPassword']?>">
                    </div>
                    <div class="form-group">
                        <LABEL for="NewPassword">New Password</LABEL><br>
                        <input type="password" class="form-control" maxlength="30" name="NewPassword" required style="width:100%">
                    </div>
                    <div class="form-group">
                        <LABEL for="ReNewPassword">Re-Enter New Password</LABEL><br>
                        <input type="password" class="form-control" maxlength="30" name="ReNewPassword" required style="width:100%">
                    </div>
                    <div class="form-group">
			            <button type="submit" class="btn btn-primary">Change Password</button>
                        <a href="<?=$MyAccountURL?>" class="btn btn-danger">Cancel</a>
		            </div>
                </div>
                </form>
            </div>

            <?php include_once(_ROOT."/account/account-common.php"); ?>

        </div>
    </div>


</body>
</html>