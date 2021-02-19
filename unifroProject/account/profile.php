<?php
    include_once("../include-files/autoload-server-files.php");
    CheckLogin();

    $ActivePage = 'My Profile';

    $GetStateList = MysqlQuery("SELECT StateID, Name FROM states WHERE Status = '1' ORDER BY Name");
    $StateList = '<option value="">Select State</option>';
    for(; $row = mysqli_fetch_assoc($GetStateList);)
    {
        $StateList .= '<option'.($MemberDetails['State'] == $row['StateID'] ? ' selected' : '').' value="'.$row['StateID'].'">'.$row['Name'].'</option>';
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <META name="robots" content="noindex,nofollow" />

        <title>My Profile - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            <?php
                echo 'GetCities('.$MemberDetails['State'].', '.$MemberDetails['City'].')';
            ?>

            $("#ProfileForm").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();

                AjaxResponse = AjaxFormSubmit(this);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        AlertBox(response.response_message, 'success', function(){ location.reload() });
                    }
                    else if(response.status == 'login')
                    {
                        AlertBox('Oops! Your session has expired! Please login again.', 'error', function(){ location.href = '/login/' } );
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

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">
            <div class="row">
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="/account/">My Account</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profile</li>
                    </ol>
                </nav>
            </div>
            <div class="row mg-t-30">

                <div class="col-sm-9">

                    <div class="font21 mg-b-20">Profile Details</div>
                    <form action="/ajax/customer/save-profile/" id="ProfileForm" style="max-width: 700px">

                        <div class="table-content">

                            <div class="row mg-b-15">
                                <div class="col-md-6">
                                    <LABEL class="required">First Name</LABEL>
                                    <input type="text" class="form-control" maxlength="50" name="FirstName" required value="<?=$MemberDetails['FirstName']?>">
                                </div>
                                <div class="col-md-6">
                                    <LABEL class="required">Last Name</LABEL>
                                    <input type="text" class="form-control" maxlength="50" name="LastName" required value="<?=$MemberDetails['LastName']?>">
                                </div>
                            </div>

                            <div class="row mg-b-15">
                                <div class="col-md-6">
                                    <LABEL class="required">Email</LABEL><br>
                                    <input type="email" class="form-control" maxlength="30" name="EmailID" value="<?=$MemberDetails['EmailID']?>">
                                </div>
                                <div class="col-md-6">
                                    <LABEL>Mobile</LABEL>
                                    <input type="number" class="form-control" maxlength="30" name="Mobile" value="<?=$MemberDetails['Mobile']?>">
                                </div>
                            </div>
                            <div class="row mg-b-15">
                                <div class="col-md-6">
                                    <LABEL>State</LABEL><br>
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
                            <div class="row mg-b-15">
                                <div class="col-md-12 custom-checkbox">
                                    <LABEL>
                                        <input type="checkbox"<?=$MemberDetails['Newsletter'] == 'y' ? ' checked' : ''?> name="Newsletter" value="y">
                                        <span></span> I would like to receive email notification about product updates.
                                    </LABEL>
                                </div>
                            </div>

                            <div class="mg-t-30 pd-b-30">
                                <button type="submit" class="btn btn-primary">Save Details</button>
                            </div>
                        </div>
                    </form>

                </div>      <!-- END OF COL -->

                <?php include_once(_ROOT."/account/account-common.php"); ?>

            </div>  <!-- END OF ROW -->

        </div>  <!-- END OF CONTAINER -->

    </body>
</html>