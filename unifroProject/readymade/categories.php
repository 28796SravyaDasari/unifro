<?php

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">

        <title>Readymade Products - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>

        <style>
        .shadow, .shadow-narrow {
            position: relative;
            background-color: #fff;
        }
        .shadow-narrow:before, .shadow:before {
            position: absolute;
            left: 0;
            height: 60%;
            bottom: 0;
            width: 100%;
            content: "";
            background-color: #fff;
            z-index: 2;
        }
        .shadow-narrow:after, .shadow:after {
            content: "";
            position: absolute;
            height: 50%;
            width: 96%;
            left: 50%;
            bottom: 2px;
            margin-left: -48%;
            -webkit-box-shadow: 0 5px 7px #999;
            box-shadow: 0 5px 7px #999;
            z-index: 1;
            border-radius: 10%;
            -webkit-transition: all .3s ease-in-out;
            -o-transition: all .3s ease-in-out;
            transition: all .3s ease-in-out;
        }
        .shadow *, .shadow-narrow * {
            position: relative;
            z-index: 3;
        }
        .overlay-container {
            position: relative;
            display: block;
            text-align: center;
            overflow: hidden;
            min-height: 280px;
        }
        .image-box img {
            display: block;
            max-width: 100%;
            height: auto;
        }
        .overlay-bottom, .overlay-top {
            padding: 15px;
            bottom: 0;
            left: 0;
            right: 0;
            overflow: hidden;
            -webkit-transition: all ease-in-out .25s;
            -o-transition: all ease-in-out .25s;
            transition: all ease-in-out .25s;
            height: 0;
        }
        .overlay-visible .overlay-bottom, .overlay-visible .overlay-top {
            opacity: 1;
            filter: alpha(opacity=100);
            height: auto!important;
            padding-bottom: 20px;
        }
        @media (min-width: 1200px) {
            .overlay-container .text {
                top: 30%;
            }
        }
        .overlay-container .text {
            padding: 0 20px;
            position: relative;
        }
        </style>

        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

    </head>

    <body>

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">
            <div class="row mg-t-20">

                <div class="col-lg-12">
                    <h1 class="cat-heading">Readymade Products</h1>

                    <div class="row">
                        <?php
                        foreach(ReadymadeCategories(2) as $category)
                        {
                            ?>
                            <div class="col-sm-4">
                                <div class="image-box shadow text-center mg-b-20">
                                    <div class="overlay-container overlay-visible">
                                        <a href="<?=$category['CategoryURL']?>">
                                            <img src="<?=_CategoryPicDir.$category['WidgetImage']?>" alt="">
                                        </a>

                                        <div class="overlay-bottom hidden-xs">
                                            <div class="text">
                                                <p class="lead mg-0"><?=$category['CategoryTitle']?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                </div>
            </div>
        </div>

        <?php include(_ROOT._IncludesDir."footer.php"); ?>

    </body>
</html>