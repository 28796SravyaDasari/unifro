<?php
	if(!isset($_COOKIE['asid']))
	{
		echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
		exit();
	}

    $response['status'] = 'error';

    if(isset($_POST['FreeShippingLimit']))
	{
	    if(!is_numeric($_POST['FreeShippingLimit']))
        {
            $error['FreeShippingLimit'] = 'Please enter the amount';
        }
        elseif($_POST['FreeShippingLimit'] < 0)
        {
            $error['FreeShippingLimit'] = 'Cannot be a negative value';
        }
        if(!is_numeric($_POST['ShippingCharge']))
        {
            $error['ShippingCharge'] = 'Please enter the amount';
        }
        elseif($_POST['ShippingCharge'] < 0)
        {
            $error['ShippingCharge'] = 'Cannot be a negative value';
        }

        if(!isset($error))
        {
            if(isset($_POST['ApplyToAll']))
            {
                MysqlQuery("UPDATE states SET FreeShippingLimit = '".$_POST['FreeShippingLimit']."', ShippingCharge = '".$_POST['ShippingCharge']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."'");

                $desc = 'Shipping details updated for all the States';
            }
            else
            {
                MysqlQuery("UPDATE states SET FreeShippingLimit = '".$_POST['FreeShippingLimit']."', ShippingCharge = '".$_POST['ShippingCharge']."',
                            UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE StateID = '".$_POST['StateID']."' LIMIT 1");

                $desc = 'Shipping details updated for '.GetStateDetails($_POST['StateID'], 'Name');
            }

    		if(MysqlAffectedRows() >= 0)
    		{
    		    $desc = $desc.' as FreeShippingLimit = '.$_POST['FreeShippingLimit'].', ShippingCharge = '.$_POST['ShippingCharge'];

                RecordAdminActivity('Shipping Details Updated', 'states', $_POST['StateID'], $desc);

                $response['status'] = 'success';
                $response['message'] = 'Shipping Details Saved!';
                $response['redirect'] = GoToLastPage();
    		}
    		else
    		{
    		    $response['message'] = 'Something went wrong! Please try again. [LN 100]';
    		}
        }
        else
        {
            $response['error'] = $error;
            $response['status'] = 'validation';
        }
	}
	else
	{
	    $response['message'] = 'Invalid Access!';
	}

    echo json_encode($response);
?>