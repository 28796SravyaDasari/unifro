<?php

    include_once("../include-files/autoload-server-files.php");
    include_once("custom-design-vars.php");

    if($MemberType == 'Sales' && !is_numeric($_SESSION['ClientID']))
    {
        $_SESSION['AlertMessage'] = 'Click on Add Product button listed against the client name';
        header('Location: /sales/');
        exit;
    }

    $NavigationMenu = MysqlQuery("SELECT * FROM master_categories WHERE ParentID = '1' AND Status = '1' ORDER By SortOrder");
    for(;$row = mysqli_fetch_assoc($NavigationMenu);)
    {
        $Menu .= '<li><a href="'.$row['CategoryURL'].'">'.$row['CategoryTitle'].'</a></li>';
    }

    if($EditMode)
    {
        $CartBtnLabel = 'Update Cart';

        if($MemberType == 'Sales')
        {
            $JsonData = mysqli_fetch_assoc(MysqlQuery("SELECT CustomData FROM client_shopping_cart WHERE CartID = '".$CartID."' LIMIT 1"))['CustomData'];
        }
        else
        {
            $JsonData = mysqli_fetch_assoc(MysqlQuery("SELECT CustomData FROM customer_shopping_cart WHERE CartID = '".$CartID."' LIMIT 1"))['CustomData'];
        }
        $JsonData = json_decode($JsonData, true);

        // If FabricID is not set in the Fabriclist fetched in custom-design-vars.php then add the ID to the list
        if(!isset($FabricsList[$JsonData['Selections']['FabricID']]))
        {
            $FabricData = GetFabricDetails($JsonData['Selections']['FabricID']);
            $FabricsList[$JsonData['Selections']['FabricID']] = $FabricData;
        }
        $DefaultStyles = array($JsonData['Selections']['Styles']);
        $DefaultFabric['FabricID'] = $JsonData['Selections']['FabricID'];

    }

    // $FabricsList is set in custom-design-vars.php
    $FabricsList = json_encode($FabricsList);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <META name="robots" content="index,follow" />
        <meta name="description" content="<?=$MetaDescription?>">
        <META name="keywords" content="<?=$MetaKeywords?>" />

        <title>Design your <?=$CategoryDetails['CategoryTitle']?> Online - Unifro</title>

        <LINK rel="canonical" href="<?=_HOST.$CategoryDetails['CategoryURL']?>">	<!-- Define Canonical !-->

        <?php include_once("../include-files/common-css.php"); ?>
        <link rel="stylesheet" type="text/css" href="/css/slick.css"/>
        <link rel="stylesheet" type="text/css" href="/css/slick-theme.css"/>
        <link rel="stylesheet" type="text/css" href="/css/nouislider.css"/>

        <style>
            body, html{ height: 100%; }
            #CUFF-04, #CUFF-05, #CUFF-06, #BTN-04, #BTN-02, #BTN-03, [id^=Elbow_Patch]{ display: none; }

        </style>

        <?php include_once("../include-files/common-js.php"); ?>
        <script src="/js/jquery.bootpag.min.js"></script>
        <script src="/js/wNumb.js"></script>
        <script src="/js/nouislider.min.js"></script>

        <script>
        var FabricsList = <?=$FabricsList?>;
        var ColorList = <?=$ColorList?>;
        var activeSVGID = '';
        var SVGData = <?=json_encode($ProductSVGs)?>;
        var SVGObjects = <?=$SVGObjects?>;
        var MasterSymbols = <?=$MasterSymbols?>;
        var SubHeadings = <?=$SubHeadings?>;
        var StyleIDByHeading = <?=$StyleIDByHeading?>;
        var Percentage = <?=$Percentage?>;
        var TotalPrice = 0;

        $(document).ready(function()
        {
            $('.pre-loader').addClass('loaderout');

            $.each(MasterSymbols, function(heading, symbols)
            {
                product.defineSymbols(heading, symbols);
            });

            $('.sidebar-content').find('ul li a').click(function()
            {
                $(this).closest('section').find('li a').removeClass('active');
                $(this).addClass('active');
            });

            $('.price-container .cartBtn').click(function()
            {
                Add2CartCustom(product.toJson());
                //AlertBox(product.toJson());
            });

            //$('.svg-container').find('ul').html('<li data-id="'+ Object.keys(SVGObjects)[0] +'" class="active">'+ SVGObjects[Object.keys(SVGObjects)[0]] +'</li>');

            $("#sidebar-content").mCustomScrollbar({ theme: "3d-dark", scrollButtons:{enable:false}, mouseWheel:{ preventDefault: true }});

            product.LoadDefaults({
                SVGType: '<?=$CategoryDetails['SVGType']?>',
                CategoryID: <?=$CategoryDetails['CategoryID']?>,
                FabricID: <?=$DefaultFabric['FabricID']?>,
                Styles: <?=json_encode($DefaultStyles)?>,
                Price: <?=$Price?>
            });

            $('.fabric-list-wrapper .fa-close').on('click', function(){
                $('.fabric-list-wrapper').removeClass("show");
            });

            ListFabrics({ id: <?=$CategoryDetails['CategoryID']?>, ContentHolder: '#content' });

            $('.elements-nav').slick({
                dots: false,
                infinite: false,
                speed: 300,
                slidesToShow: 10,
                slidesToScroll: 8,
                responsive: [
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 5,
                            slidesToScroll: 5,
                        }
                    },
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 5,
                            slidesToScroll: 5,
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 4,
                            slidesToScroll: 4
                        }
                    },
                    {
                        breakpoint: 320,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    }
                ]
            });
        })

        </script>

    </head>

