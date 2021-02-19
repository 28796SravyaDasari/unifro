<?php

    include_once("include-files/autoload-server-files.php");

    $FeaturedProducts = "SELECT p.*, pi.FileName FROM products p
                        LEFT JOIN product_images pi ON pi.ProductID = p.ProductID
                        WHERE p.Featured = '1' AND p.Status = '1' > 0 AND pi.DefaultImage = '1'
                        GROUP BY p.ProductID LIMIT 10";

    $FeaturedProducts = MysqlQuery($FeaturedProducts);
    $FeaturedTotal = mysqli_num_rows($FeaturedProducts);
    if($FeaturedTotal > 0)
    {
        $FeaturedProducts = MysqlFetchAll($FeaturedProducts);
    }

    // Fetch Banner Images
    $Banners = MysqlQuery("SELECT * FROM banners WHERE Status = '1' ORDER BY SortOrder LIMIT 5");
    if(mysqli_num_rows($Banners) > 0)
    {
        $Banners = MysqlFetchAll($Banners);
    }

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <META name="robots" content="index,follow" />

    <meta name="description" content="">
    <meta name="keywords" content="">

    <title>Unifro</title>

    <?php include_once("include-files/common-css.php"); ?>
    <link href="/css/slick.css" rel="stylesheet" type="text/css"/>
    <link href="/css/slick-theme.css" rel="stylesheet" type="text/css"/>

    <?php include_once("include-files/common-js.php"); ?>

    <script>

    $(document).ready(function()
    {
        $('.slick-slider').slick({
            autoplay: true,
            autoplaySpeed: 5000,
            infinite: true,
            speed: 1000,
            fade: true,
            slidesToShow: 1,
            arrows: true,
            infinite: true,
            pauseOnHover: false,
            cssEase: 'linear',
        });

        $('.slick-featured').slick({
            autoplay: true,
            infinite: true,
            slidesToShow: 4,
            slidesToScroll: 1,
            arrows: false,
            dots: true,
            infinite: true,
            cssEase: 'linear',
            variableWidth: true,
            variableHeight: true,
        });

        if(document.cookie.indexOf('newsletter_popup') == -1 && <?=$LoggedIn?'false':'true'?>)
        {
            ShowModal('<div id="newsletter_popup"><i class="fa fa-times" data-dismiss="modal"></i><div class="block-content text-center"><img src="<?=_LOGO?>" alt=""><h3>BE THE FIRST TO KNOW</h3><p>Subscribe to the Unifro eCommerce newsletter to receive timely updates from your favorite products.</p><div class="input-group"><input type="email" id="newsletter_popup_email" class="form-control" placeholder="Email Address" required><span class="input-group-btn"><button name="popup" class="btn btn-default" onclick="NewsletterSubscription(this, \'newsletter_popup_email\')" style="padding: 7px 12px;">Go!</button><div class="clearfix"></div></div></div><div class="custom-checkbox mg-t-10"><label><input type="checkbox" id="newsletter_popup_dont_show"><span></span> &nbsp;Don\'t show this popup again</label></div></div>');
            $('.show-modal').on('hidden.bs.modal', function ()
            {
                if($('#newsletter_popup_dont_show').prop('checked'))
                {
                    document.cookie = "newsletter_popup=dontshowitagain";
                }
            })
        }

    });
    </script>

</head>


