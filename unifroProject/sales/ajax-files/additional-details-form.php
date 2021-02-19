<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    //If additional details already saved, then fetch it
    $GetAdditionalDetails = MysqlQuery("SELECT AdditionalDetails FROM client_shopping_cart WHERE CartID = '".$_POST['id']."' AND ClientID = '".$_SESSION['ClientID']."' LIMIT 1");
    $GetAdditionalDetails = mysqli_fetch_assoc($GetAdditionalDetails);

    if($GetAdditionalDetails['AdditionalDetails'] != '')
    {
        $AdditionalDetails = json_decode($GetAdditionalDetails['AdditionalDetails'], true);
    }

    $response['status'] = 'error';
    ob_start();
?>

    <form action="/ajax/sales/save-additional-details/" id="AdditionalDetailsForm">
        <input type="hidden" name="Option" value="<?=$_POST['option']?>" />

        <?php
        if($_POST['option'] == 'Full Pant')
        {
            ?>
                <input type="hidden" name="CartID" value="<?=$_POST['id']?>" />

                <div class="row mg-b-15">
                    <div class="col-md-12">
                        <div class="custom-radiobutton">
                            <label>
                                <input<?=$AdditionalDetails['FullPantAttributes'] == 'Slim Fit' ? ' checked' : ''?> type="radio" name="FullPantAttributes" value="Slim Fit"><span></span> Slim Fit
                            </label>
                            <label>
                                <input<?=$AdditionalDetails['FullPantAttributes'] == 'Regular Fit' ? ' checked' : ''?> type="radio" name="FullPantAttributes" value="Regular Fit"><span></span> Regular Fit
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control" name="Comments" rows="3" placeholder="Type your comments here"><?=$AdditionalDetails['Comments']?></textarea>
                    </div>
                </div>

                <div class="mg-t-30 pd-b-30">
                    <button type="button" class="btn btn-primary" onclick="AddCartAttribute(this, true)">Save Details</button>
                </div>

            <?php
        }
        elseif($_POST['option'] == 'Pinafores' || $_POST['option'] == 'Skirt')
        {
            ?>
                <input type="hidden" name="CartID" value="<?=$_POST['id']?>" />

                <div class="row mg-b-15">
                    <div class="col-md-12">
                        <div class="font15 mg-b-5">Do you require Internal Pockets?</div>
                        <div class="custom-radiobutton">
                            <label>
                                <input<?=$AdditionalDetails['Attributes'] == 'Yes' ? ' checked' : ''?> type="radio" name="Attributes" value="Yes"><span></span> Yes
                            </label>
                            <label>
                                <input<?=$AdditionalDetails['Attributes'] == 'No' ? ' checked' : ''?> type="radio" name="Attributes" value="No"><span></span> No
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control" name="Comments" rows="3" placeholder="Type your comments here"><?=$AdditionalDetails['Comments']?></textarea>
                    </div>
                </div>

                <div class="mg-t-30 pd-b-30">
                    <button type="button" class="btn btn-primary" onclick="AddCartAttribute(this, true)">Save Details</button>
                </div>

            <?php
        }
    ?>

    </form>

    <?php

    $response['status'] = 'success';
    $response['html'] = ob_get_clean();

    echo json_encode($response);

    ?>