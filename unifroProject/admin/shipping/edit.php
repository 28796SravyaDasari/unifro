<?php
    $SelectedPage = 'Shipping Management';

    if(is_numeric($_GET['id']))
    {
        $ShippingDetails = MysqlQuery("SELECT * FROM states WHERE StateID = '".$_GET['id']."' LIMIT 1");
        if(mysqli_num_rows($ShippingDetails) == 1)
        {
            $_POST = mysqli_fetch_assoc($ShippingDetails);
        }
        else
        {
            $_SESSION['AlertMessage'] = 'Invalid Access!';
            header('Location: /admin/shipping/');
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
            $("#ShippingForm").submit(function(e)
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
                                <div>

                                    <ul class="nav nav-tabs responsive-tabs" style="width: 100%;">
                                        <li class="active tab"><a><?=GetStateDetails($ID, 'Name')?></a></li>
                                    </ul>

                                    <div class="tab-content">
                                        <!------------------------------
                                            START OF CONTENT TAB
                                        ------------------------------->
                                        <div class="tab-pane active">
                                            <form action="/admin/ajax/shipping/save/" class="form-horizontal" id="ShippingForm">
                                                <input hidden="hidden" name="StateID" value="<?=$ID?>">
                                                <div class="clearfix">
                                                    <div class="col-md-7">
                                                        <div class="form-group">
                                                            <div class="col-md-4">
                                                                <label class="control-label required">Free Shipping on Order Amount</label>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <input class="form-control" maxlength="5" name="FreeShippingLimit" type="number" value="<?=$_POST['FreeShippingLimit']?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-4">
                                                                <label class="control-label">Shipping Charge</label>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <input class="form-control" maxlength="4" name="ShippingCharge" type="number" value="<?=$_POST['ShippingCharge']?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-4">

                                                            </div>
                                                            <div class="col-md-8">
                                                                <div class="custom-checkbox">
                                                                    <LABEL>
                                                                        <input name="ApplyToAll" type="checkbox" value="1">
                                                                        <span></span> Applicable to all States <span data-toggle="tooltip" title="If you tick the checkbox, the amount entered would be applicable to all the States."><i class="fa fa-info-circle"></i></span>
                                                                   </LABEL>
                                                                </div>
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