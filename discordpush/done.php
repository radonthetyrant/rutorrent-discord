<?php

$req = new rXMLRPCRequest(array(
    rTorrentSettings::get()->getOnInsertCommand(array('tdiscord'.getUser(), getCmd('cat='))),
    rTorrentSettings::get()->getOnFinishedCommand(array('tdiscord'.getUser(), getCmd('cat='))),
    rTorrentSettings::get()->getOnEraseCommand(array('tdiscord'.getUser(), getCmd('cat=')))
));
$req->run();