<body class="home">

    <?php include_once(_ROOT."/include-files/header.php"); ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Start of Slider -->
            <div class="slick-slider">
                <?php
                foreach($Banners as $banner)
                {
                    echo '<div>
                            <a href="'.$banner['TargetLink'].'">
                                <img src="'._BannerDir.$banner['ImageName'].'"/>
                            </a>
                        </div>';
                }
                ?>
            </div>
            <!-- End of Slider -->
      </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="title-top">
                	    <h1>Make it your own</h1>
                   </div>
                </div>

                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                	<div class="pull-right mg-t-20">
                	    <a class="btn btn-warning" href="/custom/shirt/">CUSTOMIZE</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
            	    <div class="custom1">
            		    <img src="images/product.png">
            		    <p>Choose Product</p>
            	   </div>
                </div>

                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
            	    <div class="custom2">
            		    <img src="images/customize.png">
            		    <p>Customize it</p>
            	    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
    		        <div class="custom3">
    			        <img src="images/cart1.png">
    			        <p>Add To CARt</p>
    	            </div>
                </div>

       	        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
    		        <div class="custom4">
    			        <img src="images/delivery.png">
    			        <p>deliverey to your<br>doorstep</p>
    	            </div>
                </div>

            </div>
        </div>

        <div class="row">

            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="title-top">
                        <h1>Readymade Products</h1>
                   </div>
                </div>

                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="pull-right mg-t-20">
                	    <a class="btn btn-warning" href="/products/">View All</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12" style="padding:0px;">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12" style="padding:0px;">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;">
                            <div class="banner_img_height2"><img src="/images/boiler-suit.jpg" class="img-responsive"></div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;">
                            <div class="banner_img_height2"><img src="images/hotel-uniform.jpg" class="img-responsive"></div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12" style="padding:0px;">
                        <div class="banner_img_height"><img src="/images/school.jpg" class="img-responsive"></div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12" style="padding:0px;">
                    <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12" style="padding:0px;">
                        <div class="banner_img_height"><img src="/images/blazer.jpg" class="img-responsive" style="padding:0px;"></div>
                    </div>

                    <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12" style="padding:0px;">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12" style="padding:0px;">
                            <img src="/images/sports.jpg" class="img-responsive">
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12" style="padding:0px;">
                            <img src="/images/corporate.jpg" class="img-responsive" style="padding:0px;">
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;">
                            <img src="/images/hospital.jpg" class="img-responsive" >
                        </div>
                    </div>
                </div>

           </div>
        </div>

        <!---------------------------------------
            START OF FEATURED PRODUCTS
        ---------------------------------------->
        <div class="row">
            <div class="col-lg-12">
                <section class="featured-products">
                <?php
                if($FeaturedTotal > 0)
                {
                    echo '<h2 class="mg-b-20">FEATURED PRODUCTS</h2>';
                    echo '<div class="slick-featured">';
                    foreach($FeaturedProducts as $product)
                    {
                        if($product['Discount'] > 0)
                        {
                            if($product['DiscountType'] == '%')
                            {
                                $DiscountPrice = round(($product['Rate'] * $product['Discount']) / 100);
                                $FinalPrice = $product['Rate'] - $DiscountPrice;
                                $Discount = floatval($product['Discount']).'% off';
                            }
                            else
                            {
                                $FinalPrice = $product['Rate'] - $product['Discount'];
                                $Discount = '<i class="fa fa-rupee"></i> '.floatval($product['Discount']).' off';
                            }

                            $price =    '<span class="strike-through"><i class="fa fa-rupee"></i> '.$product['Rate'].'</span>';
                            $price .=   '<span class="price"><i class="fa fa-rupee"></i> '.$FinalPrice.'</span>';
                            $price .=   '<span class="discount">'.$Discount.'</span>';
                        }
                        else
                        {
                            $price = '<span class="price"><i class="fa fa-rupee"></i> '.$product['Rate'].'</span>';
                        }

                        ?>
                        <div class="item">
                            <!-- Product Image -->
                            <figure class="product-grid-image">
                                <a href="<?=$product['ProductURL']?>" title="<?=$product['ProductName']?>">
                                    <img class="img-responsive" src="<?=_ProductImageThumbDir.$product['FileName']?>">
                                </a>
                            </figure>
                            <div class="product-content">
                                <div class="product-inner-content">
                                    <!-- Product Title -->
                                    <div class="product-title">
                                        <h4>
                                            <a href="<?=$product['ProductURL']?>"><?=$product['ProductName']?></a>
                                        </h4>
                                    </div>
                                    <div class="product-price">
                                        <?=$price?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    echo '</div>';
                }
                ?>
                </section>
            </div>
        </div>

        <!---------------------------------------
            END OF FEATURED PRODUCTS
        ---------------------------------------->

    </div>

    <div class="container-fluid abt-bg">
        <div class="row">
            <div class="container">
                <div class="col-lg-offset-6 col-lg-6 col-md-offset-6 col-md-6 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="abt">
                        <h1>About Unifro</h1>
                        <p>Unifro is a pioneer in School & Corporate Uniforms and its Essentials. The parent company was established in 1988 and is an Expert in Textile and Garment manufacturing. Unifro spreaded it wings across India and has left a considerable mark in the International Market too.</p>
                        <div class="mg-t-20">
                    	    <a class="btn btn-warning" href="/about/">VIEW MORE</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once(_ROOT."/include-files/footer.php"); ?>

    <script src="/js/slick.min.js"></script>

</body>
</html>