<?php
    include_once("../include-files/autoload-server-files.php");
    CheckLogin('', '/sales-login/');

    if($_GET['cid'] > 0)
    {
        $_SESSION['ClientID'] = $_GET['cid'];
        header('Location: /custom/shirt/');
        exit;
    }

    $ActiveTab = 'Home';

    // GET THE CLIENTS LIST
    $MyClients = MysqlQuery("SELECT * FROM clients WHERE SalesID = '".$MemberID."'");
    $MyClients = MysqlFetchAll($MyClients);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">

        <title>Sales My Account - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

    </head>


    <body class="bg-lightgray my-account">

        <?php include_once(_ROOT."/include-files/header.php"); ?>
        <?php include_once(_ROOT."/include-files/common-scripts.php"); ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 mg-t-20">

                    <?php include_once("account-common-tabs.php"); ?>

                    <div class="tab-content">
                        <div class="tab-pane active" id="home-2">
                            <div class="mg-b-20">
                                <a class="btn btn-warning" href="add-client/"><i class="fa fa-plus"></i>&nbsp; Add Client</a>
                            </div>

                            <table class="table footable" data-expand-first="false" data-toggle-column="last">
                                <thead>
                                    <tr>
                                        <th data-breakpoints="xs sm">Client ID</th>
                                        <th>Client Name</th>
                                        <th data-breakpoints="xs sm">Email</th>
                                        <th data-breakpoints="xs sm">Mobile</th>
                                        <th data-breakpoints="xs sm">Registered On</th>
                                        <th data-breakpoints="md sm xs">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if(count($MyClients) > 0)
                                    {
                                        foreach($MyClients as $client)
                                        {
                                        ?>

                                            <tr>
                                                <td><?=$client['ClientID']?></td>
                                                <td><?=$client['ClientName']?></td>
                                                <td><?=$client['EmailID']?></td>
                                                <td><?=$client['Mobile']?></td>
                                                <td><?=FormatDateTime('dt', $client['RegisteredOn'])?></td>
                                                <td>
                                                    <a class="btn btn-info" href="/sales/clients/<?=$client['ClientID']?>/">View Products</a>
                                                    <a class="btn btn-warning" href="?cid=<?=$client['ClientID']?>">Add Product</a>
                                                </td>
                                            </tr>

                                        <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </body>
</html>