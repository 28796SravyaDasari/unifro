<?php
    include_once("../include-files/autoload-server-files.php");
    CheckLogin();

    if(isset($_SESSION['CartDetails']['SelectedProducts']))
    {
        $_SESSION['CartDetails']['Products'] = $_SESSION['CartDetails']['SelectedProducts'];
    }

    foreach($_SESSION['CartDetails']['Products'] as $CartID => $Arr)
    {
        if(isset($Arr['Monogram']))
        {
            $ShowMonogram = true;

            // lets fetch all the Monograms uploaded by the client
            $CustomerMonograms = MysqlQuery("SELECT * FROM customer_monograms WHERE MemberID = '".$MemberID."'");
            if(mysqli_num_rows($CustomerMonograms) > 0)
            {
                $CustomerMonograms = MysqlFetchAll($CustomerMonograms);
            }
            break;
        }
    }
    //JsonPrettyPrint($_SESSION['CartDetails']);
    //exit;

    if(isset($_SESSION['CartDetails']['AdditionalDetails']))
    {
        $AdditionalDetails = json_decode($_SESSION['CartDetails']['AdditionalDetails'], true);
    }

    if(!$ShowMonogram && !$_SESSION['CartDetails']['CustomProduct'])
    {
        header('Location: /checkout/addresses/');
        exit;
    }
    if($ShowMonogram)
    {
        $Option = 'All';
    }
    elseif($_SESSION['CartDetails']['CustomProduct'])
    {
        $Option = 'CustomProduct';
    }


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title>Additional Details - Unifro</title>

        <META name="robots" content="noindex,nofollow" />

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <link href="/css/jquery.filer.css" rel="stylesheet" type="text/css" />
        <link href="/css/flatpickr.min.css" rel="stylesheet" type="text/css" />

        <?php include_once(_ROOT."/include-files/common-js.php"); ?>
        <script src="/js/jquery.filer.min.js"></script>
        <script src="/js/flatpickr.min.js"></script>

        <script>
        $(document).ready(function()
        {
            $('input[name=Monogram]').filer({
                limit: 1,
                maxSize: null,
                changeInput: true,
                showThumbs: false,
                uploadFile: {
                    url: "/ajax/customer/add-monogram/",
                    data: {'id': <?=$MemberID?>},
                    type: 'POST',
                    enctype: 'multipart/form-data',
                    synchron: true,
                    beforeSend: function(){ ShowProcessing('Uploading...') },
                    success: function(data, itemEl, listEl, boxEl, newInputEl, inputEl, id){
                        CloseProcessing();
                        data = JSON.parse(data);
                        if(data.status == 'success')
                        {
                            location.reload();
                        }
                        else
                        {
                            AlertBox(data.response_message);
                        }
                    },
                    error: function(data, el){
                        var parent = el.find(".jFiler-jProgressBar").parent();
                        el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
                            $("<div class=\"jFiler-item-others text-error\"><i class=\"icon-jfi-minus-circle\"></i> Error...</div>").hide().appendTo(parent).fadeIn("slow");
                        });
                    },
                    statusCode: null,
                    onProgress: null,
                    onComplete: null
                },
                files: null,
                addMore: false,
                allowDuplicates: true,
                clipBoardPaste: true,
                excludeName: null,
                beforeRender: null,
                afterRender: null,
                beforeShow: null,
                beforeSelect: null,
                onSelect: null,
                afterShow: null,
                onRemove: function(itemEl, file, id, listEl, boxEl, newInputEl, inputEl){
                    var filerKit = inputEl.prop("jFiler"),
                        file_name = filerKit.files_list[id].name;

                    $.post('./php/ajax_remove_file.php', {file: file_name});
                },
                onEmpty: null,
                options: null,
                dialogs: {
                    alert: function(text) {
                        return alert(text);
                    },
                    confirm: function (text, callback) {
                        confirm(text) ? callback() : null;
                    }
                },
                captions: {
                    button: "Choose Files",
                    feedback: "Choose files To Upload",
                    feedback2: "files were chosen",
                    drop: "Drop file here to Upload",
                    removeConfirmation: "Are you sure you want to remove this file?",
                    errors: {
                        filesLimit: "Only {{fi-limit}} files are allowed to be uploaded.",
                        filesType: "Only Images are allowed to be uploaded.",
                        filesSize: "{{fi-name}} is too large! Please upload file up to {{fi-maxSize}} MB.",
                        filesSizeAll: "Files you've choosed are too large! Please upload files up to {{fi-maxSize}} MB."
                    }
                }
            });

            $("#AdditionalDetailsForm").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();

                AjaxResponse = AjaxFormSubmit(this);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        location.href = response.redirect
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

            $('.monograms').find('li a').on('click', function()
            {
                $('.monograms').find('li a').removeClass('active');
                $(this).addClass('active');
            });

            $('.datepicker').flatpickr({
                 dateFormat: 'd-M-Y h:i K',
                 enableTime: true,
            });
        });

        </script>
    </head>


    <body class="my-account">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">

            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="/shopping-bag/">Shopping Bag</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Additional Details</li>
                </ol>
            </nav>

            <div class="row mg-t-20">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-12" style="max-width: 700px">

                            <form action="/ajax/customer/save-additional-details/" id="AdditionalDetailsForm">

                                <div class="additional-details">

                                    <input type="hidden" name="Option" value="<?=$Option?>" />
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Additional Details</div>
                                        <div class="panel-body">
                                            <?php
                                            /*----------------------------
                                                Start of Monogram Inputs
                                            -----------------------------*/
                                            if($ShowMonogram)
                                            {
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">

                                                    <div class="mg-b-15">
                                                        <input  type="file" name="Monogram" data-jfiler-changeInput='<div class="btn btn-white"><div><i class="fa fa-upload"></i>&nbsp; Add Monogram</div></div>'
                                                                data-jfiler-extensions="jpg,png" data-jfiler-caption="Only JPG & PNG files are allowed to be uploaded.">
                                                    </div>

                                                    <?php
                                                    if(count($CustomerMonograms) > 0)
                                                    {
                                                        echo '<ul class="list-inline monograms">';
                                                        foreach($CustomerMonograms as $monogram)
                                                        {
                                                            ?>
                                                            <li>
                                                                <div class="mg-b-10">
                                                                    <a<?=$AdditionalDetails['ClientMonogram'] > 0 ? ' class="active"' : ''?>>
                                                                        <label class="font15">
                                                                            <input type="radio"<?=$AdditionalDetails['ClientMonogram'] > 0 ? ' checked' : ''?> class="hidden" name="CustomerMonogram" value="<?=$monogram['MonogramID']?>"><span></span>
                                                                            <img src="<?=_MonogramDir.$monogram['FileName']?>" />
                                                                        </label>
                                                                    </a>
                                                                </div>
                                                            </li>
                                                            <?php
                                                        }
                                                        echo '</ul>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="row mg-b-15">
                                                <div class="col-md-12">
                                                    <ul class="list-unstyled mg-t-15">
                                                        <li class="text-center mg-b-20">Select a Monogram</li>

                                                        <li class="text-center mg-t-20 mg-b-20">--- OR ---</li>

                                                        <li class="text-center">
                                                            <input type="text" class="form-control" maxlength="50" name="MonogramText" placeholder="Type brand name" style="width: 200px;margin:auto" value="<?=$AdditionalDetails['MonogramText']?>">
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="row mg-b-15">
                                                <div class="col-md-12">
                                                    <div>How do you want Monogram?</div>
                                                    <div class="custom-radiobutton mg-t-10">
                                                        <label>
                                                            <input<?=$AdditionalDetails['MonogramType'] == 'Embossed' ? ' checked' : ''?> type="radio" name="MonogramType" value="Embossed"><span></span> Embossed
                                                        </label>
                                                        <label>
                                                            <input<?=$AdditionalDetails['MonogramType'] == 'Embroidered' ? ' checked' : ''?> type="radio" name="MonogramType" value="Embroidered"><span></span> Embroidered
                                                        </label>
                                                        <label>
                                                            <input<?=$AdditionalDetails['MonogramType'] == 'Printed' ? ' checked' : ''?> type="radio" name="MonogramType" value="Printed"><span></span> Printed
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                                <?php
                                            }
                                            /*----------------------------
                                                End of Monogram Inputs
                                            -----------------------------*/
                                            ?>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <div class="mg-b-5">Would you like to Book a Tailor?</div>
                                                        <div class="custom-radiobutton">
                                                            <label>
                                                                <input<?=$AdditionalDetails['BookATailor'] == 'y' ? ' checked' : ''?> type="radio" name="BookATailor"
                                                                onchange="LoadCalendar(this)" value="y"> <span></span> Yes
                                                            </label>
                                                            <label>
                                                                <input<?=$AdditionalDetails['BookATailor'] == 'n' ? ' checked' : ''?> type="radio" name="BookATailor"
                                                                onchange="LoadCalendar(this)" value="n"> <span></span> No
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mg-t-30" id="AppointmentDate" style="display: <?=$AdditionalDetails['BookATailor'] == 'y' ? 'block' : 'none'?>">
                                                        <div class="mg-b-5">Tailor visit date</div>
                                                        <div class="input-group" style="max-width: 230px">
                                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                            <input  class="form-control datepicker" name="AppointmentDate" required type="text" placeholder="Choose date"
                                                                    value="<?=$AdditionalDetails['AppointmentDate']?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mg-t-30">
                                                        <div class="mg-b-5">Do you want Unifro label on your custom designed product?</div>
                                                        <div class="custom-radiobutton">
                                                            <label>
                                                                <input<?=$AdditionalDetails['UnifroLabel'] == 'y' ? ' checked' : ''?> type="radio" name="UnifroLabel" value="y">
                                                                <span></span> Yes
                                                            </label>
                                                            <label>
                                                                <input<?=$AdditionalDetails['UnifroLabel'] == 'n' ? ' checked' : ''?> type="radio" name="UnifroLabel" value="n">
                                                                <span></span> No
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mg-t-30">
                                                        <textarea class="form-control" name="Comments" rows="3" placeholder="Type your comments"><?=$AdditionalDetails['Comments']?></textarea>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mg-t-30 pd-b-30">
                                        <button type="submit" class="btn btn-primary">Save & Place Order</button>
                                        <a href="/shopping-bag/" class="btn btn-danger">Back</a>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function LoadCalendar(t)
        {
            if(t.value == 'y')
            {
                $('#AppointmentDate').slideDown(300);
            }
            else
            {
                $('.datepicker').val('');
                $('#AppointmentDate').slideUp(300);
            }
        }
        </script>

    </body>
</html>