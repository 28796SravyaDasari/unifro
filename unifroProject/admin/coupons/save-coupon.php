<?php

    $response['status'] = 'error';

    if(isset($_POST['CouponCode']))
	{
	    if($_POST['CouponCode'] == '')
        {
            $error['CouponCode'] = 'Please enter Coupon Code';
        }
        if(!is_numeric($_POST['Discount']))
        {
            $error['Discount'] = 'Please enter the Discount';
        }
        elseif($_POST['Discount'] > 100 && $_POST['DiscountType'] == '%')
        {
            $error['Discount'] = 'Discount cannot be more than 100%';
        }
        if($_POST['MaxDiscount'] != '' && !is_numeric($_POST['MaxDiscount']))
        {
            $error['MaxDiscount'] = 'Enter a valid amount';
        }
        if($_POST['MinOrderAmount'] != '' && !is_numeric($_POST['MinOrderAmount']))
        {
            $error['MinOrderAmount'] = 'Enter a valid amount';
        }
        if(!strtotime($_POST['Expiry']))
        {
            $error['Expiry'] = 'Please select Coupon Expiry Date';
        }
        else
        {
            $Expiry = strtotime($_POST['Expiry']);
        }
        if($_POST['Status'] > 1)
        {
            $error['Status'] = 'Please select Coupon Status';
        }

        if(!isset($error))
        {
            $_POST['CouponCode'] = strtoupper($_POST['CouponCode']);

            if(is_numeric($_POST['CouponID']))
            {
                MysqlQuery("UPDATE coupons SET  CouponCode = '".$_POST['CouponCode']."', Discount = '".$_POST['Discount']."', DiscountType = '".$_POST['DiscountType']."',
                                                MaxDiscount = '".$_POST['MaxDiscount']."', MinOrderAmount = '".$_POST['MinOrderAmount']."', Expiry = '".$Expiry."',
                                                Status = '".$_POST['Status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE CouponID = '".$_POST['CouponID']."' LIMIT 1");

                $desc = 'Coupon details updated for coupon code: '.$_POST['CouponCode'];
            }
            else
            {
                $res = MysqlQuery("INSERT INTO coupons (   MemberID, CouponCode, Discount, DiscountType, MaxDiscount, MinOrderAmount, Expiry, Status, AddedOn, AddedBy  )
                                        VALUES  (   '0', '".$_POST['CouponCode']."', '".$_POST['Discount']."', '".$_POST['DiscountType']."', '".$_POST['MaxDiscount']."',
                                                    '".$_POST['MinOrderAmount']."', '".$Expiry."', '".$_POST['Status']."', '".time()."', '".$AID."' )
                        ");

                $desc = 'New Coupon added Coupon code: '.$_POST['CouponCode'];
            }

    		if(MysqlAffectedRows() >= 0)
    		{
                RecordAdminActivity('Coupon', 'coupons', $_POST['CouponID'], $desc);

                $response['status'] = 'success';
                $response['message'] = 'Coupon Details Saved!';
                $response['redirect'] = GoToLastPage();
    		}
    		else
    		{
    		    $response['message'] = 'Something went wrong! Please try again. [LN 100]'.$res;
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