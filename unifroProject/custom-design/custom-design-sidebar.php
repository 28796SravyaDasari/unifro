    <div class="custom-design-sidebar">
        <div class="sidebar-content" id="sidebar-content">

            <button type="button" class="close-sidebar" onclick="CloseSidebar()">
                <span class="ti-close"></span>
            </button>

            <section id="Fabric">
                <h4>Choose Fabric</h4>

                <div class="fabric-filter-holder"></div>
                <div id="content"></div>

            </section>


            <div class="style-container">
                <?php

                foreach($Style as $ElementName => $headingArr)
                {
                    echo '<section id="'.$ElementName.'">';

                    foreach($headingArr as $heading => $arr)
                    {
                        if(count($arr) == 0 && $arr[0]['StyleName'] == 'None')
                        {
                            // Do nothing
                        }
                        else
                        {
                            $arr = SortArray($arr, 'SortOrder');
                            $subStyles = '';

                            echo '<h4>'.$heading.'</h4>';

                            echo '<ul class="list-inline">';

                            foreach($arr as $subHeading => $row)
                            {
                                if(count($ProductSVGs) > 1 && $row['DefaultStyle'] == 0)
                                {
                                    $HideSymbols[] = $row['SymbolID'];
                                }

                                // CHECK FOR SUB STYLES
                                $SubElements = MysqlQuery("SELECT ss.*, h.Heading FROM element_sub_styles ss LEFT JOIN master_element_headings h ON h.HeadingID = ss.HeadingID WHERE ss.StyleID = '".$row['StyleID']."'");
                                if(mysqli_num_rows($SubElements) > 0)
                                {
                                    $SubStylesElements = MysqlFetchAll($SubElements);
                                }
                                else
                                {
                                    $SubStylesElements = '';
                                }

                                if($row['Attribute'] == 'hide')
                                {
                                    if($row['StyleName'] == 'None')
                                    {
                                        echo '<li>
                                                <a class="style-icon" onclick="product.RemoveElement(\''.$heading.'\')">
                                                    <div class="icon" style="background:url(/images/'.$row['ImageName'].') no-repeat center;background-size:100%"></div>
                                                    <label>None</label>
                                                </a>
                                            </li>';
                                    }
                                    else
                                    {
                                        if(is_array($SubStylesElements))
                                        {
                                            echo '<li>
                                                        <a class="style-icon" onclick="product.SetStyle(\''.$heading.'\', \''.$row['SymbolID'].'\', \''.$row['StyleID'].'\', \''.$SubStylesElements[0]['SubStyleID'].'\')">
                                                            <div class="icon" style="background:url('._StyleIconsDir.$row['ImageName'].') no-repeat center;background-size:100%"></div>
                                                            <label>'.$row['StyleName'].'</label>
                                                        </a>
                                                    </li>';

                                            echo '<li class="sub-styles" style="width:100%">
                                                        <ul>';

                                                foreach($SubStylesElements as $SubElements)
                                                {
                                                    $SubStyleID = $SubElements['StyleID'].'-'.$SubElements['SubStyleID'];

                                                    echo '<li class="text-center">
                                                            <h6>'.$SubElements['SubStyleName'].'</h6>
                                                                <div>
                                                                        <a class="style-icon" onclick="product.FabricPopup(\''.$SubElements['Heading'].'\', \'\', \''.$SubElements['SubStyleName'].'\', \''.$SubElements['SymbolID'].'\', \''.$SubStyleID.'\')">
                                                                            <div class="icon" style="background:url('._StyleIconsDir.$SubElements['ImageName'].') no-repeat center;background-size:100%"></div>
                                                                            <label>'.$SubElements['StyleName'].'</label>
                                                                        </a>
                                                                </div>
                                                          </li>';
                                                }
                                            echo        '</ul>
                                                    </li>';
                                        }
                                        else
                                        {
                                            echo '<li>
                                                    <a class="style-icon" onclick="product.SetStyle(\''.$heading.'\', \''.$row['SymbolID'].'\', \''.$row['StyleID'].'\')">
                                                        <div class="icon" style="background:url('._StyleIconsDir.$row['ImageName'].') no-repeat center;background-size:100%"></div>
                                                        <label>'.$row['StyleName'].'</label>
                                                    </a>
                                                </li>';
                                        }
                                    }
                                }
                                elseif($row['Attribute'] == 'xlink')
                                {
                                    if($row['StyleName'] == 'None')
                                    {
                                        echo '<li>
                                                <a class="style-icon" onclick="product.ResetElementFabric(\''.$heading.'\', \''.$subHeading.'\', \''.$row['SymbolID'].'\')">
                                                    <div class="icon" style="background:url(/images/'.$row['ImageName'].') no-repeat center;background-size:100%"></div>
                                                    <label>None</label>
                                                </a>
                                            </li>';
                                    }
                                    else
                                    {
                                        echo '<li>
                                                <a class="style-icon" onclick="product.FabricPopup(\''.$heading.'\', \'\', \''.$row['StyleName'].'\', \''.$row['SymbolID'].'\', \''.$row['StyleID'].'\', \''.$type.'\')">
                                                    <div class="icon" style="background:url('._StyleIconsDir.$row['ImageName'].') no-repeat center;background-size:100%"></div>
                                                    <label>'.$row['StyleName'].'</label>
                                                </a>
                                            </li>';
                                    }
                                }
                                elseif($row['Attribute'] == 'fill')
                                {
                                    echo '<li>
                                            <a class="style-icon" onclick="LoadColors(\''.$heading.'\', \''.$row['StyleName'].'\', \''.$row['SymbolID'].'\')">
                                                <div class="icon" style="background:url('._StyleIconsDir.$row['ImageName'].') no-repeat center;background-size:100%"></div>
                                            </a>
                                        </li>';
                                }
                                elseif($row['Attribute'] == 'image')
                                {
                                    $btn = 1;
                                    foreach($MasterButtons as $button)
                                    {
                                        if($btn == 1)
                                        {
                                            echo '<li style="width:100%">Note: Selected button will be shown in the cart</li>';
                                        }

                                        echo '<li>
                                                <a class="style-icon'.($DefaultStyles[0]['Shirt Button']['ButtonID'] == $button['ButtonID'] ? ' active' : '').'" onclick="product.ChooseButton(\''.$heading.'\', \''.$button['ButtonID'].'\')">
                                                    <div class="icon" style="background:url('._ButtonsDir.$button['ImageName'].') no-repeat center;background-size:100%"></div>
                                                </a>
                                            </li>';

                                        $btn++;
                                    }
                                }

                                if(!isset($row['Attribute']))
                                {
                                    ob_start();

                                    foreach($row as $key => $StyleFabric)
                                    {
                                        if($StyleFabric['Attribute'] == 'edge')
                                        {
                                            echo '<li class="li-edge" style="display:block"><h4>'.$subHeading.'</h4></li>';
                                            break;
                                        }
                                        else
                                        {
                                            echo '<li style="display:block"><h4>'.$subHeading.'</h4></li>';
                                            break;
                                        }
                                    }

                                    foreach($row as $key => $StyleFabric)
                                    {
                                        if($StyleFabric['Attribute'] == 'xlink')
                                        {
                                            if($StyleFabric['StyleName'] == 'None')
                                            {
                                                echo '<li>
                                                        <a class="style-icon" onclick="product.ResetElementFabric(\''.$heading.'\', \''.$subHeading.'\', \''.$StyleFabric['SymbolID'].'\')">
                                                            <div class="icon" style="background:url(/images/'.$StyleFabric['ImageName'].') no-repeat center;background-size:100%"></div>
                                                            <label>None</label>
                                                        </a>
                                                    </li>';
                                            }
                                            else
                                            {
                                                echo '<li>
                                                        <a class="style-icon" onclick="product.FabricPopup(\''.$heading.'\', \''.$subHeading.'\', \''.$StyleFabric['StyleName'].'\', \''.$StyleFabric['SymbolID'].'\', \''.$StyleFabric['StyleID'].'\')">
                                                            <div class="icon" style="background:url('._StyleIconsDir.$StyleFabric['ImageName'].') no-repeat center;background-size:100%"></div>
                                                            <label>'.$StyleFabric['StyleName'].'</label>
                                                        </a>
                                                    </li>';
                                            }
                                        }
                                        elseif($StyleFabric['Attribute'] == 'edge')
                                        {
                                            if($StyleFabric['StyleName'] == 'None')
                                            {
                                                echo '<li class="li-edge">
                                                        <a class="style-icon" onclick="product.RemoveElement(\''.$subHeading.'\')">
                                                            <div class="icon" style="background:url(/images/'.$StyleFabric['ImageName'].') no-repeat center;background-size:100%"></div>
                                                            <label>None</label>
                                                        </a>
                                                    </li>';
                                            }
                                            else
                                            {
                                                echo '<li class="li-edge">
                                                        <a class="style-icon" onclick="product.ShowEdge(\''.$heading.'\', \''.$subHeading.'\', \''.$StyleFabric['StyleName'].'\', \''.$StyleFabric['SymbolID'].'\', \''.$StyleFabric['StyleID'].'\')">
                                                            <div class="icon" style="background:url('._StyleIconsDir.$StyleFabric['ImageName'].') no-repeat center;background-size:100%"></div>
                                                            <label>'.$StyleFabric['StyleName'].'</label>
                                                        </a>
                                                    </li>';
                                            }
                                        }
                                    }
                                    $subStyles = ob_get_contents();
                                    ob_clean();
                                }
                            }
                            echo $subStyles;
                            echo '</ul>';

                        }   // END OF ELSE
                    }

                    echo '</section>';
                }
                ?>
                    <div>
                        <div class="fabric-filter-holder"></div>
                        <div id="fabric-wrapper"></div>
                    </div>

                    <div class="color-swatch"></div>
            </div>

        </div>
    </div>

    <?php
    /*
        If there are multiple SVGs, default styles set only applies to first SVG.
        So we have collected all the symbol ids apart from default one and set their visibility hidden
    */
    if(isset($HideSymbols))
    {
        echo '<style>'.implode(',',array_filter($HideSymbols)).'{ display: none }</style>';
    }
    ?>