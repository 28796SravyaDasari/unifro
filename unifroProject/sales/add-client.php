<?php
    include_once("../include-files/autoload-server-files.php");
    CheckLogin();

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

        <title>Add Client - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            $("#AddClientForm").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();

                AjaxResponse = AjaxFormSubmit(this);

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


    <body class="bg-lightgray my-account">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-12" style="max-width: 700px">
                            <div class="card-box">
                                <div class="font21 mg-b-20">Add Client</div>
                                <form action="/ajax/client/add/" id="AddClientForm">
                                    <input type="hidden" name="MemberID" value="<?=$_GET['id']?>" />

                                    <div class="table-content">

                                        <div class="row mg-b-15">
                                            <div class="col-md-12">
                                                <LABEL class="required">Client / Company Name</LABEL>
                                                <input type="text" class="form-control" maxlength="90" name="ClientName" value="<?=$_POST['ClientName']?>">
                                            </div>
                                        </div>

                                        <div class="row mg-b-15">
                                            <div class="col-md-6">
                                                <LABEL class="required">First Name</LABEL>
                                                <input type="text" class="form-control" maxlength="30" name="ContactFirstName" required value="<?=$_POST['ContactFirstName']?>">
                                            </div>
                                            <div class="col-md-6">
                                                <LABEL class="required">Last Name</LABEL>
                                                <input type="text" class="form-control" maxlength="30" name="ContactLastName" required value="<?=$_POST['ContactLastName']?>">
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
                                            <LABEL class="required">Address</LABEL>
                                            <textarea class="form-control" name="Address" ShowCounter="250"><?=$_POST['Address']?></textarea>
                                        </div>
                                        <div class="row mg-b-15">
                                            <div class="col-md-6">
                                                <LABEL class="required">Pincode</LABEL><br>
                                                <input type="number" class="form-control" name="Pincode" required value="<?=$_POST['Pincode']?>">
                                            </div>
                                        </div>

                                        <div class="mg-t-30 pd-b-30">
                                            <button type="submit" class="btn btn-primary"><?=is_numeric($_GET['id'])?'Update Details':'Add Client'?></button>
                                            <a href="<?=$MyAccountURL?>" class="btn btn-danger">Cancel</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>