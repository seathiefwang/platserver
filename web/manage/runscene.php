<?php
require_once __DIR__."/libs/db/db.php";
require_once __DIR__."/libs/Uart.php";
require_once __DIR__."/libs/Event.php";

if (isset($argv[1]))
{
    $sceneid = $argv[1];

    $db = Db::init();
    $results = $db->get_results('select * from scene_info where id='.$sceneid.' order by "order"');
    $scenes = array();
    if (isset($results[0]))
    {
        foreach ($results as $rec)
        {
            for($i=0; $i=100; $i++) //busy的情况下，重发
            {
                $res = Event::procMessage(json_decode ($rec->cmdline, true), __DIR__."/libs/Services"); //执行cmdline
                $json = json_decode($res, true);
                if ($json["code"] == 30006) continue; 
                else break;
            }
        }
    }
    echo "OOOOO\n";
}
?>
