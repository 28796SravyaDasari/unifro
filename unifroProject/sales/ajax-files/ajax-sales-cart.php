<?php

    $response['status'] = 'error';

    /*-----------------------------------------
        THIS SECTION IS FOR REMOVING CART ITEM
    ------------------------------------------*/

    if($_POST['option'] == 'remove')
    {
        if($_POST['id'] > 0)
        {
            MysqlQuery("DELETE FROM client_shopping_cart WHERE CartID = '".$_POST['id']."' LIMIT 1");

            if(MysqlAffectedRows() == 1)
            {
                // Remove product from the Cart Session
                unset($_SESSION['CartDetails'][$_SESSION['ClientID']][$_POST['id']]);

                $ProductsInCart--;
                $response['status']     = 'success';
            }
            else
            {
                $response['response_message']    = 'Error occurred! [LN 10]';
            }
        }
        else
        {
            $response['response_message'] = 'Something went wrong! Please refresh the page and try again. [LN 20]';
        }
    }

    /*---------------------------------------------------
        THIS IS FOR ADDING CUSTOMIZED PRODUCT TO THE CART
    ----------------------------------------------------*/
    elseif($_POST['data'] != '' && $_SESSION['ClientID'] > 0)
    {
        $_POST['data'] = GetCustomProductPrice($_POST['data']);
        $CategoryID = $_POST['data']['Selections']['CategoryID'];

        // GET CATEGORY URL
        $Category = MysqlQuery("SELECT CategoryTitle, CategoryURL, Size FROM master_categories WHERE CategoryID = '".$CategoryID."' LIMIT 1");
        $Category = mysqli_fetch_assoc($Category);
        $Size = json_decode($Category['Size'], true);

        $MemberID = $MemberID > 0 ? $MemberID : 0;

        $price = $_POST['data']['Selections']['Price']['TotalPrice'];
        $discount = 0;
        $FinalPrice = $price;


        $i = 0;
        foreach($Size as $key => $value)
        {
            if($i == 0)
                $CategorySize[$key] = 1;
            else
                $CategorySize[$key] = 0;
            $i++;
        }

        if($_POST['id'] > 0)
        {
            // Let's fetch the Quantity from the Cart
            $ProductQuantity = MysqlQuery("SELECT Size FROM client_shopping_cart WHERE CartID = '".$_POST['id']."' LIMIT 1");
            $ProductQuantity = mysqli_fetch_assoc($ProductQuantity)['Size'];
            $ProductQuantity = json_decode($ProductQuantity, true);

            foreach($ProductQuantity as $size => $qty)
            {
                $UpdateQty[$size] = $qty;
                $TotalCost = $TotalCost + ($FinalPrice * $qty);
            }

            $res = MysqlQuery("UPDATE client_shopping_cart SET CustomData = '".json_encode($_POST['data'])."', GrossPrice = '".$price."', FinalPrice = '".$FinalPrice."', TotalCost = '".$TotalCost."', UpdatedOn = '".time()."' WHERE CartID = '".$_POST['id']."' LIMIT 1");

            $Activity = '<div class="alertbox-success">Product Updated!</div>';
        }
        else
        {
            $res = MysqlQuery("INSERT INTO client_shopping_cart (SessionID, SalesID, ClientID, ProductID, ProductName, CustomData, Size, GrossPrice, Discount, FinalPrice, TotalCost,
                                                                TaxRate, AddedOn)
            VALUES ('".$CartSessionID."', '".$MemberID."', '".$_SESSION['ClientID']."', '".$CategoryID."', '".addslashes($Category['CategoryTitle'])."', '".json_encode($_POST['data'])."',
                    '".json_encode($CategorySize)."', '".$price."', '".$discount."', '".$FinalPrice."', '".$FinalPrice."', '"._CustomTaxRate."', '".time()."')");

            $Activity = '<div class="alertbox-success">Product Added!</div>';
        }

        if(MysqlAffectedRows() == 1)
        {
            $ProductsInCart++;

        	$response['status'] = 'success';
            $response['redirect'] = $Category['CategoryURL'];
            $response['response_message'] = $Activity;
            $response['count'] = $ProductsInCart;
            $response['btn1fn'] = '/sales/clients/'.$_SESSION['ClientID'].'/';
        }
        else
        {
        	$response['status']             = 'error';
            $response['response_message']   = 'Error occurred! LN 90';
        }
    }

    /*---------------------------------------------------
        THIS IS FOR ADDING READYMADE PRODUCT TO THE CART
    ----------------------------------------------------*/
    elseif(!isset($_POST['data']) && $_SESSION['ClientID'] > 0 && $_POST['pid'] > 0)
    {
        // LETS VALIDATE THE PRODUCT ID
        $ValidateID = MysqlQuery("SELECT c.CategoryID, p.* FROM products p LEFT JOIN product_categories c ON c.ProductID = p.ProductID WHERE p.ProductID = '".$_POST['pid']."' LIMIT 1");
        if(mysqli_num_rows($ValidateID) == 1)
    	{
    	    // Check if Stock is available
            if(mysqli_num_rows(MysqlQuery("SELECT ProductID FROM products WHERE ProductID = '".$_POST['pid']."' AND Stock = '1' LIMIT 1")) == 0)
            {
                $response['response_message'] = 'Oops! The product is sold out.';
                echo json_encode($response);
                exit;
            }

    	    $ProductDetails = mysqli_fetch_assoc($ValidateID);
            $CategoryID     = $ProductDetails['CategoryID'];

            // GET CATEGORY URL
            $Category = MysqlQuery("SELECT CategoryTitle, CategoryURL, Size FROM master_categories WHERE CategoryID = '".$CategoryID."' LIMIT 1");
            $Category = mysqli_fetch_assoc($Category);
            $Size = json_decode($Category['Size'], true);

            $i = 0;
            foreach($Size as $value)
            {
                if($i == 0)
                    $CategorySize[$value] = 1;
                else
                    $CategorySize[$value] = 0;

                $i++;
            }

            // CALCULATE PRICE AND DISCOUNT
            
            if($ProductDetails['DiscountType'] == '%')
            {
                $DiscountPrice = round(($ProductDetails['Rate'] * $ProductDetails['Discount']) / 100);
                $FinalPrice = $ProductDetails['Rate'] - $DiscountPrice;
            }
            else
            {
                $DiscountPrice = $ProductDetails['Discount'];
                $FinalPrice = $ProductDetails['Rate'] - $DiscountPrice;
            }

            $res = MysqlQuery("INSERT INTO client_shopping_cart (SessionID, SalesID, ClientID, ProductID, ProductName, CustomData, Size, GrossPrice, Discount, FinalPrice,
                                                                TotalCost, TotalDiscount, TaxRate, AddedOn)
            VALUES ('".$CartSessionID."', '".$MemberID."', '".$_SESSION['ClientID']."', '".$_POST['pid']."', '".addslashes($ProductDetails['ProductName'])."', '', '".json_encode($CategorySize)."', '".$ProductDetails['Rate']."', '".$DiscountPrice."', '".$FinalPrice."', '".$FinalPrice."', '".$DiscountPrice."', '".$ProductDetails['TaxRate']."', '".time()."')");

            if(MysqlAffectedRows() == 1)
            {
            	$response['status'] = 'success';
                $response['btn1fn'] = '/sales/clients/'.$_SESSION['ClientID'].'/';

                $response['response_message'] = '<div class="alertbox-success">Product Added!</div>';
            }
            else
            {
            	$response['status']             = 'error';
                $response['response_message']   = 'Error occurred! LN 130'.$res;
            }
    	}
        else
        {
            $response['status']             = 'error';
            $response['response_message']   = 'Error occurred! LN 140';
        }
    }

    /*-------------------------------------------------------------------
        THIS SECTION IS FOR UPDATING THE QTY & PRICE FOR THE CART PRODUCT
    --------------------------------------------------------------------*/
    elseif($_POST['CartID'] > 0 && isset($_POST['Quantity']))
    {
        // LETS VALIDATE THE CART ID AND GET THE CART DETAILS

        $CartDetails = MysqlQuery("SELECT * FROM client_shopping_cart WHERE CartID = '".$_POST['CartID']."' AND ClientID = '".$_POST['ClientID']."' LIMIT 1");
        if(mysqli_num_rows($CartDetails) == 1)
        {
            $CartDetails = mysqli_fetch_assoc($CartDetails);

            foreach($_POST['Quantity'] as $size => $qty)
            {
                if($qty == '')
                {
                    $response['response_message']   = 'Quantity for '. $size.' size cannot be blank. Set as "0" if do not want any quantity';
                    echo json_encode($response);
                    exit;
                }
                else
                {
                    $UpdateQty[$size] = $qty;
                }
            }

            $TotalQty = array_sum($UpdateQty);
            $TotalCost = $TotalQty * $CartDetails['FinalPrice'];
            $TotalDiscount = $CartDetails['Discount'] * $TotalQty;
            $UpdateQty = JSONEncode($UpdateQty);

            $res = MysqlQuery("UPDATE client_shopping_cart SET Size = '".$UpdateQty."', TotalCost = '".$TotalCost."', TotalDiscount = '".$TotalDiscount."', UpdatedOn = '".time()."'
                                WHERE CartID = '".$_POST['CartID']."' AND ClientID = '".$_POST['ClientID']."' LIMIT 1");

            if(MysqlAffectedRows() == 1)
            {
                $response['CartTotal'] = 0;

                // LETS GET THE TOTAL COST OF ALL THE PRODUCTS ADDED IN CART
                $CartTotal = MysqlQuery("SELECT TotalCost FROM client_shopping_cart WHERE ClientID = '".$_POST['ClientID']."'");
                for(; $total = mysqli_fetch_assoc($CartTotal) ;)
                {
                    $response['CartTotal'] = $response['CartTotal'] + $total['TotalCost'];
                }

                $Desc = 'Cart Quantity for '.$CartDetails['ProductName'].' has been updated for Client: '.GetClientDetails($_POST['ClientID'], 'ClientName').'. Quantity: '.$UpdateQty.', Total Cost: '.$TotalCost;

                RecordSalesActivity('Cart Updated', 'client_shopping_cart', $_POST['CartID'], $Desc);
            	$response['status'] = 'success';
            	//$response['ProductTotal'] = FormatAmount($TotalCost);
            	//$response['CartTotal'] = FormatAmount($response['CartTotal']);
            }
            else
            {
                $response['response_message']   = 'Error occurred! LN 10';
            }
        }
        else
        {
            $response['response_message'] = 'Invalid Access! [LN 20]';
        }
    }
    else
    {
        $response['response_message'] = 'Invalid Access! [LN 30]';
    }

    echo json_encode($response);


?>