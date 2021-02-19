<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        $response['redirect'] = '/login/';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

    /*---------------------
        Get Cart Content
    ----------------------*/
    if($_POST['opt'] == 'cart')
    {
        // GET PRODUCTS
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

                        foreach(json_decode($row['Size']) as $size => $qty)
                        {
                            $TotalQty[$size]    = $qty;
                            if($qty > 0)
                            {
                                $TotalCost[]    = $CustomData['Selections']['Price']['TotalPrice'] * $qty;
                            }
                        }

                        $TotalCost = array_sum($TotalCost);
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

                        foreach(json_decode($row['Size'], true) as $size => $qty)
                        {
                            $TotalQty[$size] = $qty;

                            if($qty > 0)
                            {
                                $TotalCost[] = $UpdatedPrice['FinalPrice'] * $qty;
                            }
                        }

                        $TotalCost = array_sum($TotalCost);
                        $TotalQty = array_sum($TotalQty);
                        $TotDiscount = $UpdatedPrice['DiscountPrice'] * $TotalQty;

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

                $TotalCost = $TotalCost + $row['TotalCost'];
                $TotalDiscount = $TotalDiscount + $row['TotalDiscount'];

                if($row['CustomData'] != '')
                {
                    $FabricID = 0;

                    $_SESSION['CartDetails']['CustomProduct'] = true;

                    // GET THE CATEGORY URL
                    $GetCategoryDetails = MysqlQuery("SELECT CategoryID, CategoryURL, CategoryTitle, Size, Weight FROM master_categories WHERE CategoryID = '".$row['ProductID']."' LIMIT 1");
                    $GetCategoryDetails = mysqli_fetch_assoc($GetCategoryDetails);
                    $CategoryURL = $GetCategoryDetails['CategoryURL'];
                    $Shipping[$row['CartID']]['Weight'] = $GetCategoryDetails['Weight'];

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
                }
                else
                {
                    // GET THE PRODUCT DEFAULT IMAGE
                    $GetDefaultImage = MysqlQuery("SELECT FileName FROM product_images
                                                   WHERE ProductID = '".$row['ProductID']."' AND DefaultImage = '1' LIMIT 1");
                    $GetDefaultImage = mysqli_fetch_assoc($GetDefaultImage)['FileName'];

                    $ProductURL = GetProductDetails($row['ProductID'], 'ProductURL');
                    $ProductDetails = '<div class="bold text-inverse"><a href="'.$ProductURL.'">'.$row['ProductName'].'</a></div>';
                    $ProductImage = '<img class="thumbnail" src="'._ProductImageThumbDir.$GetDefaultImage.'">';
                    $ButtonDetails = '';

                    // GET THE PRODUCT READYMADE WEIGHT
                    $ProductWeight = MysqlQuery("SELECT Weight FROM products WHERE ProductID = '".$row['ProductID']."' LIMIT 1");
                    $Shipping[$row['CartID']]['Weight'] = mysqli_fetch_assoc($ProductWeight)['Weight'];
                }

                // Get the Product Quantity
                $Quantity = json_decode($row['Size'], true);

                foreach($Quantity as $size => $qty)
                {
                    if($qty > 0)
                    {
                        $Shipping[$row['CartID']]['Quantity'] = $Shipping[$row['CartID']]['Quantity'] + $qty;
                    }
                }

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

                            <ul class="cart-size-qty">
                            <?php
                            foreach(json_decode($row['Size'], true) as $size => $qty)
                            {
                                echo    '<li>
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <label>'.$size.'</label>
                                                </div>
                                                <input type="text" class="form-control" name="Quantity['.$size.']" value="'.$qty.'" />
                                            </div>
                                        </li>';
                            }
                            ?>
                            </ul>

                        </form>
                    </td>
                    <td class="product-total bold font15">
                        <?=FormatAmount($row['TotalCost'])?>
                    </td>
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
                                        <a class="btn btn-danger" data-id="'.$row['CartID'].'" data-title="'.$GetCategoryDetails['CategoryTitle'].'" data-url="/ajax/customer/additional-details-form/" onclick="AddCartAttribute(this)">
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

                $data = GetProductWeightQuantity($product);
                $Shipping[$product['CartID']]['Weight']     = $data['Weight'];
                $Shipping[$product['CartID']]['Quantity']   = $data['Quantity'];
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
                        $_SESSION['CartDetails']['CouponDetails'] = $CouponDetails;
                        /*
                        if($CouponDetails['DiscountType'] == '%')
                        {
                            $_SESSION['CartDetails']['CouponDiscount'] = round($TotalCost * $CouponDetails['Discount'] / 100);
                        }
                        else
                        {
                            $_SESSION['CartDetails']['CouponDiscount'] = $CouponDetails['Discount'];
                        }
                        */

                        $_SESSION['CartDetails']['CouponID'] = $CouponDetails['CouponID'];

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
    /*---------------------------
        Remove Coupon
    ----------------------------*/
    elseif($_POST['opt'] == 'remove-coupon')
    {
        //Validate the SessionID
        $CartDetails = MysqlQuery("SELECT * FROM customer_shopping_cart WHERE CustomerID = '".$MemberID."'");
        if(mysqli_num_rows($CartDetails) > 0)
        {
            for($TotalCost = 0;$product = mysqli_fetch_assoc($CartDetails);)
            {
                $TotalCost = $TotalCost + $product['TotalCost'];

                $data = GetProductWeightQuantity($product);
                $Shipping[$product['CartID']]['Weight']     = $data['Weight'];
                $Shipping[$product['CartID']]['Quantity']   = $data['Quantity'];
            }
        }

        unset($_SESSION['CartDetails']['CouponDiscount']);
        unset($_SESSION['CartDetails']['CouponDetails']);
        $response['status'] = 'success';
    }
    /*---------------------------
        Set Delivery Address
    ----------------------------*/
    elseif($_POST['opt'] == 'address' && $_POST['aid'] > '0')
    {
        //Validate the SessionID
        $CartDetails = MysqlQuery("SELECT * FROM customer_shopping_cart WHERE CustomerID = '".$MemberID."'");
        if(mysqli_num_rows($CartDetails) > 0)
        {
            for($TotalCost = 0;$product = mysqli_fetch_assoc($CartDetails);)
            {
                $TotalCost = $TotalCost + $product['TotalCost'];

                $data = GetProductWeightQuantity($product);
                $Shipping[$product['CartID']]['Weight']     = $data['Weight'];
                $Shipping[$product['CartID']]['Quantity']   = $data['Quantity'];
            }
        }

        $_SESSION['CartDetails']['AddressID'] = $_POST['aid'];
        $response['status'] = 'success';
    }

    if($response['status'] == 'success')
    {
        if(isset($_SESSION['CartDetails']['CouponDetails']))
        {
            if($_SESSION['CartDetails']['CouponDetails']['DiscountType'] == '%')
            {
                $_SESSION['CartDetails']['CouponDiscount'] = round($TotalCost * $_SESSION['CartDetails']['CouponDetails']['Discount'] / 100);
            }
            else
            {
                $_SESSION['CartDetails']['CouponDiscount'] = $_SESSION['CartDetails']['CouponDetails']['Discount'];
            }
        }

        if($_SESSION['CartDetails']['CouponDiscount'] > 0)
        {
            $OrderTotal = $TotalCost - $_SESSION['CartDetails']['CouponDiscount'];

            $CouponDiscount = '<a class="fa fa-close" onclick="RemoveCoupon()">
                                    <span>(-)'.FormatAmount($_SESSION['CartDetails']['CouponDiscount']).'</span>
                                </a>';
        }
        else
        {
            $CouponDiscount = '<a class="font12" onclick="ApplyCoupon(this)">Apply Coupon</a>';
            $OrderTotal = $TotalCost;
        }

        /*---------------------------
            Calculate Shipping Cost
        ----------------------------*/

        if($TotalCost > $GlobalMinOrderTotal)
        {
            $_SESSION['CartDetails']['ShippingCost'] = '0';
        }
        else
        {
            if($LoggedIn)
            {
                if($_SESSION['CartDetails']['AddressID'] > '0')
                {
                    $DeliveryAddress = MysqlQuery("SELECT s.ShippingCharge, a.DefaultAddress FROM customer_delivery_addresses a LEFT JOIN states s ON s.StateID = a.State
                                                WHERE a.AddressID = '".$_SESSION['CartDetails']['AddressID']."' AND a.MemberID = '".$MemberID."'");
                }
                else
                {
                    $DeliveryAddress = MysqlQuery("SELECT s.ShippingCharge, a.DefaultAddress FROM customer_delivery_addresses a LEFT JOIN states s ON s.StateID = a.State
                                                WHERE a.MemberID = '".$MemberID."'");
                }

                if(mysqli_num_rows($DeliveryAddress) > 0)
                {
                    for(;$data = mysqli_fetch_assoc($DeliveryAddress);)
                    {
                        if(!isset($_SESSION['CartDetails']['AddressID']) && $data['DefaultAddress'] == 1)
                        {
                            $GlobalShippingCost = $data['ShippingCharge'];
                            break;
                        }
                        else
                        {
                            $GlobalShippingCost = $data['ShippingCharge'];
                        }
                    }
                }
            }

            foreach($Shipping as $id => $arr)
            {
                $TotalWeight[] = $arr['Weight'] * $arr['Quantity'];
            }

            $TotalWeight    = array_sum($TotalWeight);

            $_SESSION['CartDetails']['ShippingCost']    = $TotalWeight > 0 ? $TotalWeight / 100 * $GlobalShippingCost : $GlobalShippingCost;
            $OrderTotal                                 = $OrderTotal + $_SESSION['CartDetails']['ShippingCost'];
        }

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
                                    <td><h4 class="cart-shipping">'.FormatAmount($_SESSION['CartDetails']['ShippingCost']).'</h4></td>
                                </tr>
                                <tr>
                                    <td><label class="bold">Net Total</label></td>
                                    <td><h4 class="order-total bold">'.FormatAmount($OrderTotal).'</h4></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        '.($_POST['label'] == 'Address' ? '<a class="btn btn-warning btn-block font17 mg-t-10" href="#" onclick="PlaceCustomerOrder(this)">Make Payment</a>' :
                                                                          '<a class="btn btn-warning btn-block font17 mg-t-10" href="/checkout/additional-details/">Checkout</a>').'
                                    </td>
                                </tr>';
    }

    echo json_encode($response);


?>