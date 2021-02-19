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

        <title>Login - <?=_WebsiteName?></title>

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

        function Login()
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
            	    url: '/ajax/login/',
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
                        <form id="loginform" onsubmit="return Login()">
                            <input type="hidden" name="token" value="<?=GenerateFormToken('LoginForm')?>">
                        	<p class="input mg-t-20">
                        		<label for="user_login">
                        		<input autofocus type="email" name="EmailID" id="user_login" placeholder="Email" tabindex="1" ></label>
                            	<i class="fa fa-user"></i>
                            </p>
                        	<p class="input">
                        		<label for="user_pass">
                        		<input type="password" name="Password" id="user_pass" placeholder="Password" tabindex="2"></label>
                        	    <i class="fa fa-lock"></i>
                                <a href="#" class="forgot-password" onclick="ForgotPassword('customer')">Forgot Password?</a>
                            </p>

                        	<p class="submit danger">
                        	    <button class="lighter btn" id="submit-btn" tabindex="3">Log In</button>
                        	</p>

                            <p class="mg-t-20">
                        		Don't have an account? <a href="/register/">Create Account Now</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>