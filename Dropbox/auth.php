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
require_once 'autoload.php';

use \Dropbox as dbx;

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ADBX_AUTHCODE', "dbxauth");
define('ADBX_ACTION', "dbxdo");
define('ADBX_ACT_SHOW', "show");
define('ADBX_ACT_GET', "get");

function SHOW_DROPBOX() {
    $appInfo = dbx\AppInfo::loadFromJsonFile(__DIR__ . "/config.json");
    $webAuth = new dbx\WebAuthNoRedirect($appInfo, "Fracture/1.0");
    echo $webAuth->start();
}

function GET_DROPBOX() {
    $appInfo = dbx\AppInfo::loadFromJsonFile(__DIR__ . "/config.json");
    $webAuth = new dbx\WebAuthNoRedirect($appInfo, "Fracture/1.0");

    $auth = filter_input(INPUT_GET, ADBX_AUTHCODE);

    list($accessToken, $user) = $webAuth->finish($auth);

    echo $accessToken;
}

$action = filter_input(INPUT_GET, ADBX_ACTION);
switch($action){
    case ADBX_ACT_GET:
        GET_DROPBOX();
        break;
    case ADBX_ACT_SHOW:
        SHOW_DROPBOX();
        break;
}