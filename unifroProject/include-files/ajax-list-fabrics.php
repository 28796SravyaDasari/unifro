<?php

    $response['status'] = 'error';

    $PerPage = 20;
	$page = !is_numeric($_POST['page']) ? 0 : ($_POST['page'] - 1);
	$page = $page * $PerPage;

    if($_POST['id'] > 0)
    {
        /*---------------------------------------
        FETCH THE FABRICS FROM THE DATABASE
        ---------------------------------------*/
        if($_POST['id'] == 7 && $_POST['SubHeading'] != 'Pinafore Fabric')
        {
            $_POST['id'] = 3;
        }

        $GetFabricFilters = MysqlQuery("SELECT FabricColor, FabricPattern, FabricPrice, FabricBlend, FabricGSM, KnitType, Industry FROM fabrics WHERE CategoryID = '".$_POST['id']."' AND Status = '1'");
        for(; $row = mysqli_fetch_assoc($GetFabricFilters);)
        {
            $FabricFilters['Price'][$row['FabricPrice']] = trim($row['FabricPrice']);
            $FabricFilters['Color'][$row['FabricColor']] = trim($row['FabricColor']);
            $FabricFilters['Pattern'][$row['FabricPattern']] = trim($row['FabricPattern']);
            $FabricFilters['Blend'][$row['FabricBlend']] = trim($row['FabricBlend']);
            $FabricFilters['Industry'][$row['Industry']] = trim($row['Industry']);

            if($_POST['id'] == 8 || $_POST['id'] == 9)
            {
                if($row['KnitType'] != '')
                {
                    $FabricFilters['Knit Type'][$row['KnitType']] = trim($row['KnitType']);
                }
                if($row['FabricGSM'] != '')
                {
                    $FabricFilters['Fabric GSM'][$row['FabricGSM']] = trim($row['FabricGSM']);
                }
            }
        }

        /*---------------------------------------
        ---------------------------------------*/
        $_POST['Filter'] = isset($_POST['Filter']) ? array_filter($_POST['Filter']) : '';

        if($_POST['Filter']['Price'] != '')
        {
            $price = explode(',', $_POST['Filter']['Price']);

            $filter[] = "(FabricPrice BETWEEN '".$price[0]."' AND '".$price[1]."')";
        }
        if(isset($_POST['Filter']['Color']))
        {

            $filter[] = "FabricColor IN('".implode("','", $_POST['Filter']['Color'])."')";
        }
        if(isset($_POST['Filter']['Pattern']))
        {
            $filter[] = "FabricPattern IN('".implode("','", $_POST['Filter']['Pattern'])."')";
        }
        if(isset($_POST['Filter']['Blend']))
        {
            $filter[] = "FabricBlend IN('".implode("','", $_POST['Filter']['Blend'])."')";
        }
        if(isset($_POST['Filter']['Industry']))
        {
            $filter[] = "Industry IN('".implode("','", $_POST['Filter']['Industry'])."')";
        }
        if(isset($_POST['Filter']['Fabric GSM']))
        {
            $filter[] = "FabricGSM IN('".implode("','", $_POST['Filter']['Fabric GSM'])."')";
        }
        if(isset($_POST['Filter']['Knit Type']))
        {
            $filter[] = "KnitType IN('".implode("','", $_POST['Filter']['Knit Type'])."')";
        }

        $GetFabrics = "SELECT FabricID FROM fabrics WHERE CategoryID = '".$_POST['id']."' AND Status = '1'";
        $GetFabrics = $GetFabrics.(count($filter) > 0 ? " AND ".implode(' AND ', $filter) : '');
        $TotalFabrics = mysqli_num_rows(MysqlQuery($GetFabrics));

        $GetFabrics = "SELECT * FROM fabrics WHERE CategoryID = '".$_POST['id']."' AND Status = '1'";
        $GetFabrics = $GetFabrics.(count($filter) > 0 ? " AND ".implode(' AND ', $filter) : '')." ORDER BY FabricID DESC LIMIT ".$page.", ".$PerPage;

        $GetFabrics = MysqlQuery($GetFabrics);
        $FabricList = MysqlFetchAll($GetFabrics);

        if(count($filter) == 0)
        {

        ob_start();
        ?>

        <div class="panel-group" id="FabricFilters">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5 class="panel-title">
                        <a class="accordion-toggle collapsed" data-toggle="collapse" href=".collapseOne">
                            Filters
                        </a>
                        <span class="clear-filter hidden" onclick="ResetFilters(<?=$_POST['id']?>, '<?=$_POST['ContentHolder']?>')"><i class="fa fa-times-circle"></i> Clear All</span>
                    </h5>
                </div>
                <div id="collapseOne" class="panel-collapse collapse collapseOne">
                    <div class="panel-body">
                        <form action="/get/fabric-list/" class="form-horizontal" name="FilterForm" id="FilterFabricsForm">
                            <input type="hidden" id="price" name="Filter[Price]">

                            <?php
                            foreach($FabricFilters as $key => $arr)
                            {
                                $arr = array_unique(array_filter($arr));

                                echo '<div class="form-group">
                                        <div class="col-md-12">
                                            <label>'.$key.'</label>';

                                            if($key == 'Price')
                                            {
                                                $MaxPrice = max($arr);
                                                echo '<div class="mg-t-30 price-slider"></div>';
                                            }
                                            else
                                            {
                                                echo '<div class="custom-checkbox">';
                                                    foreach($arr as $value)
                                                    {
                                                        echo    '<label>
                                                                    <input type="checkbox" name="Filter['.$key.'][]" value="'.$value.'" /><span></span>&nbsp;'.ucwords($value).'
                                                                 </label>';
                                                    }
                                                echo '</div>';
                                            }

                                echo    '</div>
                                    </div>';
                            }
                            ?>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $response['filters'] = ob_get_clean();

        }

        $response['content'] .= '<ul class="list-inline">';

        foreach($FabricList as $row)
        {
            if($_POST['ContentHolder'] == '#content')
            {
                $response['content'] .= '<li>
                                            <a class="style-icon" onclick="product.MainFabric('.$row['FabricID'].', \''._FabricImageDir.$row['FabricImage'].'?v=1\')">
                                                <div class="icon" style="background:url('._FabricImageThumbDir.$row['FabricImage'].'?v=1) no-repeat center"></div>
                                            </a>
                                        </li>';
            }
            else
            {
                $response['content'] .= '<li>
                                            <a class="thumbnail" onclick="product.ElementFabric(\''.$_POST['Heading'].'\', \''.$_POST['SubHeading'].'\', '.$row['FabricID'].', \''._FabricImageDir.$row['FabricImage'].'?v=1\', \''.$_POST['SymbolID'].'\', \''.$_POST['StyleName'].'\', \''.$_POST['StyleID'].'\')">
                                                <img src="'._FabricImageThumbDir.$row['FabricImage'].'?v=1">
                                            </a>
                                        </li>';
            }

            $FabricsList[$row['FabricID']] = $row;
            $FabricsList[$row['FabricID']]['FabricImage'] = _FabricImageDir.$row['FabricImage'];
        }
        $response['content'] .= '</ul>';

        $response['content'] .= '<div class="page-selection"></div>';

        $response['total'] = ceil($TotalFabrics/$PerPage);
        $response['max'] = floatval($MaxPrice);
        $response['page'] = !is_numeric($_POST['page']) ? 1 : $_POST['page'];
        $response['clear'] = count($filter) > 0 ? true : false;

        $response['status'] = 'success';

        if(isset($_POST['page']))
        {

        }
        $response['FabricList'] = $FabricsList;

        //print_r($_POST);
    }
    else
    {
        $response['response_message'] = 'Invalid Access!';
    }

    echo json_encode($response);
?>