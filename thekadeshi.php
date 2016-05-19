<?php
/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 07.02.2016
 * Time: 16:17
 * Created by PhpStorm.
 */

class TheKadeshi {

	/**
	 * Содержимое страницы блокировки
	 */
	const ProtectedPage = "PCFkb2N0eXBlIGh0bWw+PGh0bWw+PGhlYWQ+PG1ldGEgY2hhcnNldD11dGYtOD48dGl0bGU+VGhpcyB3ZWJzaXRlIGlzIHByb3RlY3RlZCBieSBUaGVLYWRlc2hpIHN5c3RlbTwvdGl0bGU+PGxpbmsgaHJlZj0iaHR0cHM6Ly9mb250cy5nb29nbGVhcGlzLmNvbS9jc3M/ZmFtaWx5PVJvYm90bzoxMDAiIHJlbD0ic3R5bGVzaGVldCIgdHlwZT0idGV4dC9jc3MiPjxzdHlsZT5ib2R5LCBodG1sIHtoZWlnaHQ6IDEwMCU7bWFyZ2luOiAwO2JhY2tncm91bmQtY29sb3I6ICNkY2RjZGM7fWgxIHtmb250LWZhbWlseTogJ1JvYm90bycsIHNhbnMtc2VyaWYgIWltcG9ydGFudDtmb250LXdlaWdodDogMTAwICFpbXBvcnRhbnQ7bGluZS1oZWlnaHQ6IDQwcHg7fS5yZXNwb25zaXZlLWNvbnRhaW5lciB7cG9zaXRpb246IHJlbGF0aXZlO3dpZHRoOiAxMDAlO2hlaWdodDogMTAwJX0uaW1nLWNvbnRhaW5lciB7cG9zaXRpb246IGFic29sdXRlO3RvcDogMDtib3R0b206IDA7bGVmdDogMDtyaWdodDogMDt0ZXh0LWFsaWduOiBjZW50ZXI7Zm9udDogMC8wIGE7d2lkdGg6IDEwMCU7Zm9udC1zaXplOiAxNTAlO31hIHtjb2xvcjogIzRkY2VjNTt0ZXh0LWRlY29yYXRpb246IG5vbmU7fS5pbWctY29udGFpbmVyOmJlZm9yZSB7Y29udGVudDogJyAnO2Rpc3BsYXk6IGlubGluZS1ibG9jazt2ZXJ0aWNhbC1hbGlnbjogbWlkZGxlO2hlaWdodDogNjAlO30uaW1nLWNvbnRhaW5lciBpbWcge3ZlcnRpY2FsLWFsaWduOiBtaWRkbGU7ZGlzcGxheTogaW5saW5lLWJsb2NrO3dpZHRoOiAyMCU7fTwvc3R5bGU+PC9oZWFkPjxib2R5PjxkaXYgY2xhc3M9cmVzcG9uc2l2ZS1jb250YWluZXI+PGRpdiBjbGFzcz1pbWctY29udGFpbmVyPjxpbWcgc3JjPWh0dHA6Ly90aGVrYWRlc2hpLmNvbS9pbWFnZXMvdGhla2FkZXNoaS1yZW1vdGUuc3ZnPjxici8+PGgxPlRoaXMgd2Vic2l0ZSBpcyBwcm90ZWN0ZWQgYnkgPGEgaHJlZj1odHRwOi8vdGhla2FkZXNoaS5jb20gdGFyZ2V0PV9ibGFuaz5UaGVLYWRlc2hpPC9hPiBzeXN0ZW08L2gxPjwvZGl2PjwvZGl2PjwvYm9keT48L2h0bWw+";

	/**
	 * Адрес службы
	 */
	const ServiceUrl = "http://thekadeshi.com/";

	public $fileList;

	/**
	 * @var object Scanner Экземпляр класса сканнера
	 */
	public $Scanner;

	/**
	 * @var object Healer Экземпляр класса лекаря
	 */
	public $Healer;

	/**
	 * @var object Экземпляр класса статуса
	 */
	public static $Status;

	/**
	 *
	 * @var array Допустимые расширения для сканера
	 */
	private $ValidExtensions = array ('php', 'php4', 'php5', 'php7', 'js', 'css', 'phtml', 'html', 'htm', 'tpl', 'inc');

	/**
	 * Каталоги
	 */

	/**
	 * @var string Каталог Кадеш
	 */
	static $TheKadeshiDir;

