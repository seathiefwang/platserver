<?php

/**********************************************************************
 *  ezSQL initialisation for mySQL
 */

include_once "common_inc.php";
// Include ezSQL core
//include_once "ezSQL-master/shared/ez_sql_core.php";

// Include ezSQL database specific component
//include_once "ezSQL-master/mysql/ez_sql_mysql.php";


// Initialise database object and establish a connection
// at the same time - db_user / db_password / db_name / db_host
    $db = new ezSQL_mysql(
        $GLOBALS['mysql_username'],
        $GLOBALS['mysql_password'],
        $GLOBALS['mysql_database'],
        $GLOBALS['mysql_server_name']);

$GLOBALS['db'] = $db;

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
