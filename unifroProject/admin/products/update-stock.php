<?php

    $response['status'] = 'error';

    if($_POST['section'] == 'info')
	{
	    if($_POST['ProductName'] == '')
        {
            $error['ProductName'] = 'Enter Product Name';
        }
        if($_POST['ShortDescription'] == '')
        {
            $error['ShortDescription'] = 'Enter Short Description';
        }
        if(!is_numeric($_POST['Color']))
        {
            $error['Color'] = 'Select Color';
        }
        if(!is_numeric($_POST['Rate']))
        {
            $error['Rate'] = 'Only numeric values are allowed';
        }
        if($_POST['Discount'] != '' && !is_numeric($_POST['Discount']))
        {
            $error['Discount'] = 'Only numeric values are allowed';
        }
        if(!is_numeric($_POST['TaxRate']))
        {
            $error['TaxRate'] = 'Only numeric values are allowed';
        }
        if(!isset($_POST['Status']))
        {
            $error['Status'] = 'Choose Status';
        }

        if(!isset($error))
        {
            $_POST['SKU'] = strtoupper( str_replace(' ', '-', $_POST['SKU']) );
            $_POST['Discount'] = $_POST['Discount'] == '' ? 0 : $_POST['Discount'];
            $ProductURL = MakeFriendlyURL($_POST['ProductName']);

            if(is_numeric($_POST['ProductID']))
            {
                $activity = 'Product Updated';
                $Desc = $_POST['ProductName'].' updated';
                $EditMode = true;
                $ID = $_POST['ProductID'];

                $res = MysqlQuery("UPDATE products SET ProductURL = '".$ProductURL."', ProductName = '".$_POST['ProductName']."', Color = '".$_POST['Color']."',
                            Rate = '".$_POST['Rate']."', Discount = '".$_POST['Discount']."', DiscountType = '".$_POST['DiscountType']."', TaxRate = '".$_POST['TaxRate']."',
                            ShortDescription = '".$_POST['ShortDescription']."', FullDescription = '".$_POST['FullDescription']."', Status = '".$_POST['Status']."',
                            UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE ProductID = '".$ID."' LIMIT 1");
            }
            else
            {
                MysqlQuery  ("  INSERT INTO products (
                                                        SKU, ProductURL, ProductName, ProductAlias, Color, Rate, Discount, DiscountType, TaxRate,
                                                        ShortDescription, FullDescription, Status, AddedBy, AddedOn )
                                VALUES (
                                '".$_POST['SKU']."', '".$ProductURL."', '".$_POST['ProductName']."', '".$_POST['ProductAlias']."', '".$_POST['Color']."', '".$_POST['Rate']."', '".$_POST['Discount']."',
                                '".$_POST['DiscountType']."', '".$_POST['TaxRate']."', '".$_POST['ShortDescription']."', '".$_POST['FullDescription']."', '".$_POST['Status']."', '".$AID."', '".time()."'
                                )
                            ");

                $ID = MysqlInsertID();
                $activity = 'Product Added';
                $Desc = $_POST['ProductName'].' added';
            }

    		if(MysqlAffectedRows() >= 0)
    		{
    		    MysqlQuery("DELETE FROM product_categories WHERE ProductID = '".$ID."'");

                if(MysqlAffectedRows() >= 0)
                {
                    $TargetTable = 'product_categories';
                    $fields = 'ProductID, CategoryID';

                    foreach($_POST['ParentID'] as $cid)
                    {
                        $ValuesToInsert[] = "'".$ID."', '".$cid."'";
                        $MappedCats[] = $cid;
                    }

                    $InsertedRows = BulkInsert($TargetTable, $fields, $ValuesToInsert);

                    if($InsertedRows > 0)
            		{
            		    $Desc = $Desc.' and mapped with Category ID: '.implode(', ', $MappedCats);
            		}
            		else
            		{
            		    $MappedCats = '<br><span class="text-danger">But failed to map the Product to Categories</span>';
            		}
                }
                else
                {
                    $response['message'] = 'Something went wrong! Please try again. [LN 200]';
                }

                if(!$EditMode && isset($error))
                {
                    MysqlQuery("DELETE FROM products WHERE ProductID = '".$ID."' LIMIT 1");
                    $response['error'] = $error;
                    $response['status'] = 'validation';
                }
                else
                {
                    RecordAdminActivity($activity, 'products', $ID, $Desc);

                    $response['status'] = 'success';
                    $response['message'] = 'Product Details Saved!';

                    if(!$EditMode)
                        $response['redirect'] = '/admin/products/edit/'.$ID;
                }
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
    elseif($_POST['section'] == 'mapping')
    {
        if(!is_numeric($_POST['ProductID']))
        {
            $response['message'] = 'Something went wrong! Please refresh the page and try again';
            echo json_encode($response);
            exit;
        }
        if(!isset($_POST['ParentID']))
        {
            $error['ParentID'] = 'Choose Category';
        }

        if(!isset($error))
        {
            MysqlQuery("DELETE FROM product_categories WHERE ProductID = '".$_POST['ProductID']."'");

            if(MysqlAffectedRows() >= 0)
            {
                $TargetTable = 'product_categories';
                $fields = 'ProductID, CategoryID';

                foreach($_POST['ParentID'] as $cid)
                {
                    $ValuesToInsert[] = "'".$_POST['ProductID']."', '".$cid."'";
                    $MappedCats[] = $cid;
                }

                $InsertedRows = BulkInsert($TargetTable, $fields, $ValuesToInsert);

                if($InsertedRows > 0)
        		{
        		    $MappedCats = 'Product ID: '.$_POST['ProductID'].' mapped with Category ID: '.implode(', ', $MappedCats);

                    RecordAdminActivity('Product Mapped to Category', 'product_categories', $_POST['ProductID'], $MappedCats);

                    $response['status'] = 'success';
                    $response['message'] = 'Product Mapped Successfully!';
        		}
        		else
        		{
        		    $response['message'] = 'Something went wrong! Please try again. [LN 100]';
        		}
            }
            else
            {
                $response['message'] = 'Something went wrong! Please try again. [LN 200]';
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