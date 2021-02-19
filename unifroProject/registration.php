<?php

    $_SESSION['HashLength']  = rand(5, 10000);

    if($LoggedIn)
    {
        header('Location: '.(isset($_SESSION['ReturnURL']) ? $_SESSION['ReturnURL'] : $MasterMemberTypes[2]['MyAccountURL']));
        exit();
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <META name="robots" content="noindex,nofollow" />

        <title>Register - <?=_WebsiteName?></title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <style>
        .sales-login-bg {
              background: url(/images/sales-login-bg.jpg) no-repeat center center fixed;
              -webkit-background-size: cover;
              -moz-background-size: cover;
              -o-background-size: cover;
              background-size: cover;
            }
        </style>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

    </head>


    <body class="sales-login-bg">

        <?php include_once(_ROOT._IncludesDir."common-scripts.php"); ?>

        <div class="container">
            <div class="row">

                <div class="center-box" style="min-height: 600px;">
                    <div id="logo-container">
                        <img class="logo-icon" src="<?=_LOGO?>" />
                    </div>
                    <div class="col-sm-12 col-md-10 col-md-offset-1">
                        <h1 class="text-center">Register Account</h1>

                        <form class="form-horizontal" id="RegistrationForm" onsubmit="return Register()">
                            <input type="hidden" name="token" value="<?=GenerateFormToken('RegistrationForm')?>">
                            <fieldset>
                                <div class="form-group required">
                                    <div class="col-sm-12">
                                        <input type="text" class="form-control" name="FirstName" placeholder="First Name" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="text" class="form-control" name="LastName" placeholder="Last Name" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="email" class="form-control" name="EmailID" placeholder="E-Mail" required>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="password" class="form-control" name="Password" placeholder="Password" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="password" class="form-control" name="ConfirmPassword" placeholder="Password Confirm" required>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        By creating account, I accept the <a class="agree" href="/privacy-policy/"><b>Privacy Policy</b></a>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="submit" class="btn btn-primary btn-block" value="Create Account">
                                    </div>
                                </div>
                            </fieldset>
                        </form>

                        <p class="pd-l-10">Already have an account with us, <a href="/login/">Login Now</a></p>

                    </div>
                    </div>
                </div>

            </div>
        </div>

    </body>
</html>