<body>

    <div class="pre-loader">
        <div class="cssload-container">
            <div class="cssload-loading"><i></i><i></i><i></i><i></i></div>
        </div>
    </div>

    <div id="wrapper">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <?php include_once("custom-design-sidebar.php"); ?>

        <div class="content-page">

            <!-- Start content -->
            <div class="content">

                <div class="svg-container">
                    <ul>
                        <?php
                        $i = 0;
                        foreach($ProductSVGs as $id => $xml)
                        {
                            echo '<li data-id="'.$id.'"'.($i==0?' class="active"':'').'>'.file_get_contents($xml['SVGName']).'</li>';
                            $i++;
                        }
                        ?>
                    </ul>

                    <div class="price-container">
                        <div class="input-group">
                            <div class="price input-group-addon"></div>
                            <div class="input-group-btn">
                                <button class="btn btn-default cartBtn" data-id="<?=$CartID?>" data-url=<?=$AjaxCartURL?>><i class="fa fa-shopping-bag"></i>&nbsp; <?=$CartBtnLabel?></button>
                            </div>

                            <?php if($MemberType != 'Sales') { ?>

                            <div id="cart">
                                 <a href="<?=$CartURL?>" class="btn btn-block btn-lg">
                                    <span id="cart-total"><span class="cart-number"><?=$ProductsInCart?></span></span>
                                </a>
                            </div>

                            <?php } ?>

                        </div>

                    </div>
                </div>

                <div class="elements-scroller">
                <?php
                // FETCH THE CATEGORY ELEMENTS FROM THE DATABASE
                if(count($Elements) > 0)
                {
                        echo '<div class="elements-nav carousel">';

                        echo '<div class="slide">
                                    <a class="active" data-element="Fabric" onclick="product.LoadSidebarStyles(this)">
                                        <div class="icon" style="background: #fff url('._ElementIconDir.'fabric.png) no-repeat center"></div>
                                        <label>Fabric</label>
                                    </a>
                                </div>';

                        foreach($Elements as $row)
                        {
                            echo '<div class="slide">
                                    <a data-element="'.$row['ElementName'].'" data-rel="'.$row['SVGID'].'" onclick="product.LoadSidebarStyles(this)">
                                        <div class="icon" style="background: #fff url('._ElementIconDir.$row['ElementIcon'].') no-repeat center"></div>
                                        <label>'.$row['ElementDisplayName'].'</label>
                                    </a>
                                  </div>';
                        }
                        echo '</div>';
                }
                else
                {
                        echo '<div class="no-data">No Elements Found!</div>';
                }
                ?>
                </div>

            </div>
            <!-- End of content -->

        </div>

    </div>

    <script src="/js/slick.min.js"></script>

</body>

</html>