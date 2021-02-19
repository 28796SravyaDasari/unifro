<?php
    $SelectedPage = 'Products';

    if(is_numeric($ID))
    {
        // GET THE PRODUCT DETAILS
        $GetProducts = MysqlQuery("SELECT pc.CategoryID, p.* FROM products p LEFT JOIN product_categories pc ON pc.ProductID = p.ProductID WHERE p.ProductID = '".$ID."'");
        if(mysqli_num_rows($GetProducts) > 0)
        {
            $ProductDetails = MysqlFetchAll($GetProducts);
            foreach($ProductDetails as $data)
            {
                if($data['Combo'] != 'y')
                {
                    $_SESSION['AlertMessage'] = 'This is not a Combo Product.';
                    header("Location: /admin/products/");
                    exit;
                }
                $ProductID = $data['ProductID'];
                $Product[$ProductID] = $data;
                $CategoryID[] = $data['CategoryID'];
                $FileName[$data['ImageID']] = $data['FileName'];
            }
            $_POST = $Product[$ProductID];
            $_POST['ParentID'] = array_values(array_unique($CategoryID));

            /*--------------------------------
                GET THE PRODUCT COMBO DETAILS
            --------------------------------*/
            $ProductCombos = MysqlQuery("SELECT p.ProductName, pc.* FROM product_combos pc LEFT JOIN products p ON p.ProductID = pc.ComboProductID WHERE pc.ProductID = '".$ID."'");
            $TotalComboProducts = mysqli_num_rows($ProductCombos);
            for($i = 0; $row = mysqli_fetch_assoc($ProductCombos); $i++)
            {
                $ComboProductID[$i] = $row['ComboProductID'];
                $ComboProductName[$i] = $row['ProductName'];
                $Rate[$i]           = $row['Rate'];
                $Weight[$i]         = $row['Weight'];
                $WeightUnit[$i]     = $row['WeightUnit'];
                $TaxRate[$i]        = $row['TaxRate'];
            }

            // GET THE PRODUCT IMAGES
            $ProductPictures = MysqlQuery("SELECT * FROM product_images WHERE ProductID = '".$ID."'");
            if(mysqli_num_rows($ProductPictures) > 0)
            {
                $ProductPictures = MysqlFetchAll($ProductPictures);
            }
        }
        else
        {
            $_SESSION['AlertMessage'] = 'Product does not exists!';
            header('Location: /admin/products/');
            exit;
        }

    }
    else
    {
        $TotalComboProducts = 2;
    }

    // GET THE CATEGORY URL
    $GetCategoryDetails = MysqlQuery("SELECT Size FROM master_categories WHERE CategoryID = '".$ID."' LIMIT 1");
    $GetCategoryDetails = mysqli_fetch_assoc($GetCategoryDetails);

    $ProductSizeArr = json_decode($GetCategoryDetails['Size'], true);

    $GetProductColors = MysqlQuery("SELECT * FROM product_colors WHERE Status = '1' ORDER By ColorName");
    for(;$row = mysqli_fetch_assoc($GetProductColors);)
    {
        $ProductColors .= '<option'.($_POST['Color'] == $row['ColorID'] ? ' selected' : '').' value="'.$row['ColorID'].'">'.$row['ColorName'].'</option>';
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$PageTitle?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <link href="/css/autocomplete-devbridge.css" rel="stylesheet" type="text/css" />

        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>
        <script src="/js/autocomplete-devbridge-min.js"></script>

        <script>
        $(document).ready(function()
        {
            lightbox.option({ 'showImageNumberLabel': false, 'wrapAround': true, alwaysShowNavOnTouchDevices: true })

            $('.search').autocomplete({
                serviceUrl: '/admin/ajax/search-product/?combo=n',
                paramName: 'term',
                groupBy: 'category',
                minChars: 2,
                onSelect: function (suggestion)
                {
                    if(suggestion.data.ProductID != 'null')
                    {
                        $(this).closest('li').find('[name^="ComboProductID"]').val(suggestion.data.ProductID);
                        $(this).closest('li').find('[name^="Rate"]').val(suggestion.data.Rate);
                        $(this).closest('li').find('[name^="Weight"]').val(suggestion.data.Weight);
                        $(this).closest('li').find('[name^="TaxRate"]').val(suggestion.data.TaxRate);
                        if(suggestion.data.WeightUnit != '')
                        {
                            $(this).closest('li').find('[name^="WeightUnit"]').val(suggestion.data.WeightUnit);
                        }
                        else
                        {
                            var val = $(this).closest('li').find('[name^="WeightUnit"] option:first').val();
                            $(this).closest('li').find('[name^="WeightUnit"]').val(val);
                        }
                        UpdateComboTotal();
                    }
                }
            });

            $('#ComboProducts input[type=number]').on('change', function()
            {
                UpdateComboTotal();
            });

            UpdateComboTotal();

            options = {
                        showThumbs: false,
                        limit: 5,
                        maxSize: null,
                        changeInput: true,
                        afterShow: function(){
                            $('#tab-pictures').find('form').submit();
                        },
                    };

            $('input[name=ProductImage]').filer(options);

            $(".ProductForm").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();
                AjaxResponse = AjaxFormSubmit(this);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        if(typeof response.message !== 'undefined')
                        {
                            if(typeof response.redirect !== 'undefined')
                            {
                                AlertBox(response.message, 'success', function(){ location.href = response.redirect });
                            }
                            else
                            {
                                AlertBox(response.message, 'success');
                            }
                        }
                        else if(typeof response.html !== 'undefined')
                        {
                            $('.products-wrapper').append(response.html);
                        }
                    }
                    else if(response.status == 'login')
                    {
                        AlertBox('Oops! Your session has expired! Please login again.', 'error', function(){ location.href = response.redirect } );
                    }
                    else if(response.status == 'validation')
                	{
                	    ThrowError(response.error, true);
                	}
                    else
                    {
                        AlertBox(response.message);
                    }
                });
            });

        })
        </script>
    </head>
    <body>

        <?php include(_ROOT._AdminIncludesDir."admin-common-scripts.php"); ?>

        <div id="wrapper">
            <?php include(_ROOT._AdminIncludesDir."admin-header.php"); ?>
            <?php include(_ROOT._AdminIncludesDir."admin-sidebar.php"); ?>

            <!-- ==============================================================
                Start of Right content here
            ============================================================== -->
            <div class="content-page">

                <div class="content">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="content-header clearfix">
                                    <h1 class="pull-left page-title"><?=$PageTitle?></h1>
                                    <div class="pull-right">
                                        <?php if($EditMode) { ?>

                                        <button type="button" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i>&nbsp;
                                            Delete
                                        </button>

                                        <?php } ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div>
                                    <ul class="nav nav-tabs" style="width: 100%;">
                                        <li class="active tab"><a data-tab-name="tab-info" data-toggle="tab" href="#tab-info">Combo Info</a></li>
                                        <li class="tab"><a data-toggle="tab" href="#tab-pictures">Combo Images</a></li>
                                    </ul>
                                    <div class="tab-content">

                                        <!-----------------------------
                                            START OF INFO TAB
                                        ------------------------------>
                                        <div class="tab-pane active" id="tab-info">
                                            <form action="/admin/ajax/add-combox-product/" class="form-horizontal ProductForm">
                                                <input type="hidden" name="section" value="info">
                                                <input type="hidden" name="ProductID" value="<?=$ID?>">
                                                <div class="clearfix">
                                                    <div class="col-md-9">
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label required">Category</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <select class="selectric" multiple="multiple" name="ParentID[]">
                                                                    <option value="">Select Category</option>
                                                                    <?php
                                                                    foreach(OtherCategories() as $CatID => $CatTitle)
                                                                    {
                                                                        echo '<option'.(in_array($CatID, $_POST['ParentID']) ? ' selected' : '').' value="'.$CatID.'">'.$CatTitle.'</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label required">Product name</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input class="form-control" max="100" name="ProductName" required type="text" value="<?=$_POST['ProductName']?>">
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="col-lg-12">
                                                                <h4 class="underline">Combo Products</h4>
                                                                <ul id="ComboProducts">
                                                                    <?php
                                                                    for($c = 0; $c < $TotalComboProducts; $c++)
                                                                    {
                                                                        ?>
                                                                    <li class="clearfix">
                                                                        <div class="form-row">
                                                                            <div class="form-group col-lg-6 col-md-4">
                                                                                <label>Product Name</label>
                                                                                <input type="text" class="form-control search" name="ComboProductName[<?=$c?>]" placeholder="Search Product Name" value="<?=$ComboProductName[$c]?>">
                                                                                <input type="hidden" name="ComboProductID[]" value="<?=$ComboProductID[$c]?>">
                                                                            </div>
                                                                            <div class="form-group col-lg-2 col-md-3">
                                                                                <label>Rate</label>
                                                                                <div class="input-group">
                                                                                    <div class="input-group-addon">Rs.</div>
                                                                                    <input class="form-control" maxlength="50" data-name="Rate" name="Rate[<?=$c?>]" required step="0.01" type="number"
                                                                                            value="<?=$Rate[$c]?>">
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group col-lg-2 col-md-3">
                                                                                <label for="inputZip">Weight</label>
                                                                                <div class="input-group">
                                                                                    <input class="form-control" maxlength="5" data-name="Weight" name="Weight[<?=$c?>]" step="0.01" type="number"
                                                                                            value="<?=$Weight[$c]?>">
                                                                                    <div class="input-group-addon select">
                                                                                        <select name="WeightUnit[<?=$c?>]" style="padding: 7px 6px;width: 55px">
                                                                                            <option<?=$WeightUnit[$c] == 'gm' ? ' selected' : ''?> value="gm">gm</option>
                                                                                            <option<?=$WeightUnit[$c] == 'kg' ? ' selected' : ''?>  value="kg">kg</option>
                                                                                            <option<?=$WeightUnit[$c] == 'lt' ? ' selected' : ''?>  value="lt">lt</option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group col-lg-2 col-md-2">
                                                                                <label>Tax Rate</label>
                                                                                <div class="input-group">
                                                                                    <input class="form-control" maxlength="5" data-name="TaxRate" name="TaxRate[<?=$c?>]" required step="0.01" type="number"
                                                                                            value="<?=$TaxRate[$c]?>">
                                                                                    <div class="input-group-addon">%</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>


                                                                        <div class="btn-delete<?=$TotalComboProducts <= 2 ? ' hidden' : ''?>">
                                                                            <button type="button" class="btn btn-default" class="removeRow" onclick="AddProduct2Combo(this)" tabindex="-1"><i class="fa fa-minus"></i></button>
                                                                        </div>

                                                                    </li>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </ul>
                                                                <div class="clearfix">
                                                                    <div class="form-row product-total">
                                                                        <div class="form-group col-md-4">
                                                                            <p class="form-control-static">Total</p>
                                                                        </div>
                                                                        <div class="form-group col-md-3">
                                                                            <div data-name="Rate">
                                                                                <input type="number" class="form-control" readonly="readonly" value="0">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group col-md-3">
                                                                            <div data-name="Weight">
                                                                                <input type="number" class="form-control" readonly="readonly" value="0">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group col-md-2">
                                                                            <div data-name="TaxRate">
                                                                                <input type="number" class="form-control" readonly="readonly" value="0">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="bg-muted" style="padding:8px">
                                                                    <a href="javascript:void(0)" class="btn btn-white btn-xs" id="AddButton" onclick="AddProduct2Combo()" tabindex="-1">
                                                                        <i class="fa fa-plus"></i> Add Product
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label required">Description</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <textarea class="form-control" name="FullDescription" rows="10"><?=$_POST['FullDescription']?></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label">Discount</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" maxlength="50" name="Discount" step="0.01" type="number" value="<?=$_POST['Discount']?>">
                                                                    <div class="input-group-addon select">
                                                                        <select name="DiscountType">
                                                                            <option value="%">%</option>
                                                                            <option value="Rs">Rs</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                            </div>
                                                            <div class="col-md-9">
                                                                <button class="btn btn-info">
                                                                    <i class="fa fa-floppy-o"></i>&nbsp; Save Details
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">

                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <!-----------------------------
                                            END OF INFO TAB
                                        ------------------------------>

                                        <!-----------------------------
                                            START OF PICTURES TAB
                                        ------------------------------>
                                        <div class="tab-pane" id="tab-pictures">
                                            <?php
                                            if(!isset($ID))
                                            {
                                                echo 'You need to add Product before uploading the images';
                                            }
                                            else
                                            {
                                            ?>

                                            <div class="cat-grid row">
                                                <div class="products-wrapper">
                                                    <?php
                                                    if(count($ProductPictures) > 0)
                                                    {
                                                        foreach($ProductPictures as $Image)
                                                        {
                                                            if($Image['DefaultImage'] == '1')
                                                            {
                                                                $SetDefault = ' active';
                                                                $DefaultOption = ' hidden';
                                                            }
                                                            else
                                                            {
                                                                $SetDefault = '';
                                                                $DefaultOption = '';
                                                            }
                                                    ?>
                                                        <!-- Product Item Start -->
                                                        <div class="product">
                                                            <div class="col-md-3 col-sm-6 col-xs-12">
                                                                <div class="product-item">
                                                                    <!-- Product Image -->
                                                                    <figure class="product-grid-image">
                                                                        <img class="set-default<?=$SetDefault?>" src="/images/mark-default.png" title="Default Image">
                                                                        <a href="<?=_ProductImageDir.$Image['FileName']?>" data-lightbox="product">
                                                                            <img class="img-responsive" src="<?=_ProductImageDir.$Image['FileName']?>">
                                                                        </a>
                                                                        <!-- Product Buttons -->
                                                                        <div class="figure-caption">
                                                                            <ul class="icons">
                                                                                <li>
                                                                                    <a class="default-pic<?=$DefaultOption?>" href="javascript:void(0)" data-id="<?=$ID?>" data-pk="<?=$Image['ImageID']?>" data-opt="product_image" onclick="SetDefaultImage(this)"><i class="fa fa-check"></i></a>
                                                                                </li>
                                                                                <li>
                                                                                    <a href="javascript:void(0)" data-id="<?=$Image['ImageID']?>" data-opt="product_image" onclick="DeletejFilerThumbnail(this)"><i class="fa fa-trash"></i></a>
                                                                            	</li>
                                                                            </ul>
                                                                        </div>
                                                                    </figure>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Product Item Start -->
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <form action="/admin/ajax/add-product-pictures/" class="form-horizontal ProductForm">
                                                <input type="hidden" name="section" value="pictures">
                                                <input type="hidden" name="ProductID" value="<?=$ID?>">

                                                <div class="clearfix">
                                                    <div class="col-md-12 mg-t-20">
                                                        <input type="file" name="ProductImage" data-jfiler-changeInput='<div class="btn btn-white"><div><i class="fa fa-upload"></i>&nbsp; Add Picture</div></div>' data-jfiler-extensions="jpg,png" data-jfiler-caption="Only JPG & PNG files are allowed to be uploaded.">
                                                    </div>
                                                    <div class="col-md-12 mg-t-10 text-danger">
                                                        Note: Image dimension must be 1000 x 1400 px (width x height)
                                                    </div>
                                                </div>
                                            </form>

                                            <?php
                                            }   // END OF ELSE
                                            ?>
                                        </div>
                                        <!-----------------------------
                                            END OF PICTURES TAB
                                        ------------------------------>

                                        <!-----------------------------
                                            START OF SEO TAB
                                        ------------------------------>
                                        <div class="tab-pane" id="tab-seo">
                                            <?php
                                            if(!isset($ID))
                                            {
                                                echo 'You need to add Product before updating the SEO details';
                                            }
                                            else
                                            {
                                            ?>

                                            <form action="/admin/ajax/add-product/" class="form-horizontal ProductForm">
                                                <input type="hidden" name="section" value="seo">
                                                <input type="hidden" name="ProductID" value="<?=$ID?>">
                                                <input type="hidden" name="MetaID" value="<?=$_POST['MetaID']?>">
                                                <div class="clearfix">
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label">Meta Keywords</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input class="form-control" max="100" name="MetaKeywords" required type="text" value="<?=$_POST['MetaKeywords']?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label">Meta Description</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <textarea class="form-control" name="MetaDescription" required ShowCounter="150"><?=$_POST['MetaDescription']?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label">Meta Title</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input class="form-control" max="70" name="MetaTitle" required type="text" value="<?=$_POST['MetaTitle']?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label">Search Engine Friendly Page Name</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input class="form-control" max="100" name="MetaPageName" type="text" value="<?=$_POST['MetaTitle']?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                            </div>
                                                            <div class="col-md-9">
                                                                <button class="btn btn-info">
                                                                    <i class="fa fa-floppy-o"></i>&nbsp; Save Details
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </form>

                                            <?php
                                            }   // END OF ELSE
                                            ?>
                                        </div>
                                        <!-----------------------------
                                            END OF SEO TAB
                                        ------------------------------>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>   <!-- container -->

                </div>   <!-- content -->
            </div>


                <?php include(_ROOT._AdminIncludesDir."footer.php"); ?>

            <!-- ==============================================================
                End of Right content here
            ============================================================== -->
        </div>

        <script src="/js/lightbox.min.js"></script>

	</body>
</html>