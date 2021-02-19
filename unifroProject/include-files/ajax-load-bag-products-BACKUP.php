<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

        /*---------------------
            Get Cart Content
        ----------------------*/
        if($_POST['opt'] == 'cart')
        {
            // GET PRODUCTS CUSTOMIZED FOR CLIENT
            $Products = MysqlQuery("SELECT * FROM customer_shopping_cart WHERE SessionID = '".$CartSessionID."' ORDER BY CartID DESC");
            if(mysqli_num_rows($Products) > 0)
            {
                $TotalDiscount = 0;

                for($i = 0; $row = mysqli_fetch_assoc($Products); $i++)
                {
                    // CHECK IF PRICE OF THE PRODUCT HAS CHANGED, IF YES THEN UPDATE THE CART PRICE
                    if($row['UpdatePrice'] == 1)
                    {
                        if($row['CustomData'] != '')
                        {
                            $CustomData = GetCustomProductPrice($row['CustomData']);

                            $TotalCost = $CustomData['Selections']['Price']['TotalPrice'] * $row['Quantity'];
                            $TotalQty = array_sum($TotalQty);
                            $TotDiscount = $row['Discount'] * $TotalQty;

                            $res = MysqlQuery("UPDATE customer_shopping_cart SET CustomData = '".json_encode($CustomData)."', GrossPrice = '".$CustomData['Selections']['Price']['TotalPrice']."', FinalPrice = '".$CustomData['Selections']['Price']['TotalPrice']."', TotalCost = '".$TotalCost."', TotalDiscount = '".$TotDiscount."', UpdatePrice = '0', UpdatedOn = '".time()."' WHERE CartID = '".$row['CartID']."' LIMIT 1");

                            $row['CustomData'] = json_encode($CustomData);
                            $row['FinalPrice'] = $row['GrossPrice'] = $CustomData['Selections']['Price']['TotalPrice'];
                            $row['TotalCost'] = $TotalCost;
                            $row['TotalDiscount'] = $TotDiscount;
                            $row['UpdateInfo'] = '<div class="alert alert-danger md"><i class="fa fa-info-circle"></i> &nbsp;Product price has been changed!</div>';
                        }
                        else
                        {
                            /*---------------------------
                                For Readymade Products
                            ---------------------------*/
                            $UpdatedPrice = mysqli_fetch_assoc(MysqlQuery("SELECT Rate, Discount, DiscountType, TaxRate FROM products WHERE ProductID = '".$row['ProductID']."' LIMIT 1"));
                            $UpdatedPrice = CalculatePrice($UpdatedPrice);

                            $TotalCost = $UpdatedPrice['FinalPrice'] * $row['Quantity'];
                            $TotDiscount = $UpdatedPrice['DiscountPrice'] * $row['Quantity'];

                            $res = MysqlQuery("UPDATE customer_shopping_cart SET GrossPrice = '".$UpdatedPrice['Rate']."', FinalPrice = '".$UpdatedPrice['FinalPrice']."', TotalCost = '".$TotalCost."', TotalDiscount = '".$TotDiscount."', UpdatePrice = '0', UpdatedOn = '".time()."' WHERE CartID = '".$row['CartID']."' LIMIT 1");

                            $row['FinalPrice'] = $UpdatedPrice['FinalPrice'];
                            $row['GrossPrice'] = $UpdatedPrice['Rate'];
                            $row['TotalCost'] = $TotalCost;
                            $row['TotalDiscount'] = $TotDiscount;
                            $row['UpdateInfo'] = '<div class="alert alert-danger md"><i class="fa fa-info-circle"></i> &nbsp;Product price has been changed!</div>';
                        }
                    }

                    $ProductSize = array();
                    $ProductDetails = array();
                    $ButtonDetails = array();
                    $Options = array();
                    $SizeList = $QuantityList = '';
                    $QtyList[$row['CartID']] = array();

                    $TotalCost = $TotalCost + $row['TotalCost'];
                    $TotalDiscount = $TotalDiscount + $row['TotalDiscount'];

                    if($row['CustomData'] != '')
                    {
                        $FabricID = 0;

                        // GET THE CATEGORY URL
                        $GetCategoryDetails = MysqlQuery("SELECT CategoryID, CategoryURL, CategoryTitle, Size FROM master_categories WHERE CategoryID = '".$row['ProductID']."' LIMIT 1");
                        $GetCategoryDetails = mysqli_fetch_assoc($GetCategoryDetails);
                        $CategoryURL = $GetCategoryDetails['CategoryURL'];

                        $_SESSION['CartDetails']['Products'][$row['CartID']][$GetCategoryDetails['CategoryTitle']] = $GetCategoryDetails['CategoryTitle'];


                        $row['data'] = json_decode($row['CustomData'], true);
                        ksort($row['data']['Selections']['Styles']);

                        $ProductImage = '<img class="thumbnail" src="'.GetFabricDetails($row['data']['Selections']['FabricID'], 'FabricImage').'">';

                        $ProductDetails[] = '<ul class="custom-product-details">';

                        $ProductDetails[] = '<li class="bold text-inverse">Custom Designed '.$row['ProductName'].'</li>';
                        $ProductDetails[] = '<li><b>Main Fabric:</b> '.GetFabricDetails($row['data']['Selections']['FabricID'], 'FabricName').'</li>';


                        foreach($row['data']['Selections']['Styles'] as $element => $styleID)
                        {
                            if( isset($styleID['ButtonID']) )
                            {
                                $ButtonDetails[] = '<li>
                                                        <span class="button-image"
                                                        style="background:url('._ButtonsDir.GetButtonImage($styleID['ButtonID']).') no-repeat center; background-size:100%">&nbsp;</span><br>
                                                        '.$element.'
                                                     </li>';
                            }
                            else
                            {
                                $ElementValue = GetStyleNameByID($styleID['StyleID']);
                                $ProductDetails[] = GetStyleNameByID($styleID['StyleID']) != '' ? '<li>'.$element.' : '.$ElementValue.'</li>' : '';
                            }

                            if( strpos($element, 'Monogram') !== false && strtolower($ElementValue) != 'none' )
                            {
                                $_SESSION['CartDetails']['Products'][$row['CartID']]['Monogram'] = 'Monogram';
                            }

                            foreach($styleID as $subKey => $subArr)
                            {
                                if(is_array($subArr))
                                {
                                    foreach($subArr as $key => $subStyles)
                                    {
                                        if($key == 'Fabric' || $key == 'FabricID')
                                        {
                                            if(is_numeric($subStyles))
                                            {
                                                $FabricName =  GetFabricDetails($subStyles, 'FabricName');
                                                $key = '';
                                            }
                                            else
                                            {
                                                if($FabricID != $subStyles['FabricID'])
                                                {
                                                    $FabricName =  GetFabricDetails($subStyles['FabricID'], 'FabricName');
                                                    $key = $key == 'Fabric' ? '' : ' ('.$key.')';

                                                    $FabricID = $subStyles['FabricID'];
                                                }
                                            }

                                            $ProductDetails[] = $FabricName != '' ? '<li>- '.$subKey.$test.' : '.$FabricName.'</li>' : '';
                                        }
                                    }
                                }
                            }
                        }

                        $ProductDetails[] = '</ul>';
                        $ProductDetails = implode('', $ProductDetails);

                        if(count($ButtonDetails) > 0)
                        {
                            $ButtonDetails = '<ul class="custom-product-details">'.implode('', $ButtonDetails).'</ul>';
                        }
                        else
                        {
                            $ButtonDetails = '';
                        }

                        for($qc = 1; $qc <= 10; $qc++ )
                        {
                            $QuantityList .= '<option'.($qc == $row['Quantity'] ? ' selected' : '').' value="'.$qc.'">'.$qc.'</option>';
                        }

                        foreach(json_decode($GetCategoryDetails['Size'], true) as $size => $qty)
                        {
                            $SizeList .= '<option'.($size == $row['Size'] ? ' selected' : '').' value="'.$size.'">'.$size.'</option>';
                            $StockList[] = array($size => $qty);
                        }
                    }
                    else
                    {
                        // GET THE PRODUCT DEFAULT IMAGE
                        $GetDefaultImage = MysqlQuery("SELECT FileName FROM product_images
                                                       WHERE ProductID = '".$row['ProductID']."' AND DefaultImage = '1' LIMIT 1");
                        $GetDefaultImage = mysqli_fetch_assoc($GetDefaultImage)['FileName'];

                        $ProductDetails = '<div class="bold text-inverse">'.$row['ProductName'].'</div>';
                        $ProductImage = '<img class="thumbnail" src="'._ProductImageThumbDir.$GetDefaultImage.'">';
                        $ButtonDetails = '';

                        /*----------------------
                            GET PRODUCT STOCK
                        ----------------------*/
                        $GetProductStock = MysqlQuery("SELECT * FROM product_stock WHERE ProductID = '".$row['ProductID']."'");
                        $GetProductStock = MysqlFetchAll($GetProductStock);
                        $GetProductStock = array_reverse($GetProductStock);

                        foreach($GetProductStock as $item)
                        {
                            if($item['Quantity'] > 0)
                            {
                                if($item['Size'] == $row['Size'])
                                {
                                    for($qc = 1; $qc <= $item['Quantity']; $qc++ )
                                    {
                                        $QuantityList .= '<option'.($qc == $row['Quantity'] ? ' selected' : '').' value="'.$qc.'">'.$qc.'</option>';
                                    }
                                }
                                $SizeList .= '<option'.($item['Size'] == $row['Size'] ? ' selected' : '').' value="'.$item['Size'].'">'.$item['Size'].'</option>';

                                $StockList[] = array($item['Size'] => $item['Quantity']);
                            }
                        }
                    }

                        $QtyList[$row['CartID']] = $StockList;
                        $QtyObj = json_encode($QtyList);

                    ob_start();
                    ?>
                    <tr>
                        <td>
                            <!--
                            <div class="custom-checkbox">
                                <label>
                                    <input type="checkbox" name="CartID[]" value="<?=$row['CartID']?>" /><span></span>
                                </label>
                            </div>
                            -->
                            <script>
                                QtyList = '<?=$QtyObj?>';
                            </script>
                        </td>
                        <td><?=$ProductImage?></td>
                        <td>
                            <?=$row['UpdateInfo']?>
                            <?=$ProductDetails?>
                            <?=$ButtonDetails?>
                        </td>
                        <td>
                            <form action="<?=$AjaxCartURL?>" class="form-horizontal CartForm" id="CartForm<?=$row['CartID']?>">
                                <input type="hidden" name="CartID" value="<?=$row['CartID']?>" />

                                    <select name="Size" onchange="LoadQuantityList(this, <?=$row['CartID']?>)" style="width: 50px">
                                        <?=$SizeList?>
                                    </select>

                                    <select name="Quantity" style="width: 50px">
                                        <?=$QuantityList?>
                                    </select>

                            </form>
                        </td>
                        <td class="product-total bold font15"><?=FormatAmount($row['TotalCost'])?></td>
                        <td>
                            <ul class="cart-options">
                                <?php
                                if($row['CustomData'] != '')
                                {
                                    echo    '<li>
                                                <a class="btn btn-default" data-id="'.$GetCategoryDetails['CategoryID'].'" data-relation="'.$row['CartID'].'" onclick="AddMeasurement(this)">Add Measurement</a>
                                            </li>
                                            <li>
                                                <a class="btn btn-info" href="'.$CategoryURL.$row['CartID'].'"><i class="fa fa-cube"></i> &nbsp;Redesign</a>
                                            </li>';
                                }
                                ?>

                                <li>
                                    <a class="btn btn-success" onclick="$('#CartForm<?=$row['CartID']?>').submit()"><i class="fa fa-refresh"></i> &nbsp;Update</a>
                                </li>
                                <li>
                                    <a class="btn btn-white" data-id="<?=$row['CartID']?>" data-url="<?=$AjaxCartURL?>" onclick="RemoveCartItem(this)"><i class="fa fa-trash"></i> &nbsp;Remove</a>
                                </li>

                                <?php
                                if($GetCategoryDetails['CategoryTitle'] == 'Full Pant' || $GetCategoryDetails['CategoryTitle'] == 'Pinafores' || $GetCategoryDetails['CategoryTitle'] == 'Skirt')
                                {
                                    echo '<li>
                                            <a class="btn btn-danger" data-id="'.$row['CartID'].'" data-title="'.$GetCategoryDetails['CategoryTitle'].'" onclick="AddCartAttribute(this)">
                                                <i class="fa fa-plus"></i> &nbsp;Add Attributes
                                            </a>
                                        </li>';
                                }
                                ?>

                            </ul>
                        </td>
                    </tr>

                    <?php
                    $html[] = ob_get_clean();
                }

                $response['status'] = 'success';
                $response['html'] = implode(' ',$html);
            }
        }
        /*---------------------------
            Apply Coupon
        ----------------------------*/
        elseif($_POST['opt'] == 'coupon' && $_POST['code'] != '')
        {
            /*----------------------------------
                Validate the SessionID
            ----------------------------------*/
            $CartDetails = MysqlQuery("SELECT * FROM customer_shopping_cart WHERE SessionID = '".$CartSessionID."'");
            if(mysqli_num_rows($CartDetails) > 0)
            {
                for($TotalCost = 0;$product = mysqli_fetch_assoc($CartDetails);)
                {
                    $TotalCost = $TotalCost + $product['TotalCost'];

                    if($product['CustomData'] != '')
                    {
                        $LoadAdditionalData = true;
                    }
                }

                /*----------------------------------
                    Let's validate the coupon code
                ----------------------------------*/
                $CouponDetails = MysqlQuery("SELECT * FROM coupons WHERE CouponCode = '".$_POST['code']."' LIMIT 1");

                if(mysqli_num_rows($CouponDetails) == 1)
                {
                    $CouponDetails = mysqli_fetch_assoc($CouponDetails);

                    if($CouponDetails['Status'] == '1' && $CouponDetails['Expiry'] > time())
                    {
                        // Check minimum order amount is set
                        if($TotalCost < $CouponDetails['MinOrderAmount'])
                        {
                            $response['response_message'] = 'Coupon is applicable on min order of Rs. '.$CouponDetails['MinOrderAmount'];
                        }
                        else
                        {
                            if($CouponDetails['DiscountType'] == '%')
                            {
                                $_SESSION['CartDetails']['CouponDiscount'] = round($TotalCost * $CouponDetails['Discount'] / 100);
                            }
                            else
                            {
                                $_SESSION['CartDetails']['CouponDiscount'] = $CouponDetails['Discount'];
                            }

                            $response['status'] = 'success';
                        }
                    }
                    elseif($CouponDetails['Status'] == '2')
                    {
                        $response['response_message'] = 'Coupon already used!';
                    }
                    else
                    {
                        $response['response_message'] = 'Coupon Expired!';
                    }
                }
                else
                {
                    $response['response_message'] = 'Invalid Coupon Code';
                }
            }
            else
            {
                $response['response_message'] = 'Something went wrong! Please refresh the page and try again. [LN 20]';
            }
        }
        /*-------------------------------------------
            Get Order Summary for Selected Products
        -------------------------------------------*/
        elseif($_POST['opt'] == 'cart-summary')
        {
            if(count($_POST['id']) > 0)
            {
                // Lets clear the selected products session
                unset($_SESSION['CartDetails']['SelectedProducts']);

                $Products = "SELECT * FROM customer_shopping_cart WHERE CartID IN(".implode(',', $_POST['id']).") AND SessionID = '".$CartSessionID."'";

                // Lets save the selected Products in the session
                foreach($_SESSION['CartDetails'][$CartSessionID]['Products'] as $key => $arr)
                {
                    if(in_array($key, $_POST['id']))
                    {
                        $_SESSION['CartDetails']['SelectedProducts'][$key] = $arr;
                    }
                }
            }
            else
            {
                $Products = "SELECT * FROM customer_shopping_cart WHERE SessionID = '".$CartSessionID."'";
            }

            // Fetch the Cart Products for calculating Order Summary
            $Products = MysqlQuery($Products);

            $TotalCost = $TotalDiscount = 0;

            for($i = 0; $row = mysqli_fetch_assoc($Products); $i++)
            {
                $TotalCost = $TotalCost + $row['TotalCost'];
                $TotalDiscount = $TotalDiscount + $row['TotalDiscount'];
            }

            $response['status'] = 'success';
        }


    if($response['status'] == 'success')
    {
        if($_SESSION['CartDetails']['CouponDiscount'] > 0)
        {
            $OrderTotal = $TotalCost - $_SESSION['CartDetails']['CouponDiscount'];

            $CouponDiscount = '<a class="fa fa-pencil" onclick="ApplyCoupon(this)">
                                    <span>'.FormatAmount($_SESSION['CartDetails']['CouponDiscount']).'</span>
                                </a>';
        }
        else
        {
            $CouponDiscount = '<a class="font12" onclick="ApplyCoupon(this)">Apply Coupon</a>';
            $OrderTotal = $TotalCost;
        }

        $ShippingCost = 'FREE';

        $response['summary'] = '<tr>
                                    <td><label>Bag Total</label></td>
                                    <td><h4 class="cart-total">'.FormatAmount($TotalCost).'</h4></td>
                                </tr>
                                <tr>
                                    <td><label>Discount</label></td>
                                    <td><h4 class="cart-discount">'.$CouponDiscount.'</h4></td>
                                </tr>
                                <tr>
                                    <td><label>Shipping Cost</label></td>
                                    <td><h4 class="cart-shipping">'.$ShippingCost.'</h4></td>
                                </tr>
                                <tr>
                                    <td><label class="bold">Net Total</label></td>
                                    <td><h4 class="order-total bold">'.FormatAmount($OrderTotal).'</h4></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <a class="btn btn-warning btn-block mg-t-10" href="/checkout/additional-details/">Checkout</a>
                                    </td>
                                </tr>';
    }

    echo json_encode($response);


?>