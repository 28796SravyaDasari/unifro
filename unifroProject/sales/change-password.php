<?php

    CheckLogin();

    $SelectedTab = "Change Password";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="robots" content="noindex,nofollow" />
    <title><?=$SelectedTab?> | <?=_WebsiteName?></title>

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
<body class="bg-lightgray">

	<?php include(_ROOT._IncludesDir.'common-scripts.php'); ?>
    <?php include_once(_ROOT."/include-files/header.php"); ?>

    <div class="container-fluid">

            <div class="row bg-white">
                <div class="col-lg-4">
                    <div class="loginBox">
                        <h2 class="page-title">Change Password</h2>
                        <form action="/ajax/sales/change-password/" id="ChangePasswordForm">
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

                </div>
            </div>

        </div>


</body>
</html>