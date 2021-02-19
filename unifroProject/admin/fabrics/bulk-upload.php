<?php

    $SelectedPage = 'Fabrics';

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$PageTitle?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            options = {
                        showThumbs: true,
                        limit: 1,
                        maxSize: null,
                        changeInput: true,
                        templates: {
                                        box: '<ul class="jFiler-item-list"></ul>',
                                        item: '<li class="jFiler-item">\
                                                    <div class="jFiler-item-container">\
                                                        <div class="jFiler-item-inner">\
                                                            <div class="jFiler-item-thumb">\
                                                                {{fi-image}}\
                                                            </div>\
                                                            <div class="jFiler-item-assets jFiler-row">\
                                                                <ul class="list-inline pull-left">\
                                                                    <li><span class="jFiler-item-others">{{fi-size2}}</span></li>\
                                                                </ul>\
                                                                <ul class="list-inline pull-right">\
                                                                    <li><a class="icon-jfi-trash jFiler-item-trash-action"><i class="fa fa-trash" title="Delete"></i></a></li>\
                                                                </ul>\
                                                            </div>\
                                                        </div>\
                                                    </div>\
                                                </li>',

                                        removeConfirmation: true,
                                        _selectors: {
                                                        list: '.jFiler-item-list',
                                                        item: '.jFiler-item',
                                                        remove: '.jFiler-item-trash-action',
                                                    }
                                    },
                        };

            $('input[name=FabricFile]').filer(options);

            $("#FabricForm").submit(function(e)
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
                                        <a class="btn btn-warning" href="/admin/fabric-swatch.xls"><i class="fa fa-download"></i> Excel Format</a>
                                        <a href="<?=GoToLastPage()?>" class="btn bg-primary">
                                            <i class="fa fa-long-arrow-left"></i>&nbsp;
                                            Back to All Fabrics
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-box" style="min-height: 500px">
                                    <!------------------------------
                                        START OF FORM
                                    ------------------------------->
                                    <form action="/admin/ajax/fabric-bulk-upload/" class="form-horizontal" id="FabricForm">
                                        <input hidden="hidden" name="FabricID" value="<?=$_GET['id']?>">
                                        <div class="clearfix">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="col-md-6">
                                                        <label class="control-label required">Category</label><br>
                                                        <select class="selectric" name="CategoryID">
                                                            <option value="">Choose Category</option>
                                                            <?=CustomCategories('1', '')?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-6">
                                                        <input type="file" name="FabricFile" data-jfiler-changeInput='<div class="btn btn-white"><div><i class="fa fa-upload"></i>&nbsp; Attach CSV File</div></div>' data-jfiler-extensions="csv, xls, xlsx" data-jfiler-caption="Only XLS & CSV files are allowed to be uploaded.">
                                                    </div>
                                                </div>
                                                <div class="form-group mg-t-30">
                                                    <div class="col-md-6">
                                                        <button class="btn btn-primary btn-block">
                                                            <i class="fa fa-upload"></i>&nbsp; Upload
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!------------------------------
                                        END OF FORM
                                    ------------------------------->
                                    <div>
                                        <p class="pd-l-20">Note: Using FTP, upload the swatch images to <b><?=_FabricImageDir?></b> folder</p>
                                    </div>
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

	</body>
</html>