<?php
include_once("../include-files/autoload-server-files.php");

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">

        <title>Sales My Account - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>

        <style>
        #shirt-container { position: relative; }
        #shirt-container img{ position: absolute; top: 0; left: 0; max-width: 100%; }
        </style>

        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

    </head>


    <body>

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">
            <div class="row">
                <div id="sidebar" class="collection-sidebar col-sm-4 col-md-3">
                    <div class="filters-bar">
                        <div class="nav_close">
                            <button type="button" class="fa fa-close"></button>
                            <!-- Sidebar-collection Start -->

                            <!-- Category Sidebar Start-->
                            <div class="widget-category widget">
                                <h3 class="widget-title">OUR COLLECTION</h3>
                                <ul class="list-border">
                                    <li><a href="/readymade/socks/" title="Socks">Socks <span class="count">4</span></a></li>
                                    <li><a href="/readymade/tie/" title="Tie">Tie <span class="count">9</span></a></li>
                                </ul>
                            </div>
                            <!-- Category Sidebar End-->

                            <!-- Filter Block Start -->
                            <div class="filter-block">

                                <div class="widget  filter-custom filter-tag">

                                    <h3 class="filter-title widget-title">
                                        Size
                                        <a href="javascript:void(0)" class="clear" style="display:none">clear</a>
                                    </h3>
                                    <div class="filter-content">
                                        <ul class="box-grid-stype sizes">
                                            <li class="li-size">
                                                <input type="checkbox" value="xs">
                                                <label>XS</label>
                                            </li>
                                            <li class="li-size">
                                                <input type="checkbox" value="l">
                                                <label>L</label>
                                            </li>
                                            <li class="li-size">
                                                <input type="checkbox" value="xl">
                                                <label>XL</label>
                                            </li>
                                            <li class="li-size">
                                                <input type="checkbox" value="m">
                                                <label>M</label>
                                            </li>
                                            <li class="li-size">
                                                <input type="checkbox" value="s">
                                                <label>S</label>
                                            </li>
                                            <li class="li-size">
                                                <input type="checkbox" value="xxl">
                                                <label>XXL</label>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Collection Filter Shop By Color Start-->

                                <div class="widget filter-custom filter-tag color">
                                    <!-- Color Title Start -->
                                    <h3 class="filter-title widget-title"> Color<a href="javascript:void(0)" class="clear" style="display:none">clear</a> </h3>
                                    <div class="filter-content">
                                        <ul class="box-grid-stype colors list-border">
                                            <!-- Collection Filter Color Start-->
                                            <li class="li-color">
                                                <input type="checkbox" value="black">
                                                <a href="javascript:void(0)" title="black">
                                                    <span class="color black" style="background: #000000"></span>
                                                    <span class="name">black</span>
                                                </a>
                                            </li>
                                            <!-- Collection Filter Color End-->
                                        </ul>
                                    </div>
                                </div>

                            <!-- Collection Filter Shop By Color End-->
                            </div>
                            <!-- Filter Block End -->

                        <!-- Sidebar-collection End -->
                        </div>
                    </div>
                </div>

                <div id="col-main" class="collection-page col-sm-8 col-md-9">
                    <h2 class="1"><?=$CategoryDetails['CategoryTitle']?></h2>

                    <div class="cat-grid row">
                        <div class="product_item">
                            <!-- Product Grid Item Start -->

                            <div class="product">
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <div class="product-wrap product-item theme-hover-effect">
                                        <div class="product-image">

                                            <div class="wrap">
                                                <!-- Product Image -->
                                                <figure class="product-grid-image">
                                                    <a href="/collections/jackets/products/fashion-city-jackets-clothing-product-sample-1" class="grid-image">
                                                        <img src="/images/products/blazers/1_5886c11b-0110-4483-a1a3-625ba8d060f8_large.jpg?v=1498893027" alt="Fashion City   Jackets  Clothing Product Sample  1" class="main-image img-responsive">
                                                    </a>
                                                    <!-- Product Buttons -->
                                                    <div class="figure-caption">
                                                        <ul class="wa-icons light-icon">
                                                            <li><a href="javascript:;" class="quick-shop"><i class="fa fa-eye"></i></a></li>
                                                            <li>
                                                                <form action="/cart/add" method="post" enctype="multipart/form-data">
                                                                    <input type="hidden" name="quantity" value="1">
                                                                    <button type="submit" class="addtocart btn-cart"><i class="fa fa-shopping-bag"></i></button>
                                                                    <select class="hide" name="id">
                                                                        <option value="33580838021">Default Title - $3,296.00</option>
                                                                    </select>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <a href="/account/login" class="btn-wishlist"><i class="fa fa-heart-o" aria-hidden="true"></i></a>
                                                          	</li>
                                                        </ul>
                                                    </div>
                                                </figure>
                                            </div>
                                        </div>
                                        <div class="product-content caption price-box">
                                            <div class="product-inner-content">
                                              <!-- Product Title -->
                                                <div class="product-title">
                                                    <h4><a href="/collections/jackets/products/fashion-city-jackets-clothing-product-sample-1" title="Fashion City   Jackets  Clothing Product Sample  1">
                                                            Fashion City   Jackets  Clothing Product Sample  1
                                                        </a>
                                                    </h4>
                                                </div>
                                                <div class="price-val product-price">
                                                    <span class="price"><i class="fa fa-rupee"></i> 3,296.00</span>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- Product Grid Item End -->

                            <div class="product">
                                                                <div class="col-md-4 col-sm-6 col-xs-12">
                                                                        <div class="product-wrap product-item theme-hover-effect">
                                                                                <div class="product-image">

                                                                                        <div class="wrap">
                                                                                                <!-- Product Image -->
                                                                                                <figure class="product-grid-image">
                                                                                                        <a href="/collections/jackets/products/fashion-city-jackets-clothing-product-sample-1" class="grid-image">
                                                                                                                <img src="/images/products/blazers/1_5886c11b-0110-4483-a1a3-625ba8d060f8_large.jpg?v=1498893027" alt="Fashion City   Jackets  Clothing Product Sample  1" class="main-image img-responsive">
                                                                                                        </a>
                                                                                                        <!-- Product Buttons -->
                                                                                                        <div class="figure-caption">
                                                                                                                <ul class="wa-icons light-icon">
                                                                                                                        <li><a href="javascript:;" class="quick-shop"><i class="fa fa-eye"></i></a></li>
                                                                                                                        <li>
                                                                                                                                <form action="/cart/add" method="post" enctype="multipart/form-data">
                                                                                                                                        <input type="hidden" name="quantity" value="1">
                                                                                                                                        <button type="submit" class="addtocart btn-cart"><i class="fa fa-shopping-bag"></i></button>
                                                                                                                                        <select class="hide" name="id">
                                                                                                                                                <option value="33580838021">Default Title - $3,296.00</option>
                                                                                                                                        </select>
                                                                                                                                </form>
                                                                                                                        </li>
                                                                                                                        <li>
                                                                                                                                <a href="/account/login" class="btn-wishlist"><i class="fa fa-heart-o" aria-hidden="true"></i></a>
                                                                                                                    	</li>
                                                                                                                </ul>
                                                                                                        </div>
                                                                                                </figure>
                                                                                        </div>
                                                                                </div>
                                                                                <div class="product-content caption price-box">
                                                                                        <div class="product-inner-content">
                                                                                            <!-- Product Title -->
                                                                                                <div class="product-title">
                                                                                                        <h4><a href="/collections/jackets/products/fashion-city-jackets-clothing-product-sample-1" title="Fashion City   Jackets  Clothing Product Sample  1">
                                                                                                                        Fashion City   Jackets  Clothing Product Sample  1
                                                                                                                </a>
                                                                                                        </h4>
                                                                                                </div>
                                                                                                <div class="price-val product-price">
                                                                                                        <span class="price"><i class="fa fa-rupee"></i> 3,296.00</span>
                                                                                                </div>

                                                                                        </div>
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                        </div>
                                                        <!-- Product Grid Item End -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>