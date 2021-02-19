<?php
    include("../include-files/vars.php");
    include("../include-files/connect.php");
    include("../include-files/funcs.php");
    include("include-files/admin-login-check.php");

    if(isset($_POST['Username']))
    {
        for($c = 0, $password = ''; $c < strlen($_POST['Password']); $c = $c + $_SESSION['HashLength'] + 1)
        {
            $password .= substr($_POST['Password'], $c, 1);
        }
        $_POST['Password'] = $password;

        if(count($_COOKIE) == 0)
        {
            $_SESSION['AlertMessage'] = '<SPAN class="darkRed">It seems cookies are disabled!</SPAN> <SPAN class="font11 gray">TIP: Refresh the page and try again.</SPAN>';
        }

        $q = MysqlQuery("SELECT * FROM admins WHERE Username = '".$_POST['Username']."' LIMIT 1");

        if(mysqli_num_rows($q) == 1)
        {
            $data = mysqli_fetch_assoc($q);

            if($data['Username'] == $_POST['Username'] && $data['Status'] != '1')
            {
                $_SESSION['AlertMessage'] = 'Your account is inactive! Please contact Administrator.';
            }
            elseif(trim($_POST['Password']) == "")
            {
                $_SESSION['AlertMessage'] = "Incorrect Login Details!";
                // Record this attempt in the database
                //LoginAttempts(''.$_POST['Username'].'');
            }
            elseif($data['Username'] == $_POST['Username'] && !VerifyPwd($_POST['Password'], $data['Password']))
            {
                $_SESSION['AlertMessage'] = 'Incorrect Password!';
                // Record this attempt in the database
                //LoginAttempts(''.$_POST['Username'].'');
            }
            elseif($_SESSION['Captcha'] != $_POST['Captcha'])
            {
                $_SESSION['AlertMessage'] = 'Incorrect Captcha!';
                // Record this attempt in the database
                //LoginAttempts(''.$_POST['Username'].'');
            }
            else
    		{
        		$SID = GeneratePwd(AlphaNumericCode(30).microtime());

                $q = MysqlQuery("UPDATE admins SET SID = '".$SID."', LastLogin = CurrentLogin, LastLoginIP = CurrentLoginIP, CurrentLogin = ".time().", CurrentLoginIP = '".GetIP()."' WHERE Username = '".$_POST['Username']."' LIMIT 1");

        		if(MysqlAffectedRows($q) == 1)
        		{
            			unset($_SESSION['UnlockCode']);
                        unset($_SESSION['Captcha']);

            			//$q = MysqlQuery("DELETE FROM login_attempts WHERE Username = '".$_POST['Username']."'");
            			$q = MysqlQuery("SELECT AdminID FROM admins WHERE SID = '".$SID."' LIMIT 1");

            			$AdminID = mysqli_fetch_assoc($q);
            			$AID = $AdminID['AdminID'];

            			setcookie('asid', $SID, time()+3600, '/');
            			RecordAdminActivity('Logged In', 'admins', $AID);

            			$LoggedIn = true;

            			if(isset($_SESSION['AdminReturnURL']))
            			{
                			if($_SESSION['AdminReturnURL'] == '/')
                			{
                    		    $targetURL = '/admin/dashboard/';
                			}
                			else
                            {
                    		    $targetURL = $_SESSION['AdminReturnURL'];
                                unset($_SESSION['AdminReturnURL']);
                            }
            			}
            			else
            			{
                			$targetURL = '/admin/dashboard/';
            			}
        		}
        		else
        		{
            	    $_SESSION['AlertMessage'] = 'Error Occurred! Try after some time [LN 67]';
        		}
    		}
        }
        else
        {
            $_SESSION['AlertMessage'] = 'No records found for this Username! Please contact administrator.';
        }
    }
    else
    {
        $_SESSION['HashLength']  = rand(5, 10000);
    }

    if($LoggedIn == true)
    {
        header('Location: '.$targetURL);
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
        <meta name="author" content="Coderthemes">

        <title>Admin Panel</title>

        <?php include_once("include-files/common-css.php"); ?>
        <?php include_once("include-files/common-js.php"); ?>

        <script type="text/javascript">

        $(document).ready(function()
        {
            if($('#targetURL').length > 0)
        	{
        	    setTimeout(function(){$('#loadingText').fadeOut(200, function(){$('#loadingText').text('Redirecting'); $('#loadingText').fadeIn(200, function(){window.location = $('#targetURL').val();});});}, 500);
        	}

            $('#LoginForm').submit(function(event)
            {
                $('.myCaptcha').slideDown();

                if($('input[name=Captcha]').val() == '')
                {
                    event.preventDefault();
                }
                else
                {
                    this.Password.value = AntiHack(this.Password.value);
                }
            });

            $('.myCaptcha').find('button').on('click', function()
            {
                var value = $(this).attr('data-value');
                $('input[name=Captcha]').val(value);
                $('#LoginForm').submit();
            });
        });

        function MakeRandID(){var e="";var t="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";for(var n=0;n<<?=$_SESSION['HashLength']?>;n++)e+=t.charAt(Math.floor(Math.random()*t.length));return e}function AntiHack(e){var t="";for(var n=0;n<e.length;n++){t=t+e.charAt(n)+MakeRandID()}return t}
    </script>

    </head>
    <body>

        <?php include("include-files/admin-common-scripts.php"); ?>

        <div class="wrapper-page">

            <?php
            if(isset($targetURL))
            { ?>
                <input type="hidden" id="targetURL" value="<?=$targetURL?>" />
                <div align="center" style="padding:50px 0 200px 0">
                    <span class="font17 gray" id="loadingText">Logged In</span><br><br>
                    <img src="/images/loader.gif">
                </div>
            <?php
            }
            else
            {
            ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class=" card-box">
                            <div class="panel-heading text-center bg-primary">
                                <img src="<?=_LOGO?>" alt="<?=_WebsiteName?> Logo" />
                            </div>

                            <h3 class="text-center mg-t-20"> Admin Panel </h3>

                            <div class="panel-body">
                                <form class="form-horizontal m-t-20" id="LoginForm" method="post">
                                    <input type="hidden" name="Captcha">

                                    <div class="form-group ">
                                        <div class="col-xs-12">
                                            <input class="form-control" name="Username" type="text" required="" placeholder="Username">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-xs-12">
                                            <input class="form-control" name="Password" type="password" required="" placeholder="Password">
                                        </div>
                                    </div>

                                    <div class="form-group text-center m-t-40">
                                        <div class="col-xs-12">
                                            <button class="btn btn-primary btn-block text-uppercase" type="submit">Log In</button>
                                        </div>
                                    </div>

                                    <?=GenerateCaptcha(10)?>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>

        </div>

	</body>
</html>