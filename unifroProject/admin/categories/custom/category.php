<?php
    include_once("../../../include-files/autoload-server-files.php");

    $SelectedPage = 'Custom';
    SetReturnURL();

    // FETCH THE CATEGORY ELEMENTS FROM THE DATABASE
    $GetElements = MysqlQuery("SELECT * FROM master_elements WHERE CategoryID = '".$ID."'");
    if(mysqli_num_rows($GetElements) > 0)
    {
        $Elements = MysqlFetchAll($GetElements);
    }

    // GET THE LIST OF COLORS
    $GetColors = MysqlQuery("SELECT * FROM color_list");
    for(;$color = mysqli_fetch_assoc($GetColors);)
    {
        $ColorList .= '<li><a class="color-button" style="background-color:'.$color['ColorCode'].'"></a></li>';
    }
    $MasterButtons = MysqlQuery("SELECT * FROM master_buttons");
    $MasterButtons = MysqlFetchAll($MasterButtons);

    /*-----------------------------------------
    let's fetch the Element Styles
    ------------------------------------------*/
    $query = "SELECT me.ElementID, me.ElementName, me.StyleType, eh.Heading, eh.ParentID, eh.Attribute, es.* FROM master_elements me LEFT JOIN master_element_headings eh ON eh.ElementID = me.ElementID LEFT JOIN element_styles es ON es.HeadingID = eh.HeadingID WHERE me.CategoryID = '".$_GET['id']."'";

    $query = MysqlQuery($query);

    for(;$row = mysqli_fetch_assoc($query);)
    {
        if($row['DefaultStyle'] == 1)
        {
            if($row['Attribute'] == 'hide')
            {
                $DefaultStyles[] = array($row['Heading'] => array('StyleID' => $row['StyleID'], 'SymbolID' => $row['SymbolID']));
            }
            elseif($row['Attribute'] == 'xlink')
            {
                $DefaultStyles[] = array($row['Heading'] => array( $row['StyleName'] => array('FabricID' => '', 'StyleID' => $row['StyleID'], 'SymbolID' => $row['SymbolID'])));
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

        $Percentage[$row['StyleID']] = $row['Percentage'];
    }

    // GET THE ELEMENT HEADER LABELS
    $GetHeaderLabels = MysqlQuery("SELECT eh.*, es.* FROM master_element_headings eh LEFT JOIN element_styles es ON es.HeadingID = eh.HeadingID WHERE eh.ElementID = '".$_GET['eid']."'");
    $TotalHeadings = mysqli_num_rows($GetHeaderLabels);
    if($TotalHeadings > 0)
    {
        for( ;$row = mysqli_fetch_assoc($GetHeaderLabels); )
        {
            $HeaderLabels[$row['HeadingID']][] = $row;

            if($row['ParentID'] == 0)
            {
                $Style['HeadingID'] = $row['HeadingID'];
                $Style['Name'] = $row['Heading'];
            }
            else
            {
                $StyleOptions[] = '<li>
                                        <a onclick="window.location=\''.GoToLastPage().'&sid='.$row['StyleID'].'\'">'.$row['StyleName'].'</a>
                                   </li>';
            }
        }

        // LETS FETCH THE UPLOADED STYLES
        $GetStyles = MysqlQuery("SELECT * FROM element_styles WHERE HeadingID = '".$Style['HeadingID']."' AND ElementID = '".$_GET['eid']."'");
        $TotalStyles = mysqli_num_rows($GetStyles);
        if($TotalStyles > 0)
        {
            $GetStyles = MysqlFetchAll($GetStyles);
        }
    }


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$PageTitle?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <link rel="stylesheet" type="text/css" href="/css/slick.css"/>
        <link rel="stylesheet" type="text/css" href="/css/slick-theme.css"/>
        <link rel="stylesheet" href="/css/bootstrap-editable.css" type="text/css">

        <style>
        .jFiler-theme-default .jFiler-input{ box-shadow: none; }
        </style>

        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>
        <script type="text/javascript" src="/js/bootstrap-editable.min.js"></script>

        <script>
        $(document).ready(function()
        {
            $('.elements-nav').slick({
                dots: false,
                infinite: false,
                speed: 300,
                slidesToShow: 8,
                slidesToScroll: 8,
                responsive: [
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 5,
                            slidesToScroll: 5,
                        }
                    },
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 5,
                            slidesToScroll: 5,
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 4,
                            slidesToScroll: 4
                        }
                    },
                    {
                        breakpoint: 320,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    }
                ]
            });

            $('input[name=ImageName]').filer(
            {
                captions:   {
                                button: "Choose File",
                                feedback: "",
                                feedback2: "file chosen",
                            }
            });

            $("#StylesForm").submit(function(e)
            {
                e.preventDefault();
                AjaxResponse = AjaxFormSubmit(this);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        $('#AddModal').modal('hide');
                        AlertBox(response.message, 'success', function(){ location.href = response.redirect });
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

            $('.editable').editable(
            {
                url: '/admin/ajax/element-percentage/',
                type: 'number',
                success: function(response, newValue)
                {
                    data = $.parseJSON(response);

                    if(data.status == 'success')
                        return true;
                    else if(data.status == 'login')
                        location.reload();
                    else
                        return data.response_message;
                },
            });
        });
        </script>

    </head>
    <body>

        <?php include_once(_ROOT._AdminIncludesDir."admin-common-scripts.php"); ?>

        <div id="wrapper">
            <?php include_once(_ROOT._AdminIncludesDir."admin-header.php"); ?>
            <?php include_once(_ROOT._AdminIncludesDir."admin-sidebar.php"); ?>

            <!-- ==============================================================
                Start of Right content here
            ============================================================== -->
            <div class="content-page">

                <div class="content">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="content-header clearfix">
                                    <h4 class="page-title"><?=$PageTitle?></h4>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-box">
                                    <div class="elements-scroller">
                                    <?php
                                    // FETCH THE CATEGORY ELEMENTS FROM THE DATABASE
                                    if(count($Elements) > 0)
                                    {
                                        echo '<div class="elements-nav carousel">';

                                        foreach($Elements as $row)
                                        {
                                            $active = $row['ElementID'] == $_GET['eid'] ? ' class="active"' : '';

                                            echo '<div class="slide">
                                                    <a href="?id='.$_GET['id'].'&eid='.$row['ElementID'].'"'.$active.'>
                                                        <div class="icon" style="background: #fff url('._ElementIconDir.$row['ElementIcon'].') no-repeat center"></div>
                                                        <label>'.$row['ElementName'].'</label>
                                                    </a>
                                                    <div>
                                                        <LABEL class="switch">
                                                            <INPUT type="checkbox" class="switch-input"'.($row['ElementStatus'] == '1' ? ' checked' : '').'
                                                                    data-id="'.$row['ElementID'].'" data-value="Element" onclick="ChangeStatus(this)">
                                                            <SPAN class="switch-label" data-on="On" data-off="Off"></SPAN><SPAN class="switch-handle"></SPAN>
                                                       </LABEL>
                                                    </div>
                                                </div>';

                                        }
                                        echo '</div>';
                                    }
                                    else
                                    {
                                        echo '<div class="no-data">No Elements Found!</div>';
                                    }
                                    ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-box">
                                    <?php
                                    if($TotalHeadings > 0)
                                    {
                                        foreach($HeaderLabels as $attributeID => $arr)
                                        {
                                            echo '<div class="options-header clearfix bg-muted mg-b-15'.($arr[0]['ParentID'] > 0 ? ' mg-t-30' : '').'">
                                                        <h3 class="pull-left">'.$arr[0]['Heading'].'</h3>
                                                        <!--
                                                        <div class="pull-right">
                                                            <button type="button" class="btn btn-primary" data-id="'.$arr[0]['HeadingID'].'" onclick="OpenAddModal(this)">
                                                                '.$arr[0]['ButtonName'].'
                                                            </button>
                                                        </div>
                                                        -->
                                                  </div>';

                                            echo '<ul class="attribute-options-list">';

                                            if($arr[0]['Attribute'] == 'image')
                                            {
                                                foreach($MasterButtons as $button)
                                                {
                                                    echo '<li class="font12 text-center">
                                                            <a class="thumbnail">
                                                                <img class="style-img" src="'._ButtonsDir.$button['ImageName'].'">
                                                            </a>
                                                            <div class="mg-t-5 mg-b-15">
                                                                <LABEL class="switch">
                                                                    <INPUT type="checkbox" class="switch-input"'.($button['Status'] == '1' ? ' checked' : '').'
                                                                            data-id="'.$button['ButtonID'].'" data-value="Button" onclick="ChangeStatus(this)">
                                                                    <SPAN class="switch-label" data-on="On" data-off="Off"></SPAN><SPAN class="switch-handle"></SPAN>
                                                               </LABEL>
                                                            </div>
                                                          </li>';
                                                }
                                            }
                                            else
                                            {
                                                foreach($arr as $subArr)
                                                {
                                                    if($subArr['StyleID'] > 0)
                                                    {
                                                        if($subArr['DefaultStyle'] == 1)
                                                        {
                                                            $SetDefault = '<img class="set-default" src="/images/mark-default.png" title="Default Style">';
                                                        }
                                                        else
                                                        {
                                                            $SetDefault = '';
                                                        }

                                                        if($subArr['StyleName'] != 'None')
                                                        {
                                                            echo '<li class="font12 text-center">
                                                                    <a class="thumbnail">
                                                                        '.$SetDefault.'
                                                                        <img class="style-img" src="'._StyleIconsDir.$subArr['ImageName'].'">
                                                                        <ul class="options">
                                                                        <!--
                                                                            <li data-id="'.$subArr['StyleID'].'" data-name="'.$subArr['StyleName'].'" data-symbol="'.$subArr['SymbolID'].'" onclick="EditElementStyle(this)">Edit</li>
                                                                        -->
                                                                            '.($arr[0]['ParentID'] == 0 && $subArr['DefaultStyle'] == 0 ? '<li data-id="'.$subArr['HeadingID'].'" data-pk="'.$subArr['StyleID'].'" data-opt="element_style" onclick="SetDefault(this)">Set Default</li>' : '').'
                                                                        </ul>
                                                                    </a>
                                                                    <div class="option-name">'.$subArr['StyleName'].'</div>
                                                                    <LABEL class="switch">
                                                                        <INPUT type="checkbox" class="switch-input"'.($subArr['StyleStatus'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$subArr['StyleID'].'" data-value="Element Style" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="On" data-off="Off"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>';

                                                            if($arr[0]['Attribute'] == 'xlink' || $arr[0]['Attribute'] == 'edge')
                                                            {
                                                                echo '<br>
                                                                    <span id="ShippingPincode" class="editable" data-pk="'.$subArr['StyleID'].'" data-title="Update Percentage">
                                                                        '.floatval($subArr['Percentage']).'
                                                                    </span>%';
                                                            }
                                                            echo '</li>';
                                                        }
                                                    }
                                                }
                                            }

                                            echo '</ul>';
                                        }
                                    }
                                    else
                                    {
                                        if(!is_numeric($_GET['eid']))
                                        {
                                            echo '<div class="no-data">Click on the above elements to list the Styles</div>';
                                        }
                                        else
                                        {
                                            echo '<div class="no-data">Styles Not Defined!</div>';
                                        }
                                    }

                                    ?>
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

        <!-- Modal -->
        <div id="AddModal" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"></h4>
                    </div>
                    <div class="modal-body">
                        <form action="/admin/ajax/save-element-style/" id="StylesForm">
                            <input type="hidden" name="CategoryID" value="<?=$_GET['id']?>" />
                            <input type="hidden" name="ElementID" value="<?=$_GET['eid']?>" />
                            <input type="hidden" id="HeadingID" name="HeadingID" />
                            <input type="hidden" id="StyleID" name="StyleID" />

                            <div class="form-group">
                                <label class="control-label required">Name</label>
                                <input type="text" class="form-control" id="StyleName" name="StyleName" maxlength="40">
                            </div>
                            <div class="form-group">
                                <label class="control-label required">Symbol ID</label>
                                <span class="font12">(It has to be the same ID which is used in SVG image for this style)</span>
                                <input type="text" class="form-control" id="SymbolID" name="SymbolID" maxlength="40">
                            </div>
                            <div class="form-group">
                                <label class="control-label required">Upload Image</label>

                                <div class="style-thumb hidden">
                                    <img class="img-thumbnail" src="" alt="" />
                                </div>

                                <input type="file" name="ImageName" data-jfiler-limit="1" data-jfiler-extensions="jpg,jpeg,png" data-jfiler-caption="Only JPG & PNG files are allowed to be uploaded.">
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary">Save Details</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <script src="/js/slick.min.js"></script>
        <?=$OpenModal?>

	</body>
</html>