<?php
    include_once("../include-files/autoload-server-files.php");
    CheckLogin();
    
    $ActivePage = 'My Account';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <META name="robots" content="noindex,nofollow" />

        <title>My Account - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

    </head>


    <body>

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">
            <div class="row">
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">My Account</li>
                    </ol>
                </nav>
            </div>

            <div class="row mg-t-20">
                <div class="col-sm-9" id="content">
                    <h2>My Account</h2>
                    <ul class="list-unstyled">
                    <li><a href="/account/profile/">Edit your account information</a></li>
                    <li><a href="/account/change-password/">Change your password</a></li>
                    <li><a href="/account/addresses/">Modify your address book entries</a></li>
                    </ul>
                    <h2>My Orders</h2>
                    <ul class="list-unstyled">
                    <li><a href="/account/orders/">View your order history</a></li>
                    </ul>
                    <h2>Newsletter</h2>
                    <ul class="list-unstyled">
                    <li><a href="/account/profile/">Subscribe / unsubscribe to newsletter</a></li>
                    </ul>
                </div>

                <?php include_once(_ROOT."/account/account-common.php"); ?>
            </div>
        </div>
    </body>
</html>