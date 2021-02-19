<?php
    include_once("../include-files/autoload-server-files.php");

    if($LoggedIn)
    {
        $_POST = $MemberDetails;
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="robots" content="index,follow" />
        <meta name="Description" content="">
        <meta name="keywords" content="">

        <!--*********Define Canonical*********-->
        <link rel="canonical" href="<?=_HOST?>/upload-your-design/">

        <title>Upload Your Design - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>

        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            $('input[name=SampleDesign]').filer(options);

            $("#UploadDesignForm").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();

                AjaxResponse = AjaxFormSubmit(this);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        AlertBox(response.response_message, 'success', function(){ location.reload() });
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


    <body class="bg-lightgray my-account">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card-box center-block mg-t-20" style="max-width: 700px">
                        <h1 class="font25 mg-b-20">Upload Your Design</h1>
                        <form action="/ajax/upload-design/" id="UploadDesignForm">
                            <input type="hidden" name="token" value="<?=GenerateFormToken('UploadYourDesignForm')?>">

                            <div class="table-content">

                                <div class="row mg-b-15">
                                    <div class="col-md-6">
                                        <LABEL class="required">First Name</LABEL>
                                        <input type="text" class="form-control" maxlength="30" name="FirstName" required value="<?=$_POST['FirstName']?>">
                                    </div>
                                    <div class="col-md-6">
                                        <LABEL class="required">Last Name</LABEL>
                                        <input type="text" class="form-control" maxlength="30" name="LastName" required value="<?=$_POST['LastName']?>">
                                    </div>
                                </div>

                                <div class="row mg-b-15">
                                    <div class="col-md-6">
                                        <LABEL class="required">Email</LABEL><br>
                                        <input type="email" class="form-control" name="EmailID" required value="<?=$_POST['EmailID']?>">
                                    </div>
                                    <div class="col-md-6">
                                        <LABEL class="required">Mobile</LABEL><br>
                                        <input type="number" class="form-control" maxlength="30" name="Mobile" required="required" value="<?=$_POST['Mobile']?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <LABEL>Address</LABEL>
                                    <textarea class="form-control" name="Address" ShowCounter="250"><?=$_POST['Address']?></textarea>
                                </div>

                                <div class="row mg-b-15">
                                    <div class="col-md-12">
                                        <input  type="file" name="SampleDesign"
                                            data-jfiler-changeInput='<div class="btn btn-white"><div><i class="fa fa-plus"></i>&nbsp; Add Your Design</div></div>'
                                            data-jfiler-extensions="jpg,png" data-jfiler-caption="Only JPG & PNG files are allowed to be uploaded.">
                                    </div>
                                </div>

                                <div class="mg-t-30 pd-b-30">
                                    <button type="submit" class="btn btn-info btn-block"><i class="fa fa-upload"></i> &nbsp;Upload</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>