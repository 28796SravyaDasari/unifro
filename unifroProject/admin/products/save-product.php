<?php

    $response['status'] = 'error';

    if($_POST['section'] == 'info')
	{
	    $_POST['ParentID'] = array_values(array_filter($_POST['ParentID']));

	    if($_POST['ParentID'][0] == '')
        {
            $error['ParentID'] = 'Choose Category';
        }
	    if($_POST['ProductName'] == '')
        {
            $error['ProductName'] = 'Enter Product Name';
        }
        elseif(!is_numeric($_POST['ProductID']) && mysqli_num_rows(MysqlQuery("SELECT ProductID FROM products WHERE ProductName = '".$_POST['ProductName']."' LIMIT 1")) == 1)
        {
            $error['ProductName'] = 'Product with this name already exists!';
        }
        if($_POST['FullDescription'] == '')
        {
            $error['FullDescription'] = 'Enter the Description';
        }
        if(!is_numeric($_POST['Color']))
        {
            $error['Color'] = 'Select Color';
        }
        if(!is_numeric($_POST['Rate']))
        {
            $error['Rate'] = 'Only numeric values are allowed';
        }
        elseif($_POST['Rate'] < 0)
        {
            $error['Rate'] = 'Rate cannot be a negative value';
        }
        if(!is_numeric($_POST['Weight']))
        {
            $error['Weight'] = 'Only numeric values are allowed';
        }
        elseif($_POST['Weight'] < 0)
        {
            $error['Weight'] = 'Weight cannot be a negative value';
        }
        if($_POST['Discount'] != '' && !is_numeric($_POST['Discount']))
        {
            $error['Discount'] = 'Only numeric values are allowed';
        }
        if($_POST['Discount'] > 100 && $_POST['DiscountType'] == '%')
        {
            $error['Discount'] = 'Discount cannot be more than 100%';
        }
        if($_POST['Discount'] < 0)
        {
            $error['Discount'] = 'Discount cannot be a negative value';
        }
        if(!is_numeric($_POST['TaxRate']))
        {
            $error['TaxRate'] = 'Only numeric values are allowed';
        }
        if($_POST['TaxRate'] > 100)
        {
            $error['TaxRate'] = 'Tax Rate cannot be more than 100%';
        }
        if($_POST['TaxRate'] < 0)
        {
            $error['TaxRate'] = 'Tax Rate cannot be a negative value';
        }

        if(!isset($error))
        {
            //$CategoryURL = GenerateCategoryURL($_POST['ParentID'][0]);

            $_POST['SKU'] = strtoupper( str_replace(' ', '-', $_POST['SKU']) );
            $_POST['Discount'] = $_POST['Discount'] == '' ? 0 : $_POST['Discount'];
            $_POST['ProductName'] = trim($_POST['ProductName']);

            if(is_numeric($_POST['ProductID']))
            {
                $activity = 'Product Updated';
                $Desc = $_POST['ProductName'].' updated';
                $EditMode = true;
                $ID = $_POST['ProductID'];
                $ProductURL = MakeFriendlyURL('/buy/'.$_POST['ProductName'].'/'.$ID.'/');

                // Let's get the current price before updating the product details
                $CurrentPrice = mysqli_fetch_assoc(MysqlQuery("SELECT Rate, TaxRate FROM products WHERE ProductID = '".$ID."' LIMIT 1"));

                $res = MysqlQuery("UPDATE products SET ProductURL = '".$ProductURL."', ProductName = '".$_POST['ProductName']."', Color = '".$_POST['Color']."',
                            Rate = '".$_POST['Rate']."', Weight = '".$_POST['Weight']."', WeightUnit = '".$_POST['WeightUnit']."', Discount = '".$_POST['Discount']."',
                            DiscountType = '".$_POST['DiscountType']."', TaxRate = '".$_POST['TaxRate']."', FullDescription = '".$_POST['FullDescription']."',
                            UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE ProductID = '".$ID."' LIMIT 1");
            }
            else
            {
                $res = MysqlQuery  ("  INSERT INTO products (
                                                        ProductURL, ProductName, ProductAlias, Color, Rate, Weight, WeightUnit, Discount, DiscountType, TaxRate,
                                                        ShortDescription, FullDescription, Status, AddedBy, AddedOn )
                                VALUES (
                                '', '".$_POST['ProductName']."', '".$_POST['ProductAlias']."', '".$_POST['Color']."', '".$_POST['Rate']."', '".$_POST['Weight']."', '".$_POST['WeightUnit']."',
                                '".$_POST['Discount']."', '".$_POST['DiscountType']."', '".$_POST['TaxRate']."', '', '".$_POST['FullDescription']."', '0', '".$AID."', '".time()."'
                                        )
                                    ");

                $ID = MysqlInsertID();
                $activity = 'Product Added';
                $Desc = $_POST['ProductName'].' added';
            }

    		if(MysqlAffectedRows() >= 0)
    		{
    		    if(!$EditMode)
                {
                    $ProductURL = MakeFriendlyURL('/buy/'.$_POST['ProductName'].'/'.$ID.'/');

                    MysqlQuery("UPDATE products SET ProductURL = '".$ProductURL."' WHERE ProductID = '".$ID."' LIMIT 1");
                    if(MysqlAffectedRows() < 0)
                    {
                        MysqlQuery("DELETE FROM products WHERE ProductID = '".$ID."'");

                        $response['message'] = 'Something went wrong! Please refresh the page and try again [LN 100]';
                        echo json_encode($response);
                        exit;
                    }
                }

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

                    //If product Price or Tax rate is changed, then check this product in the Cart and mark as price updated
                    if($CurrentPrice['Rate'] != $_POST['Rate'] || $CurrentPrice['TaxRate'] != $_POST['TaxRate'])
                    {
                        //Update Customer Cart
                        MysqlQuery("UPDATE customer_shopping_cart SET UpdatePrice = '1' WHERE ProductID = '".$ID."' AND CustomData = ''");

                        //Update Client Cart
                        MysqlQuery("UPDATE client_shopping_cart SET UpdatePrice = '1' WHERE ProductID = '".$ID."' AND CustomData = ''");
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
    elseif($_POST['section'] == 'stock')
    {
        if(!is_numeric($_POST['ProductID']))
        {
            $response['message'] = 'Something went wrong! Please refresh the page and try again';
            echo json_encode($response);
            exit;
        }

        foreach($_POST['Stock'] as $size => $arr)
        {
            if($arr['SKU'] == '')
            {
                $error['Stock'][$size]['SKU'] = 'Enter SKU';
            }
            else
            {
                $ValuesToInsert[] = "'".$_POST['ProductID']."', '".strtoupper($size)."', '".strtoupper($arr['SKU'])."', '".$arr['Quantity']."', '".time()."'";
            }

            if(!is_numeric($arr['Quantity']))
            {
                $error['Stock'][$size]['Quantity'] = 'Enter Quantity';
            }
        }

        if(!isset($error))
        {
            MysqlQuery("DELETE FROM product_stock WHERE ProductID = '".$_POST['ProductID']."'");

            if(MysqlAffectedRows() >= 0)
            {
                $TargetTable = 'product_stock';
                $fields = 'ProductID, Size, SKU, Quantity, AddedOn';

                $InsertedRows = BulkInsert($TargetTable, $fields, $ValuesToInsert);

                if($InsertedRows > 0)
        		{
                    RecordAdminActivity('Product Stock Updated', 'product_stock', $_POST['ProductID'], JSONEncode($ValuesToInsert));

                    $response['status'] = 'success';
                    $response['message'] = 'Product Stock Updated!';
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
    elseif($_POST['section'] == 'seo')
    {
        if(!is_numeric($_POST['ProductID']))
        {
            $response['message'] = 'Something went wrong! Please refresh the page and try again';
            echo json_encode($response);
            exit;
        }

        if($_POST['MetaKeywords'] == '')
        {
            $error['MetaKeywords'] = 'Enter Meta Keywords';
        }
        if($_POST['MetaDescription'] == '')
        {
            $error['MetaDescription'] = 'Enter Meta Description';
        }
        if($_POST['MetaTitle'] == '')
        {
            $error['MetaTitle'] = 'Enter Meta Title';
        }

        if(!isset($error))
        {
            if(is_numeric($_POST['MetaID']))
            {
                $activity = 'Meta Data Updated';
                $Desc = $_POST['ProductName'].' meta content updated';
                $EditMode = true;
                $ID = $_POST['MetaID'];

                $res = MysqlQuery("UPDATE product_seo SET MetaKeywords = '".$_POST['MetaKeywords']."', MetaDescription = '".$_POST['MetaDescription']."', MetaTitle = '".$_POST['MetaTitle']."',
                                    MetaPageName = '".$_POST['MetaPageName']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE MetaID = '".$ID."' LIMIT 1");
            }
            else
            {
                $res = MysqlQuery  ("INSERT INTO product_seo ( ProductID, MetaKeywords, MetaDescription, MetaTitle, MetaPageName, AddedOn, AddedBy)
                                VALUES ('".$_POST['ProductID']."', '".$_POST['MetaKeywords']."', '".$_POST['MetaDescription']."', '".$_POST['MetaTitle']."', '".$_POST['MetaPageName']."', '".time()."', '".$AID."')
                            ");

                $ID = MysqlInsertID();
                $activity = 'Meta Data Added';
                $Desc = $_POST['ProductName'].' meta content added';
            }

            if(MysqlAffectedRows() >= 0)
            {
        		    RecordAdminActivity($activity, 'product_seo', $ID, $Desc);

                    $response['status'] = 'success';
                    $response['message'] = 'Product SEO Content Saved!';
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