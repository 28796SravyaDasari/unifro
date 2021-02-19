<?php
    $SelectedPage = 'Client Orders';

    // Let's validate the order id
    $OrderDetails = MysqlQuery("SELECT os.Status, c.ClientName, o.*  FROM client_orders o LEFT JOIN client_order_status os ON os.OrderID = o.OrderID LEFT JOIN clients c ON c.ClientID = o.ClientID WHERE o. OrderID = '".$ID."'");

    if(mysqli_num_rows($OrderDetails) > 0)
    {
        $OrderDetails = mysqli_fetch_assoc($OrderDetails);

        // GET THE PRODUCT DETAILS
        $GetProducts = MysqlQuery("SELECT * FROM client_order_details WHERE OrderID = '".$ID."'");
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
                $ProductDetails[$row['OrderDetailsID']]['MeasurementFile'] = $row['MeasurementFile'];

                if($row['CustomData'] != '')
                {
                    // GET THE CATEGORY URL
                    $GetCategoryDetails = MysqlQuery("SELECT CategoryTitle FROM master_categories WHERE CategoryID = '".$row['ProductID']."' LIMIT 1");
                    $ProductDetails[$row['OrderDetailsID']]['CategoryTitle'] = mysqli_fetch_assoc($GetCategoryDetails)['CategoryTitle'];

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
                                        $ProductDetails[$row['OrderDetailsID']]['html'][] = $FabricName != '' ? '<li>- '.$subKey.$key.' : '.$FabricName.'</li>' : '';
                                    }
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
        header('Location: /admin/clients/orders/');
        exit;
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$PageTitle?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <link rel="stylesheet" href="/css/bootstrap-editable.css" type="text/css">

        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>
        <script type="text/javascript" src="/js/bootstrap-editable.min.js"></script>

        <script>
        $(document).ready(function()
        {
            $('.responsive-tabs').responsiveTabs({
                accordionOn: ['xs', 'sm']
            });

            lightbox.option({ 'showImageNumberLabel': false, 'wrapAround': true, alwaysShowNavOnTouchDevices: true })

            UpdateOrderQuantity();

            $('.editable').editable(
            {
                url: '/admin/ajax/edit-order/',
                type: 'text',
                mode: 'inline',
                params: function(params) {
                    //originally params contain pk, name and value
                    params.option = 'update-shipping';
                    return params;
                },
                success: function(response, newValue)
                {
                    data = $.parseJSON(response);

                    if(data.status == 'success')
                        return true;
                    else if(data.status == 'login')
                        location.reload();
                    else
                        return data.response_message;
                },
            });

            $('.invoice-btn').on('click', function()
            {
                $('#DownloadInvoice').submit();
            })
        })
        </script>
    </head>
    <body>

        <?php include(_ROOT._AdminIncludesDir."admin-common-scripts.php"); ?>

        <div id="wrapper">
            <?php include(_ROOT._AdminIncludesDir."admin-header.php"); ?>

            <?php
                include(_ROOT._AdminIncludesDir."admin-sidebar.php");
            ?>

            <form id="DownloadInvoice" method="post" action="/admin/clients/orders/invoice-download.php">
            	<input type="hidden" name="OrderID" value="<?=$ID?>">
            </form>

            <!-- ==============================================================
                Start of Right content here
            ============================================================== -->
            <div class="content-page">

                <div class="content">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="content-header clearfix">
                                    <h1 class="pull-left page-title">
                                        <?=$PageTitle?>
                                        <small><a href="/admin/clients/orders/"><i class="fa fa-arrow-circle-left"></i> Back to Order List</a></small>
                                    </h1>
                                    <div class="pull-right">
                                        <button class="btn btn-success invoice-btn">Download Invoice</button>
                                        <button type="button" class="btn btn-danger" data-id="<?=$ID?>" onclick="DeleteOrder(this)">
                                            <i class="fa fa-trash-o"></i>&nbsp;Delete
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row">
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
                                                    <div class="col-md-6">
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
                                                                <a href="#" id="OrderStatus" data-type="select" data-pk="<?=$OrderDetails['OrderID']?>" data-value="<?=$OrderDetails['Status']?>"><?=$OrderDetails['Status']?></a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-sm-3">
                                                                <label class="control-label">Client Name</label>
                                                            </div>
                                                            <div class="col-sm-9">
                                                                <?=$OrderDetails['ClientName']?>
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
                                                                <a href="#" id="PaymentStatus" data-type="select" data-pk="<?=$OrderDetails['OrderID']?>"
                                                                data-value="<?=$OrderDetails['PaymentStatus']?>"><?=$OrderDetails['PaymentStatus']?></a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-sm-3">
                                                                <label class="control-label">Payment Mode</label>
                                                            </div>
                                                            <div class="col-sm-9">
                                                                <a href="#" id="PaymentMode" data-type="select" data-pk="<?=$OrderDetails['OrderID']?>"
                                                                data-value="<?=$OrderDetails['PaymentMode']?>"><?=$OrderDetails['PaymentMode']?></a>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <?php
                                                        if($OrderDetails['OrderAdditionalDetails'] != '')
                                                        {
                                                            $OrderAdditionalDetails = json_decode($OrderDetails['OrderAdditionalDetails'], true);

                                                            foreach($OrderAdditionalDetails as $key => $value)
                                                            {
                                                                if($key == 'ClientMonogram')
                                                                {
                                                                    // Get the monogram from the customer monograms table
                                                                    $Monogram = mysqli_fetch_assoc(MysqlQuery("SELECT FileName FROM client_monograms WHERE MonogramID = '".$value."' LIMIT 1"));
                                                                    $Monogram = _MonogramDir.$Monogram['FileName'];
                                                                    echo '<h4>Monogram</h4>';
                                                                    echo '<a href="'.$Monogram.'" target="_blank"><img src="'.$Monogram.'" class="thumbnail"></a>';
                                                                }
                                                                elseif($key == 'MonogramText')
                                                                {
                                                                    echo '<h4>Monogram Text: '.$value.'</h4>';
                                                                }
                                                                else
                                                                {
                                                                    $value = $value == 'n' ? 'No' : ($value == 'y' ? 'Yes' : $value);

                                                                    echo $value != '' ? '<h5>'.$key.'<div class="bold mg-t-5">'.$value.'</div></h5>' : '';
                                                                }
                                                            }
                                                        }
                                                        ?>
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
                                                                        <?=is_array($row['html']) ? implode('',$row['html']) : $row['html']?>
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
                                                                                                <input type="text" class="form-control" name="Quantity['.$size.']" readonly value="'.$qty.'" />
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
                                                                            <!--
                                                                            <li>
                                                                                <a class="btn btn-success" onclick="$('#CartForm<?=$key?>').submit()">
                                                                                    <i class="fa fa-refresh"></i> &nbsp;Update
                                                                                </a>
                                                                            </li>
                                                                            -->
                                                                            <?=$row['MeasurementFile'] != '' ?
                                                                            '<li><a class="btn btn-white" href="'._ClientMeasurementsDir.$row['MeasurementFile'].'"><i class="fa fa-file-excel-o"></i> Download File</a></li>'
                                                                            : ''?>
                                                                            <li>
                                                                                <a class="btn btn-white" data-product="<?=$row['ProductID']?>" data-id="<?=$key?>"
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
                                                                            if($row['Measurement'] != '')
                                                                            {
                                                                                echo '<div class="mg-b-5">
                                                                                            <button class="btn btn-white btn-sm" data-toggle="modal" data-target="#MeasurementModal">Measurements</button>
                                                                                      </div>';

                                                                                echo '<div class="modal fade" id="MeasurementModal" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Measurements</h4></div><div class="modal-body">';

                                                                                $Measurements = json_decode($row['Measurement'], true);
                                                                                foreach($Measurements as $key => $value)
                                                                                {
                                                                                    if($key != 'CartID')
                                                                                    {
                                                                                        echo '<div class="form-group">
                                                                                                <label class="control-label">'.$key.' : '.$value.'</label>
                                                                                              </div>';
                                                                                    }
                                                                                }

                                                                                echo '</div></div></div></div>';

                                                                            }
                                                                            if($row['AdditionalDetails'] != '')
                                                                            {
                                                                                echo '<div class="mg-b-5">
                                                                                        <button class="btn btn-white btn-sm" data-toggle="modal" data-target="#AdditionalDetailsModal">Addtional Details</button>
                                                                                        </div>';

                                                                                echo '<div class="modal fade" id="AdditionalDetailsModal" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Additional Details</h4></div><div class="modal-body">';

                                                                                $AdditionalDetails = json_decode($row['AdditionalDetails'], true);
                                                                                foreach($AdditionalDetails as $key => $value)
                                                                                {
                                                                                    echo '<div class="form-group">
                                                                                            <label class="control-label">'.$key.' : '.$value.'</label>
                                                                                            </div>';

                                                                                }

                                                                                echo '</div></div></div></div>';
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
                    </div>   <!-- container -->

                </div>   <!-- content -->
            </div>


            <?php include(_ROOT._AdminIncludesDir."footer.php"); ?>

            <!-- ==============================================================
                End of Right content here
            ============================================================== -->
        </div>

        <script src="/js/lightbox.min.js"></script>
        <script>
        $(function(){
            $('#OrderStatus').editable({
                source: <?=json_encode($StatusArr)?>,
                mode: 'inline',
                url: '/admin/ajax/edit-order/',
                success: function(response, newValue)
                {
                    data = $.parseJSON(response);

                    if(data.status == 'success')
                        return true;
                    else if(data.status == 'login')
                        location.reload();
                    else
                        return data.response_message;
                },
                display: function(value)
                {
                    if(!value) {
                        $(this).empty();
                        return;
                    }
                    $(this).html(value);
                },
            });

            $('#PaymentStatus').editable({
                source: [
                    {value: 'Pending', text: 'Pending'},
                    {value: 'Paid', text: 'Paid'},
                    {value: 'Partially Paid', text: 'Partially Paid'},
                ],
                mode: 'inline',
                url: '/admin/ajax/edit-order/',
                success: function(response, newValue)
                {
                    data = $.parseJSON(response);

                    if(data.status == 'success')
                        return true;
                    else if(data.status == 'login')
                        location.reload();
                    else
                        return data.response_message;
                },
                display: function(value)
                {
                    if(!value) {
                        $(this).empty();
                        return;
                    }
                    $(this).html(value);
                },
            });

            $('#PaymentMode').editable({
                source: [
                    {value: 'Cash', text: 'Cash'},
                    {value: 'Cheque', text: 'Cheque'},
                ],
                mode: 'inline',
                url: '/admin/ajax/edit-order/',
                success: function(response, newValue)
                {
                    data = $.parseJSON(response);

                    if(data.status == 'success')
                        return true;
                    else if(data.status == 'login')
                        location.reload();
                    else
                        return data.response_message;
                },
                display: function(value)
                {
                    if(!value) {
                        $(this).empty();
                        return;
                    }
                    $(this).html(value);
                },
            });
        });
        </script>

	</body>
</html>