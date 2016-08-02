<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 02.08.16
 * Time: 19:18
 */
class TheKadeshiSniffer {

    public function __construct() {

        $date = gmdate("Y/m/d/H");

        $currentLogDir = __DIR__ . $date;

        echo $currentLogDir;
    }

    public function WriteLog() {

    }
}

$sniffer = new TheKadeshiSniffer();