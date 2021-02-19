<?php

    $TotalCost = isset($TotalCost) ? $TotalCost : 0;
    $ActiveTab = 'Cart';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="robots" content="noindex,nofollow" />

        <title>Cart - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>


    </head>


    <body class="bg-lightgray my-account">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12">

                    <?php include_once("account-common-tabs.php"); ?>

                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="row">
                                <div class="col-lg-8">
                                    
                                </div>

                                <div class="col-lg-4">
                                    <div class="card-box gray">
                                        <h4 class="border-bottom-light pd-b-10 mg-t-0">ORDER SUMMARY</h4>
                                        <table class="table table-borderless">
                                        <tr>
                                            <td><h4>Total</h4></td>
                                            <td class="text-right"><h4><?=FormatAmount($TotalCost)?></h4></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">

                                            </td>
                                        </tr>
                                    </table>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>

    </body>
</html>