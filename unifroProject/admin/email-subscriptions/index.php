<?php
    include_once("../../include-files/autoload-server-files.php");

    $SelectedPage = 'Email Subscriptions';
    SetReturnURL();

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if(is_numeric($_GET['s']))
    {
        $filter[] = "Subscribed = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }
    if($_GET['q'] != '')
    {
        $_GET['q'] = CleanText($_GET['q']);
        $ActiveFilter['q'] = 'filtered';

        $filter[] = "(Email LIKE '%".$_GET['q']."%')";
    }

    // FETCH THE SALES AGENTS FROM THE DATABASE
    $GetEmails = "SELECT Email FROM newsletter_subscription".(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '');
    $GetEmails = MysqlQuery($GetEmails);
    $TotalRecords = mysqli_num_rows($GetEmails);


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$SelectedPage?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
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
                                <div class="filter-box">
                                    <form>
                                        <ul class="filters list-inline">
                                            <li>
                                                <i class="fa fa-filter"></i> Filter
                                            </li>
                                            <li>
                                                <select class="selectric xs <?=$ActiveFilter['s']?>" name="s">
                                                    <option value="">Subscribed</option>
                                                    <option<?=$_GET['s'] == 1 ? ' selected' : ''?> value="1">Yes</option>
                                                    <option<?=$_GET['s'] == '0' ? ' selected' : ''?> value="0">No</option>
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
                                                    <th data-breakpoints="md sm xs" style="width: 300px">Email ID</th>
                                                    <th>Subscribed</th>
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

                                            	    $GetEmails = "SELECT * FROM newsletter_subscription";
                                                    $GetEmails = $GetEmails.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '')." LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetEmails);

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
                                                                <td>'.$row['Email'].'</td>
                                                                <td>
                                                                    <LABEL class="switch">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Subscribed'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$row['ID'].'" data-value="Subscription" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Yes" data-off="No"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
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

	</body>
</html>