<?php

    include_once("../include-files/autoload-server-files.php");
    $_SESSION['HashLength']  = rand(5, 10000);

    if($LoggedIn)
    {
        header('Location: '.(isset($_SESSION['ReturnURL']) ? $_SESSION['ReturnURL'] : $MasterMemberTypes[1]['MyAccountURL']));
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <META name="robots" content="noindex,nofollow" />
        <meta name="description" content="">
        <meta name="keywords" content="">

        <title>Sales Login - <?=_WebsiteName?></title>

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

        <script>
        function MakeRandID(){var e="";var t="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";for(var n=0;n<<?=$_SESSION['HashLength']?>;n++)e+=t.charAt(Math.floor(Math.random()*t.length));return e}function AntiHack(e){var t="";for(var n=0;n<e.length;n++){t=t+e.charAt(n)+MakeRandID()}return t}

        function SalesLogin()
        {
            if($('#user_login').val() == '')
            {
                $('#submit-btn')
                        .popover({
                            html: true,
                            trigger: 'manual',
                            content: 'Invalid Email!',
                            placement: 'top',
                        }).popover('show');

            }
            else if($('#user_pass').val() == '')
            {
                $('#submit-btn')
                        .popover({
                            html: true,
                            trigger: 'manual',
                            placement: 'top',
                            content: 'Enter Password',
                        }).popover('show');
            }
            else
            {
                var passwd = $('#user_pass').val();
                $('#user_pass').val(AntiHack(passwd));

                //$('#login_error').addClass('hidden');
                $('#submit-btn').addClass('spinner large');

                var formData = new FormData($('#loginform')[0]);
                $.ajax({
            	    url: '/ajax/sales-login/',
            	    type: 'POST',
            		data: formData,
            		async: true,
            		cache: false,
            		contentType: false,
            		processData: false,
            	}).done(function(response)
                {
                    data = $.parseJSON(response);

                    if(data.status == 'success')
                	{
                        window.location = data.redirect;
                	}
                    else if(data.status == 'login')
                	{
                        location.reload();
                	}
                	else
                	{
                        $('#submit-btn').removeClass('spinner large');

                	    $('#user_pass').val(data.pwd);
                	    $('#submit-btn')
                        .popover({
                            html: true,
                            trigger: 'manual',
                            placement: 'top',
                            content: data.message,
                        }).popover('show');
                	}
            	});
            }
            setTimeout(function(){ $('#submit-btn').popover('hide') }, 5000);

            return false;
        }
        </script>
    </head>


    <body class="sales-login-bg">

        <?php include_once(_ROOT._IncludesDir."common-scripts.php"); ?>

        <div class="container">
            <div class="row">

                <div class="center-box">
                    <div id="logo-container">
                        <img class="logo-icon" src="<?=_LOGO?>" />
                    </div>
                    <div class="col-sm-12 col-md-10 col-md-offset-1">
                        <form id="loginform" onsubmit="return SalesLogin()">
                            <input type="hidden" name="token" value="<?=GenerateFormToken('LoginForm')?>">
                        	<p class="input mg-t-20">
                        		<label for="user_login">
                        		<input autofocus type="email" name="EmailID" id="user_login" placeholder="Email"></label>
                            	<i class="fa fa-user"></i>
                            </p>
                        	<p class="input">
                        		<label for="user_pass">
                        		<input type="password" name="Password" id="user_pass" placeholder="Password"></label>
                        	    <i class="fa fa-lock"></i>
                                <a href="#" class="forgot-password" onclick="ForgotPassword('sales')">Forgot Password?</a>
                            </p>

                        	<p class="submit danger">
                        	    <button class="lighter btn" id="submit-btn">Log In</button>
                        	</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>