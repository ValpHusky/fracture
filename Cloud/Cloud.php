<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cloud
 *
 * @author HuskyLair
 */
require_once __DIR__.'/Dropbox/autoload.php';


use \Dropbox as dbx;


abstract class Cloud {
   
    protected $token = null;
    protected $userid = null;
    protected $currentFolder = "/";
    
    public function __construct($userid,$token) {
        $this->token = $token;
        $this->userid = $userid;
    }
    
    public function setFolder($folder){
        $this->currentFolder = $folder;
    }
    
}
