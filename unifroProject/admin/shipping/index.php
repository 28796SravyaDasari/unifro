<?php
    include_once("../../include-files/autoload-server-files.php");

    $SelectedPage = 'Shipping Management';

    SetReturnURL();

    $TotalRecords = mysqli_num_rows(MysqlQuery("SELECT StateID FROM states"));
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$SelectedPage?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>

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
                            <div class="col-lg-12">
                                <div class="card-box">

                                    <table class="table" data-expand-first="false" data-toggle-column="last">
                                        <thead>
                                            <tr>
                                                <th data-breakpoints="md sm xs">State</th>
                                                <th>Free Shipping<br>on Order Total</th>
                                                <th>Shipping Cost</th>
                                                <th data-breakpoints="md sm xs">Status</th>
                                                <th data-breakpoints="md sm xs">Updated By</th>
                                                <th data-breakpoints="md sm xs">Updated On</th>
                                                <th data-breakpoints="md sm xs">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                                <?php

                                            	    $PerPage = 20;
                                    				$page = !is_numeric($_GET['page'])?0:($_GET['page'] - 1);
                                    				$page = $page * $PerPage;

                                    				$ShowTotalPages = $TotalRecords > $PerPage ? true : false;
                                    				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Records - ', $ShowTotalPages);

                                            	    $GetStates = "SELECT s.*, a.Name AS AdminName FROM states s LEFT JOIN admins a ON a.AdminID = s.UpdatedBy ORDER BY s.Name LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetStates);

                                                    for(; $row = mysqli_fetch_assoc($res); )
                                                    {
                                                        echo '  <td>'.$row['Name'].'</td>
                                                                <td>'.$row['FreeShippingLimit'].'</td>
                                                                <td>'.$row['ShippingCharge'].'</td>
                                                                <td style="width: 15%">
                                                                    <LABEL class="switch big">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Status'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$row['StateID'].'" data-value="states" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
                                                                </td>
                                                                <td>'.$row['AdminName'].'</td>
                                                                <td>'.FormatDateTime('dt',$row['UpdatedOn']).'</td>
                                                                <td>
                                                                    <a class="btn btn-default" href="/admin/shipping/edit/?id='.$row['StateID'].'"><i class="fa fa-pencil"></i>&nbsp; Edit</a>
                                                                </td>
                                                            </tr>';
                                                    }

                                                ?>

                                        </tbody>
                                    </table>
                                    <?=$navs != '' ? '<div>'.$navs.'</div>' : ''?>
                                </div>
                            </div>
                        </div>
                    </div>   <!-- container -->

                </div>   <!-- content -->
            </div>


                <?php include(_ROOT._AdminIncludesDir."footer.php"); ?>

            </div>
            <!-- ==============================================================
                End of Right content here
            ============================================================== -->
        </div>

	</body>
</html>