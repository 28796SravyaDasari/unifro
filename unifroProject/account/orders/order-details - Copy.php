<?php
    $SelectedPage = 'Client Orders';

    // Let's validate the order id
    $OrderDetails = MysqlQuery("SELECT os.Status, o.*  FROM customer_orders o LEFT JOIN customer_order_status os ON os.OrderID = o.OrderID WHERE o.OrderID = '".$ID."'");

    if(mysqli_num_rows($OrderDetails) > 0)
    {
        $OrderDetails = mysqli_fetch_assoc($OrderDetails);

        // GET THE PRODUCT DETAILS
        $GetProducts = MysqlQuery("SELECT * FROM customer_order_details WHERE OrderID = '".$ID."'");
        if(mysqli_num_rows($GetProducts) > 0)
        {
            $ProductDetails = array();

            for(; $row = mysqli_fetch_assoc($GetProducts);)
            {
                $ButtonDetails = array();

                $ProductDetails[$row['OrderDetailsID']]['OrderID'] = $row['OrderID'];
                $ProductDetails[$row['OrderDetailsID']]['ProductID'] = $row['ProductID'];
                $ProductDetails[$row['OrderDetailsID']]['Size'] = $row['Size'];
                $ProductDetails[$row['OrderDetailsID']]['TotalCost'] = $row['TotalCost'];
                $ProductDetails[$row['OrderDetailsID']]['TaxRate'] = $row['TaxRate'];
                $ProductDetails[$row['OrderDetailsID']]['AdditionalDetails'] = $row['AdditionalDetails'];

                // GET THE CATEGORY URL
                $GetCategoryDetails = MysqlQuery("SELECT CategoryTitle FROM master_categories WHERE CategoryID = '".$row['ProductID']."' LIMIT 1");
                $ProductDetails[$row['OrderDetailsID']]['CategoryTitle'] = mysqli_fetch_assoc($GetCategoryDetails)['CategoryTitle'];

                if($row['CustomData'] != '')
                {
                    $FabricID = 0;

                    $row['data'] = json_decode($row['CustomData'], true);
                    ksort($row['data']['Selections']['Styles']);

                    $ProductDetails[$row['OrderDetailsID']]['image'] = '<img class="thumbnail" src="'.GetFabricDetails($row['data']['Selections']['FabricID'], 'FabricImage').'">';

                    $ProductDetails[$row['OrderDetailsID']]['html'][] = '<ul class="custom-product-details">';

                    $ProductDetails[$row['OrderDetailsID']]['html'][] = '<li class="bold text-inverse">Custom Designed '.$row['ProductName'].'</li>';
                    $ProductDetails[$row['OrderDetailsID']]['html'][] = '<li><b>Main Fabric:</b> '.GetFabricDetails($row['data']['Selections']['FabricID'], 'FabricName').'</li>';


                    foreach($row['data']['Selections']['Styles'] as $element => $styleID)
                    {
                        if( isset($styleID['ButtonID']) )
                        {
                            $ButtonDetails[] = '<li>
                                                    <img src="'._ButtonsDir.GetButtonImage($styleID['ButtonID']).'" style="width:60px">&nbsp;
                                                    <br>'.$element.'
                                                </li>';
                        }
                        else
                        {
                            $ProductDetails[$row['OrderDetailsID']]['html'][] = GetStyleNameByID($styleID['StyleID']) != '' ? '<li>'.$element.' : '.GetStyleNameByID($styleID['StyleID']).'</li>' : '';
                        }

                        foreach($styleID as $subKey => $subArr)
                        {
                            if(is_array($subArr))
                            {
                                foreach($subArr as $key => $subStyles)
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
                                        }
                                    }
                                    $ProductDetails[$row['OrderDetailsID']]['html'][] = $FabricName != '' ? '<li>- '.$subKey.$key.' : '.$FabricName.'</li>' : '';
                                }
                            }
                        }
                    }

                    $ProductDetails[$row['OrderDetailsID']]['html'][] = '</ul>';
                    //$ProductDetails = implode('', $ProductDetails);

                    if(count($ButtonDetails) > 0)
                    {
                        $ProductDetails[$row['OrderDetailsID']]['button'] = '<ul class="custom-product-details">'.implode('', $ButtonDetails).'</ul>';
                    }
                }
                else
                {
                    // GET THE PRODUCT DEFAULT IMAGE
                    $GetDefaultImage = MysqlQuery("SELECT FileName FROM product_images
                                                   WHERE ProductID = '".$row['ProductID']."' AND DefaultImage = '1' LIMIT 1");
                    $GetDefaultImage = mysqli_fetch_assoc($GetDefaultImage)['FileName'];

                    $ProductDetails[$row['OrderDetailsID']]['html'] = '<div class="bold text-inverse">'.$row['ProductName'].'</div>';
                    $ProductDetails[$row['OrderDetailsID']]['image'] = '<img class="thumbnail" src="'._ProductImageThumbDir.$GetDefaultImage.'">';
                }
            }
        }

        foreach($MasterOrderStatus as $key => $value)
        {
            $StatusArr[] = array('id' => $key, 'text' => $value);
        }
    }
    else
    {
        $_SESSION['AlertMessage'] = 'No Order Found!';
        header('Location: /account/orders/');
        exit;
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$PageTitle?></title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            $('.responsive-tabs').responsiveTabs({
                accordionOn: ['xs', 'sm']
            });

            lightbox.option({ 'showImageNumberLabel': false, 'wrapAround': true, alwaysShowNavOnTouchDevices: true })
        })
        </script>
    </head>
    <body class="bg-lightgray">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb" style="background:#fff">
                            <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                            <li class="breadcrumb-item"><a href="/account/">My Account</a></li>
                            <li class="breadcrumb-item"><a href="/account/orders/">Orders</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?=$ID?></li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row mg-t-30">
                <div class="col-lg-12">
                    <div>
                        <ul class="nav nav-tabs responsive-tabs" style="width: 100%;">
                            <li class="active tab"><a data-tab-name="tab-info" data-toggle="tab" href="#tab-info">Order Info</a></li>
                            <li class="tab"><a data-toggle="tab" href="#tab-shipping">Billing & Shipping</a></li>
                            <li class="tab"><a data-toggle="tab" href="#tab-products">Products</a></li>
                        </ul>
                        <div class="tab-content">

                            <!-----------------------------
                                START OF INFO TAB
                            ------------------------------>
                            <div class="tab-pane active" id="tab-info">
                                <div class="form-horizontal">
                                    <div class="clearfix">
                                        <div class="col-md-9">
                                            <div class="form-group">
                                                <div class="col-sm-3">
                                                    <label class="control-label">Order ID</label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <?=$OrderDetails['OrderID']?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-3">
                                                    <label class="control-label">Order Date</label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <?=FormatDateTime('dt', $OrderDetails['OrderDate'])?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-3">
                                                    <label class="control-label">Order Status</label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <?=$OrderDetails['Status']?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-3">
                                                    <label class="control-label">Total Cost</label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <?=FormatAmount($OrderDetails['TotalCost'])?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-3">
                                                    <label class="control-label">Discount</label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <?=FormatAmount($OrderDetails['DiscountAmount']).' ('.$OrderDetails['DiscountPercent'].'%)'?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-3">
                                                    <label class="control-label">Final Cost</label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <?=FormatAmount($OrderDetails['FinalTotal'])?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-3">
                                                    <label class="control-label">Payment Status</label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <?=$OrderDetails['PaymentStatus']?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-3">
                                                    <label class="control-label">Payment Mode</label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <?=$OrderDetails['PaymentMode']?>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-----------------------------
                                END OF INFO TAB
                            ------------------------------>

                            <!-----------------------------
                                START OF SHIPPING TAB
                            ------------------------------>
                            <div class="tab-pane" id="tab-shipping">

                                <div class="clearfix">
                                    <div class="col-md-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading bold">Shipping Details</div>
                                            <div class="panel-body">
                                                <div class="form-horizontal">
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <label class="control-label">Name</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <?=$OrderDetails['ShippingName']?>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <label class="control-label">Email</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <?=$OrderDetails['ShippingEmail']?>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <label class="control-label">Phone</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <?=$OrderDetails['ShippingPhone']?>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <label class="control-label">Address</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <div id="ShippingAddress" class="editable" data-pk="<?=$OrderDetails['OrderID']?>" data-title="Change Address">
                                                                <?=$OrderDetails['ShippingAddress']?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <label class="control-label">City</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <div id="ShippingCity" class="editable" data-pk="<?=$OrderDetails['OrderID']?>" data-title="Change City">
                                                                <?=$OrderDetails['ShippingCity']?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <label class="control-label">Pincode</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <div id="ShippingPincode" class="editable" data-pk="<?=$OrderDetails['OrderID']?>" data-title="Change Pincode">
                                                                <?=$OrderDetails['ShippingPincode']?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <label class="control-label">State</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <div id="ShippingState" class="editable" data-pk="<?=$OrderDetails['OrderID']?>" data-title="Change State">
                                                                <?=$OrderDetails['ShippingState']?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading bold">Billing Details</div>
                                            <div class="panel-body">
                                                <div class="form-horizontal">
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <label class="control-label">Name</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <?=$OrderDetails['BillingName']?>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <label class="control-label">Email</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <?=$OrderDetails['BillingEmail']?>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <label class="control-label">Phone</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <?=$OrderDetails['BillingPhone']?>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <label class="control-label">Address</label>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <?=$OrderDetails['BillingAddress']?><br>
                                                            <?=$OrderDetails['BillingCity'].' - '.$OrderDetails['BillingPincode']?><br>
                                                            <?=$OrderDetails['BillingState']?>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-----------------------------
                                END OF SHIPPING TAB
                            ------------------------------>

                            <!-----------------------------
                                START OF PRODUCTS TAB
                            ------------------------------>
                            <div class="tab-pane" id="tab-products">
                                <div class="clearfix">
                                    <div class="col-lg-12">
                                        <table class="table footable" data-expand-first="false" data-toggle-column="last" id="CartTable">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Product Details</th>
                                                    <th data-breakpoints="xs sm">Size / Quantity</th>
                                                    <th data-breakpoints="xs sm">Amount</th>
                                                    <th data-breakpoints="md sm xs">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach($ProductDetails as $key => $row)
                                                {
                                                    ?>

                                                    <tr>
                                                        <td><?=$row['image']?></td>
                                                        <td>
                                                            <?=implode('',$row['html'])?>
                                                            <?=$row['button']?>
                                                        </td>
                                                        <td class="text-center">
                                                            <form action="/admin/ajax/edit-order/" class="form-horizontal CartForm" id="CartForm<?=$key?>">
                                                                <input type="hidden" name="OrderDetailsID" value="<?=$key?>" />
                                                                <input type="hidden" name="OrderID" value="<?=$row['OrderID']?>" />
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
                                                                <li>
                                                                    <a class="btn btn-success" onclick="$('#CartForm<?=$key?>').submit()">
                                                                        <i class="fa fa-refresh"></i> &nbsp;Update
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="btn btn-white" data-product="<?=$key?>" data-id="<?=$key?>"
                                                                        onclick="RemoveOrderProduct(this)"><i class="fa fa-trash"></i> &nbsp;Remove
                                                                    </a>
                                                                </li>
                                                                <?php
                                                                if( $row['CategoryTitle'] == 'Full Pant' ||
                                                                    $row['CategoryTitle'] == 'Pinafores' ||
                                                                    $row['CategoryTitle'] == 'Skirt')
                                                                {
                                                                    echo '<li>
                                                                            <a  class="btn btn-danger" data-id="'.$key.'"
                                                                                data-title="'.$row['CategoryTitle'].'" onclick="AddProductAttribute(this)">
                                                                                '.($row['AdditionalDetails'] != '' ? 'Update Attributes' : 'Add Attributes').'
                                                                            </a>
                                                                        </li>';
                                                                }
                                                                ?>
                                                            </ul>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-----------------------------
                                END OF PRODUCTS TAB
                            ------------------------------>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="/js/lightbox.min.js"></script>

	</body>
</html>