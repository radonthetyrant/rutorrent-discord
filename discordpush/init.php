<?php

require_once( $rootPath.'/plugins/discordpush/discord.php' );

$discord = Discord::load();
if($discord->setHandlers())
{
    $theSettings->registerPlugin($plugin["name"],$pInfo["perms"]);
    $jResult .= $discord->get();
}
else
    $jResult .= "plugin.disable(); noty('discord: Failed to start the plugin','error');";
