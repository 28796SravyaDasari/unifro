<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

    if(isset($_POST['id']))
    {
        /*---------------------------------------------------------------
            Let's validate the cart & category id and send the appropriate form
        ----------------------------------------------------------------*/
        $Category = MysqlQuery("SELECT c.CategoryTitle, ct.Measurement FROM customer_shopping_cart ct
                                LEFT JOIN master_categories c ON c.CategoryID = ct.ProductID
                                WHERE ct.CartID = '".$_POST['CartID']."' AND ct.CustomerID = '".$MemberID."' AND ct.ProductID = '".$_POST['id']."' LIMIT 1");
        if(mysqli_num_rows($Category) == 1)
        {
            $Category           = mysqli_fetch_assoc($Category);
            $Measurement        = $Category['Measurement'];
            $Category           = $Category['CategoryTitle'];

            $response['title']  = 'Measurement for '.$Category;
            $response['html']   = '<form class="form-horizontal" id="MeasurementForm" onsubmit="return AddMeasurement(this, true)">
                                    <input type="hidden" name="CartID" value="'.$_POST['CartID'].'">';

            if($Measurement != '')
            {
                $_POST = json_decode($Measurement, true);
            }

            ob_start();

            if($Category == 'Full Pant' || $Category == 'Half Pant' || $Category == 'Track Pant' || $Category == 'Track Pant Half' || $Category == 'Pinafores' || $Category == 'Skirt')
            {
                ?>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Waist" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Waist" required placeholder="Waist in inches" value="<?=$_POST['Waist']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Length" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Length" required placeholder="Length in inches" value="<?=$_POST['Length']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Hip" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="HipWidth" required placeholder="Hip in inches" value="<?=$_POST['HipWidth']?>">
                        </div>
                    </div>

                <?php
            }
            elseif($Category == 'Shirt')
            {
                ?>
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-xs-12">Neck Size" :</label>
                        <div class="col-sm-9 col-xs-12">
                            <input type="number" class="form-control" name="NeckSize" required placeholder="Neck Size in inches" value="<?=$_POST['NeckSize']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Shoulder" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Shoulder" required placeholder="Shoulder Size in inches" value="<?=$_POST['Shoulder']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Chest" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Chest" required placeholder="Chest Size in inches" value="<?=$_POST['Chest']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Shirt Length" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="ShirtLength" required placeholder="Shirt Length in inches" value="<?=$_POST['ShirtLength']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Bicep" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Bicep" required placeholder="Bicep in inches" value="<?=$_POST['Bicep']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Sleeves" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Sleeves" required placeholder="Sleeves in inches" value="<?=$_POST['Sleeves']?>">
                        </div>
                    </div>

                <?php
            }
            elseif($Category == 'Ladies Shirt')
            {
                ?>
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-xs-12">Neck Size" :</label>
                        <div class="col-sm-9 col-xs-12">
                            <input type="number" class="form-control" name="NeckSize" required placeholder="Neck Size in inches" value="<?=$_POST['NeckSize']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Shoulder" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Shoulder" required placeholder="Shoulder Size in inches" value="<?=$_POST['Shoulder']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Chest" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Chest" required placeholder="Chest Size in inches" value="<?=$_POST['Chest']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Back Width" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="BackWidth" required placeholder="Back Width in inches" value="<?=$_POST['BackWidth']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Shirt Length" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="ShirtLength" required placeholder="Shirt Length in inches" value="<?=$_POST['ShirtLength']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Sleeves" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Sleeves" required placeholder="Sleeves in inches" value="<?=$_POST['Sleeves']?>">
                        </div>
                    </div>

                <?php
            }
            elseif($Category == 'T-Shirt')
            {
                ?>
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-xs-12">Neck Size" :</label>
                        <div class="col-sm-9 col-xs-12">
                            <input type="number" class="form-control" name="NeckSize" required placeholder="Neck Size in inches" value="<?=$_POST['NeckSize']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Shoulder" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Shoulder" required placeholder="Shoulder Size in inches" value="<?=$_POST['Shoulder']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Chest" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Chest" required placeholder="Chest Size in inches" value="<?=$_POST['Chest']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Back Width" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="BackWidth" required placeholder="Back Width in inches" value="<?=$_POST['BackWidth']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Shirt Length" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="ShirtLength" required placeholder="Shirt Length in inches" value="<?=$_POST['ShirtLength']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Bicep" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Bicep" required placeholder="Bicep in inches" value="<?=$_POST['Bicep']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Sleeves" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="Sleeves" required placeholder="Sleeves in inches" value="<?=$_POST['Sleeves']?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Side Slit" :</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="SideSlit" required placeholder="Side Slit in inches" value="<?=$_POST['SideSlit']?>">
                        </div>
                    </div>

                <?php
            }

            $response['html'] .=  ob_get_clean();

            $response['html'] .= '<div class="form-group">
                                    <div class="col-sm-offset-3">
                                        <div class="col-sm-12">
                                            <button class="btn btn-info">Save Measurement</button>
                                       </div>
                                    </div>
                                </div>';

            $response['html'] .= '</form>';

            $response['status'] = 'success';
        }
        else
        {
            $response['response_message'] = 'Invalid Access! [LN 100]';
        }
    }
    else
    {
        /*---------------------------------------------------------------
            SAVE MEASUREMENT
        ----------------------------------------------------------------*/

        // Let's check if Cart id is belongs to the member or not

        $Category = MysqlQuery("SELECT CartID FROM customer_shopping_cart WHERE CartID = '".$_POST['CartID']."' AND CustomerID = '".$MemberID."' LIMIT 1");
        if(mysqli_num_rows($Category) == 1)
        {
            foreach($_POST as $key => $value)
            {
                if(!is_numeric($value))
                {
                    $error[$key] = 'Enter a valid measurement';
                }

                if(!isset($error))
                {
                    $Measurements = json_encode($_POST);

                    MysqlQuery("UPDATE customer_shopping_cart SET Measurement = '".$Measurements."' WHERE CartID = '".$_POST['CartID']."' AND CustomerID = '".$MemberID."' LIMIT 1");

                    if(MysqlAffectedRows() >= 0)
                    {
                        $response['status'] = 'success';
                        $response['response_message'] = 'Measurements Saved Successfully!';
                    }
                    else
                    {
                        $response['response_message'] = 'Error Occurred! Please try again. [LN 100]';
                    }
                }
                else
                {
                    $response['error'] = $error;
                    $response['status'] = 'validation';
                }
            }
        }
        else
        {
            $response['response_message'] = 'Invalid Access! [LN 200]';
        }
    }

    echo json_encode($response);


?>