<?php

include_once "ez_sql_core.php";
include_once "ez_sql_sqlite3.php";

class Db
{
    public static function init()
    {
        $db = new ezSQL_sqlite3('/air/','smarthome.db');
        $my_tables = $db->get_results("SELECT * FROM sqlite_master WHERE sql NOTNULL;");
        if(count($my_tables) == 0)
        {
            echo "Create DB\n";

            $a = <<<EO3
CREATE TABLE "Root" (
"tdid"  TEXT NOT NULL,
PRIMARY KEY ("tdid" ASC)
);
EO3;
            $db->query($a);
            $b = <<<EOa
CREATE TABLE "Modules" (
"type"  TEXT,
"mid"  TEXT NOT NULL,
"extra"  INTEGER,
PRIMARY KEY ("mid" ASC)
);
EOa;
            $db->query($b);
            $c = <<<EOb
CREATE TABLE "mod_child" (
"mid"  TEXT NOT NULL,
"no"  INTEGER NOT NULL,
"name"  TEXT NOT NULL,
CONSTRAINT "mid" FOREIGN KEY ("mid") REFERENCES "Modules" ("mid")
);
EOb;
            $db->query($c);
            $d = <<<EOc
CREATE TABLE "Scene" (
"name"  TEXT NOT NULL,
"sceneid"  INTEGER NOT NULL,
PRIMARY KEY ("name")
);
EOc;
            $db->query($d);
            $e = <<<EOD
CREATE TABLE "scene_info" (
"id"  TEXT NOT NULL,
"order"  INTEGER NOT NULL,
"cmdline"  TEXT NOT NULL,
CONSTRAINT "sceneid" FOREIGN KEY ("id") REFERENCES "Scene" ("sceneid")
);
EOD;
            $db->query($e);
            $a = <<<EO3
CREATE TABLE "switch_info" (
"switchid"  TEXT NOT NULL,
"number"  INTEGER NOT NULL
);
EO3;
            $db->query($a);

        }
        return $db;
    }
}
?>
