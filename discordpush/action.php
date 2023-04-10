<?php
require_once( 'discord.php' );

if(isset($_REQUEST['cmd']))
{
    $cmd = $_REQUEST['cmd'];
    switch($cmd)
    {
        case "set":
        {
            $up = Discord::load();
            $up->set();
            if (function_exists('cachedEcho')) {
                cachedEcho($up->get(),"application/javascript");
            } else {
                CachedEcho::send($up->get(),"application/javascript");
            }
            break;
        }
        case "get":
        {
            $up = rHistoryData::load();
            if (function_exists('cachedEcho')) {
                cachedEcho(safe_json_encode($up->get($_REQUEST['mark'])),"application/json");
            } else {
                CachedEcho::send(safe_json_encode($up->get($_REQUEST['mark'])),"application/json");
            }
            break;
        }
        case "delete":
        {
            $up = rHistoryData::load();
            $hashes = array();
            if(!isset($HTTP_RAW_POST_DATA))
                $HTTP_RAW_POST_DATA = file_get_contents("php://input");
            if(isset($HTTP_RAW_POST_DATA))
            {
                $vars = explode('&', $HTTP_RAW_POST_DATA);
                foreach($vars as $var)
                {
                    $parts = explode("=",$var);
                    $hashes[] = $parts[1];
                }
                $up->delete( $hashes );
            }
            if (function_exists('cachedEcho')) {
                cachedEcho(safe_json_encode($up->get(0)),"application/json");
            } else {
                CachedEcho::send(safe_json_encode($up->get(0)),"application/json");
            }
            break;
        }
    }
}
