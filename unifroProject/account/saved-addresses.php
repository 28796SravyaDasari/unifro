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
        <meta name="robots" content="noindex,nofollow" />

        <title>My Saved Address - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

    </head>


    <body>

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">

            <div class="row">
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="/account/">My Account</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Addresses</li>
                    </ol>
                </nav>
            </div>

            <div class="row mg-t-30">

                <div class="col-sm-9">
                    <div>
                        <h4>My Addresses</h4>

                        <ul class="address-list">
                            <?php
                            if(count($DeliveryAddresses) > 0)
                            {
                                foreach($DeliveryAddresses as $address)
                                {
                                    if($address['DefaultAddress'] == '1')
                                    {
                                        $SetDefault = '<span class="mg-r-15"><img src="/images/mark-default.png" title="Default Delivery Address"></span>';
                                        $DefaultOption = ' hidden';
                                    }
                                    else
                                    {
                                        $SetDefault = '';
                                        $DefaultOption = '';
                                    }

                                    ?>
                                    <li class="card-box gray">
                                        <label><?=$address['ContactName']?></label>
                                        <div><?=$address['Address']?></div>
                                        <div><?=$address['CityName'].', '.$address['Name'].' '.$address['Pincode']?></div>
                                        <div class="mg-t-15">Mobile: <?=$address['Mobile']?></div>
                                        <div class="mg-t-15">
                                            <?=$SetDefault?>
                                            <a  class="default-address<?=$DefaultOption?>" href="javascript:void(0)"
                                                data-id="<?=$MemberID?>" data-pk="<?=$address['AddressID']?>" data-url="/ajax/customer/set-default-address/" onclick="SetDefaultAddress(this)">
                                                <i class="fa fa-id-card-o"></i>&nbsp; Set Default
                                            </a>
                                            <a href="/account/addresses/edit/?id=<?=$address['AddressID']?>"><i class="fa fa-pencil"></i>&nbsp; Edit</a>
                                            <a href="javascript:void(0)" data-id="<?=$MemberID?>" data-pk="<?=$address['AddressID']?>" data-url="/ajax/customer/save-address/"
                                                onclick="DeleteAddress(this)"><i class="fa fa-trash"></i>&nbsp; Delete
                                            </a>
                                        </div>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                                    <li class="card-box gray add">
                                        <a href="/account/addresses/add/">
                                            <i class="fa fa-plus"></i> <br>Add Address
                                        </a>
                                    </li>
                        </ul>

                    </div>  <!-- END OF TAB CONTENT -->

                </div>  <!-- END OF COL -->

                <?php include_once(_ROOT."/account/account-common.php"); ?>

            </div>  <!-- END OF ROW -->

        </div>  <!-- END OF CONTAINER -->

    </body>
</html>