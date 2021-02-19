<?php

    CheckLogin();

    // GET CLIENT ADDRESS
    $DeliveryAddresses = MysqlQuery("SELECT a.*, s.Name, c.CityName FROM customer_delivery_addresses a
                                    LEFT JOIN cities c ON c.CityID = a.City
                                    LEFT JOIN states s ON s.StateID = a.State
                                    WHERE a.MemberID = '".$MemberID."'");
    if(mysqli_num_rows($DeliveryAddresses) > 0)
    {
        $DeliveryAddresses = MysqlFetchAll($DeliveryAddresses);
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">

        <title>Delivery Address - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            LoadCartProducts();

        }); // END OF DOCUMENT READY
        </script>
    </head>


    <body data-relation="Customer" data-location="Address">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">

            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="/shopping-bag/">Shopping Bag</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Delivery Address</li>
                </ol>
            </nav>

            <div class="row mg-t-30">

                <div class="col-lg-8">
                    <div>
                        <h4>Delivery Addresses</h4>

                        <ul class="address-list">
                            <?php
                            if(count($DeliveryAddresses) > 0)
                            {
                                foreach($DeliveryAddresses as $address)
                                {
                                    ?>
                                    <li class="card-box gray">
                                        <label><?=$address['ContactName']?></label>
                                        <address>
                                            <div><?=$address['Address']?></div>
                                            <div><?=$address['CityName'].', '.$address['Name'].' '.$address['Pincode']?></div>
                                            <div class="mg-t-15">Mobile: <?=$address['Mobile']?></div>
                                        </address>

                                        <div class="mg-t-15">
                                            <a href="/checkout/edit-addresses/?id=<?=$address['AddressID']?>"><i class="fa fa-pencil"></i>&nbsp; Edit</a>
                                            <a href="javascript:void(0)" data-id="<?=$MemberID?>" data-pk="<?=$address['AddressID']?>"  data-url="/ajax/customer/save-address/"
                                                onclick="DeleteAddress(this)">
                                                <i class="fa fa-trash"></i>&nbsp; Delete
                                            </a>
                                        </div>
                                        <div class="mg-t-15">
                                            <button class="btn btn-info" data-id="<?=$address['AddressID']?>" onclick="SetDeliveryAddress(this)">
                                                <?=$_SESSION['CartDetails']['AddressID'] == $address['AddressID'] ? '<i class="fa fa-check text-warning"></i>&nbsp;' : ''?>Deliver Here
                                            </button>
                                        </div>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                                    <li class="card-box gray add">
                                        <a href="/checkout/add-address/">
                                            <i class="fa fa-plus"></i> <br>Add Address
                                        </a>
                                    </li>
                        </ul>

                    </div>  <!-- END OF TAB CONTENT -->

                </div>  <!-- END OF COL -->

                <div class="col-md-4">
                    <div class="card-box gray">
                        <h4 class="border-bottom-light pd-b-10 mg-t-0">ORDER SUMMARY</h4>
                        <table class="table table-borderless" id="order-summary">

                        </table>
                    </div>
                </div>

            </div>  <!-- END OF ROW -->

        </div>  <!-- END OF CONTAINER -->

    </body>
</html>