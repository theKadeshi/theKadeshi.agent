<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 02.08.16
 * Time: 19:18
 * SubProject: Sniffer
 */
class TheKadeshiSniffer {

	private static $currentLogDir;

	private static $snifferLogFile;

	private static $notWriteAble;

    public function __construct() {

        $date = gmdate('Y/m/d/H');

	    $currentIp = '127.0.0.1';
	    if(array_key_exists('REMOTE_ADDR', $_SERVER)) {
		    $currentIp = $_SERVER['REMOTE_ADDR'];
	    }
	    $currentMinuteMin = str_pad(floor(gmdate('i') / 10) * 10, '0', STR_PAD_LEFT);
	    $currentMinuteMax = str_pad(ceil(((gmdate('i') / 10) < 1) ? 1 : (gmdate('i') / 10)) * 10, '0', STR_PAD_LEFT);
	    $currentLogFile = gmdate('H') . '-' . $currentMinuteMin . '-' . $currentMinuteMax . '~' . $currentIp . '.log.json';

        self::$currentLogDir = __DIR__ . '/' . $date;

	    self::$snifferLogFile = self::$currentLogDir . '/' . $currentLogFile;
    }

    private function GenerateLogData() {
    	$log = null;
    	if(count($_POST) > 0) {
		    $log['post'] = $_POST;
	    }

	    if(count($_GET) > 0) {
		    $log['get'] = $_GET;
	    }

	    if(count($_COOKIE) > 0) {
		    $log['cookie'] = $_COOKIE;
	    }

	    return $log;
    }

    public function WriteLog() {
    	$data = $this->GenerateLogData();
    	if($data !== null) {

    		if(!@mkdir(self::$currentLogDir, 0755, true)  && !is_dir(self::$currentLogDir )){
                self::$notWriteAble = true;
	        }

    		if(file_exists(self::$snifferLogFile)) {
    			$currentFileContent = file_get_contents(self::$snifferLogFile);
			    if(!($currentjsonContent = json_decode($currentFileContent, true))) {
			    	$currentjsonContent = array();
			    }
		    }
		    $currentJsonContent[gmdate('Y-m-d H:i:s')][] = $data;
		    file_put_contents(self::$snifferLogFile, json_encode($currentJsonContent));
	    }
    }
}

$sniffer = new TheKadeshiSniffer();

$sniffer->WriteLog();