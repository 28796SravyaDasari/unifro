<?php
    include_once("include-files/autoload-server-files.php");

    if(SendMailHTML('sanjeev@myzow.com', 'Test', 'Test Mail'))
    {

    }
    else
    {
        echo 'Failed';
        exit;
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

            <meta name="viewport" content="width=device-width, initial-scale=1.0">

            <link rel="shortcut icon" href="/images/favicon.ico">

            <link href="/css/animate.min.css" rel="stylesheet" type="text/css" />
            <link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
            <link href="/css/themify-icons.css" rel="stylesheet" type="text/css" />
            <link href="/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
            <link href="/css/footable.bootstrap.min.css" rel="stylesheet" type="text/css" />
            <link href="/css/selectric.css" rel="stylesheet" type="text/css" />
            <link href="/css/jquery.mCustomScrollbar.css" rel="stylesheet" type="text/css" />
            <link href="/css/jquery.filer.css" rel="stylesheet" type="text/css" />
            <link href="/css/common.css?v=<?=time()?>" rel="stylesheet" type="text/css" />
            <link href="/css/style.css?v=<?=time()?>" rel="stylesheet" type="text/css" />
            <link href="/css/style-2.css?v=<?=time()?>" rel="stylesheet" type="text/css" />
            <link href="/css/responsive.css?v=<?=time()?>" rel="stylesheet" type="text/css" />

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

                var qty = $(this).attr('data-qty');
                if(qty >= 5)
                {
                    $('.stock').html(qty +' products in stock');
                }
                else
                {
                    $('.stock').html('Hurry! only '+ qty +' left');
                }
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

                            echo    '<div class="slide" data-image="/images/v1_small.jpg">
                                        <img src="/images/v1_small.jpg">
                                    </div>';
                    echo '</div>';
                ?>
                </div>

                <div class="col-md-8 col-sm-7 col-md-push-1">

                </div>
            </div>


 </div>

        <script src="/js/slick.min.js"></script>
        <script>
            var elements = document.querySelectorAll( '.slide' );
            Intense( elements );
        </script>

    </body>
</html>