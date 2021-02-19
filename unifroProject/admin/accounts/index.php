<?php
    include_once("../../include-files/autoload-server-files.php");

    SetReturnURL();

    $Admins = MysqlQuery("SELECT AdminID FROM admins");
    $TotalRecords = mysqli_num_rows($Admins);

    $SelectedPage = 'Admin Accounts';
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

        });
        </script>

    </head>
    <body>

        <?php include(_ROOT._AdminIncludesDir."admin-common-scripts.php"); ?>

        <div id="wrapper">

            <?php include(_ROOT._AdminIncludesDir."admin-header.php"); ?>
            <?php include(_ROOT._AdminIncludesDir."admin-sidebar.php"); ?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="content-page">
                <!-- Start content -->
                <div class="content">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="content-header clearfix mg-b-5">
                                    <a href="/admin/accounts/add/" class="btn btn-primary">
                                        <i class="fa fa-plus-square"></i>&nbsp;
                                        Create New Account
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-box">
                                    <table class="table" data-expand-first="false" data-toggle-column="last">
                                        <thead>
                                            <tr>
                                                <th data-breakpoints="md sm xs">Admin ID</th>
                                                <th>User Name</th>
                                                <th>Name</th>
                                                <th data-breakpoints="xs">Email</th>
                                                <th data-breakpoints="xs">Role</th>
                                                <th data-breakpoints="xs">Status</th>
                                                <th data-breakpoints="xs sm">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if($TotalRecords > 0)
                                        	{
                                        	    $PerPage = 25;
                                                $page = !is_numeric($_GET['page'])?0:($_GET['page'] - 1);
                                                $page = $page * $PerPage;

                                                $navs = CreateNavs($TotalRecords, $PerPage, 'page', 5, '', false);

                                                $q = "SELECT a.*, r.Role FROM admins a LEFT JOIN roles r ON r.RoleID = a.RoleID";
                                                $q = $q.(count($filter) > 0 ? ' AND '.implode(' AND ', $filter) : '')." ORDER BY AdminID LIMIT ".$page.", ".$PerPage;

                        				        $res = MysqlQuery($q);
                                                for(; $data = mysqli_fetch_assoc($res); )
                                                {
                                                    echo '<tr>
                                                            <td class="text-center" style="width:90px">'.$data['AdminID'].'</td>
                                                            <td>'.$data['Username'].'</td>
                                                            <td>'.$data['Name'].'</td>
                                                            <td>'.$data['Email'].'</td>
                                                            <td>'.$data['Role'].'</td>
                                                            <td>
                                                                <LABEL class="switch big">
                                                                    <INPUT type="checkbox" class="switch-input"'.($data['Status'] == '1' ? ' checked' : '').'
                                                                            data-id="'.$data['AdminID'].'" data-value="Admin" onclick="ChangeStatus(this)">
                                                                    <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                               </LABEL>
                                                            </td>
                                                            <td>
                                                                '.($data['Username'] != 'super_admin'
                                                                    ? ' <a class="btn btn-success" href="/admin/accounts/edit/?id='.$data['AdminID'].'">Edit</a>
                                                                        <button class="btn btn-danger" data-id="'.$data['AdminID'].'">Delete</button>'
                                                                    : '').'
                                                            </td>
                                                        </tr>';
                                                }
                                                echo $navs != '' ? '<tr><td colspan="8"><div class="pd-t-30">'.$navs.'</div></td></tr>' : '';
                                            }
                                            else
                                                echo '<tr><td colspan="8">No Data Found</td></tr>';
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div> <!-- container -->

                </div> <!-- content -->

                <footer class="footer text-right">
                    <?=date('Y')?> &copy; Admin
                </footer>

            </div>


            <!-- ============================================================== -->
            <!-- End Right content here -->
            <!-- ============================================================== -->

        </div>
        <!-- END wrapper -->

    </body>
</html>