<?php
require_once __DIR__."/libs/db/db.php";
require_once (__DIR__."/libs/functions.php");

$db=Db::init();

$res = $db->get_results("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
//var_dump($res);

$arr_tables=array();

//遍历表
if (is_array($res))
{
    foreach ($res as $table)
    {
        $recodes = $db->get_results("SELECT * FROM $table->name;");
        $arr_tables["$table->name"]=array();
        //遍历表中所有元素
        if (is_array($recodes))
        {
            foreach ($recodes as $rec)
            {
                $arr_record=array();
                //保存所有元素
                foreach ($rec as $class=>$value)
                {
                    $arr_record["$class"]=$value;
                }
                $arr_tables["$table->name"][]=$arr_record;
            }
        }
    }
}
echo json_encode ($arr_tables);

?>
