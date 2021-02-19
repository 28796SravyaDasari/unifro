<?php
    include_once("../../../include-files/autoload-server-files.php");

    ini_set("memory_limit", "256M");

    require_once("../../../domPDF/dompdf-master/dompdf_config.inc.php");

    if(is_numeric($_POST['OrderID']))
    {
        /*-----------------------------------------------------------
            Let's validate the order id and fetch the order details
        -----------------------------------------------------------*/
        $OrderDetails = MysqlQuery("SELECT * FROM client_orders WHERE OrderID = '".$_POST['OrderID']."' LIMIT 1");
        $OrderDetails = mysqli_fetch_assoc($OrderDetails);

        $GetProducts = MysqlQuery("SELECT * FROM client_order_details WHERE OrderID = '".$_POST['OrderID']."'");
        $ProductDetails = MysqlFetchAll($GetProducts);

        $FileName           = $OrderDetails['OrderID'].'-'.$OrderDetails['OrderDate'];
        $InvoiceNo          = $OrderDetails['OrderID'];
        $InvoiceDate        = date('d-M-Y', $OrderDetails['OrderDate']);
        $ShippingName       = $OrderDetails['ShippingName'];
        $ShippingAddress    = $OrderDetails['ShippingAddress'];
        $ShippingCity       = $OrderDetails['ShippingCity'];
        $ShippingPincode    = $OrderDetails['ShippingPincode'];
        $ShippingState      = $OrderDetails['ShippingState'];
        $ShippingPhone      = $OrderDetails['ShippingPhone'];
        $ShippingCost       = $OrderDetails['ShippingCharges'];
        $CouponDiscount     = $OrderDetails['DiscountAmount'];
    }
    ob_start();
?>

<html>
<head>
<meta charset="utf-8">
<title>Unifro</title>
<style>
html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, font, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td {
    background: transparent;
    border: 0;
    margin: 0;
    padding-bottom: 5px;
}

