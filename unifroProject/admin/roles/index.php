<?php
    include_once("../../include-files/autoload-server-files.php");

    $SelectedPage = 'Role Management';
    SetReturnURL();

    if($_GET['id'] > 0)
    {
        $_POST = mysqli_fetch_assoc(MysqlQuery("SELECT * FROM roles WHERE RoleID = '".$_GET['id']."' LIMIT 1"));
    }
    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if(is_numeric($_GET['s']))
    {
        $filter[] = "Status = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }
    if($_GET['q'] != '')
    {
        $_GET['q'] = CleanText($_GET['q']);
        $ActiveFilter['q'] = 'filtered';

        if(is_numeric($_GET['q']))
        {
            $filter[] = "RoleID = '".$_GET['q']."'";
        }
        else
        {
            $filter[] = "(Role LIKE '%".$_GET['q']."%')";
        }
    }

    // FETCH THE SALES AGENTS FROM THE DATABASE
    $GetRoles = "SELECT RoleID FROM roles".(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '');
    $GetRoles = MysqlQuery($GetRoles);
    $TotalRecords = mysqli_num_rows($GetRoles);


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$SelectedPage?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <link href="/css/lightbox.css" rel="stylesheet" />

        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            $('input[name=CheckAll]').on('change', function(){
                $('input[name*=RoleID]').prop('checked', this.checked);
            });

            $('button.permissions').on('click', function ()
            {
                $('.overlay-loader').show();
                $('.access-settings-menu').removeClass('sleep');
                $('.access-settings-inner').scrollTop(0);

                $.post( "permissions.php", { id: $(this).attr('data-id'), opt: 'get' })
                .done(function( response )
                {
                    $('.overlay-loader').hide();

                    res = $.parseJSON(response);

                    if(res.status == 'success')
                	{
                	    $('.admin-pages').html(res.message);

                        $('input[type=checkbox]').change(function ()
                        {
                            ToggleCheckboxSiblings(this);
                        });

                        $('.CheckAll').on('change', function(){
                            $('.admin-pages form').find(':checkbox').prop('checked', this.checked);
                        });
                	}
                    else if(res.status == 'login')
                	{
                	    AlertBox('Oops! Your session has expired! Please login again.', function(){ window.location = data.redirect } )
                	}
                	else
                	{
                        AlertBox( data.message );
                	}
                });

            });

            $("#AddRoleForm").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();
                AjaxResponse = AjaxFormSubmit(this);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
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
                                <div class="card-box">
                                    <form class="clearfix" action="/admin/ajax/add-role/" id="AddRoleForm">
                                        <input type="hidden" name="RoleID" value="<?=$_POST['RoleID']?>">
                                        <div class="col-md-3 col-sm-6">
                                            <input type="text" class="form-control" name="Role" placeholder="Type Role Name" value="<?=$_POST['Role']?>">
                                        </div>
                                        <div class="col-md-9 col-sm-6">
                                            <button class="btn btn-info"><?=$_GET['id'] > 0 ? 'Save' : '<i class="fa fa-plus-square"></i> &nbsp;Add'?> Role</button>
                                            <button type="button" class="btn btn-danger" data-id="RoleForm" onclick="DeleteSelected(this)">
                                                <i class="fa fa-trash-o"></i>&nbsp;
                                                Delete Selected
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="filter-box">
                                    <form>
                                        <ul class="filters list-inline">
                                            <li>
                                                <i class="fa fa-filter"></i> Filter
                                            </li>
                                            <li>
                                                <select class="selectric xs <?=$ActiveFilter['s']?>" name="s">
                                                    <option value="">Status</option>
                                                    <option<?=$_GET['s'] == 1 ? ' selected' : ''?> value="1">Active</option>
                                                    <option<?=$_GET['s'] == '0' ? ' selected' : ''?> value="0">Inactive</option>
                                                </select>
                                            </li>
                                            <li>
                                                <input type="text" class="form-control xs <?=$ActiveFilter['q']?>" name="q" value="<?=$_GET['q']?>">
                                            </li>
                                            <li>
                                                <button class="btn btn-info btn-xs"><i class="fa fa-search"></i> &nbsp;Search</button>
                                                <?=ClearFilter()?>
                                            </li>
                                        </ul>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-box">
                                    <form action="/admin/ajax/delete-selected/" id="RoleForm">
                                        <input type="hidden" name="option" value="Roles" />
                                        <input type="hidden" name="FieldName" value="RoleID" />
                                        <table class="table" data-expand-first="false" data-toggle-column="last">
                                            <thead>
                                                <tr>
                                                    <th style="width: 40px">
                                                        <div class="custom-checkbox">
                                                            <label>
                                                                <input type="checkbox" name="CheckAll" value="All" /><span></span>
                                                            </label>
                                                        </div>
                                                    </th>
                                                    <th data-breakpoints="md sm xs">Role Name</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if($TotalRecords > 0)
                                            	{
                                            	    $PerPage = 20;
                                    				$page = !is_numeric($_GET['page'])?0:($_GET['page'] - 1);
                                    				$page = $page * $PerPage;

                                    				$ShowTotalPages = $TotalRecords > $PerPage ? true : false;
                                    				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total - ', $ShowTotalPages);

                                            	    $GetRoles = "SELECT * FROM roles";
                                                    $GetRoles = $GetRoles.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '')." ORDER BY RoleID DESC LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetRoles);

                                                    for(; $row = mysqli_fetch_assoc($res); )
                                                    {
                                                        echo '<tr id="row'.$row['RoleID'].'">
                                                                <td>
                                                                    <div class="custom-checkbox">
                                                                        <label>
                                                                            <input type="checkbox" name="RoleID[]" value="'.$row['RoleID'].'" /><span></span>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td>'.$row['Role'].'</td>
                                                                <td>
                                                                    <LABEL class="switch big">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Status'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$row['RoleID'].'" data-value="Role" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
                                                                </td>
                                                                <td>
                                                                    <a class="btn btn-default" href="?id='.$row['RoleID'].'">Edit</a>
                                                                    <button class="btn btn-success permissions" data-id="'.$row['RoleID'].'" type="button">Set Permissions</button>
                                                                </td>
                                                            </tr>';
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </form>
                                </div>

                                <?=$navs != '' ? '<div class="card-box">'.$navs.'</div>' : ''?>

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

        <!-- ============================================================
             START OF ACCESS SETTINGS MENU
        ============================================================== -->
        <div class="access-settings-menu sleep">
            <div class="access-settings-inner">
                <div class="admin-pages"></div>
            </div>
            <div class="menu-footer">
                <button class="btn btn-info" onclick="SavePermissions(this)">Save Settings</button>
                <button class="btn btn-danger" onclick="$('.access-settings-menu').addClass('sleep')">Cancel</button>
            </div>

            <div class="overlay-loader">
                <div>
                    <img src="/images/loader.gif" alt="Loader" /><br>
                    Loading...
                </div>
            </div>
        </div>

        <script>
        $('.access-settings-inner').slimScroll({
            height: 'auto',
            position: 'right',
            size: "5px",
            color: '#454545',
            wheelStep: 10,
            touchScrollStep : 20
        });
        </script>

        <!-- ============================================================
             START OF ACCESS SETTINGS MENU
        ============================================================== -->

	</body>
</html>