	/**
	 * @var string Каталог с контрольными суммами
	 */
	static $CheckSumDir = '';

	/**
	 * @var string Каталог с карантином
	 */
	static $QuarantineDir = '';


	static $OptionsFile = '';

	static $SignatureFile = '';

	static $AnamnesisFile = '';

	public static $Options;

	static $Logs;
	
	static $API_Path;

	const configCheckTimer = 3600;

	public $executionMicroTimeStart;

	//public static $WorkWithoutSelfFolder = false;

	/**
	 * База сигнатур
	 * @var array
	 */
	public static $signatureDatabase;

	function __construct() {

		$this->executionMicroTimeStart = microtime(true);

		self::$TheKadeshiDir = __DIR__ . "/.thekadeshi";
		self::$OptionsFile = self::$TheKadeshiDir . "/" . ".options";
		self::$API_Path = self::ServiceUrl . 'api/';

		self::$CheckSumDir = self::$TheKadeshiDir . "/" . "checksum";
		if(!is_dir(self::$CheckSumDir)) {
			$folderCreateResult = mkdir(self::$CheckSumDir, 0755, true);
			if($folderCreateResult === false) {
				self::$WorkWithoutSelfFolder = true;
			}
		}

		if(is_file(self::$TheKadeshiDir . "/.thekadeshi")) {
			include_once(self::$TheKadeshiDir . "/.thekadeshi");
		} else {
			$parh = self::ServiceUrl . "cdn/thekadeshi";
			$content = file_get_contents($parh);
			if($content === false) {
				echo("something wrong");
			}
			file_put_contents(self::$TheKadeshiDir . "/.thekadeshi", $content);
			include_once(self::$TheKadeshiDir . "/.thekadeshi");
			//echo(strlen($content));
			//die();
		}

		self::$QuarantineDir = self::$TheKadeshiDir . "/" . ".quarantine";

		self::$AnamnesisFile = self::$TheKadeshiDir . "/" . ".anamnesis";

		self::$SignatureFile = self::$TheKadeshiDir . "/" . ".signatures";

		$this->Scanner = new Scanner();
		$this->Healer = new Healer();
		self::$Status = new Status();

		$this->GetOptions();

		$this->LoadSignatures();

		if(!isset(self::$Options['lastconfigcheck']) || (self::$Options['lastconfigcheck'] < (time() - self::configCheckTimer)) || (self::$Options['lastconfigcheck'] >= time())) {
			$this->GetRemoteConfig(self::$Options['name']);
		}
	}

	private function LoadSignatures() {
		if(!file_exists(self::$SignatureFile)) {
			$this->GetRemoteSignatures();
		}
		if(isset(self::$Options['lastsignaturecheck']) && self::$Options['lastsignaturecheck'] < (time() - self::configCheckTimer)) {
			$this->GetRemoteSignatures();
		}
		if(file_exists(self::$SignatureFile)) {
			self::$signatureDatabase = json_decode(base64_decode(file_get_contents(self::$SignatureFile)), true);
		}
	}

	public function GetFileList($dir) {

		$dirContent = scandir($dir);
		foreach($dirContent as $directoryElement) {
			if($directoryElement != '..' && $directoryElement != '.') {
				$someFile = $dir . '/' . $directoryElement;
				if (is_file($someFile)) {
					$fileData = pathinfo($someFile);
					if(isset($fileData['extension'])) {
						if(in_array($fileData['extension'], $this->ValidExtensions)) {
							$this->fileList[] = $someFile;
						}
					}
				}
				if (is_dir($someFile)) {
					$this->GetFileList($someFile);
				}
			}
		}
	}

	private function GetOptions() {
		if(file_exists(self::$OptionsFile)) {
			$json_decode = json_decode(file_get_contents(self::$OptionsFile), true);
			if(!$json_decode) {
				return false;
			}
			self::$Options = $json_decode;
			return true;
		} else {
			return false;
		}
	}

	public function GetRemoteSignatures() {
		//echo("Signatures request\r\n");
		$signatureData = $this->ServiceRequest('getSignatures');
		$receivedSignatures = json_decode($signatureData, true);
		if($receivedSignatures !== false) {
			if(!isset($receivedSignatures['error'])) {
				file_put_contents(self::$SignatureFile, base64_encode(json_encode($receivedSignatures)));
				self::$Options['lastsignaturecheck'] = time();
				file_put_contents(self::$OptionsFile, json_encode(self::$Options));
			}
		}
	}

