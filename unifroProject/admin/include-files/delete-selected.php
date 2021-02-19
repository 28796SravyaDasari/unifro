<?php
	if(!isset($_COOKIE['asid']))
	{
		echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
		exit();
	}

    $response['status'] = 'error';

    if(isset($_POST['FieldName']))
	{
	    $FieldName = $_POST['FieldName'];

	    if(isset($_POST[$FieldName]) && $_POST['option'] != '')
        {
            $ids = implode(',', $_POST[$FieldName]);

            if($_POST['option'] == 'Category')
    		{
    		    if(count($_POST[$FieldName]) > 1)
                {
                    $activity = 'Categories Deleted. IDs: '.$ids;
                }
                else
                {
                    $activity = 'Category Deleted. IDs: '.$ids;
                }

                /****************************ADD DELETE IMAGE SCRIPT HRE*************************************/

    			MysqlQuery("DELETE FROM master_categories WHERE CategoryID IN(".$ids.")");
    		}
            elseif($_POST['option'] == 'Roles')
    		{
    		    if(count($_POST[$FieldName]) > 1)
                {
                    $activity = 'Roles Deleted. IDs: '.$ids;
                }
                else
                {
                    $activity = 'Role Deleted. ID: '.$ids;
                }

                MysqlQuery("DELETE FROM roles WHERE RoleID IN(".$ids.")");
    		}
            elseif($_POST['option'] == 'Fabric')
    		{
    		    if(count($_POST[$FieldName]) > 1)
                {
                    $activity = 'Fabrics Deleted. IDs: '.$ids;
                }
                else
                {
                    $activity = 'Fabric Deleted. IDs: '.$ids;
                }

                // GET THE FABRIC SWATCH NAMES
                $images = MysqlQuery("SELECT FabricImage FROM fabrics WHERE FabricID IN(".$ids.")");
                if(mysqli_num_rows($images) > 0)
                {
                    for(;$file = mysqli_fetch_assoc($images);)
                    {
                       $imagefiles[] = $file['FabricImage'];
                    }
                    $ImageDir = _ROOT._FabricImageDir;
                }
    			MysqlQuery("DELETE FROM fabrics WHERE FabricID IN(".$ids.")");
    		}
            elseif($_POST['option'] == 'product')
    		{
    		    if(count($_POST[$FieldName]) > 1)
                {
                    $activity = 'Products Deleted. IDs: '.$ids;
                }
                else
                {
                    $activity = 'Product Deleted. IDs: '.$ids;
                }

                MysqlQuery("DELETE FROM products WHERE ProductID IN(".$ids.")");

                if(MysqlAffectedRows() >= 0)
                {
                    // GET THE PRODUCT IMAGE NAMES
                    $images = MysqlQuery("SELECT FileName FROM product_images WHERE ProductID IN(".$ids.")");
                    if(mysqli_num_rows($images) > 0)
                    {
                        for(;$file = mysqli_fetch_assoc($images);)
                        {
                           $imagefiles[] = $file['FileName'];
                        }
                        $ImageDir = _ROOT._ProductImageDir;
                        $ImageThumbDir = _ROOT._ProductImageThumbDir;
                    }

        		    // IF IMAGES FOUND THEN DELETE THEM
        		    if(isset($imagefiles))
                    {
                        foreach($imagefiles as $filename)
                        {
                            @unlink($ImageDir.$filename);
                            @unlink($ImageThumbDir.$filename);
                        }
                    }

                    MysqlQuery("DELETE FROM product_images WHERE ProductID IN(".$ids.")");
                    MysqlQuery("DELETE FROM product_stock WHERE ProductID IN(".$ids.")");
                    MysqlQuery("DELETE FROM product_categories WHERE ProductID IN(".$ids.")");
                    MysqlQuery("DELETE FROM product_combos WHERE ProductID IN(".$ids.")");
                }
    		}
            elseif($_POST['option'] == 'Banners')
    		{
    		    // GET THE BANNER IMAGE NAME
                $images = MysqlQuery("SELECT ImageName FROM banners WHERE BannerID IN(".$ids.")");
                if(mysqli_num_rows($images) > 0)
                {
                    for(;$file = mysqli_fetch_assoc($images);)
                    {
                       $imagefiles[] = $file['ImageName'];
                    }
                    $ImageDir = _ROOT._BannerDir;
                }

    		    // IF IMAGES FOUND THEN DELETE THEM
    		    if(isset($imagefiles))
                {
                    foreach($imagefiles as $filename)
                    {
                        @unlink($ImageDir.$filename);
                    }
                }

    		    if(count($_POST[$FieldName]) > 1)
                {
                    $activity = 'Banners Deleted. IDs: '.$ids;
                }
                else
                {
                    $activity = 'Banner Deleted. ID: '.$ids;
                }

                MysqlQuery("DELETE FROM banners WHERE BannerID IN(".$ids.")");
    		}

    		if(MysqlAffectedRows() >= 0)
    		{
                count($_POST[$FieldName]) > 1 ? RecordAdminActivity($activity, $_POST['option']) : RecordAdminActivity($activity, $_POST['option'], $ids);
                $response['status'] = 'success';
                $response['message'] = count($_POST[$FieldName]).' records deleted';

                foreach ($_POST[$FieldName] as $value)
                {
                    $rows[] = '#row'.$value;
                }
                $response['rows'] = implode(',',$rows);
    		}
    		else
    		{
    		    $response['message'] = 'Something went wrong! Please try again. [LN 80]';
    		}
        }
        else
        {
            $response['message'] = 'Please select the records to be deleted';
        }
	}
	else
	{
	    $response['message'] = 'Invalid Access!';
	}

    echo json_encode($response);
?>