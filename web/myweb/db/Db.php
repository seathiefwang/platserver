<?php

include_once __DIR__."/ez_sql_core.php";
include_once __DIR__."/ez_sql_mysql.php";
/**********************************************************************
 *  ezSQL initialisation for mySQL
 */

class Db
{
    public static $mysql_username     = "root"; //数据库服务器名称 
    public static $mysql_password     = "lvxin123"; // 连接数据库用户名     
    public static $mysql_database     = "sopings"; // 连接数据库密码  
    public static $mysql_server_name  = "120.25.105.190"; // 数据库的名字    
    public static function init()
    {
        $db = new ezSQL_mysql(
            Db::$mysql_username,
            Db::$mysql_password,
            Db::$mysql_database,
            Db::$mysql_server_name,
            "utf8");
        //var_dump($db->get_var("SELECT " . $db->sysdate()));
        return $db;
    }
}

/**********************************************************************
 *  ezSQL demo for mySQL database
 */

// Demo of getting a single variable from the db
// (and using abstracted function sysdate)
//$current_time = $db->get_var("SELECT " . $db->sysdate());
//print "ezSQL demo for mySQL database run @ $current_time\n";

// Print out last query and results..
//$db->debug();

// Get list of tables from current database..
//$my_tables = $db->get_results("SHOW TABLES",ARRAY_N);

// Print out last query and results..
//$db->debug();

// Loop through each row of results..
//foreach ( $my_tables as $table )
//{
// Get results of DESC table..
//$db->get_results("DESC $table[0]"); 
//$row = $db->get_row();
//echo $row;
// Print out last query and results..
//$db->debug();
//}

//$user = $db->get_results("select * from user_info");
////$db->vardump($user);
//foreach ($user as $a)
//{
//echo $a->openid."\n";
//}

?>
