<?php
    include_once("../include-files/autoload-server-files.php");
    CheckLogin('', '/sales-login/');

    if(isset($_GET['add']))
    {
        header('Location: /custom/shirt/');
        exit;
    }

    $_SESSION['ClientID'] = $ClientID;
    //unset($_SESSION['CartDetails']);


    $customdata = '{"Selections":{"CategoryID":7,"FabricID":46,"Styles":{"Shirt Style":{"StyleID":"84","SymbolID":"#Texture-Shirt-Full"},"Pinafore Variants":{"StyleID":"161","SymbolID":"#Pinafore-2","Pinafore Fabric":{"FabricID":"47","StyleID":168,"StyleName":"Pinafore Fabric","SymbolID":"#Pinafore-2","Fabric":{"FabricID":51,"StyleID":"168","StyleName":"Fabric","SymbolID":"#Pinafore-1,#Pinafore-2,#Pinafore-3,#Pinafore-4,#Pinafore-5,#Pinafore-6"}}}},"Price":{"168":1000,"BasePrice":1200,"FabricPrice":800,"TotalPrice":3600}}}';

    //JsonPrettyPrint($customdata);
    //exit;

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">

        <title>Client Products - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            LoadCartProducts();

            options = {
                        showThumbs: false,
                        limit: 5,
                        maxSize: null,
                        changeInput: true,
                        onSelect: function(item, listEl, parentEl, newInputEl, inputEl)
                        {

                        },
                        afterShow: function(){
                            //$('#tab-pictures').find('form').submit();
                        },
                    };

            

        }); // END OF DOCUMENT READY
        </script>

    </head>


    <body class="bg-lightgray">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card-box">
                        <a class="btn btn-white pull-right" href="/sales/"><i class="fa fa-long-arrow-left"></i> Back to All Clients</a>
                        <h3 class="mg-0"><?=GetClientDetails($ClientID, 'ClientName')?></h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <ul class="nav nav-tabs responsive-tabs" style="width: 100%;">
                        <li class="active tab"><a data-toggle="tab" href="#tab-products">Saved Products</a></li>
                        <li class="tab"><a href="/sales/clients/orders/<?=$ClientID?>/">Orders</a></li>
                    </ul>
                    <div class="tab-content">
                        <!-----------------------------
                            START OF SAVED PRODUCTS TAB
                        ------------------------------>
                        <div class="tab-pane active" id="tab-products">
                            <div class="clearfix">
                                <div class="col-md-9">
                                    <table class="table footable" data-expand-all="true" id="CartTable">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Product</th>
                                                <th>Product Details</th>
                                                <th data-breakpoints="xs sm">Size / Quantity</th>
                                                <th data-breakpoints="xs sm">Amount</th>
                                                <th data-breakpoints="md sm xs">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>

                                    <div>
                                        <a class="btn btn-primary" href="?add">Add More Products</a>
                                    </div>
                                </div>

                                <?php if(!$Nodata) { ?>

                                <div class="col-lg-3">
                                    <div class="card-box gray">
                                        <h4 class="border-bottom-light pd-b-10 mg-t-0">ORDER SUMMARY</h4>
                                        <table class="table table-borderless" id="order-summary"></table>
                                    </div>
                                </div>

                                <?php } ?>

                            </div>
                        </div>
                        <!-----------------------------
                            END OF SAVED PRODUCTS TAB
                        ------------------------------>

                    </div>  <!-- END OF TAB CONTENT -->

                </div>  <!-- END OF COL -->

            </div>  <!-- END OF ROW -->

        </div>  <!-- END OF CONTAINER -->

    </body>
</html>