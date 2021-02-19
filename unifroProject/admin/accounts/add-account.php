<?php
    $SelectedPage = 'Admin Accounts';

    if($ID)
    {
        $AdminDetails = MysqlQuery("SELECT a.*, r.RoleID FROM admins a LEFT JOIN roles r ON r.RoleID = a.RoleID WHERE a.AdminID = '".$ID."' LIMIT 1");
        if(mysqli_num_rows($AdminDetails) == 1)
        {
            $_POST = mysqli_fetch_assoc($AdminDetails);

            if($_POST['Username'] == 'super_admin')
            {
                $_SESSION['AlertMessage'] = 'Not allowed to edit Super Admin details.';
                header('Location: /admin/accounts/');
                exit;
            }
        }
        else
        {
            $_SESSION['AlertMessage'] = 'Invalid Access!';
            header('Location: /admin/accounts/');
            exit;
        }
    }

    // Get Roles
    $Roles = MysqlQuery("SELECT * FROM roles WHERE Status = '1'");
    if(mysqli_num_rows($Roles) > 0)
    {
        for(; $row = mysqli_fetch_assoc($Roles);)
        {
            $RolesList .= '<option'.($_POST['RoleID'] == $row['RoleID'] ? ' selected' : '').' value="'.$row['RoleID'].'">'.$row['Role'].'</option>';
        }
    }
    else
    {
        $_SESSION['AlertMessage'] = 'No Roles Found! Please add Roles then create the admin account.';
        header('Location: /admin/roles/');
        exit;
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
            $("#AdminForm").submit(function(e)
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
                                    <form action="/admin/ajax/create-account/" class="form-horizontal" id="AdminForm">
                                        <input hidden="hidden" name="AdminID" value="<?=$_GET['id']?>">
                                        <div class="clearfix">
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Role</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <select class="selectric" name="Role">
                                                            <option value="">Select Role</option>
                                                            <?=$RolesList?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Admin Name</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="100" name="Name" required type="text" value="<?=$_POST['Name']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Email</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="email" class="form-control" name="Email" required value="<?=$_POST['Email']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Username</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="20" name="Username"<?=is_numeric($ID)?' readonly':''?> required type="text" value="<?=$_POST['Username']?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-4">
                                                        <label class="control-label required">Password</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input class="form-control" maxlength="20" name="Password" required type="Password" value="<?=$_POST['Password']?>">
                                                    </div>
                                                </div>

                                                <div class="form-group mg-t-30">
                                                    <div class="col-md-4">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <button class="btn btn-primary btn-block">
                                                            <i class="fa fa-floppy-o"></i>&nbsp; <?=is_numeric($_GET['id'])?'Update Details':'Create Account'?>
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