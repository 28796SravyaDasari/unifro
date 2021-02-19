<?php
    include_once("../include-files/autoload-server-files.php");
    CheckLogin();

    if($ID)
    {
        $Addresses = MysqlQuery("SELECT a.*, s.Name, c.CityName FROM customer_delivery_addresses a
                                    LEFT JOIN cities c ON c.CityID = a.City
                                    LEFT JOIN states s ON s.StateID = a.State
                                    WHERE a.AddressID = '".$_GET['id']."' AND a.MemberID = '".$MemberID."' LIMIT 1");
        if(mysqli_num_rows($Addresses) > 0)
        {
            $_POST = mysqli_fetch_assoc($Addresses);
        }
    }

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

        <title>Add Delivery Address - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            <?php
            if($ID)
            {
                echo 'GetCities('.$_POST['State'].', '.$_POST['City'].')';
            }
            ?>

            $("#AddAddress").submit(function(e)
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


    <body class="bg-lightgray my-account">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-12" style="max-width: 700px">
                            <div class="card-box">
                                <div class="font21 mg-b-20">Address Details</div>
                                <form action="/ajax/customer/save-address/" id="AddAddress">
                                    <input type="hidden" name="AddressID" value="<?=$ID?>" />
                                    <input type="hidden" name="ReturnURL" value="<?=$ReturnURL?>" />

                                    <div class="table-content">

                                        <div class="row mg-b-15">
                                            <div class="col-md-12">
                                                <LABEL class="required">Contact Person Name</LABEL>
                                                <input type="text" class="form-control" maxlength="180" name="ContactName" required value="<?=$_POST['ContactName']?>">
                                            </div>
                                        </div>

                                        <div class="row mg-b-15">
                                            <div class="col-md-12">
                                                <LABEL>Mobile</LABEL><br>
                                                <input type="text" class="form-control" maxlength="30" name="Mobile" required onkeypress="return StopNonInt(event)" value="<?=$_POST['Mobile']?>">
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
                                                <LABEL class="required">City</LABEL><br>
                                                <select class="selectric" name="City" id="City">
                                                    <option>Select State to load Cities</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <LABEL class="required">Address</LABEL>
                                            <textarea class="form-control" name="Address" required ShowCounter="250"><?=$_POST['Address']?></textarea>
                                        </div>
                                        <div class="row mg-b-15">
                                            <div class="col-md-6">
                                                <LABEL class="required">Pincode</LABEL><br>
                                                <input type="number" class="form-control" name="Pincode" required value="<?=$_POST['Pincode']?>">
                                            </div>
                                        </div>

                                        <div class="mg-t-30 pd-b-30">
                                            <button type="submit" class="btn btn-primary"><?=is_numeric($_GET['id'])?'Update Details':'Add Address'?></button>
                                            <a href="<?=$CancelURL?>" class="btn btn-danger">Cancel</a>
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