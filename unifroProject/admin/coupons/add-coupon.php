<?php
    $SelectedPage = 'Coupons';

    if($ID)
    {
        $CouponDetails = MysqlQuery("SELECT * FROM coupons WHERE CouponID = '".$ID."' LIMIT 1");
        if(mysqli_num_rows($CouponDetails) == 1)
        {
            $_POST = mysqli_fetch_assoc($CouponDetails);
            $_POST['Expiry'] = date('d-M-Y h:i K', $_POST['Expiry']);
        }
        else
        {
            $_SESSION['AlertMessage'] = 'Invalid Access!';
            header('Location: /admin/coupons/');
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
            $('.datepicker').flatpickr({
                dateFormat: 'd-M-Y h:i K',
                enableTime: true,
            });

            $("#CouponForm").submit(function(e)
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
                                            Back to All Coupons
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
                                    <form action="/admin/ajax/add-coupon/" class="form-horizontal" id="CouponForm">
                                        <input hidden="hidden" name="CouponID" value="<?=$_GET['id']?>">
                                        <div class="clearfix">
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Coupon Code</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control text-upper" maxlength="18" name="CouponCode" required type="text" value="<?=$_POST['CouponCode']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Discount</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="input-group">
                                                            <input class="form-control" maxlength="50" name="Discount" step="0.01" type="number" value="<?=$_POST['Discount']?>">
                                                            <div class="input-group-addon select">
                                                                <select name="DiscountType" onchange="ToggleMaxDiscount(this)">
                                                                    <option<?=$_POST['DiscountType'] == '%' ? ' selected' : ''?> value="%">%</option>
                                                                    <option<?=$_POST['DiscountType'] == 'Rs' ? ' selected' : ''?> value="Rs">Rs</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group" id="MaxDiscount">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Max Discount</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="input-group">
                                                            <div class="input-group-addon">Rs.</div>
                                                            <input class="form-control" maxlength="5" name="MaxDiscount" type="number" value="<?=$_POST['MaxDiscount']?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label">Minimum Order Amount</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="input-group">
                                                            <div class="input-group-addon">Rs.</div>
                                                            <input class="form-control" maxlength="10" name="MinOrderAmount" type="number" value="<?=$_POST['MinOrderAmount']?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Expiry Date</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="input-group">
                                                            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                                            <input class="form-control datepicker" name="Expiry" required type="text" value="<?=$_POST['Expiry']?>">
                                                        </div>
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
                                                            <i class="fa fa-floppy-o"></i>&nbsp; Save Coupon
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