<?php
    include_once("../include-files/autoload-server-files.php");
    CheckLogin();

    if(isset($_SESSION['CartDetails']['SelectedProducts']))
    {
        $_SESSION['CartDetails'][$ClientID]['Products'] = $_SESSION['CartDetails']['SelectedProducts'];
    }

    foreach($_SESSION['CartDetails'][$ClientID]['Products'] as $CartID => $Arr)
    {
        if(in_array('Monogram', $Arr))
        {
            $ShowMonogram = true;

            // lets fetch all the Monograms uploaded by the client
            $ClientMonograms = MysqlQuery("SELECT * FROM client_monograms WHERE ClientID = '".$ClientID."'");
            if(mysqli_num_rows($ClientMonograms) > 0)
            {
                $ClientMonograms = MysqlFetchAll($ClientMonograms);
            }
            break;
        }
    }
    //JsonPrettyPrint($_SESSION['CartDetails']);
    //exit;

    if(!$ShowMonogram)
    {
        header('Location: /sales/clients/addresses/'.$_SESSION['ClientID'].'/');
        exit;
    }

    if(isset($_SESSION['CartDetails'][$_SESSION['ClientID']]['AdditionalDetails']))
    {
        $AdditionalDetails = json_decode($_SESSION['CartDetails'][$_SESSION['ClientID']]['AdditionalDetails'], true);
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title>Additional Details - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <link href="/css/jquery.filer.css" rel="stylesheet" type="text/css" />

        <?php include_once(_ROOT."/include-files/common-js.php"); ?>
        <script src="/js/jquery.filer.min.js"></script>

        <script>
        $(document).ready(function()
        {
            $('input[name=Monogram]').filer({
                limit: 1,
                maxSize: null,
                changeInput: true,
                showThumbs: false,
                uploadFile: {
                    url: "/ajax/sales/add-monogram/",
                    data: {'id': <?=$ClientID?>},
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
        });

        </script>
    </head>


    <body class="bg-lightgray my-account">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card-box">
                        <a class="btn btn-white pull-right" href="/sales/"><i class="fa fa-long-arrow-left"></i> Back to All Clients</a>
                        <h3 class="mg-0"><?=GetClientDetails($ClientID, 'ClientName')?></h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-12" style="max-width: 700px">

                            <form action="/ajax/sales/save-additional-details/" id="AdditionalDetailsForm">
                                <input type="hidden" name="ClientID" value="<?=$ClientID?>" />

                                <div class="additional-details">

                                    <input type="hidden" name="Option" value="Monogram" />
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Monogram</div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-lg-12">

                                                    <div class="mg-b-15">
                                                        <input  type="file" name="Monogram" data-jfiler-changeInput='<div class="btn btn-white"><div><i class="fa fa-upload"></i>&nbsp; Add Monogram</div></div>'
                                                                data-jfiler-extensions="jpg,png" data-jfiler-caption="Only JPG & PNG files are allowed to be uploaded.">
                                                    </div>

                                                    <?php
                                                    if(count($ClientMonograms) > 0)
                                                    {
                                                        echo '<ul class="list-inline monograms">';
                                                        foreach($ClientMonograms as $monogram)
                                                        {
                                                            ?>
                                                            <li>
                                                                <div class="mg-b-10">
                                                                    <a<?=$AdditionalDetails['ClientMonogram'] > 0 ? ' class="active"' : ''?>>
                                                                        <label class="font15">
                                                                            <input type="radio"<?=$AdditionalDetails['ClientMonogram'] > 0 ? ' checked' : ''?> class="hidden" name="ClientMonogram" value="<?=$monogram['MonogramID']?>"><span></span>
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
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <textarea class="form-control" name="Comments" rows="3" placeholder="Type your comments"><?=$AdditionalDetails['Comments']?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mg-t-30 pd-b-30">
                                        <button type="submit" class="btn btn-primary">Save & Place Order</button>
                                        <a href="/sales/clients/<?=$ClientID?>/" class="btn btn-danger">Cancel</a>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>