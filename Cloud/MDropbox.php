<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'Cloud.php';

class MDropbox extends Cloud{
    /**
     * Dropbox Client from API
     * @var Dropbox\Client 
     */
    private $client = null;
    
    public function __construct($userid, $token) {
        parent::__construct($userid, $token);
        
        $this->client = new dbx\Client($this->token, "Fracture/1.0");
    }
    
    public function createFolder($folder){
        $this->client->createFolder($folder);
    }
    
    public function storeFile($name,$data){
        return $this->client->uploadFile($this->currentFolder.$name, dbx\WriteMode::add(), $data);
    }
    
}