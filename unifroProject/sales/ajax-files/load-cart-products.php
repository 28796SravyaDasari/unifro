<?php

    if(!$LoggedIn)
    {
        $response['status'] = 'login';
        $response['response_message'] = 'Your session has expired! Please login again.';
        echo json_encode($response);
        exit;
    }

    $response['status'] = 'error';

    if($_SESSION['ClientID'] > 0)
    {
        /*---------------------
            Get Cart Content
        ----------------------*/
        if($_POST['opt'] == 'cart')
        {
            // GET PRODUCTS CUSTOMIZED FOR CLIENT
            $Products = MysqlQuery("SELECT * FROM client_shopping_cart WHERE SalesID = '".$MemberID."' AND ClientID = '".$_SESSION['ClientID']."' ORDER BY CartID DESC");
            if(mysqli_num_rows($Products) > 0)
            {
                $TotalDiscount = $CorporateDiscount = 0;
                $CorporateDiscountPercent = $_SESSION['CartDetails']['CorporateDiscount'] > 0 ? $_SESSION['CartDetails']['CorporateDiscount'] : 0;

                for($i = 0; $row = mysqli_fetch_assoc($Products); $i++)
                {
                    //$ClientProducts[$i] = $row;

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

                            // LETS UPDATE THE GROSS AND FINAL PRICE IN THE CART

                            $res = MysqlQuery("UPDATE client_shopping_cart SET CustomData = '".json_encode($CustomData)."', GrossPrice = '".$CustomData['Selections']['Price']['TotalPrice']."', FinalPrice = '".$CustomData['Selections']['Price']['TotalPrice']."', TotalCost = '".$TotalCost."', TotalDiscount = '".$TotDiscount."', UpdatePrice = '0', UpdatedOn = '".time()."' WHERE CartID = '".$row['CartID']."' LIMIT 1");

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

                        // GET THE CATEGORY URL
                        $GetCategoryDetails = MysqlQuery("SELECT CategoryURL, CategoryTitle FROM master_categories WHERE CategoryID = '".$row['ProductID']."' LIMIT 1");
                        $GetCategoryDetails = mysqli_fetch_assoc($GetCategoryDetails);
                        $CategoryURL = $GetCategoryDetails['CategoryURL'];

                        $_SESSION['CartDetails'][$_SESSION['ClientID']]['Products'][$row['CartID']][] = $GetCategoryDetails['CategoryTitle'];


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
                                                        <span class="button-image" style="background:url('._ButtonsDir.GetButtonImage($styleID['ButtonID']).') no-repeat center; background-size:100%">&nbsp;</span><br>
                                                        '.$element.'
                                                     </li>';
                            }
                            else
                            {
                                $ProductDetails[] = GetStyleNameByID($styleID['StyleID']) != '' ? '<li>'.$element.' : '.GetStyleNameByID($styleID['StyleID']).'</li>' : '';
                            }

                            if( strpos($element, 'Monogram') !== false )
                            {
                                $_SESSION['CartDetails'][$_SESSION['ClientID']]['Products'][$row['CartID']][] = 'Monogram';
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

                                            $ProductDetails[] = $FabricName != '' ? '<li>- '.$subKey.$key.' : '.$FabricName.'</li>' : '';
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

                        $ProductDetails = '<div class="bold text-inverse">'.$row['ProductName'].'</div>';
                        $ProductImage = '<img class="thumbnail" src="'._ProductImageThumbDir.$GetDefaultImage.'">';
                        $ButtonDetails = '';

                        $_SESSION['CartDetails'][$_SESSION['ClientID']]['Products'][$row['CartID']][] = $row['ProductName'];
                    }

                    ob_start();
                    ?>
                    <tr>
                        <td>
                            <div class="custom-checkbox">
                                <label>
                                    <input type="checkbox" name="CartID[]" value="<?=$row['CartID']?>" /><span></span>
                                </label>
                            </div>
                        </td>
                        <td><?=$ProductImage?></td>
                        <td>
                            <?=$row['UpdateInfo']?>
                            <?=$ProductDetails?>
                            <?=$ButtonDetails?>
                        </td>
                        <td class="text-center">
                            <form action="/ajax/cart-sales-add/" class="form-horizontal CartForm" id="CartForm<?=$row['CartID']?>">
                                <input type="hidden" name="CartID" value="<?=$row['CartID']?>" />
                                <input type="hidden" name="ClientID" value="<?=$row['ClientID']?>" />
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
                        <td class="product-total bold font15"><?=FormatAmount($row['TotalCost'])?></td>
                        <td>
                            <ul class="cart-options">
                                <li>
                                    <?=$row['MeasurementFile'] != '' ?
                                        '<a href="'._ClientMeasurementsDir.$row['MeasurementFile'].'"><i class="fa fa-file-excel-o"></i> Download File</a>'
                                        : ''?>
                                    <form action="/ajax/upload-measurement/" class="UploadForm mg-t-5" id="Cart<?=$row['CartID']?>">
                                        <input type="hidden" name="CartID" value="<?=$row['CartID']?>">
                                        <input type="file" name="AddMeasurement" id="<?=$row['CartID']?>" data-jfiler-changeInput='<div class="btn btn-default" style="width: 150px"><div><i class="fa fa-upload"></i>&nbsp;Measurement</div></div>' data-jfiler-extensions="xls,xlsx" data-jfiler-caption="Only Excel files are allowed to be uploaded." data-jfiler-limit="1">
                                    </form>
                                </li>
                                <?php
                                if($row['CustomData'] != '')
                                {
                                    echo    '<li>
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
                                            <a class="btn btn-danger" data-id="'.$row['CartID'].'" data-title="'.$GetCategoryDetails['CategoryTitle'].'" data-url="/ajax/sales/additional-details-form/" onclick="AddCartAttribute(this)">
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
            Apply Corporate Discount
        ----------------------------*/
        elseif($_POST['opt'] == 'discount' && $_POST['percent'] > 0)
        {
            // LETS VALIDATE THE CLIENT ID
            $CartDetails = MysqlQuery("SELECT CartID FROM client_shopping_cart WHERE SalesID = '".$MemberID."' AND ClientID = '".$_SESSION['ClientID']."' LIMIT 1");
            if(mysqli_num_rows($CartDetails) > 0)
            {
                $_SESSION['CartDetails']['CorporateDiscount'] = $_POST['percent'];
                $CorporateDiscountPercent = $_SESSION['CartDetails']['CorporateDiscount'];

                $TotalCost = $TotalDiscount = $CorporateDiscount = 0;

                if(count($_POST['id']) > 0)
                {
                    $Products = "SELECT * FROM client_shopping_cart WHERE CartID IN(".implode(',', $_POST['id']).") AND SalesID = '".$MemberID."' AND ClientID = '".$_SESSION['ClientID']."'";
                }
                else
                {
                    $Products = "SELECT * FROM client_shopping_cart WHERE SalesID = '".$MemberID."' AND ClientID = '".$_SESSION['ClientID']."'";
                }

                // Fetch the Cart Products for calculating Order Summary
                $Products = MysqlQuery($Products);

                for($i = 0; $row = mysqli_fetch_assoc($Products); $i++)
                {
                    $TotalCost = $TotalCost + $row['TotalCost'];
                    $TotalDiscount = $TotalDiscount + $row['TotalDiscount'];
                }

                $response['status'] = 'success';

                /*
                // UPDATE THE CORPORATE DISCOUNT PERCENT
                MysqlQuery("UPDATE client_shopping_cart SET CorporateDiscount = '".$_POST['percent']."' WHERE SalesID = '".$MemberID."' AND ClientID = '".$_SESSION['ClientID']."'");

                if(MysqlAffectedRows() >= 0)
                {
                    // Fetch the Cart Products for calculating Order Summary
                    $Products = MysqlQuery("SELECT * FROM client_shopping_cart WHERE SalesID = '".$MemberID."' AND ClientID = '".$_SESSION['ClientID']."'");

                    $TotalCost = $TotalDiscount = $CorporateDiscount = $CorporateDiscountPercent = 0;

                    for($i = 0; $row = mysqli_fetch_assoc($Products); $i++)
                    {
                        $TotalCost = $TotalCost + $row['TotalCost'];
                        $TotalDiscount = $TotalDiscount + $row['TotalDiscount'];

                        if($row['CorporateDiscount'] > 0)
                            $CorporateDiscountPercent = $row['CorporateDiscount'];
                    }

                    $response['status'] = 'success';
                }
                else
                {
                    $response['response_message'] = 'Something went wrong! Please refresh the page and try again. [LN 10]';
                }
                */
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

                $Products = "SELECT * FROM client_shopping_cart WHERE CartID IN(".implode(',', $_POST['id']).") AND SalesID = '".$MemberID."' AND ClientID = '".$_SESSION['ClientID']."'";

                // Lets save the selected Products in the session
                foreach($_SESSION['CartDetails'][$_SESSION['ClientID']]['Products'] as $key => $arr)
                {
                    if(in_array($key, $_POST['id']))
                    {
                        $_SESSION['CartDetails']['SelectedProducts'][$key] = $arr;
                    }
                }
            }
            else
            {
                $Products = "SELECT * FROM client_shopping_cart WHERE SalesID = '".$MemberID."' AND ClientID = '".$_SESSION['ClientID']."'";
            }

            // Fetch the Cart Products for calculating Order Summary
            $Products = MysqlQuery($Products);

            $TotalCost = $TotalDiscount = $CorporateDiscount = 0;
            $CorporateDiscountPercent = $_SESSION['CartDetails']['CorporateDiscount'] > 0 ? $_SESSION['CartDetails']['CorporateDiscount'] : 0;

            for($i = 0; $row = mysqli_fetch_assoc($Products); $i++)
            {
                $TotalCost = $TotalCost + $row['TotalCost'];
                $TotalDiscount = $TotalDiscount + $row['TotalDiscount'];
            }

            $response['status'] = 'success';
        }
    }
    else
    {
        $response['response_message'] = 'Invalid Access!';
    }

    if($response['status'] == 'success')
    {
        $CorporateDiscount = $TotalCost * $CorporateDiscountPercent / 100;
        $OrderTotal = $TotalCost - $CorporateDiscount;

        if($CorporateDiscount > 0)
        {
            $CorporateDiscount = '<a class="fa fa-pencil" onclick="ApplyDiscount(this)">
                                    <span><small>('.floatval($CorporateDiscountPercent).'%)</small> '.FormatAmount($CorporateDiscount).'</span>
                                  </a>';
        }
        else
        {
            $CorporateDiscount = '<a class="font12" onclick="ApplyDiscount(this)">Apply Discount</a>';
        }

        $response['summary'] = '<tr>
                                    <td><label>Cart Total</label></td>
                                    <td><h4 class="cart-total">'.FormatAmount($TotalCost).'</h4></td>
                                </tr>
                                <tr>
                                    <td><label>Discount</label></td>
                                    <td><h4 class="cart-discount">'.$CorporateDiscount.'</h4></td>
                                </tr>
                                <tr>
                                    <td><label class="bold">Net Total</label></td>
                                    <td><h4 class="order-total bold">'.FormatAmount($OrderTotal).'</h4></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <a class="btn btn-warning btn-block mg-t-10" href="/sales/clients/additional-details/'.$_SESSION['ClientID'].'/">Checkout</a>
                                    </td>
                                </tr>';
    }

    echo json_encode($response);


?>