<?php
    include_once("../../../include-files/autoload-server-files.php");

    $SelectedPage = 'Custom';

    SetReturnURL();

    $ChildCats = implode(',', GetChildCats('1'));

    // FETCH THE CATEGORIES FROM THE DATABASE
    $GetCategories = "SELECT CategoryID FROM master_categories WHERE CategoryID IN(".$ChildCats.")".(count($filter) > 0 ? implode(' AND ', $filter) : '');
    $GetCategories = MysqlQuery($GetCategories);
    $TotalRecords = mysqli_num_rows($GetCategories);

    $NavigationMenu = MysqlQuery("SELECT * FROM master_categories ORDER By SortOrder");
    for(;$row = mysqli_fetch_assoc($NavigationMenu);)
    {
        $MasterCategory['List'][$row['ParentID']][] = $row['CategoryID'];
        $MasterCategory['Data'][$row['CategoryID']] = $row;
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$SelectedPage?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>
        <script type="text/javascript" src="/js/bootstrap-editable.min.js"></script>

        <script>
        $(document).ready(function()
        {
            $('.editable').editable(
            {
                url: '/admin/category/update-baseprice/',
                type: 'text',
                params: function(params)
                {
                    //originally params contain pk, name and value
                    return params;
                },
                success: function(response, newValue)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                        return true;
                    else if(response.status == 'login')
                        location.reload();
                    else
                        return response.message;
                },
            });

            $('input[name=CheckAll]').on('change', function(){
                $('input[name="CategoryID[]"]').prop('checked', this.checked);
            });

            $('input[name=SVGImage]').filer({
                uploadFile: {
                    url: "/admin/ajax/upload-svg/",
                    type: 'POST',
                    enctype: 'multipart/form-data',
                    synchron: true,
                    beforeSend: function(item, listEl, parentEl, newInputEl, inputEl) {
                        ShowProcessing('Uploading...');
                    },
                    success: function(data, itemEl, listEl, boxEl, newInputEl, inputEl, id)
                    {
                        CloseProcessing();
                        response = JSON.parse(data);
                        if(response.status == 'success')
                        {
                            AlertBox(response.message, 'success', function(){ location.reload() } )
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
                    },
                },
            });
        })
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
                                    <div>
                                        <button type="button" class="btn btn-danger" data-id="CategoryForm" onclick="DeleteSelected(this)">
                                            <i class="fa fa-trash-o"></i>&nbsp;
                                            Delete Selected
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-box">
                                    <form action="/admin/ajax/delete-selected/" id="CategoryForm">
                                    <table class="table" data-expand-first="false" data-toggle-column="last">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <div class="custom-checkbox">
                                                        <label>
                                                            <input type="checkbox" name="CheckAll" value="All" /><span></span>
                                                        </label>
                                                    </div>
                                                </th>
                                                <th data-breakpoints="md sm xs">Category Title</th>
                                                <th class="text-center">Weight (in Grams)</th>
                                                <th class="text-center">Base Price (Rs.)</th>
                                                <th class="text-center">Status</th>

                                                <th data-breakpoints="md sm xs">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                                <input type="hidden" name="option" value="Category" />
                                                <input type="hidden" name="FieldName" value="CategoryID" />
                                                <?php
                                                if($TotalRecords > 0)
                                            	{
                                            	    $PerPage = 25;
                                    				$page = !is_numeric($_GET['page'])?0:($_GET['page'] - 1);
                                    				$page = $page * $PerPage;

                                    				$ShowTotalPages = $TotalRecords > $PerPage ? true : false;
                                    				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Records - ', $ShowTotalPages);

                                            	    $GetCategories = "SELECT * FROM master_categories WHERE CategoryID IN(".$ChildCats.")";
                                                    $GetCategories = $GetCategories.(count($filter) > 0 ? implode(' AND ', $filter) : '')." ORDER BY SortOrder LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetCategories);

                                                    for(; $row = mysqli_fetch_assoc($res); )
                                                    {
                                                        $ParentCat = implode(' &raquo; ', GetParentCats($row['CategoryID'], $MasterCategory));

                                                        $AddSVG = '<input type="file" name="SVGImage" data-jfiler-id="'.$row['CategoryID'].'" data-jfiler-changeInput=\'<div class="btn btn-white btn-sm"><div><i class="fa fa-upload"></i>&nbsp; Upload SVG</div></div>\' data-jfiler-limit="3" data-jfiler-extensions="svg" data-jfiler-caption="Only SVG files are allowed to be uploaded.">';

                                                        echo '<tr id="row'.$row['CategoryID'].'">
                                                                <td style="width: 50px">
                                                                    <div class="custom-checkbox">
                                                                        <label>
                                                                            <input type="checkbox" name="CategoryID[]" value="'.$row['CategoryID'].'" /><span></span>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td>'.($ParentCat != '' ? $ParentCat.' &raquo; ' : '').$row['CategoryTitle'].'</td>
                                                                <td class="text-center" style="width: 15%">
                                                                    <span class="editable" data-pk="'.$row['CategoryID'].'" data-title="Update Weight" id="Weight">'.$row['Weight'].'</span>
                                                                </td>
                                                                <td class="text-center" style="width: 15%">
                                                                    <span class="editable" data-pk="'.$row['CategoryID'].'" data-title="Update Price" id="BasePrice">'.$row['BasePrice'].'</span>
                                                                </td>
                                                                <td class="text-center" style="width: 15%">
                                                                    <LABEL class="switch big">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Status'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$row['CategoryID'].'" data-value="Category" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
                                                                </td>

                                                                <td>
                                                                    <a class="btn btn-default" href="/admin/categories'.$row['CategoryURL'].'?id='.$row['CategoryID'].'"><i class="fa fa-eye"></i>&nbsp; View</a>
                                                                    
                                                                </td>
                                                            </tr>';
                                                    }
                                                }
                                                ?>

                                        </tbody>
                                    </table>
                                    </form>

                                    <?=$navs != '' ? '<div>'.$navs.'</div>' : ''?>

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

	</body>
</html>