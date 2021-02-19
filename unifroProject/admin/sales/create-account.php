<?php

    $SelectedPage = 'Sales Executives';

    $GetStateList = MysqlQuery("SELECT StateID, Name FROM states WHERE Status = '1' ORDER BY Name");
    $StateList = '<option value="">Select State</option>';
    for(; $row = mysqli_fetch_assoc($GetStateList);)
    {
        $StateList .= '<option'.($_POST['State'] == $row['StateID'] ? ' selected' : '').' value="'.$row['StateID'].'">'.$row['Name'].'</option>';
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">

        <title><?=$SelectedPage?> - Unifro</title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            $("#AddSalesForm").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();

                AjaxResponse = AjaxFormSubmit(this,'', false);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        AlertBox(response.response_message, 'success', function(){ location.href = response.redirect });
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
        });

        </script>
    </head>


    <body>

        <?php include_once(_ROOT._AdminIncludesDir."admin-common-scripts.php"); ?>

        <div id="wrapper">
            <?php include_once(_ROOT._AdminIncludesDir."admin-header.php"); ?>
            <?php include_once(_ROOT._AdminIncludesDir."admin-sidebar.php"); ?>

            <!-- ==============================================================
                Start of Right content here
            ============================================================== -->
            <div class="content-page">

                <div class="content">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12" style="max-width: 700px">
                                <div class="card-box">
                                    <div class="font21 mg-b-20">Create Sales Account</div>
                                    <form action="/admin/ajax/sales/add/" id="AddSalesForm">

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
                                                    <LABEL>Mobile</LABEL><br>
                                                    <input type="text" class="form-control" maxlength="30" name="Mobile" onkeypress="return StopNonInt(event)" value="<?=$_POST['Mobile']?>">
                                                </div>
                                            </div>
                                            <div class="row mg-b-15">
                                                <div class="col-md-6">
                                                    <LABEL class="required">State</LABEL><br>
                                                    <select class="selectric" name="State" onchange="GetCities(this.value)">
                                                        <?=$StateList?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <LABEL>City</LABEL><br>
                                                    <select class="selectric" name="City" id="City">
                                                        <option>Select State to load Cities</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <LABEL>Address</LABEL>
                                                <textarea class="form-control" name="Address" ShowCounter="250"><?=$_POST['Address']?></textarea>
                                            </div>
                                            <div class="row mg-b-15">
                                                <div class="col-md-6">
                                                    <LABEL class="required">Pincode</LABEL><br>
                                                    <input type="number" class="form-control" name="Pincode" required value="<?=$_POST['Pincode']?>">
                                                </div>
                                            </div>

                                            <div class="mg-t-30 pd-b-30">
                                                <button type="submit" class="btn btn-primary"><?=is_numeric($_GET['id'])?'Update Details':'Create Account'?></button>
                                                <a href="<?=GoToLastPage()?>" class="btn btn-danger">Cancel</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>