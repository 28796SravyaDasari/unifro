<?php
    $customdata = '{"Selections":{"CategoryID":3,"FabricID":3572,"Styles":{"Collar Style":{"StyleID":"11","SymbolID":"#Col-Button-Down"},"Placket Style":{"StyleID":"12","SymbolID":"#Placket-Normal-placket"},"Pocket Style":{"StyleID":"21","SymbolID":"#Pocket-V-Shaped"},"Bottom Variant":{"StyleID":"30","SymbolID":"#Bottom-straight-cut"},"Sleeve Variant":{"StyleID":"32","SymbolID":"#Sleeve-half"},"Elbow Patch":{"StyleID":"260","SymbolID":"#Elbow-patch"},"Monogram":{"StyleID":"254","SymbolID":"#Mono-on-pocket"},"Epaulette Style":{"StyleID":"257","SymbolID":"#Epaulette","Epaulette Fabric":{"Fabric":{"FabricID":3406,"StyleID":"259","StyleName":"Fabric","SymbolID":"#Epaulette"}}}},"Price":{"259":150,"BasePrice":1200,"FabricPrice":1000,"TotalPrice":2350,"":0}}}';

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">

        <title>Shopping Bag - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <style>
        .error{ text-align: center; }
        </style>

        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            LoadCartProducts();

        }); // END OF DOCUMENT READY
        </script>

    </head>


    <body class="bg-lightgray" data-relation="Customer">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card-box mg-t-20">
                        <div class="font19">Your Shopping Bag</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card-box">
                        <!-----------------------------
                            START OF SAVED PRODUCTS TAB
                        ------------------------------>
                        <div id="tab-products">
                            <div class="clearfix">
                                <div class="col-md-9">
                                    <table class="table footable" data-expand-all="true" data-toggle-column="false" id="CartTable">
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