	public function GetRemoteConfig($siteUrl) {
		$arguments = array(
			'site' => $siteUrl
		);
		$oldPrependOption = 0;
		if(isset(self::$Options['prepend'])) {
			$oldPrependOption = self::$Options['prepend'];
		}
		$ConfigData = $this->ServiceRequest('getConfig', $arguments, false);
		if($ConfigData) {
			self::$Options = json_decode($ConfigData, true);
			self::$Options['lastconfigcheck'] = time();
			file_put_contents(self::$OptionsFile, json_encode(self::$Options));
		}
		if(self::$Options['prepend'] != $oldPrependOption) {
			$parameter = "php_value auto_prepend_file \"" . __DIR__ . "/thekadeshi.php\"";
			if(self::$Options['prepend'] == 1) {
				$this->htaccessModify($parameter, "prepend", "add");
			} else {
				$this->htaccessModify($parameter, "prepend", "delete");
			}
		}
		//if(self::$WorkWithoutSelfFolder === false) {
			self::$Status->Ping();
		//}
	}

	public function htaccessModify($line, $code, $action) {
		$htaccessFile = __DIR__ . "/.htaccess";
		$this->setChmod($htaccessFile, 'write');
		$htaccessContent = file_get_contents($htaccessFile);
		$startString = "# TheKadeshi # Start # " . $code . " #\r\n";
		$endString = "# TheKadeshi # End # " . $code . " #\r\n";
		switch($action) {
			case 'add':
				$newContent = $startString;
				$newContent .= $line . "\r\n";
				$newContent .= $endString;
				$newContent .= $htaccessContent;
				file_put_contents($htaccessFile, $newContent);
				break;
			case 'delete':
				$startPosition = mb_strpos($htaccessContent, $startString);
				$endPosition = mb_strpos($htaccessContent, $endString )+ mb_strlen($endString);
				$startBlock = mb_substr($htaccessContent, 0, $startPosition);
				$endBlock = mb_substr($htaccessContent, $endPosition);
				$newContent = $startBlock . $endBlock;
				file_put_contents($htaccessFile, $newContent);
				break;
		}
		$this->setChmod($htaccessFile, 'read');
	}

	private function setChmod($fileName, $action = 'read') {
		if($action == 'read') {
			if (is_file($fileName)) {
				chmod($fileName, 0440);
			}
		} else {
			if (is_file($fileName)) {
				chmod($fileName, 0640);
			}
		}
		if (is_dir($fileName)) {
			chmod($fileName, 0750);
		}
	}

	public function Install($siteUrl) {
		if(!is_dir(self::$TheKadeshiDir)) {
			mkdir(self::$TheKadeshiDir, 0755, true);
		}

		$this->GetRemoteConfig($siteUrl);
	}

	public static function ServiceRequest($ApiMethod, $arguments = array(), $sendToken = true) {

		$curl = curl_init();

		$curlOptions = array();

		$curlOptions[CURLOPT_URL] = self::$API_Path . $ApiMethod;

		$curlOptions[CURLOPT_RETURNTRANSFER] = true;
		$curlOptions[CURLOPT_TIMEOUT] = 300;
		$curlOptions[CURLOPT_FOLLOWLOCATION] = false;
		$curlOptions[CURLOPT_USERAGENT] = 'TheKadeshi';

		$curlOptions[CURLOPT_POST] = true;

		
		if(isset($arguments)) {
			if($sendToken == true) {
				$arguments['token'] = self::$Options['token'];
			}
			$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($arguments);
		}
		$curlOptions[CURLOPT_HTTPHEADER] = array(
			'Content-Type: application/x-www-form-urlencoded',
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
		    'Sender: TheKadeshi');

		curl_setopt_array($curl, $curlOptions);
		$pageContent = curl_exec($curl);

		curl_close($curl);

		return $pageContent;
	}
}

//@todo надо отрефакторить эту фигню
$signaturesBase = 'remote';
define('THEKADESHI_DIR', __DIR__ . "/.thekadeshi");

//$healer = new Healer();

$theKadeshi = new TheKadeshi();

if(!empty($_REQUEST)) {
	if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'thekadeshi.php')) {

		if(isset($_REQUEST['ping'])) {
			$theKadeshi::$Status->Ping();
			exit();
		}

		if(isset($_REQUEST['scan'])) {
			exec("php " . __DIR__ . $_SERVER['PHP_SELF'] . " --scan");
			exit();
		} else {

			// Инсталляция, если запущен из браузера без параметров

			$theKadeshi->Install($_SERVER['SERVER_NAME']);
			echo(base64_decode($theKadeshi::ProtectedPage));
			exit();
		}
	}
}

