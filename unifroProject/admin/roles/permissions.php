<?php
    include("../../include-files/autoload-server-files.php");

    if($LoggedIn)
    {
        $response['status'] = 'error';

        if(is_numeric($_POST['id']))
        {
            // LETS VALIDATE THE ROLE ID
            $q = "SELECT r.*, GROUP_CONCAT(p.PageID) AS Permissions FROM roles r LEFT JOIN role_permissions p ON p.RoleID = r.RoleID WHERE r.RoleID = '".$_POST['id']."'";
            $res = MysqlQuery($q);
            if(mysqli_num_rows($res))
            {
                if($_POST['opt'] == 'get')
                {
                    $res = mysqli_fetch_assoc(MysqlQuery($q));

                    if($res['Permissions'] == '')
                    {
                        $res['Permissions'] = "All";
                    }

                    $data = '<header>
                                <p>Set Permissions for</p>
                                <h3>'.$res['Role'].'</h3>
                            </header>

                            <form action="/admin/roles/permissions.php" id="AccessSettingsForm" data-id="'.$res['RoleID'].'" data-opt="save">
                                <div class="check-all">
                                    <div class="custom-checkbox medium">
                                        <label>
                                            <input'.($res['Permissions'] == 'All' ? ' Checked' : '').' type="checkbox" class="CheckAll" name="CheckAll" value="y" />
                                            <span></span> Check All
                                        </label>
                                    </div>
                                </div>
                                '.AccessSettings(0, $AdminPages, $res['Permissions']).'
                            </form>';

                    $response['status']  = 'success';
                    $response['message'] = $data;
                }
                elseif($_POST['opt'] == 'save')
                {
                    // Let's delete all the permissions from the table
                    MysqlQuery("DELETE FROM role_permissions WHERE RoleID = '".$_POST['id']."'");

                    if(MysqlAffectedRows() >= 0)
                    {
                        if($_POST['CheckAll'] == 'y')
                        {
                            // Do nothing
                            RecordAdminActivity('Admin Access Settings Updated for Role ID: '.$_POST['id']);

                            $response['status']  = 'success';
                            $response['message'] = 'Settings Saved Successfully';
                        }
                        else
                        {
                            foreach($_POST['PageIDs'] as $id)
                            {
                                $ValuesToInsert[] = "'".$_POST['id']."', '".$id."'";
                            }

                            $TargetTable    = 'role_permissions';
                            $fields         = 'RoleID,PageID';
                            $count          = BulkInsert($TargetTable, $fields, $ValuesToInsert);

                            if($count > 0)
                            {
                                RecordAdminActivity('Admin Access Settings Updated for Role ID: '.$_POST['id']);

                                $response['status']  = 'success';
                                $response['message'] = 'Settings Saved Successfully';
                            }
                            else
                            {
                                $response['message'] = 'Error Occurred! Please try again. [LN 10]';
                            }
                        }
                    }
                    else
                    {
                        $response['message'] = 'Error Occurred! Please try again. [LN 200]';
                    }
                }
            }
            else
            {
                $response['message'] = 'Invalid Access! [LN 100]';
            }
        }
        else
        {
            $response['message'] = 'Invalid Access! [LN 200]';
        }
    }
    else
    {
        $response['status']     = 'login';
        $response['redirect']   = '/login/';
    }

    echo json_encode($response);

?>
