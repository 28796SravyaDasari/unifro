<?php

    $Price = array('BasePrice' => $CategoryDetails['BasePrice']);

    // FETCH THE CATEGORY ELEMENTS FROM THE DATABASE
    $GetElements = MysqlQuery("SELECT * FROM master_elements WHERE CategoryID = '".$CategoryDetails['CategoryID']."' AND ElementStatus = '1'");
    if(mysqli_num_rows($GetElements) > 0)
    {
        $Elements = MysqlFetchAll($GetElements);
    }

    /*-----------------------------------------
    let's fetch the Element Styles
    ------------------------------------------*/
    $query = "SELECT me.ElementID, me.ElementName, me.StyleType, eh.Heading, eh.ParentID, eh.Attribute, es.* FROM master_elements me LEFT JOIN master_element_headings eh ON eh.ElementID = me.ElementID LEFT JOIN element_styles es ON es.HeadingID = eh.HeadingID WHERE me.CategoryID = '".$CategoryDetails['CategoryID']."' AND me.ElementStatus = 1 AND es.StyleStatus = 1";

    $query = MysqlQuery($query);

    for(;$row = mysqli_fetch_assoc($query);)
    {
        if($row['DefaultStyle'] == 1)
        {
            if($row['Attribute'] == 'hide')
            {
                $DefaultStyles[] = array($row['Heading'] => array('StyleID' => $row['StyleID'], 'SymbolID' => $row['SymbolID'], 'StyleType' => $row['StyleType']));
            }
            elseif($row['Attribute'] == 'xlink' || $row['Attribute'] == 'edge')
            {
                $DefaultStyles[] = array($row['Heading'] => array( $row['StyleName'] => array('FabricID' => '', 'StyleID' => $row['StyleID'], 'SymbolID' => $row['SymbolID'], 'StyleType' => $row['StyleType'])));
            }
        }

        if($row['ParentID'] == '0')
        {
            $Style[$row['ElementName']][$row['Heading']][] = $row;
        }
        else
        {
            // GET THE HEADING OF PARENT ID
            $ParentHeading = MysqlQuery("SELECT Heading FROM master_element_headings WHERE HeadingID = '".$row['ParentID']."' LIMIT 1");
            $ParentHeading = mysqli_fetch_assoc($ParentHeading)['Heading'];

            $Style[$row['ElementName']][$ParentHeading][$row['Heading']][] = $row;
        }

        // Get the Sub Styles
        $SubStyles = MysqlQuery("SELECT SubStyleID, Percentage FROM element_sub_styles WHERE StyleID = '".$row['StyleID']."'");
        if(mysqli_num_rows($SubStyles) > 0)
        {
            for(;$ss = mysqli_fetch_assoc($SubStyles);)
            {
                $Percentage[$row['StyleID'].'-'.$ss['SubStyleID']] = $ss['Percentage'];
            }
        }

        $Percentage[$row['StyleID']] = $row['Percentage'];
    }

    $Percentage = json_encode($Percentage);

    /*-------------------------------------------------------
    let's fetch the Element Symbols group by Element Headings
    --------------------------------------------------------*/

    $query = "SELECT s.StyleID, s.SymbolID, h.HeadingID, h.Heading, h.Attribute FROM element_styles s LEFT JOIN master_element_headings h ON h.HeadingID = s.HeadingID LEFT JOIN master_elements e ON e.ElementID = s.ElementID WHERE e.CategoryID = '".$CategoryDetails['CategoryID']."' AND s.StyleName != 'None'";
    $DefineSymbols = MysqlFetchAll(MysqlQuery($query));

    foreach($DefineSymbols as $symbol)
    {
        if($symbol['Attribute'] == 'hide')
        {
            $MasterSymbols[$symbol['Heading']][] = $symbol['SymbolID'];

            $Childs = array_filter( GetChildHeadings($symbol['HeadingID']) );
            if(count($Childs) > 0)
            {
                foreach($Childs as $row)
                {
                    $SubHeadings[$symbol['Heading']][$row['HeadingID']] = $row;
                }
            }
        }
        elseif($symbol['Attribute'] == 'xlink')
        {
            $StyleIDByHeading[$symbol['Heading']][] = $symbol['StyleID'];
        }
        elseif($symbol['Attribute'] == 'edge')
        {
            $MasterSymbols[$symbol['Heading']][] = $symbol['SymbolID'];
            $StyleIDByHeading[$symbol['Heading']][] = $symbol['StyleID'];
            $SubHeadings[$symbol['Heading']][$row['HeadingID']] = $row;
        }
    }
    foreach($MasterSymbols as $key => $value)
    {
        $MasterSymbols[$key] = implode(',', $value);
    }

    // TShirt Moonpatch
    $StyleIDByHeading['MoonPatch Fabric'][] = '132-1';
    $StyleIDByHeading['Placket Fabric'][] = '132-2';
    $StyleIDByHeading['Twill Fabric'][] = '132-3';

    $MasterSymbols = json_encode($MasterSymbols);
    $SubHeadings = json_encode($SubHeadings);
    $StyleIDByHeading = isset($StyleIDByHeading) ? json_encode($StyleIDByHeading) : '""';

    /*------------------------------
    let's fetch the Fabric List
    -------------------------------*/
    $GetFabric = MysqlQuery("SELECT * FROM fabrics WHERE CategoryID = '".$CategoryDetails['CategoryID']."' AND Status = '1' ORDER BY FabricID DESC LIMIT 20");

    for( ;$fabric = mysqli_fetch_assoc($GetFabric); )
    {
        if($fabric['DefaultFabric'] == 1)
        {
            $DefaultFabric = $fabric;
            $Price['FabricPrice'] = $fabric['FabricPrice'];
        }

        if(!isset($DefaultFabric))
        {
            $DefaultFabric = $fabric;
            $Price['FabricPrice'] = $fabric['FabricPrice'];
        }
        else
        {
            if($CategoryDetails['CategoryID'] == 7)
            {
                if($fabric['FabricID'] != $DefaultFabric['FabricID'])
                {
                    $DefaultFabricSec = $fabric;
                    $Price[168] = $fabric['FabricPrice'];
                }
            }
            else
            {
                $DefaultFabricSec = '';
            }
        }

        $fabric['FabricImageThumb'] = _FabricImageThumbDir.$fabric['FabricImage'];
        $fabric['FabricImage'] = _FabricImageDir.$fabric['FabricImage'];

        $FabricsList[$fabric['FabricID']] = $fabric;
    }

    /*------------------------------
    let's fetch the Color List
    -------------------------------*/
    $GetColorList = MysqlQuery("SELECT * FROM color_list WHERE Status = '1'");
    $AllColors = MysqlFetchAll($GetColorList);
    foreach($AllColors as $color)
    {
        $ColorList[$color['ColorID']] = $color['ColorCode'];
    }
    $ColorList = json_encode($ColorList);

    /*------------------------------
    let's fetch the Buttons List
    -------------------------------*/
    $MasterButtons = MysqlQuery("SELECT * FROM master_buttons WHERE Status = '1'");
    $MasterButtons = MysqlFetchAll($MasterButtons);
    foreach($MasterButtons as $button)
    {
        $ButtonsList[$button['ButtonID']] = _ButtonsDir.$color['ImageName'];
    }
    $ColorList = json_encode($ColorList);

    $Price = json_encode($Price);
?>