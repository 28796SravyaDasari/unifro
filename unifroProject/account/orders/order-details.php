<?php

    // Let's validate the order id
    $OrderDetails = MysqlQuery("SELECT os.Status, o.*  FROM customer_orders o LEFT JOIN customer_order_status os ON os.OrderID = o.OrderID WHERE o.OrderID = '".$ID."'");

    if(mysqli_num_rows($OrderDetails) > 0)
    {
        $OrderDetails = mysqli_fetch_assoc($OrderDetails);

        // GET THE PRODUCT DETAILS
        $ProductDetails = GetOrderedProducts($ID);

        foreach($MasterOrderStatus as $key => $value)
        {
            $StatusArr[] = array('id' => $key, 'text' => $value);
        }

        if($OrderDetails['Status'] == 'Cancelled' || $OrderDetails['Status'] == 'Cancelled By Customer')
        {
            $OrderStatus = '<span class="label label-danger">Cancelled</span>';
        }
        else
        {
            $OrderStatus = '<span class="label label-primary">'.$OrderDetails['Status'].'</span>';
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

        <title>Order Details | <?=_WebsiteName?></title>

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

        <?php include_once(_ROOT."/include-files/common-scripts.php"); ?>
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
                    <div class="card-box clearfix">
                        <h3 class="bold" style="margin-left: 22px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
                            Order Status &nbsp;<?=$OrderStatus?>
                            <?php
                            if( $OrderDetails['Status'] == 'Awaiting Fulfillment' ||
                                $OrderDetails['Status'] == 'Awaiting Shipment' ||
                                $OrderDetails['Status'] == 'Awaiting Pickup')
                            {
                                echo '<a class="btn btn-danger pull-right" data-id="'.$ID.'" onclick="CancelOrder(this)">Cancel Order</a>';
                            }
                            ?>
                        </h3>
                        <div class="col-sm-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td>Order ID</td>
                                    <td class="bold"><?=$OrderDetails['OrderID']?></td>
                                </tr>
                                <tr>
                                    <td>Order Date</td>
                                    <td class="bold"><?=FormatDateTime('dt', $OrderDetails['OrderDate'])?></td>
                                </tr>
                                <tr>
                                    <td>Order Status</td>
                                    <td class="bold"><?=$OrderDetails['Status']?></td>
                                </tr>
                                <tr>
                                    <td>Total Cost</td>
                                    <td class="bold"><?=FormatAmount($OrderDetails['TotalCost'])?></td>
                                </tr>
                                <tr>
                                    <td>Discount</td>
                                    <td class="bold">
                                        - <?=FormatAmount($OrderDetails['DiscountAmount'])?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Shipping Cost</td>
                                    <td class="bold"><?=FormatAmount($OrderDetails['ShippingCharges'])?></td>
                                </tr>
                                <tr>
                                    <td>Net Amount</td>
                                    <td class="bold"><?=FormatAmount($OrderDetails['FinalTotal'])?></td>
                                </tr>
                                <tr>
                                    <td>Payment Status</td>
                                    <td class="bold">
                                        <?=$OrderDetails['PaymentStatus']?> <?=$OrderDetails['PaymentMode'] != '' ? '('.$OrderDetails['PaymentMode'].')' : ''?>
                                        <?=$OrderDetails['PaymentStatus'] != 'Successful' &&
                                            $OrderDetails['Status'] != 'Cancelled By Customer' &&
                                            $OrderDetails['Status'] != 'Cancelled' &&
                                            $OrderDetails['Status'] != 'Declined' ?
                                            '&nbsp;&nbsp;&nbsp;<button class="btn btn-warning btn-xs" data-id="'.$OrderDetails['OrderID'].'" onclick="CustomerRetryOrder(this)"><i class="fa fa-refresh"></i>&nbsp; Retry Payment</button>' : ''?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-sm-6">
                            <div class="mg-b-20">Shipping Address</div>
                            <div class="bold"><?=$OrderDetails['ShippingName']?></div>
                            <div><?=$OrderDetails['ShippingAddress'].', '.$OrderDetails['ShippingCity'].' - '.$OrderDetails['ShippingPincode'].', '.$OrderDetails['ShippingState']?></div>
                            <div>Phone: <?=$OrderDetails['ShippingPhone']?></div>
                        </div>
                    </div>

                    <div class="card-box">
                        <table class="table footable" data-expand-first="false" data-toggle-column="last">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Product Details</th>
                                    <th data-breakpoints="xs sm">Size / Quantity</th>
                                    <th data-breakpoints="xs sm">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?=DisplayOrderedProducts($ProductDetails)?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script src="/js/lightbox.min.js"></script>

	</body>
</html>