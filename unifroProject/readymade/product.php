<?php

    if($MemberType == 'Sales' && !is_numeric($_SESSION['ClientID']))
    {
        $_SESSION['AlertMessage'] = 'Click on Add Product button listed against the client name';
        header('Location: /sales/');
        exit;
    }

    /*----------------------
        GET PRODUCT IMAGES
    ----------------------*/
    $GetProductImages = MysqlQuery("SELECT * FROM product_images WHERE ProductID = '".$ProductID."'");
    $GetProductImages = MysqlFetchAll($GetProductImages);
    $GetProductImages = SortArray($GetProductImages, 'DefaultImage', 'd');

    /*-------------------------
        HTML FOR PRODUCT PRICE
    -------------------------*/
    if($ProductDetails['Discount'] > 0)
    {
        if($ProductDetails['DiscountType'] == '%')
        {
            $DiscountPrice = round( ($ProductDetails['Rate'] * $ProductDetails['Discount']) / 100 );
            $FinalPrice = $ProductDetails['Rate'] - $DiscountPrice;
            $Discount = floatval($ProductDetails['Discount']).'% off';
        }
        else
        {
            $FinalPrice = $ProductDetails['Rate'] - $ProductDetails['Discount'];
            $Discount = '<i class="fa fa-rupee"></i> '.floatval($ProductDetails['Discount']).' off';
        }

        $price =    '<span class="strike-through"><i class="fa fa-rupee"></i> '.$ProductDetails['Rate'].'</span>';
        $price .=   '<span class="price"><i class="fa fa-rupee"></i> '.$FinalPrice.'</span>';
        $price .=   '<span class="discount">'.$Discount.'</span>';
    }
    else
    {
        $price = '<span class="price"><i class="fa fa-rupee"></i> '.$ProductDetails['Rate'].'</span>';
    }

    /*-------------------------
        FOR RATINGS
    -------------------------*/
    $OverallRatings = floor($ProductDetails['Rating'] * 2) / 2;

    if($LoggedIn)
    {
        // Get the ratings given by the user for this product
        $MemberRatings = mysqli_fetch_assoc(MysqlQuery("SELECT Rating FROM product_ratings WHERE MemberID = '".$MemberID."' AND ProductID = '".$ProductID."' LIMIT 1"))['Rating'];
    }

    $GetReviews = MysqlQuery("SELECT pr.*, c.FirstName AS ReviewBy, ratings.Rating FROM product_reviews pr LEFT JOIN product_ratings ratings ON ratings.ProductID = pr.ProductID AND ratings.MemberID = pr.ReviewBy LEFT JOIN customers c ON c.MemberID = pr.ReviewBy WHERE pr.ProductID = '".$ProductID."' AND pr.Status = '1' ORDER BY pr.SortOrder DESC LIMIT 20");
    $NoOfReviews = mysqli_num_rows($GetReviews);

    /*-----------------------------
        FOR RELATED PRODUCTS
    -----------------------------*/
    //GetProductCategories($ProductID);
    // Get Product Categories
    $ProductCategories = MysqlQuery("SELECT CategoryID FROM product_categories WHERE ProductID = '".$ProductID."'");
    for(;$row = mysqli_fetch_assoc($ProductCategories);)
    {
        $Cats[] = $row['CategoryID'];
    }

    $RelatedProducts = "SELECT p.*, pi.FileName FROM products p
                        LEFT JOIN product_categories pc ON pc.ProductID = p.ProductID
                        LEFT JOIN master_categories c ON c.CategoryID = pc.CategoryID
                        LEFT JOIN product_images pi ON pi.ProductID = p.ProductID
                        WHERE p.ProductID <> '".$ProductID."' AND p.Discount > 0 AND p.Status = '1' > 0 AND c.Status = '1' AND pi.DefaultImage = '1'
                        AND pc.CategoryID IN (".implode(',',$Cats).")
                        GROUP BY p.ProductID ORDER BY RAND() LIMIT 4";

    if(mysqli_num_rows($RelatedProducts) == 0)
    {
        //	IF NO DISCOUNTED PRODUCTS FOUND, CHOOSE RELATED PRODUCTS
        $RelatedProducts = "SELECT p.*, pi.FileName FROM products p
                            LEFT JOIN product_categories pc ON pc.ProductID = p.ProductID
                            LEFT JOIN master_categories c ON c.CategoryID = pc.CategoryID
                            LEFT JOIN product_images pi ON pi.ProductID = p.ProductID
                            WHERE p.ProductID <> '".$ProductID."' AND p.Status = '1' AND c.Status = '1' AND pi.DefaultImage = '1'
                            AND pc.CategoryID IN (".implode(',',$Cats).")
                            GROUP BY p.ProductID ORDER BY RAND() LIMIT 4";
    }

    $RelatedProducts = MysqlQuery($RelatedProducts);
    $RelatedProducts = MysqlFetchAll($RelatedProducts);

    /*----------------------
        GET PRODUCT SIZES
    ----------------------*/
    $GetProductStock = MysqlQuery("SELECT Size FROM master_categories WHERE CategoryID = '".$Cats[0]."' LIMIT 1");
    for(;$row = mysqli_fetch_assoc($GetProductStock);)
    {
        $GetProductStock = json_decode($row['Size'], true);
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <meta name="robots" content="index,follow" />
        <meta name="Description" content="Buy <?=$ProductDetails['ProductName']?> online at Rs. <?=$ProductDetails['Rate']?>/- only. Online shopping store for quality Kashmiri products in India.">
        <meta name="keywords" content="<?=$ProductDetails['ProductName']?>, buy, online, shopping, save, low price, kashmiri, India">

        <title><?=$MetaTitle?></title>

        <link rel="canonical" href="<?=$ProductDetails['ProductURL']?>">	<!-- Define Canonical !-->

        <?php include_once(_ROOT._IncludesDir."common-css.php"); ?>
        <link rel="stylesheet" type="text/css" href="/css/slick.css"/>
        <link rel="stylesheet" type="text/css" href="/css/slick-theme.css"/>
        <link rel="stylesheet" type="text/css" href="/css/star-rating.min.css"/>

        <?php include_once(_ROOT._IncludesDir."common-js.php"); ?>
        <script src='/js/intense.js?v=1'></script>
        <SCRIPT type="text/javascript" src="/js/star-rating.min.js"></SCRIPT>

        <script>
        $(document).ready(function()
        {
            $('.product-nav').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                mobileFirst: true
            });

            $('.product-stock li').on('click', function()
            {
                $('.product-stock li').removeClass('active');
                $(this).addClass('active');

                /*
                var qty = $(this).attr('data-qty');
                if(qty >= 5)
                {
                    $('.stock').html(qty +' products in stock');
                }
                else
                {
                    $('.stock').html('Hurry! only '+ qty +' left');
                }
                */
            });

            $('.rnr').rating({
                'showCaption':true,
                'stars':'5',
                'min':'0',
                'max':'5',
                'step':'0.5',
                'size':'xs',
                'clearCaption': '',
                'starCaptions': {.5:"0.5",1:"1",1.5:"1.5",2:"2",2.5:"2.5",3:"3",3.5:"3.5",4:"4",4.5:"4.5",5:"5"},
                'starCaptionClasses':{.5:"label label-danger",1:"label label-danger",1.5:"label label-warning",2:"label label-warning",2.5:"label label-info",3:"label label-info",3.5:"label label-primary",4:"label label-primary",4.5:"label label-success",5:"label label-success"}
            });

            $("#AddReview").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();

                AjaxResponse = AjaxFormSubmit(this);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        $('#ReviewModal').modal('hide');
                        AlertBox(response.response_message, 'success', function(){ location.reload(); });
                    }
                    else if(response.status == 'login')
                    {
                        AlertBox('Please sign in to post a review!', 'error', function(){ location.href = '/login/' } );
                    }
                    else if(response.status == 'validation')
                	{
                	    ThrowError(response.error, true);
                	}
                    else
                    {
                        AlertBox(response.response_message);
                    }
                });

            });

        });
        </script>
    </head>

    <body>

        <?php include_once(_ROOT."/include-files/common_scripts.php"); ?>
        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container product-page">

            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?=$_SESSION['ProductListURL']?>"><?=$_SESSION['CategoryHeading']?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?=$ProductDetails['ProductName']?></li>
                </ol>
            </nav>

            <div class="row">

                <div class="col-md-4 col-sm-5">
                <?php
                    echo '<div class="product-nav carousel dark">';
                        foreach($GetProductImages as $image)
                        {
                            echo    '<div class="slide" data-image="'._ProductImageDir.$image['FileName'].'">
                                        <img src="'._ProductImageThumbDir.$image['FileName'].'">
                                    </div>';
                        }

                    echo '</div>';
                ?>
                </div>

                <div class="col-md-8 col-sm-7 col-md-push-1">
                    <form>
                        <div class="product-name">
                            <h1><?=$ProductDetails['ProductName']?></h1>
                        </div>
                        <div class="product-price">
                            <?=$price?>
                        </div>

                        <?php
                        if($ProductDetails['Status'] == '1')
                        {
                            if($TotalStock != 0)
                            {
                                echo '<div class="no-stock">Out of Stock</div>';
                            }
                            else
                            {
                                if($MemberType != 'Sales')
                                {
                                    ?>
                                    <div class="product-stock">
                                        <label>Select Size</label>
                                        <ul>
                                            <?php
                                            foreach($GetProductStock as $stock)
                                            {
                                                echo '<li><a>'.$stock.'</a></li>';
                                            }
                                            ?>
                                        </ul>
                                        <div class="stock"></div>
                                    </div>

                                    <div class="delivery-pincode">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-map-marker"></i>
                                            </span>
                                            <input type="number" class="form-control" placeholder="Enter Pincode">
                                            <span class="input-group-btn">
                                                <button class="btn btn-secondary" onclick="ValidateDeliveryPincode(this)" type="button">Check</button>
                                            </span>
                                        </div>
                                    </div>

                                    <?php
                                }
                                ?>

                                <div class="add-btn">
                                    <button type="button" class="btn btn-warning" data-id=<?=$ProductID?> data-url=<?=$AjaxCartURL?> onclick="AddToBag(this)">
                                        <i class="fa fa-shopping-bag"></i>&nbsp; <?=$CartBtnLabel?>
                                    </button>
                                </div>

                                <?php
                            }
                        }
                        else
                        {
                            echo '<div class="no-stock">Sorry! This product is no longer available.</div>';
                        }
                        ?>

                        <div class="product-desc-short">
                            <?=$ProductDetails['ShortDescription']?>
                        </div>

                        <div class="product-desc-short">
                            <h3>Description</h3>
                            <pre><?=$ProductDetails['FullDescription']?></pre>
                        </div>
                        <!-- Go to www.addthis.com/dashboard to customize your tools -->
                        <div class="addthis_inline_share_toolbox"></div>
                    </form>
                </div>
            </div>

            <!------------------------------------------------
                START OF REVIEWS & RATINGS TABS
            ------------------------------------------------->
            <div class="row">
                <div class="col-lg-12">
                    <div class="product-tabs pd-t-30">
                        <ul class="nav nav-tabs responsive-tabs" style="width: 100%;">
                            <li class="active tab"><a data-toggle="tab" href="#tab_reviews">Reviews & Ratings</a></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_reviews">
                                <div class="clearfix">
                                    <div class="rate-product">
                                        <label class="control-label">Overall Ratings</label><br>
                                        <div class="pull-left">
                                            <?php
                                            if($OverallRatings)
                                            {
                                                echo '<input name="ratings" class="rnr rating-loading" data-show-clear="false" data-hover-enabled="false" data-size="xs"
                                                            value="'.$OverallRatings.'">';
                                            }
                                            else
                                            {
                                                echo 'Not Rated';
                                            }
                                            ?>
                                        </div>

                                        <a class="font17 btn btn-warning pull-right" data-toggle="modal" data-target="#ReviewModal"><i class="fa fa-pencil"></i> &nbsp;Review this Product</a>
                                    </div>

                                    <div class="reviews">
                                    <?php
                                    if($NoOfReviews)
                                    {
                                        for(; $review = mysqli_fetch_assoc($GetReviews);)
                                        {
                                            ?>
                                            <div class="comment-content">
                                                <p class="font15"><?=$review['Review']?></p>
                                                <div class="comment-content-head">
                                                <?php
                                                if($review['Rating'] != '')
                                                {
                                                    ?>
                                                    <div class="stars">
                                                        <?=$review['Rating']?> <i class="fa fa-star"></i>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                                    <div class="comment-title"><?=$review['ReviewBy'].', '.FormatDateTime('d', $review['ReviewDate']).'</SPAN>'?></div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    else
                                    {
                                        echo '<div class="mg-t-10">Be the first one to write a review for this product!</div>';
                                    }
                                    ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!---------------------------------------
                START OF ALSO BOUGHT
            ---------------------------------------->
            <div class="row mg-t-30">
                <div class="col-lg-12">

                </div>

                <div class="grid row">
                    <div class="products-wrapper">
                        <?php
                        if(count($RelatedProducts) > 0)
                        {
                            echo '<h2 class="bold text-center mg-b-30">Related Products</h2>';

                            foreach($RelatedProducts as $product)
                            {
                                echo ProductWidget($product);
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!---------------------------------------
                END OF ALSO BOUGHT
            ---------------------------------------->
        </div>

        <div class="modal fade" id="ReviewModal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Write Review</h4>
                    </div>
                    <div class="modal-body">
                        <form action="/ajax/customer/save-review/" id="AddReview">
                            <input type="hidden" name="ProductID" value="<?=$ProductID?>">
                            <div class="write-review">
                                <div class="form-group">
                                    <label>Review</label><br>
                                    <textarea class="form-control" name="review" rows="5" placeholder="(Optional)"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Rate This Product</label><br>
                                    <input name="ProductRating" class="rnr" data-show-clear="false" value="<?=$MemberRatings?>">
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary">Submit</button>
                                    <button class="btn btn-danger" data-dismiss="modal" type="button">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once(_ROOT."/include-files/footer.php"); ?>

        <script src="/js/slick.min.js"></script>
        <script>
            var elements = document.querySelectorAll( '.slide' );
            Intense( elements );
        </script>

        <!-- Go to www.addthis.com/dashboard to customize your tools -->
        <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5a0558158e12a944"></script>
    </body>
</html>