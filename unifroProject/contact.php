<?php

    include_once("include-files/autoload-server-files.php");

    if(isset($_POST['Name']))
    {
        if($_POST['Name'] == '')
        {
            $_SESSION['AlertMessage'] = 'Please enter your Name';
        }
        elseif(!preg_match( "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $_POST['Email']))
        {
            $_SESSION['AlertMessage'] = 'Invalid Email Address! <SPAN class="font11 gray">(e.g. xyz@abc.com)</SPAN>';
        }
        elseif($_POST['Message'] == '')
        {
            $_SESSION['AlertMessage'] = 'Please enter your Message.';
        }
        elseif($_SESSION['Captcha'] != $_POST['Captcha'])
        {
            $_SESSION['AlertMessage'] = 'Incorrect captcha code, try again!';
        }
        else
        {
            $EmailMessage = '<TR>
                                            <TD>
                                                <h1>Enquiry</h1>
                                            </TD>
                                        </TR>
                                        <TR>
                                            <TD>
                                            <TABLE bgcolor="#CCCCCC" cellpadding="10" cellspacing="1" width="350px" style="font-family:arial,tahoma;font-size:13px">
                                            <TR bgcolor="#FFFFFF">
                                                <TD>Name</TD>
                                                <TD>'.$_POST['Name'].'</TD>
                                            </TR>
                                            <TR bgcolor="#FFFFFF">
                                                <TD>Phone</TD>
                                                <TD>'.$_POST['Phone'].'</TD>
                                            </TR>
                                            <TR bgcolor="#FFFFFF">
                                                <TD>Email</TD>
                                                <TD>'.$_POST['Email'].'</TD>
                                            </TR>
                                            <TR bgcolor="#FFFFFF">
                                                <TD>Message</TD>
                                                <TD>'.$_POST['Message'].'</TD>
                                            </TR>
                                            </TABLE>
                                            </TD>
                                        </TR>';

            $EmailMessage = FormatEmail($EmailMessage);

            if(SendMailHTML('sanjeev@myzow.com', 'Enquiry via Contact Form', $EmailMessage))
            {
                $_SESSION['AlertMessage'] = '<SPAN class="text-success bold">Thank you for contacting us. We will get back to you soon.</SPAN>';
                header('Location: /contact/');
                exit;
            }
            else
            {
                $_SESSION['AlertMessage'] = 'Error occurred! Try again later.';
            }
        }
    }

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <META name="robots" content="index,follow" />

    <meta name="description" content="">
    <meta name="keywords" content="">

    <title>Contact Us - Unifro</title>

    <?php include_once("include-files/common-css.php"); ?>
    <?php include_once("include-files/common-js.php"); ?>


</head>


<body>

    <?php include_once(_ROOT."/include-files/common-scripts.php"); ?>
    <?php include_once(_ROOT."/include-files/header.php"); ?>

    <div class="container">
        <div class="row mg-t-30">
            	<div class="col-lg-12 mg-b-20">
                	<H1>Contact Us</H1>
                    <div class="separator-2"></div>
                </div>
            </div>
            <div class="row">
                <div class="main col-md-12">
                    <div class="row" style="margin-bottom: 30px">
                        <div class="col-md-6">
                            <div class="alert alert-success hidden" id="MessageSent">
                                We have received your message, we will contact you very soon.
                            </div>

                            <div class="contact-form">
                                <form method="post" onSubmit="ShowProcessing('Sending message...')">
                                    <div class="form-group has-feedback">
                                        <LABEL for="name">Name</LABEL>
                                        <input type="text" class="form-control" id="Name" name="Name" required style="width:100%">
                                        <i class="fa fa-user form-control-feedback"></i>
                                    </div>
                                    <div class="form-group has-feedback">
                                        <LABEL for="email">Email</LABEL>
                                        <input type="email" class="form-control" id="Email" name="Email" required style="width:100%">
                                        <i class="fa fa-envelope form-control-feedback"></i>
                                    </div>
                                    <div class="form-group has-feedback">
                                        <LABEL for="Phone">Phone</LABEL>
                                        <input type="text" class="form-control" id="Phone" name="Phone" style="width:100%">
                                        <i class="fa fa-phone form-control-feedback"></i>
                                    </div>
                                    <div class="form-group has-feedback">
                                        <LABEL for="Message">Message</LABEL>
                                        <TEXTAREA class="form-control" rows="5" id="Message" name="Message" required style="width:100%"></TEXTAREA>
                                        <i class="fa fa-pencil form-control-feedback"></i>
                                    </div>
                                    <input type="submit" value="Submit" class="btn btn-primary">
                                </form>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="side vertical-divider-left">
                                    <H3 class="bold">Unifro Textiles Pvt Ltd.</H3>
                                    <ul class="address">
                                        <li><i class="fa fa-building"></i> &nbsp;406 Sonal Link Industrial Estate<br><span class="pl-20">Opposite Sai Palace Hotel Malad (West)</span><br>
                                        <span class="pl-20">Mumbai - 400064</span></li>
                                        <li style="padding-top: 15px"><i class="fa fa-phone pr-10"></i> &nbsp;<?=SpamProtect('(91) - 7506714057')?> / <?=SpamProtect('7506714046')?></li>
                                    </ul>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>

    <?php include_once(_ROOT."/include-files/footer.php"); ?>

</body>
</html>