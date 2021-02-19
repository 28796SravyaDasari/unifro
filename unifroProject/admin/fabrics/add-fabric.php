<?php
    $SelectedPage = 'Fabrics';

    if($EditMode)
    {
        $CategoryDetails = MysqlQuery("SELECT * FROM fabrics WHERE FabricID = '".$_GET['id']."' LIMIT 1");
        if(mysqli_num_rows($CategoryDetails) == 1)
        {
            $_POST = mysqli_fetch_assoc($CategoryDetails);
            $_POST['FabricWash'] = json_decode($_POST['FabricWash']);
        }
        else
        {
            $_SESSION['AlertMessage'] = 'Invalid Access!';
            header('Location: /admin/fabrics/');
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

            $('input[name=FabricImage]').filer(options);

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
                                            Back to All Fabrics
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
                                    <form action="/admin/ajax/add-fabric/" class="form-horizontal" id="FabricForm">
                                        <input hidden="hidden" name="FabricID" value="<?=$_GET['id']?>">
                                        <div class="clearfix">
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Category</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <select class="selectric" name="CategoryID">
                                                            <option value="">Choose Category</option>
                                                            <?=GetFabricCategories('1', '')?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Company Name</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="100" name="CompanyName" required type="text" value="<?=$_POST['CompanyName']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Industry</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <select class="selectric" name="Industry">
                                                            <option value="">Choose Industry</option>
                                                            <option<?=$_POST['Industry'] == 'Corporate' ? ' selected' : ''?> value="Corporate">Corporate</option>
                                                            <option<?=$_POST['Industry'] == 'School' ? ' selected' : ''?> value="School">School</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Fabric Name</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="100" name="FabricName" required type="text" value="<?=$_POST['FabricName']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Fabric Code</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="50" name="FabricCode" required type="text" value="<?=$_POST['FabricCode']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Fabric Color</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="30" name="FabricColor" required type="text" value="<?=$_POST['FabricColor']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Fabric Pattern</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="30" name="FabricPattern" required type="text" value="<?=$_POST['FabricPattern']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Fabric Price</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">Rs.</span>
                                                            <input class="form-control" min="1" name="FabricPrice" required type="number" value="<?=$_POST['FabricPrice']?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Fabric Blend</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <select class="selectric" name="FabricBlend" required>
                                                            <option value="">Choose Blend</option>
                                                            <option<?=$_POST['FabricBlend'] == '100% Cotton' ? ' selected' : ''?> value="100% Cotton">100% Cotton</option>
                                                            <option<?=$_POST['FabricBlend'] == '100% Polyester' ? ' selected' : ''?> value="100% Polyester">100% Polyester</option>
                                                            <option<?=$_POST['FabricBlend'] == 'Polyester Cotton' ? ' selected' : ''?> value="Polyester Cotton">Polyester Cotton</option>
                                                            <option<?=$_POST['FabricBlend'] == 'Cotton Spandex' ? ' selected' : ''?> value="Cotton Spandex">Cotton Spandex</option>
                                                            <option<?=$_POST['FabricBlend'] == 'Cotton Lycra' ? ' selected' : ''?> value="Cotton Lycra">Cotton Lycra</option>
                                                            <option<?=$_POST['FabricBlend'] == 'Polyester Spandex' ? ' selected' : ''?> value="Polyester Spandex">Polyester Spandex</option>
                                                            <option<?=$_POST['FabricBlend'] == 'Polyester Lycra' ? ' selected' : ''?> value="Polyester Lycra">Polyester Lycra</option>
                                                            <option<?=$_POST['FabricBlend'] == 'Polyester Viscose' ? ' selected' : ''?> value="Polyester Viscose">Polyester Viscose</option>
                                                            <option<?=$_POST['FabricBlend'] == 'Spun Polyester' ? ' selected' : ''?> value="Spun Polyester">Spun Polyester</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Fabric Composition</label>
                                                        <div class="help-icon" title="e.g. 100% Cotton"><i class="fa fa-question-circle"></i></div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="30" name="FabricComposition" type="text" value="<?=$_POST['FabricComposition']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Fabric Count</label>
                                                        <div class="help-icon" title="e.g. 60/1 x 60/1"><i class="fa fa-question-circle"></i></div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="20" name="FabricCount" type="text" value="<?=$_POST['FabricCount']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Fabric GSM</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="20" name="FabricGSM" required type="text" value="<?=$_POST['FabricGSM']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Fabric Weave</label>
                                                        <div class="help-icon" title="e.g. Poplin, Oxford, etc"><i class="fa fa-question-circle"></i></div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="20" name="FabricWeave" type="text" value="<?=$_POST['FabricWeave']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Knit Type</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <select class="selectric" name="KnitType">
                                                            <option value="">Choose Knit Type</option>
                                                            <option<?=$_POST['KnitType'] == 'Pique' ? ' selected' : ''?> value="Pique">Pique</option>
                                                            <option<?=$_POST['KnitType'] == 'Single Jersey l, Sinker' ? ' selected' : ''?> value="Single Jersey l, Sinker">Single Jersey l, Sinker</option>
                                                            <option<?=$_POST['KnitType'] == 'French Terry' ? ' selected' : ''?> value="French Terry">French Terry</option>
                                                            <option<?=$_POST['KnitType'] == 'Looper' ? ' selected' : ''?> value="Looper">Looper</option>
                                                            <option<?=$_POST['KnitType'] == 'Rice Knit' ? ' selected' : ''?> value="Rice Knit">Rice Knit</option>
                                                            <option<?=$_POST['KnitType'] == 'Interlock' ? ' selected' : ''?> value="Interlock">Interlock</option>
                                                            <option<?=$_POST['KnitType'] == 'Lacoste' ? ' selected' : ''?> value="Lacoste">Lacoste</option>
                                                            <option<?=$_POST['KnitType'] == 'Twill' ? ' selected' : ''?> value="Twill">Twill</option>
                                                            <option<?=$_POST['KnitType'] == 'Ribs' ? ' selected' : ''?> value="Ribs">Ribs</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Fabric Wash</label>
                                                        <div class="help-icon" title="e.g. 60/1 x 60/1"><i class="fa fa-question-circle"></i></div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="custom-checkbox FabricWash">
                                                            <?php
                                                            foreach($FabricWashIcons as $id => $arr)
                                                            {
                                                                echo '<label>
                                                                        <input'.(in_array($id, $_POST['FabricWash']) ? ' checked' : '').' type="checkbox" name="FabricWash[]" value="'.$id.'" /><span></span>&nbsp; <img class="icon" src="'.$arr['ImagePath'].'" data-toggle="tooltip" title="'.$arr['Title'].'" />
                                                                    </label>';
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Fabric Picture</label>
                                                        <div class="help-icon" title="Add Fabric Swatch"><i class="fa fa-question-circle"></i></div>
                                                    </div>
                                                    <div class="col-md-8">

                                                        <?php if($_POST['FabricImage'] != '') { ?>
                                                            <ul class="jFiler-item-list">
                                                                <li class="jFiler-item">
                                                                    <div class="jFiler-item-inner">
                                                                        <div class="jFiler-item-thumb">
                                                                            <img class="img-thumbnail" src="<?=_FabricImageDir.$_POST['FabricImage']?>" alt="" />
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        <?php } ?>

                                                        <input type="file" name="FabricImage" data-jfiler-changeInput='<div class="btn btn-default"><div><i class="fa fa-upload"></i>&nbsp; <?=$EditMode?'Change':'Add'?> Swatch</div></div>' data-jfiler-extensions="jpg,png" data-jfiler-caption="Only JPG & PNG files are allowed to be uploaded.">
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
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Mark as default</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="custom-checkbox">
                                                            <LABEL>
                                                                <input<?=$_POST['DefaultFabric'] == '1' ? ' checked' : ''?> name="DefaultFabric" type="checkbox" value="1">
                                                                <span></span>
                                                           </LABEL>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group mg-t-30">
                                                    <div class="col-md-4">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <button class="btn btn-primary btn-block">
                                                            <i class="fa fa-floppy-o"></i>&nbsp; Save Details
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

	</body>
</html>