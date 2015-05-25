<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Fracture/Fracture.php';
require_once __DIR__ . '/mail/Server.php';
require_once __DIR__ . '/Cloud/MDropbox.php';



$drop = new MDropbox(DROPBOX_USERNAME,DROPBOX_TOKEN);
$m = new Fetch\Server(MAIL_SERVER);
$m->setAuthentication(MAIL_USERNAME, MAIL_PASSWORD);

/*
if(!$m->hasMailBox("INBOX/".MAIL_FRACTURE_NOREAD)){
    $m->createMailBox("INBOX/".MAIL_FRACTURE_NOREAD);
}
 * 
 */

$list = $m->getMessages();

foreach ($list as $mes) {
    $monthfolder = Fracture::determineFolder($mes->getDate(), FRACTURE_GROUPING);
    $drop->createFolder($monthfolder);
    $drop->setFolder($monthfolder);
    
    $files = Fracture::scavenge($mes);
    
    if($files){
        $xml = Fracture::datafile($files,Fracture::EXT_XML);
        $pdf = Fracture::datafile($files,Fracture::EXT_PDF);
        
        if($xml){
            $n = Fracture::namefile($mes,Fracture::FRACTURE_XML);
            $drop->storeFile($n, $xml);
            try{
                fclose($xml);
            }catch(Excpetion $e){}
        }
        if($pdf){
            $n = Fracture::namefile($mes,Fracture::FRACTURE_PDF);
            $drop->storeFile($n, $pdf);
            try{
                fclose($pdf);
            }catch(Excpetion $e){}
        }
        
        /*
        if(!$m->hasMailBox($monthfolder)){
            $m->createMailBox($monthfolder);
        }
        $mes->moveToMailBox($monthfolder);
         * 
         */
    }else{
        //$mes->moveToMailBox(MAIL_FRACTURE_NOREAD);
        echo "NOT ABLE TO READ->".$mes->getSubject()."<br />";
    }
    
    Fracture::cleanTemporal();
    
}