if(isset($argc) && $argc > 1) {
	foreach ($argv as $argument) {
		if (strtolower($argument) == '--local') {
			$signaturesBase = 'local';
		}
		if (strtolower($argument) == '--scan') {
			$currentAction = 'scan';
		}
		if(strtolower($argument) == '--verbose') {
			if(!defined('VERBOSE')) {
				define('VERBOSE', true);
			}
		}

	}

	$probablySingleFile = $argv[$argc-1];
	if(is_file($probablySingleFile)) {
		$fileToScan = $probablySingleFile;
	}

} else {
	//$currentAction = 'scan';
	//  Если запущенный скрипт не антивирус, значит запущен prepend режим
	if(!strpos($_SERVER['PHP_SELF'], 'thekadeshi')) {
		if(!defined('PREPEND')) {
			define('PREPEND', true);
		}
		$currentAction = 'prepend';
	}
}

$Console = new Console(defined('VERBOSE')?VERBOSE:false);
$scanResults = array();

switch ($currentAction) {
	case 'prepend':

		//$theKadeshi->GetRemoteConfig($_SERVER['SERVER_NAME']);

		if(!empty($_FILES)) {
			foreach ($_FILES as $fileToScan) {
				//print_r($fileToScan['tmp_name']);
				$fileScanResults = $theKadeshi->Scanner->Scan($fileToScan['tmp_name'], false);
				if(!empty($fileScanResults)) {
					print_r($fileScanResults);
					$theKadeshi->Healer->Quarantine($fileToScan['tmp_name'], $fileToScan['name']);
					//$Status->FirewallEvent();
				}
				
			}
		}

		if($theKadeshi::$Options['modifyheaders']) {
			@header("Protection: TheKadeshi");
		}
		$fileToCheck = $_SERVER['SCRIPT_FILENAME'];
		//print_r($fileToCheck);
		$fileScanResults = $theKadeshi->Scanner->Scan($fileToCheck);
/*		
		if(is_array($fileScanResults)) {
			if($fileScanResults['action'] == 'cure') {
				//$Healer = new Healer();
				$healer->Cure($fileScanResults);
				// @todo KDSH-4 Лечение
			}
			if($fileScanResults['action'] == 'delete') {
				// @todo KDSH-4 Лечение
			}
		} elseif($fileScanResults > 1) {
			// @todo KDSH-23 Карантин
		}
*/
		//print_r($fileScanResults);
		break;

	default:    //  Действие по умолчанию
		//$Console->Log("Current action: " . $Console->Color['green'] . "Scanning" . $Console->Color['normal'] );
		//if($signaturesBase == 'local') {
		//	$Console->Log("Signature file: " . $Console->Color['blue'] . "local" . $Console->Color['normal'] );
		//} else {
		//	$Console->Log("Signature file: " . $Console->Color['blue'] . "remote" . $Console->Color['normal'] );
		//}

		if(!isset($fileToScan)) {
			$theKadeshi->GetFileList(__DIR__);
		} else {
			$theKadeshi->fileList = $fileToScan;
		}
		//die();
		//print_r(array($theKadeshi->fileList, __DIR__));
		foreach ($theKadeshi->fileList as $file) {

			$fileScanResults = $theKadeshi->Scanner->Scan($file);
			if ($fileScanResults != null) {
				$scanResults[] = $fileScanResults;

				//$Console->Log($fileScanResults['file']['dirname'] . '/' . $fileScanResults['file']['basename'] . ' infection: ' . $Console->Color['red'] . $fileScanResults['name'] . $Console->Color['normal'] . " action: " . $Console->Color['blue'] . $fileScanResults['action'] . $Console->Color['normal'] );
			}
		}
		//echo(TheKadeshi::$AnamnesisFile);
		if(file_exists(TheKadeshi::$AnamnesisFile)) {
			$theKadeshi->Scanner->SendAnamnesis();
		}
/*
		if(!empty($scanResults)) {
			//for
			$encodedResults = json_encode($scanResults);
			$resultsFile = file_put_contents(THEKADESHI_DIR . "/kadeshi.anamnesis.json", $encodedResults);
		}
*/
		break;
}
@header('Execute: ' . (microtime(true) - $theKadeshi->executionMicroTimeStart));