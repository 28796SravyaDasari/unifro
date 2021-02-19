<?php
    $SelectedPage = 'Other';

    if(is_numeric($_GET['id']))
    {
        $CategoryDetails = MysqlQuery("SELECT * FROM master_categories WHERE CategoryID = '".$_GET['id']."' LIMIT 1");
        if(mysqli_num_rows($CategoryDetails) == 1)
        {
            $_POST = mysqli_fetch_assoc($CategoryDetails);
            $_POST['Size'] = json_decode($_POST['Size'], true);
            $_POST['Size'] = implode(',',$_POST['Size']);
        }
        else
        {
            $_SESSION['AlertMessage'] = 'Invalid Access!';
            header('Location: /admin/categories/');
            exit;
        }
    }

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
            $('input[name=WidgetImage]').filer(options);

            $("#CategoryForm").submit(function(e)
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
                                            Back to All Categories
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div>

                                    <ul class="nav nav-tabs responsive-tabs" style="width: 100%;">
                                        <li class="active tab"><a href="/admin/categories/other/edit/?id=<?=$_GET['id']?>">Category Info</a></li>
                                    </ul>

                                    <div class="tab-content">
                                        <!------------------------------
                                            START OF CONTENT TAB
                                        ------------------------------->
                                        <div class="tab-pane active">
                                            <form action="/admin/ajax/categories/other-save/" class="form-horizontal" id="CategoryForm">
                                                <input hidden="hidden" name="CategoryID" value="<?=$_GET['id']?>">
                                                <div class="clearfix">
                                                    <div class="col-md-7">
                                                        <div class="form-group">
                                                            <div class="col-md-4">
                                                                <label class="control-label required">Category Title</label>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <input class="form-control" maxlength="100" name="CategoryTitle" type="text" value="<?=$_POST['CategoryTitle']?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-4">
                                                                <label class="control-label required">Parent Category</label>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <select class="selectric" name="ParentID">
                                                                    <?php
                                                                    foreach(OtherCategories('0', '', true) as $CatID => $CatTitle)
                                                                    {
                                                                        echo '<option'.($CatID == $_POST['ParentID'] ? ' selected' : '').' value="'.$CatID.'">'.$CatTitle.'</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-4">
                                                                <label class="control-label required">Sizes</label>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <input class="form-control" maxlength="100" name="Size" type="text" value="<?=$_POST['Size']?>">
                                                                <div class="text-danger">Note: Enter only comma separated sizes</div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-4">
                                                                <label class="control-label">Category Picture</label>
                                                            </div>
                                                            <div class="col-md-8">

                                                                <?php if($_POST['WidgetImage'] != '') { ?>
                                                                    <ul class="jFiler-item-list">
                                                                        <li class="jFiler-item">
                                                                            <div class="jFiler-item-inner">
                                                                                <div class="jFiler-item-thumb">
                                                                                    <img src="<?=_CategoryPicDir.$_POST['WidgetImage']?>" alt="" />
                                                                                </div>
                                                                                <div class="pull-right">
                                                                                    <i class="fa fa-trash" data-id="<?=$_POST['CategoryID']?>" data-opt="category" onclick="DeletejFilerThumbnail(this)" title="Delete"></i>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                    </ul>
                                                                <?php } ?>

                                                                <input type="file" name="WidgetImage" data-jfiler-changeInput='<div class="btn btn-default"><div><i class="fa fa-upload"></i>&nbsp; Add Picture</div></div>' data-jfiler-extensions="jpg,jpeg,png" data-jfiler-caption="Only image files are allowed to be uploaded.">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-4">
                                                                <label class="control-label">Display Order</label>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <input class="form-control" maxlength="3" name="SortOrder" type="number" value="<?=$_POST['SortOrder']?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group mg-t-30">
                                                            <div class="col-md-4">
                                                            </div>
                                                            <div class="col-md-8">
                                                                <button class="btn btn-primary">
                                                                    <i class="fa fa-floppy-o"></i>&nbsp; Save Details
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">

                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <!------------------------------
                                            END OF CONTENT TAB
                                        ------------------------------->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>   <!-- container -->

                </div>   <!-- content -->
            </div>
            <!-- ==============================================================
                End of Right content page
            ============================================================== -->

            <?php include(_ROOT._AdminIncludesDir."footer.php"); ?>
        </div>

	</body>
</html>