<?php

require_once( dirname(__FILE__).'/../../php/cache.php');
require_once( dirname(__FILE__).'/../../php/util.php');
require_once( dirname(__FILE__).'/../../php/settings.php');
eval(getPluginConf('discordpush'));

class Discord {

    public $hash = "discord.dat";
    public $log = array
    (
        "discord_enabled"=>0,
        "discord_addition"=>0,
        "discord_finish"=>0,
        "discord_deletion"=>0,
        "discord_webhook"=>'',
        "discord_avatar"=>'',
        "discord_pushuser"=>'',
    );

    public function store()
    {
        $cache = new rCache();
        return($cache->set($this));
    }
    public function set()
    {
        if(!isset($HTTP_RAW_POST_DATA))
            $HTTP_RAW_POST_DATA = file_get_contents("php://input");
        if(isset($HTTP_RAW_POST_DATA))
        {
            $vars = explode('&', $HTTP_RAW_POST_DATA);
            foreach($vars as $var)
            {
                $parts = explode("=",$var);
                $this->log[$parts[0]] = in_array($parts[0], array('discord_webhook', 'discord_avatar', 'discord_pushuser')) ? $parts[1] : intval($parts[1]);
            }
            $this->store();
            $this->setHandlers();
        }
    }
    public function get()
    {
        if (function_exists("safe_json_encode")) {
            return("theWebUI.discord = ".safe_json_encode($this->log).";");
        } else {
            // We dont really need safe_json_encode here since we dont store any values other than numeric and hash values, but sometimes this throws an error
            return("theWebUI.discord = ".json_encode($this->log).";");
        }
    }

    public function setHandlers()
    {
        global $rootPath;
        if($this->log["discord_enabled"] && $this->log["discord_addition"])
        {
            $addCmd = getCmd('execute').'={'.getPHP().','.$rootPath.'/plugins/discordpush/push.php'.',1,$'.
                getCmd('d.get_name').'=,$'.getCmd('d.get_size_bytes').'=,$'.getCmd('d.get_bytes_done').'=,$'.
                getCmd('d.get_up_total').'=,$'.getCmd('d.get_ratio').'=,$'.getCmd('d.get_creation_date').'=,$'.
                getCmd('d.get_custom').'=addtime,$'.getCmd('d.get_custom').'=seedingtime'.
                ',"$'.getCmd('t.multicall').'=$'.getCmd('d.get_hash').'=,'.getCmd('t.get_url').'=,'.getCmd('cat').'=#",$'.
                getCmd('d.get_custom1')."=,$".getCmd('d.get_custom')."=x-pushbullet,".
                getUser().'}';
        }
        else
            $addCmd = getCmd('cat=');
        if($this->log["discord_enabled"] && $this->log["discord_finish"])
            $finCmd = getCmd('execute').'={'.getPHP().','.$rootPath.'/plugins/discordpush/push.php'.',2,$'.
                getCmd('d.get_name').'=,$'.getCmd('d.get_size_bytes').'=,$'.getCmd('d.get_bytes_done').'=,$'.
                getCmd('d.get_up_total').'=,$'.getCmd('d.get_ratio').'=,$'.getCmd('d.get_creation_date').'=,$'.
                getCmd('d.get_custom').'=addtime,$'.getCmd('d.get_custom').'=seedingtime'.
                ',"$'.getCmd('t.multicall').'=$'.getCmd('d.get_hash').'=,'.getCmd('t.get_url').'=,'.getCmd('cat').'=#",$'.
                getCmd('d.get_custom1')."=,$".getCmd('d.get_custom')."=x-pushbullet,".
                getUser().'}';
        else
            $finCmd = getCmd('cat=');
        if($this->log["discord_enabled"] && $this->log["discord_deletion"])
            $delCmd = getCmd('execute').'={'.getPHP().','.$rootPath.'/plugins/discordpush/push.php'.',3,$'.
                getCmd('d.get_name').'=,$'.getCmd('d.get_size_bytes').'=,$'.getCmd('d.get_bytes_done').'=,$'.
                getCmd('d.get_up_total').'=,$'.getCmd('d.get_ratio').'=,$'.getCmd('d.get_creation_date').'=,$'.
                getCmd('d.get_custom').'=addtime,$'.getCmd('d.get_custom').'=seedingtime'.
                ',"$'.getCmd('t.multicall').'=$'.getCmd('d.get_hash').'=,'.getCmd('t.get_url').'=,'.getCmd('cat').'=#",$'.
                getCmd('d.get_custom1')."=,$".getCmd('d.get_custom')."=x-pushbullet,".
                getUser().'}';
        else
            $delCmd = getCmd('cat=');
        $req = new rXMLRPCRequest( array(
            rTorrentSettings::get()->getOnInsertCommand( array('tdiscord'.getUser(), $addCmd ) ),
            rTorrentSettings::get()->getOnFinishedCommand( array('tdiscord'.getUser(), $finCmd ) ),
            rTorrentSettings::get()->getOnEraseCommand( array('tdiscord'.getUser(), $delCmd ) ),
        ));
        $res = $req->success();
        return($res);
    }

    static public function load()
    {
        $cache = new rCache();
        $ar = new Discord();
        if($cache->get($ar))
        {
            if(!array_key_exists("discord_enabled",$ar->log))
            {
                $ar->log["discord_enabled"] = 0;
                $ar->log["discord_addition"] = 0;
                $ar->log["discord_finish"] = 0;
                $ar->log["discord_deletion"] = 0;
                $ar->log["discord_webhook"] = '';
                $ar->log["discord_avatar"] = '';
                $ar->log["discord_pushuser"] = '';
            }
        }
        return($ar);
    }

    public function pushNotify($data)
    {
        global $discordNotifications;
        $actions = array
        (
            1 => 'addition',
            2 => 'finish',
            3 => 'deletion',
        );
        $section = $discordNotifications[$actions[$data['action']]];
        $fields = array
        (
            '{name}', '{label}', '{size}', '{downloaded}', '{uploaded}', '{ratio}',
            '{creation}', '{added}', '{finished}', '{tracker}',
        );
        $values = array
        (
            $data['name'],
            $data['label'],
            self::bytes($data['size']),
            self::bytes($data['downloaded']),
            self::bytes($data['uploaded']),
            $data['ratio'],
            strftime('%c',$data['creation']),
            strftime('%c',$data['added']),
            strftime('%c',$data['finished']),
            $data['tracker'],
        );
        $body = str_replace( $fields, $values, $section );
        $payload = array("content" => $body);
        if (!empty($this->log['discord_avatar']))
            $payload["avatar_url"] = $this->log["discord_avatar"];
        if (!empty($this->log['discord_pushuser']))
            $payload["username"] = $this->log["discord_pushuser"];

        $ch = curl_init($this->log['discord_webhook']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
    }

    static protected function bytes( $bt )
    {
        $a = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $ndx = 0;
        if($bt == 0)
            $ndx = 1;
        else
        {
            if($bt < 1024)
            {
                $bt = $bt / 1024;
                $ndx = 1;
            }
            else
            {
                while($bt >= 1024)
                {
                    $bt = $bt / 1024;
                    $ndx++;
                }
            }
        }
        return((floor($bt*10)/10)." ".$a[$ndx]);
    }

}