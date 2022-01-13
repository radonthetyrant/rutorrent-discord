<?php

require_once( dirname(__FILE__).'/../../php/cache.php');
require_once( dirname(__FILE__).'/../../php/util.php');
require_once( dirname(__FILE__).'/../../php/settings.php');
eval(FileUtil::getPluginConf('discordpush'));

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
        if (method_exists('JSON', 'safeEncode')) {
            return("theWebUI.discord = ".JSON::safeEncode($this->log).";");
        } else {
            // We dont really need JSON::safeEncode here since we dont store any values other than numeric and hash values, but sometimes this throws an error
            return("theWebUI.discord = ".json_encode($this->log).";");
        }
    }

    public function setHandlers()
    {
        global $rootPath;
        if($this->log["discord_enabled"] && $this->log["discord_addition"])
        {
            $addCmd = getCmd('execute').'={'.Utility::getPHP().','.$rootPath.'/plugins/discordpush/push.php'.',1,$'.
                getCmd('d.get_name').'=,$'.getCmd('d.get_size_bytes').'=,$'.getCmd('d.get_bytes_done').'=,$'.
                getCmd('d.get_up_total').'=,$'.getCmd('d.get_ratio').'=,$'.getCmd('d.get_creation_date').'=,$'.
                getCmd('d.get_custom').'=addtime,$'.getCmd('d.get_custom').'=seedingtime'.
                ',"$'.getCmd('t.multicall').'=$'.getCmd('d.get_hash').'=,'.getCmd('t.get_url').'=,'.getCmd('cat').'=#",$'.
                getCmd('d.get_custom1')."=,$".getCmd('d.get_custom')."=x-pushbullet,".
                User::getUser().'}';
        }
        else
            $addCmd = getCmd('cat=');
        if($this->log["discord_enabled"] && $this->log["discord_finish"])
            $finCmd = getCmd('execute').'={'.Utility::getPHP().','.$rootPath.'/plugins/discordpush/push.php'.',2,$'.
                getCmd('d.get_name').'=,$'.getCmd('d.get_size_bytes').'=,$'.getCmd('d.get_bytes_done').'=,$'.
                getCmd('d.get_up_total').'=,$'.getCmd('d.get_ratio').'=,$'.getCmd('d.get_creation_date').'=,$'.
                getCmd('d.get_custom').'=addtime,$'.getCmd('d.get_custom').'=seedingtime'.
                ',"$'.getCmd('t.multicall').'=$'.getCmd('d.get_hash').'=,'.getCmd('t.get_url').'=,'.getCmd('cat').'=#",$'.
                getCmd('d.get_custom1')."=,$".getCmd('d.get_custom')."=x-pushbullet,".
                User::getUser().'}';
        else
            $finCmd = getCmd('cat=');
        if($this->log["discord_enabled"] && $this->log["discord_deletion"])
            $delCmd = getCmd('execute').'={'.Utility::getPHP().','.$rootPath.'/plugins/discordpush/push.php'.',3,$'.
                getCmd('d.get_name').'=,$'.getCmd('d.get_size_bytes').'=,$'.getCmd('d.get_bytes_done').'=,$'.
                getCmd('d.get_up_total').'=,$'.getCmd('d.get_ratio').'=,$'.getCmd('d.get_creation_date').'=,$'.
                getCmd('d.get_custom').'=addtime,$'.getCmd('d.get_custom').'=seedingtime'.
                ',"$'.getCmd('t.multicall').'=$'.getCmd('d.get_hash').'=,'.getCmd('t.get_url').'=,'.getCmd('cat').'=#",$'.
                getCmd('d.get_custom1')."=,$".getCmd('d.get_custom')."=x-pushbullet,".
                User::getUser().'}';
        else
            $delCmd = getCmd('cat=');
        $req = new rXMLRPCRequest( array(
            rTorrentSettings::get()->getOnInsertCommand( array('tdiscord'.User::getUser(), $addCmd ) ),
            rTorrentSettings::get()->getOnFinishedCommand( array('tdiscord'.User::getUser(), $finCmd ) ),
            rTorrentSettings::get()->getOnEraseCommand( array('tdiscord'.User::getUser(), $delCmd ) ),
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
            1 => 'Added',
            2 => 'Finished',
            3 => 'Deleted',
        );
        //$section = $discordNotifications[$actions[$data['action']]];
        $fields = array();

        switch ($data['action']) {
            case 1:
                $fields[] = array("name" => "Name", "value" => $data['name']);
                if (!empty($data['label'])) $fields[] = array("name" => "Label", "value" => $data['label']);
                $fields[] = array("name" => "Size", "value" => self::bytes(round($data['size'],2)));
                //$fields[] = array("name" => "Added", "value" => strftime('%c',$data['added']));
                $fields[] = array("name" => "Tracker", "value" => parse_url($data['tracker'], PHP_URL_HOST));
                $color = 4886754;
                break;
            case 2:
                $fields[] = array("name" => "Name", "value" => $data['name']);
                if (!empty($data['label'])) $fields[] = array("name" => "Label", "value" => $data['label']);
                $fields[] = array("name" => "Size", "value" => self::bytes(round($data['size'],2)));
                //$fields[] = array("name" => "Downloaded", "value" => self::bytes(round($data['downloaded'],2)));
                //$fields[] = array("name" => "Uploaded", "value" => self::bytes(round($data['uploaded'],2)));
                //$fields[] = array("name" => "Ratio", "value" => $data['ratio']);
                //$fields[] = array("name" => "Added", "value" => strftime('%c',$data['added']));
                //$fields[] = array("name" => "Finished", "value" => strftime('%c',$data['finished']));
                $fields[] = array("name" => "Tracker", "value" => parse_url($data['tracker'], PHP_URL_HOST));
                $color = 8311585;
                break;
            case 3:
                $fields[] = array("name" => "Name", "value" => $data['name']);
                if (!empty($data['label'])) $fields[] = array("name" => "Label", "value" => $data['label']);
                $fields[] = array("name" => "Size", "value" => self::bytes(round($data['size'],2)));
                //$fields[] = array("name" => "Downloaded", "value" => self::bytes(round($data['downloaded'],2)));
                //$fields[] = array("name" => "Uploaded", "value" => self::bytes(round($data['uploaded'],2)));
                //$fields[] = array("name" => "Ratio", "value" => $data['ratio']);
                //$fields[] = array("name" => "Creation", "value" => strftime('%c',$data['creation']));
                //$fields[] = array("name" => "Added", "value" => strftime('%c',$data['added']));
                //$fields[] = array("name" => "Finished", "value" => strftime('%c',$data['finished']));
                $fields[] = array("name" => "Tracker", "value" => parse_url($data['tracker'], PHP_URL_HOST));
                $color = 10562619;
                break;
        }

        $avatarUrl = !empty($this->log['discord_avatar']) ? $this->log['discord_avatar'] : null;
        $botUsername = !empty($this->log['discord_pushuser']) ? $this->log['discord_pushuser'] : null;

        $payload = json_encode(array(
            "content" => "",
            'avatar_url' => $avatarUrl,
            "username" => $botUsername,
            "embeds" => array(
                array(
                    "title" => "Torrent ".$actions[$data['action']].": ".$data['name'],
                    "color" => $color,
                    "timestamp" => date('Y-m-d\TH:i:s.u'),
                    "thumbnail" => array(
                        "url" => $avatarUrl
                    ),
                    "author" => array(
                        "name" => "rutorrent-discord"
                    ),
                    "fields" => $fields
                )
            )
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);


        $ch = curl_init($this->log['discord_webhook']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Length: '.strlen($payload)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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
