<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Fracture
 *
 * @author HuskyLair
 */
class Fracture {

    const TIME_MONTHLY = 1;
    const TIME_BIMESTRAL = 2;
    const TIME_QUARTER = 3;
    const TEMPORAL_WRITE = "temp";
    const SPLIT = "-";
    const QUARTER = "Cuarto";
    const EXT_XML = "xml";
    const EXT_PDF = "pdf";
    const EXT_ZIP = "zip";
    const FRACTURE_XML = "application/xml";
    const FRACTURE_TXML = "text/xml";
    const FRACTURE_PDF = "application/pdf";
    const FRACTURE_ZIP = "application/zip";
    const FRACTURE_OCTET = "application/octet-stream";

    private static $_quarters = Array("I", "II", "III", "IV");
    private static $_months = Array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

    //put your code here
    public static function determineFolder($timestamp, $timegroup) {
        $m = ((int) date("m", $timestamp)) - 1;
        $folder = "/";
        switch ($timegroup) {
            case 1:
                $folder .= self::$_months[$m];
                break;
            case 2:
                if ($m % 2 == 0) {
                    $folder .= self::$_months[$m] . self::SPLIT . self::$_months[$m + 1];
                } else {
                    $folder .= self::$_months[$m - 1] . self::SPLIT . self::$_months[$m];
                }
                break;
            case 3:
                $q = $m / 3;
                $folder .= self::$_quarters[$q];
                break;
        }
        return $folder;
    }

    public static function dataAsStream($data, $mime) {
        return fopen('data://' . $mime . ';base64,' . base64_encode($data), 'r');
    }

    /**
     * Looks for the file indicated by the mime
     * @param Fetch\Message $mes
     * @param String $mime
     */
    public static function scavenge($mes) {
        $at = $mes->getAttachments();
        $files = null;
        if ($at) {
            $files = self::_lookinattachlist($mes);
        } else {
            $files = self::_lookinbody($mes);
        }
        return $files;
    }

    public static function datafile($files, $ext) {
        if (isset($files[$ext])) {
            return $files[$ext];
        }
    }

    /**
     * Names the file indicated by the mime and message
     * @param Fetch\Message $mes
     * @param String $mime
     */
    public static function namefile($mes, $mime) {
        $rem = self::remitent($mes);
        $date = date('YmHis', $mes->getDate());
        $ext = self::extension($mime);
        $ran = rand(0, 1000);

        return "/" . $date . "_" . $ran . "_" . $rem . "." . $ext;
    }

    public static function extension($mime) {
        $rmime = $mime;
        if (strpos($mime, ";")) {
            $ex = explode(";", $mime);
            $rmime = $ex[0];
        }


        switch ($rmime) {
            case self::FRACTURE_PDF:
                return "pdf";
            case self::FRACTURE_XML:
            case self::FRACTURE_TXML:
                return "xml";
            default:
                return "data";
        }
    }

    /**
     * Gets remitent domain from message from header.
     * @param Fetch\Message $mes
     */
    public static function remitent($mes) {
        $regex = '/@((([^.]+)\.)+)([a-zA-Z]{3,}|[a-zA-Z.]{5,})/';
        $rem = $mes->getAddresses('from');
        $name = str_replace("@", "_", $rem['address']);
        return str_replace(".", "_", $name);
    }

    public static function unzip($data) {
        $zip = new ZipArchive();
        $now = time() . rand(1, 1000);
        $tempdir = __DIR__ . "/" . self::TEMPORAL_WRITE . "/" . $now;

        if (!file_exists($tempdir)) {
            mkdir($tempdir, 0777, true);
        }

        $file = __DIR__ . "/" . self::TEMPORAL_WRITE . "/" . $now . "/temp_unziped.zip";
        $rs = fopen($file, "w");
        stream_copy_to_stream(self::dataAsStream($data, self::FRACTURE_ZIP), $rs);
        fclose($rs);

        $is = $zip->open($file);
        if ($is) {
            $zip->extractTo($tempdir);
        }
        unlink($file);

        $files = self::readFilesFromPath($tempdir);

        return $files;
    }

    public static function cleanTemporal($dir = NULL) {
        if (is_null($dir)) {
            $dir = __DIR__ . "/" . self::TEMPORAL_WRITE;
        }
        $dit = new RecursiveDirectoryIterator($dir);
        $it = new RecursiveIteratorIterator($dit, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $file) {
            if ('.' === $file->getBasename() || '..' === $file->getBasename()){
                continue;
            }
            if ($file->isDir()){
                rmdir($file->getPathname());
            }else{
                unlink($file->getPathname());
            }
        }
        //rmdir($dir);
    }

    public static function toMIME($ext) {
        switch ($ext) {
            case self::EXT_XML:
                return self::FRACTURE_XML;
            case self::EXT_PDF:
                return self::FRACTURE_PDF;
            default:
                return self::FRACTURE_OCTET;
        }
    }

    public static function readFilesFromPath($dir) {
        $files = Array();
        $it = new RecursiveDirectoryIterator($dir);
        while ($it->valid()) {
            if ($it->isFile()) {
                $files[$it->getExtension()] = fopen($it->getPath() . "/" . $it->getFilename(), 'r');
            }
            $it->next();
        }
        return $files;
    }

    /**
     * Gets the data form message attatchments
     * @param Fetch\Message $mes
     */
    private static function _lookinattachlist($mes) {
        $alist = $mes->getAttachments();
        $files = Array();
        if ($alist) {
            foreach ($alist as $a) {
                if ($a->getMimeType() != self::FRACTURE_ZIP) {
                    $files[self::extension($a->getMimeType())] = self::dataAsStream($a->getData(), $a->getMimeType());
                } else {
                    $files = self::unzip($a->getData());
                }
            }
            return $files;
        }
    }

    /**
     * Gets the data from body's href.
     * @param Fetch\Message $mes
     */
    private static function _lookinbody($mes) {
        $files = Array();
        $body = $mes->getMessageBody(true);

        $dom = new DOMDocument;
        $dom->loadHTML($body);
        foreach ($dom->getElementsByTagName('a') as $node) {
            $item = $node->getAttribute('href');

            if (substr($item, 0, 7) != 'mailto:') {
                $ch = curl_init($item);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $res = curl_exec($ch);
                $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $files[self::extension($mime)] = self::dataAsStream($res, $mime);
            }
        }
        return $files;
    }

}
