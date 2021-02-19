<?php

    include_once("../include-files/autoload-server-files.php");

    $ActivityParams['attribute_options']['attribute_options'] = array('PrimaryKey' => 'OptionID', 'FieldID' => $ID, 'FieldName' => 'OptionName', 'ParentID' => 'AttributeID');
    $ActivityParams['attribute_options']['master_element_attributes'] = array('PrimaryKey' => 'AttributeID', 'FieldName' => 'AttributeName', 'ParentID' => 'ElementID');
    $ActivityParams['attribute_options']['master_elements'] = array('PrimaryKey' => 'ElementID', 'FieldName' => 'ElementName', 'ParentID' => 'CategoryID');
    $ActivityParams['attribute_options']['master_categories'] = array('PrimaryKey' => 'CategoryID', 'FieldName' => 'CategoryTitle');

    function GetActivityData($TableName, $ID)
    {
        global $ActivityParams;

        $NoofTables = count($ActivityParams[$TableName]);
        $PrevTable = $TableName;

        $query = "SELECT Fields FROM ".$TableName;

        foreach($ActivityParams[$TableName] as $table => $arr)
        {
            $FieldNames[] = $arr['FieldName'];

            if($NoofTables > 1)
            {
                if($TableName != $table)
                {
                    $query .= " LEFT JOIN ".$table." ON ".$table.".".$arr['PrimaryKey']." = ".$PrevTable.".".$ActivityParams[$TableName][$PrevTable]['ParentID'];
                    $PrevTable = $table;
                }
                else
                {
                    $PrimaryKey = $arr['PrimaryKey'];
                }
            }
            else
            {
                $PrimaryKey = $arr['PrimaryKey'];
            }
        }
        krsort($FieldNames);
        $query .= " WHERE ".$TableName.".".$PrimaryKey." = ".$ID;
        $query = str_replace('Fields', implode(', ',$FieldNames), $query);
        $response = mysqli_fetch_assoc(MySQLQuery($query));

        $html = '<table class="table">';

        foreach($FieldNames as $field)
        {
            $html .= '<tr><td>'.$field.'</td><td>'.$response[$field].'</td></tr>';
        }
        $html .= '</table>';

        print_r($html);
    }

    echo GetActivityData(attribute_options, 8);
?>
