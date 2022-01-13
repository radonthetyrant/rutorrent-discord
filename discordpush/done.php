<?php

$req = new rXMLRPCRequest(array(
    rTorrentSettings::get()->getOnInsertCommand(array('tdiscord'.User::getUser(), getCmd('cat='))),
    rTorrentSettings::get()->getOnFinishedCommand(array('tdiscord'.User::getUser(), getCmd('cat='))),
    rTorrentSettings::get()->getOnEraseCommand(array('tdiscord'.User::getUser(), getCmd('cat=')))
));
$req->run();
