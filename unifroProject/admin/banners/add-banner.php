<?php
    $SelectedPage = 'Banner Management';

    if($ID)
    {
        $BannerDetails = MysqlQuery("SELECT * FROM banners WHERE BannerID = '".$ID."' LIMIT 1");
        if(mysqli_num_rows($BannerDetails) == 1)
        {
            $_POST = mysqli_fetch_assoc($BannerDetails);
            $_POST['ImageName'] = _BannerDir.$_POST['ImageName'];
        }
        else
        {
            $_SESSION['AlertMessage'] = 'Invalid Access!';
            header('Location: /admin/banners/');
            exit;
        }
    }

    $_POST['SortOrder'] = $_POST['SortOrder'] > 0 ? $_POST['SortOrder'] : 0;

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$PageTitle?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <link  href="/admin/css/cropper.min.css" rel="stylesheet">
        <style>
            .img-container img {
                max-width: 100%;
            }
            .img-container {
                width: 100%;
                height: 300px;
                position: relative;
                background-color: rgba(0, 0, 0, 0.1);
                margin-bottom: 10px;
            }
            .result{ margin-bottom: 20px; }
            #CropBannerModal .alert {
                padding: 10px;
                margin-bottom: 10px;
            }
        </style>

        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>
        <script src="/admin/js/cropper.min.js"></script>
        <script src="/admin/js/loader.min.js"></script>

        <script>
        $(document).ready(function()
        {
            $('input[name=BannerImage]').filer();

            $("#BannerForm").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();
                AjaxResponse = AjaxFormSubmit(this);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        AlertBox(response.message, 'success', function(){ location.href = response.redirect });
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
                        AlertBox(response.message);
                    }
                });
            });
        })
        </script>

    </head>
    <body>

        <?php include(_ROOT._AdminIncludesDir."admin-common-scripts.php"); ?>

        <div id="wrapper">
            <?php include(_ROOT._AdminIncludesDir."admin-header.php"); ?>
            <?php include(_ROOT._AdminIncludesDir."admin-sidebar.php"); ?>

            <!-- ==============================================================
                Start of Right content here
            ============================================================== -->
            <div class="content-page">

                <div class="content">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="content-header clearfix">
                                    <h1 class="pull-left page-title"><?=$PageTitle?></h1>
                                    <div class="pull-right">
                                        <a href="<?=GoToLastPage()?>" class="btn btn-primary">
                                            <i class="fa fa-long-arrow-left"></i>&nbsp;
                                            Back
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-box">
                                    <!------------------------------
                                        START OF FORM
                                    ------------------------------->
                                    <form action="/admin/ajax/save-banner/" class="form-horizontal" id="BannerForm">
                                        <input type="hidden" name="BannerID" value="<?=$_GET['id']?>">
                                        <input type="hidden" id="ImageName" name="ImageName">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="result">
                                                    <img src="<?=$_POST['ImageName']?>" class="result-img">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix">
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <button type="button" class="btn btn-warning" name="AddImage" data-target="#CropBannerModal" data-toggle="modal">
                                                            <?=$ID ? 'Change Image' : 'Add Image'?>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Target Link</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="240" name="TargetLink" type="text" value="<?=$_POST['TargetLink']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Display Order</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" min="0" name="SortOrder" type="number" value="<?=$_POST['SortOrder']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Status</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="custom-radiobutton">
                                                            <LABEL>
                                                                <input<?=$_POST['Status'] == '1' ? ' checked' : ''?> name="Status" type="radio" value="1">
                                                                <span></span>&nbsp; Active
                                                           </LABEL>
                                                           <LABEL>
                                                                <input<?=!isset($_POST['Status']) || $_POST['Status'] == '0' ? ' checked' : ''?> name="Status" type="radio" value="0">
                                                                <span></span>&nbsp; Inactive
                                                           </LABEL>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group mg-t-30">
                                                    <div class="col-md-4">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <button class="btn btn-primary btn-block">
                                                            <i class="fa fa-floppy-o"></i>&nbsp; Save Banner
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5">

                                            </div>
                                        </div>
                                    </form>
                                    <!------------------------------
                                        END OF FORM
                                    ------------------------------->
                                </div>
                            </div>
                        </div>
                    </div>   <!-- container -->

                </div>   <!-- content -->


                <?php include(_ROOT._AdminIncludesDir."footer.php"); ?>

            </div>
            <!-- ==============================================================
                End of Right content here
            ============================================================== -->
        </div>

        <!-- Modal -->
        <div class="modal fade" id="CropBannerModal" role="dialog" tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Banner Image</h4>
                    </div>
                    <div class="modal-body">
                        <div class="text-danger mg-b-5">Note: Image dimensions must be <?=_HomepageBannerWidth?> x <?=_HomepageBannerHeight?> px ( W x H)</div>
                        <div class="img-container">
                            <img id="image">
                        </div>
                        <div class="divBlock">
                            <input type="file" id="imageInput" name="BannerImage" data-jfiler-changeInput='<div class="btn btn-info"><div><i class="fa fa-paperclip"></i>&nbsp; Attach Image</div></div>' data-jfiler-extensions="jpg,png" data-jfiler-limit="1" data-jfiler-caption="Only JPG & PNG files are allowed to be uploaded.">
                        </div>
                        <div class="divBlock">
                            <button type="button" class="btn btn-white" onclick="cropper.zoom('0.1')">Zoom In</button>
                            <button type="button" class="btn btn-white" onclick="cropper.zoom('-0.1')">Zoom Out</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" id="CropBtn">Crop Image</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            var options = {
                    dragMode: 'move',
                    aspectRatio: 16 / 7.2,
                    cropBoxResizable: false,
                    minCropBoxWidth: 1440,
                };

            var image   = document.getElementById('image');
            var cropper = new Cropper(image, options);
            var _URL    = window.URL || window.webkitURL;

            $(document).ready(function ()
            {
                $('#imageInput').on('change', function(e)
                {
                    var file, img;

                    if ((file = this.files[0]))
                    {
                        img = new Image();
                        img.onload = function()
                        {
                            $('.alert').remove();

                            if(this.width < <?=_HomepageBannerWidth?>)
                            {
                                $('.modal-body').append('<div class="alert alert-danger mg-t-15"><i class="fa fa-exclamation-triangle"></i> &nbsp;Image width must be <?=_HomepageBannerWidth?>px. Your image width is '+this.width+'px</div>');
                            }
                            else
                            {
                                var files = e.target.files;

                                if (files && files.length)
                                {
                                    loadImageFromFile(files[0]);
                                }
                            }
                        };
                        img.onerror = function() {
                            alert( "not a valid file: " + file.type);
                        };
                        img.src = _URL.createObjectURL(file);
                    }
                });

                $('#CropBtn').on('click', function ()
                {
                    $('#CropBannerModal').modal('hide');

                    var imgUrl = cropper.getCroppedCanvas().toDataURL('image/jpeg');
                    document.querySelector('.result-img').src = imgUrl;
                    $('#ImageName').val(imgUrl);
                });

            });

            // load image from input, trans exif orientation
            function loadImageFromFile(file)
            {
                var image;
                if (URL)
                {
                    image = new Image();

                    image.onload = function()
                    {
                        this.onload = null;
                        URL.revokeObjectURL(file);
                    };

                    image.src = URL.createObjectURL(file);

                    var loader = new Loader(image,
                    {
                        done: function(transedImage)
                        {
                            cropper.replace(transedImage.src);
                        }
                    });
                }
            }
        </script>

	</body>
</html>