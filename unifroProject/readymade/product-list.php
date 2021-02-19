<?php
    $_SESSION['ProductListURL'] = $_SERVER['REQUEST_URI'];
    $_SESSION['CategoryHeading'] = $CategoryHeading;

    // Get color & rate list for Filters
    $FilterColorRate = MysqlQuery("SELECT c.ColorID, c.ColorName, p.Rate, p.Discount FROM product_categories pc
                                    LEFT JOIN products p ON p.ProductID = pc.ProductID
                                    LEFT JOIN product_colors c ON c.ColorID = p.Color
                                    WHERE pc.CategoryID = '".$CategoryDetails['CategoryID']."'");

    for(; $row = mysqli_fetch_assoc($FilterColorRate);)
    {
        $Filters['Color'][$row['ColorID']] = $row['ColorName'];
        $Filters['Price'][$row['Rate']] = $row['Rate'];

        if($row['Discount'] > 0)
            $Filters['Discount'][$row['Discount']] = floatval($row['Discount']);
    }

    // Get Product Sizes for Filters
    $ProductSizes = mysqli_fetch_assoc(MysqlQuery("SELECT Size FROM master_categories WHERE CategoryID = '".$CategoryDetails['CategoryID']."' LIMIT 1"));
    $ProductSizes = $ProductSizes['Size'];

    $ProductSizes = json_decode($ProductSizes, true);
    $ProductSizes = array_keys($ProductSizes);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">

        <title><?=$MetaTitle?> - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <link href="/css/nouislider.css" rel="stylesheet" type="text/css"/>

        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

        <script src="/js/jquery.bootpag.min.js"></script>
        <script src="/js/jquery.slimscroll.js"></script>
        <script src="/js/wNumb.js"></script>
        <script src="/js/nouislider.min.js"></script>

        <script>

        $(document).ready(function()
        {
            $('#FiltersForm').find('input[type=checkbox]').on('click', function()
            {
                LoadProducts();
            })
            LoadProducts();
        })

        </script>
    </head>

    <body>

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">

            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?=$CategoryHeading?></li>
                </ol>
            </nav>

            <div class="row mg-t-20">
                <div class="col-lg-12">
                    <div class="clearfix">
                        <div class="col-lg-10 col-md-8 col-sm-8">
                            <h2 class="cat-heading"><?=$CategoryHeading?>
                                <span id="ProductCount"></span>
                            </h2>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-4">
                            <select class="selectric" onchange="SortProducts(this)">
                                <option value="">Sort by</option>
                                <option value="h">Price: High to Low</option>
                                <option value="l">Price: Low to High</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid row">
                        <div class="products-wrapper"></div>
                    </div>

                    <div class="page-selection"></div>

                </div>
            </div>
        </div>

        <?php include_once(_ROOT."/include-files/footer.php"); ?>

        <!-- ========================
             START OF FILTERS
        ==========================-->
        <div class="filters-main-div sleep">
            <button class="btn-filter" onclick="ToggleFilterBox()">Filters</button>
            <div class="filters-inner-div">
                <div class="filters">
                    <span class="clear-filter hidden" onclick="ClearProductFilters()"><i class="fa fa-times-circle"></i> Clear All</span>
                    <h2>Filters</h2>
                    <form id="FiltersForm">
                        <input type="hidden" name="CategoryID" value="<?=$CategoryDetails['CategoryID']?>">
                        <input type="hidden" id="price" name="Filter[Price]">
                        <input type="hidden" id="sort" name="SortBy[Price]" value="">
                        <?php
                        if(count($Filters) > 0)
                        {
                            echo '<ul>';
                            foreach($Filters as $key => $arr)
                            {
                                echo '<div class="filter-heading">'.$key.'</div>';

                                if($key == 'Price')
                                {
                                    $arr = array_unique(array_filter($arr));
                                    $MaxPrice = max($arr);

                                    echo '<div class="price-slider" style="margin-bottom:20px;margin-top:40px"></div>';
                                }
                                elseif($key == 'Discount')
                                {
                                    echo '<div class="custom-checkbox mg-b-20">';
                                        foreach($arr as $k => $value)
                                        {
                                            echo    '<div><label>
                                                        <input type="checkbox" name="Filter['.$key.'][]" value="'.$k.'" /><span></span>&nbsp;'.ucwords($value).'%
                                                     </label></div>';
                                        }
                                    echo '</div>';
                                }
                                else
                                {
                                    echo '<div class="custom-checkbox mg-b-20">';
                                        foreach($arr as $k => $value)
                                        {
                                            echo    '<div><label>
                                                        <input type="checkbox" name="Filter['.$key.'][]" value="'.$k.'" /><span></span>&nbsp;'.ucwords($value).'
                                                     </label></div>';
                                        }
                                    echo '</div>';
                                }

                                echo '<li></li>';
                            }
                            echo '</ul>';
                        }

                        if(count($ProductSizes) > 0)
                        {
                            echo '<div class="filter-heading" style="margin-top:30px">Size</div>';

                            echo '<div class="custom-checkbox">';
                                foreach($ProductSizes as $size)
                                {
                                    echo '<div>
                                            <label>
                                                <input type="checkbox" name="Filter[Size][]" value="'.$size.'" /><span></span>&nbsp;'.$size.'
                                            </label>
                                        </div>';
                                }
                            echo '</div>';
                        }
                        ?>
                    </form>
                </div>
            </div>
        </div>

        <script>
        $('.filters-inner-div').slimScroll({
            height: 'auto',
            position: 'right',
            size: "5px",
            color: '#454545',
            wheelStep: 10,
            touchScrollStep : 20
        });

        var priceSlider = document.getElementsByClassName('price-slider')[0];

        noUiSlider.create(priceSlider, {
            start: [100, 1000],
            step: 100,
            tooltips: true,
            range: {
                'min': 100,
                'max': <?=$MaxPrice?>,
            },
            format: wNumb({
                decimals: 0,
            })
        });

        priceSlider.noUiSlider.on('change', function()
        {
            $('#price').val(priceSlider.noUiSlider.get());
            LoadProducts();
        });

         </script>

        <!-- ======================
             END OF FILTERS
        ========================-->

    </body>
</html>