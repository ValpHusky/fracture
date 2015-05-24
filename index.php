<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);



require_once __DIR__.'/mail/Server.php';
require_once __DIR__.'/Cloud/MDropbox.php';
require_once __DIR__.'config.php';


$drop = new MDropbox();
$m = new Fetch\Server(MAIL_SERVER);
$m->setAuthentication(MAIL_USERNAME, MAIL_PASSWORD);
$list = $m->getMessages();

foreach($list as $mes){
    $atlist = $mes->getAttachments();
    if($atlist){
        foreach($atlist as $at){
            $drop->createFolder($mes->getDate());
        }
            //header('Content-Type:'.$at->getMimeType());
            //echo $at->getData();
    }
}


