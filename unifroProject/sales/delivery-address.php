<?php
    include_once("../include-files/autoload-server-files.php");
    CheckLogin('', '/sales-login/');

    // GET CLIENT ADDRESS
    $ClientAddresses = MysqlQuery("SELECT a.*, s.Name, c.CityName FROM client_delivery_addresses a LEFT JOIN cities c ON c.CityID = a.City LEFT JOIN states s ON s.StateID = a.State WHERE a.ClientID = '".$ClientID."'");
    if(mysqli_num_rows($ClientAddresses) > 0)
    {
        $ClientAddresses = MysqlFetchAll($ClientAddresses);
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

    </head>


    <body class="bg-lightgray">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card-box">
                        <h3 class="mg-0"><?=GetClientDetails($ClientID, 'ClientName')?></h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">

                    <div>
                        <h4>Delivery Addresses</h4>

                        <ul class="address-list">
                            <?php
                            if(count($ClientAddresses) > 0)
                            {
                                foreach($ClientAddresses as $address)
                                {
                                    if($address['DefaultAddress'] == '1')
                                    {
                                        $SetDefault = ' checked';
                                        $DefaultOption = ' hidden';
                                    }
                                    else
                                    {
                                        $SetDefault = '';
                                        $DefaultOption = '';
                                    }

                                    ?>
                                    <li class="card-box gray">
                                        <div class="custom-radiobutton mg-b-10">
                                            <label class="font15">
                                                <input<?=$SetDefault?> type="radio" name="DeliveryAddress" value="<?=$address['AddressID']?>"><span></span> Choose Address
                                            </label>
                                        </div>
                                        <label><?=$address['ContactName']?></label>
                                        <div><?=$address['Address']?></div>
                                        <div><?=$address['CityName'].', '.$address['Name'].' '.$address['Pincode']?></div>
                                        <div class="mg-t-15">Mobile: <?=$address['Mobile']?></div>
                                        <div class="mg-t-15">
                                            <a  class="default-address<?=$DefaultOption?>" href="javascript:void(0)"
                                                data-id="<?=$ClientID?>" data-pk="<?=$address['AddressID']?>" data-url="/ajax/sales/set-default-address/" onclick="SetDefaultAddress(this)">
                                                <i class="fa fa-id-card-o"></i>&nbsp; Set Default
                                            </a>
                                            <a href="/sales/clients/edit-address/<?=$ClientID?>/?id=<?=$address['AddressID']?>"><i class="fa fa-pencil"></i>&nbsp; Edit</a>
                                            <a href="javascript:void(0)" data-id="<?=$ClientID?>" data-pk="<?=$address['AddressID']?>" data-url="/ajax/sales/add-address/"
                                                onclick="DeleteAddress(this)">
                                                <i class="fa fa-trash"></i>&nbsp; Delete
                                            </a>
                                        </div>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                                    <li class="card-box gray add">
                                        <a href="/sales/clients/add-address/<?=$ClientID?>/">
                                            <i class="fa fa-plus"></i> <br>Add Address
                                        </a>
                                    </li>
                        </ul>

                        <div>
                            <a class="btn btn-warning btn-lg" href="javascript:void(0)" onclick="PlaceClientOrder(this)">Place Order</a>&nbsp;&nbsp;
                            <a class="btn btn-white btn-lg" href="/sales/clients/<?=$ClientID?>/"><i class="fa fa-long-arrow-left"></i> Back</a>
                        </div>

                    </div>  <!-- END OF TAB CONTENT -->

                </div>  <!-- END OF COL -->

            </div>  <!-- END OF ROW -->

        </div>  <!-- END OF CONTAINER -->

    </body>
</html>