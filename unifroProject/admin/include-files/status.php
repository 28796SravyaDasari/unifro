<?php
	if(!isset($_COOKIE['asid']))
	{
		echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
		exit();
	}

    $response['status'] = 'error';

	if(is_numeric($_POST['id']) && $_POST['option'] != '')
	{
	    $activity = 'Status Update';

		if($_POST['option'] == 'Category')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'master_categories';
            $Desc = 'Category '.GetCategoryDetails($_POST['id'], 'CategoryTitle').' status has been '.$status;

			MysqlQuery("UPDATE master_categories SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE CategoryID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Fabric')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'fabrics';
            $Desc = 'Fabric '.GetFabricCodeByID($_POST['id']).' status has been '.$status;

            if($_POST['status'] == '1')
            {
                // FIRST CHECK IF FABRIC IMAGE IS UPLOADED BEFORE MAKING IT LIVE
                if(!mysqli_num_rows(MysqlQuery("SELECT FabricID FROM fabrics WHERE FabricID = '".$_POST['id']."' LIMIT 1")))
                {
                    $response['message'] = 'Please upload the Fabric Image to make it live';
                    echo json_encode($response);
                    exit;
                }
            }

			MysqlQuery("UPDATE fabrics SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE FabricID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Sales Agent')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'sales';
            $Desc = 'Account status of Sales Executive '.GetSalesAgentName($_POST['id']).' has been '.$status;

			MysqlQuery("UPDATE sales SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE SalesID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Product')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'products';
            $Desc = GetProductDetails($_POST['id'], 'ProductName').' status has been '.$status;

            if($_POST['status'] == '1')
            {
                // FIRST CHECK IF PRODUCT IMAGE IS UPLOADED BEFORE MAKING IT LIVE
                if(!mysqli_num_rows(MysqlQuery("SELECT ProductID FROM product_images WHERE ProductID = '".$_POST['id']."' AND DefaultImage = '1' LIMIT 1")))
                {
                    $response['message'] = 'Default Image not found for this product';
                    echo json_encode($response);
                    exit;
                }
            }

			MysqlQuery("UPDATE products SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE ProductID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Featured Product')
		{
		    $Desc = $_POST['status'] == '1' ? ' marked as Featured' : ' unmarked from Featured Products';
			$activity = 'Featured Product';
            $table = 'products';
            $Desc = GetProductDetails($_POST['id'], 'ProductName').$Desc;

			$res = MysqlQuery("UPDATE products SET Featured = '".$_POST['status']."' WHERE ProductID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Element')
		{
		    // Let's fetch the Element name by it's ID
            $ElementName = mysqli_fetch_assoc(MysqlQuery("SELECT c.CategoryTitle, e.ElementDisplayName FROM master_elements e LEFT JOIN master_categories c ON c.CategoryID = e.CategoryID WHERE e.ElementID = '".$_POST['id']."' LIMIT 1"));

            $ElementName = $ElementName['CategoryTitle'].' '.$ElementName['ElementDisplayName'];

		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'master_elements';
            $Desc = 'Element '.$ElementName.' has been '.$status;

			MysqlQuery("UPDATE master_elements SET ElementStatus = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE ElementID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Element Style')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'element_styles';
            $Desc = 'Element Style '.GetStyleNameByID($_POST['id']).' has been '.$status;

			MysqlQuery("UPDATE element_styles SET StyleStatus = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE StyleID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Button')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'master_buttons';
            $Desc = 'Button Status has been '.$status;

			MysqlQuery("UPDATE master_buttons SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE ButtonID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'states')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'states';
            $Desc = 'State '.GetStateDetails($_POST['id'], 'Name').' has been '.$status;

			MysqlQuery("UPDATE states SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE StateID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Customer')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'customers';
            $Name = GetMemberDetails($_POST['id'], true);
            $Name = $Name['FirstName'].' '.$Name['LastName'];
            $Desc = 'Account '.$status.' for customer '.$Name;

			MysqlQuery("UPDATE customers SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE MemberID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Client')
		{
		    if($_POST['status'] == '1')
            {
                $status = 'Activated';
                $_POST['status'] = 'y';
            }
            else
            {
                $status = 'Deactivated';
                $_POST['status'] = 'n';
            }

            $table = 'clients';
            $Name = GetClientDetails($_POST['id'], 'ClientName');
            $Desc = 'Account '.$status.' for client '.$Name;

			MysqlQuery("UPDATE clients SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE ClientID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Review')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'product_reviews';
            $Desc = 'Product review has been '.$status;

			MysqlQuery("UPDATE product_reviews SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE ID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Coupon')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'coupons';
            $Desc = 'Coupon code '.GetCouponDetails($_POST['id'], 'CouponCode').' has been '.$status;

			MysqlQuery("UPDATE coupons SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE CouponID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Role')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'roles';
            $Name = GetRoleName($_POST['id']);
            $Desc = 'Role name '.$Name.' has been '.$status;

			MysqlQuery("UPDATE roles SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE RoleID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Subscription')
		{
		    $status = $_POST['status'] == '1' ? 'Subscribed' : 'UnSubscribed';
            $table = 'product_reviews';
            $Desc = 'Email '.$status;

			MysqlQuery("UPDATE newsletter_subscription SET Subscribed = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE ID = '".$_POST['id']."' LIMIT 1");
		}
        elseif($_POST['option'] == 'Banner')
		{
		    $status = $_POST['status'] == '1' ? 'Activated' : 'Deactivated';
            $table = 'banners';
            $Desc = 'Banner '.$status;

			MysqlQuery("UPDATE banners SET Status = '".$_POST['status']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE BannerID = '".$_POST['id']."' LIMIT 1");
		}

		if(MysqlAffectedRows() >= 0)
		{
            RecordAdminActivity($activity, $table, $_POST['id'], $Desc);
            $response['status'] = 'success';
		}
		else
		{
		    $response['message'] = 'Something went wrong! Please try again.'.$res;
		}
	}
	else
	{
	    $response['message'] = 'Invalid Access!';
	}

    echo json_encode($response);
?>