/* tables still need 'cellspacing="0"' in the markup */
table { background-color: transparent; border-collapse: collapse; border-spacing: 0; max-width: 100%; font-size: 13px}
table > tbody > tr > td { padding: 8px; }
.table-bordered { border: 1px solid #CCCCCC;}
.table-bordered > thead > tr > th,.table-bordered > thead > tr > td { background-color: #F5F5F6; border-bottom-width: 1px;}
.table-bordered > thead > tr > th,
.table-bordered > tbody > tr > th,
.table-bordered > tfoot > tr > th,
.table-bordered > thead > tr > td,
.table-bordered > tbody > tr > td,
.table-bordered > tfoot > tr > td { border: 1px solid #cccccc;}

body { color: rgb(7,39,54); font-family: "Open Sans", Helvetica,Arial, Geneva, sans-serif; margin: 20px 10px}

.pdf-header { border-bottom: 1px solid #333; text-align: center;}
.company-name { font-size: 21px; font-weight: bold;}
.address, .gst { font-size: 14px; }
.gst { font-weight: bold; }

table thead, table tfoot { border-bottom: 1px solid #333; border-top: 1px solid #333; }
table thead th, table tfoot th{ padding: 8px; text-align: left; }
.products tr td{ padding-bottom: 10px; padding-top: 10px; }
.signature{ font-size: 14px; text-align: right; margin-bottom: 30px; }
.auth{ font-size: 14px; text-align: right; }
.net-amount{ font-size: 16px; font-weight: bold; margin-bottom: 30px; text-align: right; }
.order-summary{ font-size: 14px; margin-right: 20px; }
.order-summary .table td{ text-align: right; }

</style>
</head>
<body>

    <div class="pdf-header">
        <div class="company-name"><?=_CompanyName?></div>
        <div class="address">406, Sonal Link Industrial Estate, Opposite Sai Palace Hotel,<br> Malad (West), Mumbai 400064</div>
        <div class="gst">GSTIN: <?=_GSTIN?></div>
    </div>

    <div>
        <table style="width: 100%">
            <tr>
                <td style="width:50%;vertical-align: top">
                    <table>
                        <tr>
                            <td>Invoice No.</td>
                            <td>: <?=$InvoiceNo?></td>
                        </tr>
                        <tr>
                            <td>Invoice Date</td>
                            <td>: <?=$InvoiceDate?></td>
                        </tr>
                    </table>
                </td>
                <td style="width:50%">
                    <table>
                        <tr>
                            <td>
                                <b>To:</b><br>
                                <?=$ShippingName?><br>
                                <?=$ShippingAddress?><br>
                                <?=$ShippingCity.' '.$ShippingPincode.', '.$ShippingState?><br>
                                Phone: <?=$ShippingPhone?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="products" style="width: 100%">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Rate (Rs.)</th>
                    <th>Quantity</th>
                    <th>Gross Amt<br>(Rs.)</th>
                    <th>Taxable Amt<br>(Rs.)</th>
                    <th>CGST<br>(Rs.)</th>
                    <th>SGST<br>(Rs.)</th>
                    <th>IGST<br>(Rs.)</th>
                    <th>Total<br>(Rs.)</th>
                </tr>
            </thead>
            <?php
            foreach($ProductDetails as $key => $row)
            {
                $Quantity = array();
                $TaxableAmount = $Tax = $CGST = $SGST = $IGST = 0;

                foreach(json_decode($row['Size'], true) as $size => $qty)
                {
                    $Quantity[]    = $qty;
                }
                $Quantity       = array_sum($Quantity);
                $TaxableAmount  = round( ($row['TotalCost'] * 100) / ($row['TaxRate'] + 100), 2 );
                $Tax            = $row['TotalCost'] - $TaxableAmount;

                if($ShippingState == _HomeState)
                {
                    $CGST = round($Tax / 2, 2);
                    $SGST = round($Tax / 2, 2);
                    $IGST = 0;
                }
                else
                {
                    $CGST = 0;
                    $SGST = 0;
                    $IGST = round($Tax, 2);
                }

                $TotCGST            = $TotCGST + $CGST;
                $TotSGST            = $TotSGST + $SGST;
                $TotIGST            = $TotIGST + $IGST;
                $TotTaxableAmount   = $TotTaxableAmount + $TaxableAmount;
                $GrandTotal         = $GrandTotal + $row['TotalCost'];

                ?>
                <tr>
                    <td><?=$row['ProductName']?></td>
                    <td><?=$row['FinalPrice']?></td>
                    <td><?=$Quantity?></td>
                    <td><?=$row['TotalCost']?></td>
                    <td><?=$TaxableAmount?></td>
                    <td><?=$CGST?></td>
                    <td><?=$SGST?></td>
                    <td><?=$IGST?></td>
                    <td><?=$row['TotalCost']?></td>
                </tr>
                <?php
            }
            ?>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>Total</th>
                    <th><?=$TotTaxableAmount?></th>
                    <th><?=$TotCGST?></th>
                    <th><?=$TotSGST?></th>
                    <th><?=$TotIGST?></th>
                    <th><?=$GrandTotal?></th>
                </tr>
            </tfoot>
        </table>

        <div class="order-summary">
            <table align="right" class="table">
                <tr>
                    <td>Grand Total</td>
                    <td style="text-align:right;width:100px"><?=FormatAmount($GrandTotal)?></td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td style="text-align:right">- <?=FormatAmount($CouponDiscount)?></td>
                </tr>
                <tr>
                    <td class="net-amount">Net Payable</td>
                    <td class="net-amount" style="text-align:right"><?=FormatAmount($GrandTotal - $CouponDiscount)?></td>
                </tr>
                <tr>
                    <td colspan="2" style="height:20px"></td>
                </tr>
                <tr>
                    <td colspan="2">Unifro Pvt. Ltd.</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <img src="<?=_HOST._InvoiceSignature?>">
                    </td>
                </tr>
                <tr>
                    <td colspan="2">Authorised Signatory</td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>

<?php
    $html = ob_get_clean();

    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
    $dompdf->render();
    $dompdf->stream( $FileName . ".pdf");
    exit;
?>