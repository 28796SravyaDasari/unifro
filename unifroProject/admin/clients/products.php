<?php

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if(is_numeric($_GET['s']))
    {
        $filter[] = "Status = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }
    if($_GET['q'] != '')
    {
        $_GET['q'] = CleanText($_GET['q']);
        $ActiveFilter['q'] = 'filtered';

        if(is_numeric($_GET['q']))
        {
            $filter[] = "f.SalesID = '".$_GET['q']."'";
        }
        else
        {
            $filter[] = "(FirstName LIKE '%".$_GET['q']."%' OR LastName LIKE '%".$_GET['q']."%')";
        }
    }

    // GET PRODUCTS CUSTOMIZED FOR CLIENT
    $ClientProducts = MysqlQuery("SELECT * FROM client_shopping_cart WHERE ClientID = '".$ClientID."'");
    $ClientProducts = MysqlFetchAll($ClientProducts);

    $ClientName = GetClientDetails($ClientID, 'ClientName');

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title>Client Products</title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>

    </head>
    <body>

        <?php include_once(_ROOT._AdminIncludesDir."admin-common-scripts.php"); ?>

        <div id="wrapper">
            <?php
            $SelectedPage = 'Products';
            include_once(_ROOT._AdminIncludesDir."admin-header.php");
            ?>
            <?php
            $SelectedPage = 'Clients';
            include_once(_ROOT._AdminIncludesDir."admin-sidebar.php");
            ?>

            <!-- ==============================================================
                Start of Right content here
            ============================================================== -->
            <div class="content-page">

                <div class="content">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-box">
                                    <a class="btn btn-white pull-right" href="<?=GoToLastPage()?>"><i class="fa fa-long-arrow-left"></i> Back to All Clients</a>
                                    <h3 class="mg-0"><?=GetClientDetails($ClientID, 'ClientName')?></h3>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-box">
                                    <form action="/admin/ajax/delete-selected/" id="FabricForm">
                                        <input type="hidden" name="option" value="Fabric" />
                                        <input type="hidden" name="FieldName" value="FabricID" />
                                        <table class="table" data-expand-first="false" data-toggle-column="last">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Product Details</th>
                                                    <th data-breakpoints="xs sm">Size / Quantity</th>
                                                    <th data-breakpoints="xs sm">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if(count($ClientProducts) > 0)
                                                {
                                                    $TotalCost = $TotalDiscount = $CorporateDiscount = 0;

                                                    foreach($ClientProducts as $product)
                                                    {
                                                        $ProductSize = array();
                                                        $ProductDetails = array();

                                                        $TotalCost = $TotalCost + $product['TotalCost'];
                                                        $TotalDiscount = $TotalDiscount + $product['TotalDiscount'];
                                                        $CorporateDiscount = $CorporateDiscount + $product['CorporateDiscount'];

                                                        if($product['CustomData'] != '')
                                                        {
                                                            $FabricID = 0;

                                                            $SizeLabels = json_decode($product['Size'], true);

                                                            $product['data'] = json_decode($product['CustomData'], true);
                                                            ksort($product['data']['Selections']['Styles']);

                                                            $ProductImage = '<img class="thumbnail" src="'.GetFabricDetails($product['data']['Selections']['FabricID'], 'FabricImage').'">';

                                                            $ProductDetails[] = '<ul class="custom-product-details">';

                                                            $ProductDetails[] = '<li class="bold">Custom Designed '.$product['ProductName'].'</li>';
                                                            $ProductDetails[] = '<li><b>Fabric:</b> '.GetFabricDetails($product['data']['Selections']['FabricID'], 'FabricName').'</li>';


                                                            foreach($product['data']['Selections']['Styles'] as $element => $styleID)
                                                            {
                                                                if( isset($styleID['ColorID']) )
                                                                {
                                                                    $ProductDetails[] = '<li>'.$element.' :
                                                                                            <span class="button-color" style="background-color:'.$styleID['ColorCode'].'">&nbsp;</span>
                                                                                         </li>';
                                                                }
                                                                else
                                                                {
                                                                    $ProductDetails[] = '<li>'.$element.' : '.GetStyleNameByID($styleID['StyleID']).'</li>';
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
                                                        }
                                                        else
                                                        {
                                                            // GET THE PRODUCT DEFAULT IMAGE
                                                            $GetDefaultImage = MysqlQuery("SELECT FileName FROM product_images
                                                                                           WHERE ProductID = '".$product['ProductID']."' AND DefaultImage = '1' LIMIT 1");
                                                            $GetDefaultImage = mysqli_fetch_assoc($GetDefaultImage)['FileName'];

                                                            $ProductDetails = '<div class="bold text-inverse">'.$product['ProductName'].'</div>';
                                                            $ProductImage = '<img class="thumbnail" src="'._ProductImageThumbDir.$GetDefaultImage.'">';
                                                        }

                                                        ob_start();
                                                        ?>

                                                        <tr>
                                                            <td><?=$ProductImage?></td>
                                                            <td><?=$ProductDetails?></td>
                                                            <td class="text-center">
                                                                <ul class="cart-size-qty">
                                                                <?php
                                                                foreach($SizeLabels as $k => $s)
                                                                {
                                                                    echo   '<li>
                                                                                <div class="input-group">
                                                                                    <div class="input-group-addon">
                                                                                        <label>'.$k.'</label>
                                                                                    </div>
                                                                                    <input type="text" class="form-control" name="Quantity['.$k.']" readonly value="'.$s.'" />
                                                                                </div>
                                                                            </li>';
                                                                }
                                                                ?>
                                                                </ul>
                                                            </td>
                                                            <td><?=FormatAmount($product['TotalCost'])?></td>
                                                        </tr>

                                                        <?php
                                                        ob_get_contents();
                                                    }
                                                }
                                                else
                                                {
                                                    echo '<tr>
                                                            <td class="no-data" colspan="5">No Products in the Cart</td>
                                                          </tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </form>
                                </div>

                                <?=$navs != '' ? '<div class="card-box">'.$navs.'</div>' : ''?>

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

	</body>
</html>