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
	 * @var object Экземпляр класса статуса
	 */
	public static $Status;

	/**
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

	static $OptionsFile = '';

	static $SignatureFile = '';

	static $FirewallFile = '';

	static $AnamnesisFile = '';

	static $FirewallLogFile = '';

	public static $Options;

	static $Logs;

	static $API_Path;

	const configCheckTimer = 3600;

	public $executionMicroTimeStart;

	/**
	 * База сигнатур
	 * @var array
	 */
	public static $signatureDatabase;

	/**
	 * База правил фаервола
	 * @var string
	 */
	public static $firewallRules = '';

	function __construct() {

		$this->executionMicroTimeStart = microtime(true);

		self::$TheKadeshiDir = __DIR__ . "/.thekadeshi";
		self::$OptionsFile = self::$TheKadeshiDir . "/" . ".options";
		self::$FirewallFile = self::$TheKadeshiDir . "/" . ".firewall";
		self::$FirewallLogFile = self::$TheKadeshiDir . "/" . ".firewall.log";
		self::$API_Path = self::ServiceUrl . 'api/';

		self::$CheckSumDir = self::$TheKadeshiDir . "/" . "checksum";
		if(!is_dir(self::$CheckSumDir)) {
			$folderCreateResult = mkdir(self::$CheckSumDir, 0755, true);
			if($folderCreateResult === false) {
				self::$WorkWithoutSelfFolder = true;
			}
		}

		$this->GetOptions();

		self::$AnamnesisFile = self::$TheKadeshiDir . "/" . ".anamnesis";

		self::$SignatureFile = self::$TheKadeshiDir . "/" . ".signatures";

		if(!is_file(self::$TheKadeshiDir . "/.thekadeshi")) {
			$this->Update();
		}
		if(is_file(self::$TheKadeshiDir . "/.thekadeshi")) {
			include_once(self::$TheKadeshiDir . "/.thekadeshi");

			$this->Scanner = new Scanner();

			self::$Status = new Status();

			$this->LoadSignatures();
		}

		$this->Ping();

		if(!isset(self::$Options['lastconfigcheck']) ||
			(self::$Options['lastconfigcheck'] < (time() - self::configCheckTimer)) ||
			(self::$Options['lastconfigcheck'] >= time())) {
			$this->GetRemoteConfig(self::$Options['name']);
			$this->SendFirewallLogs();
		}
	}

	/**
	 * Фунция обновления
	 * @todo необходимо добавить проверку на наличие этого самого обновления
	 */
	private function Update() {
		/*
		 * Обновление ядра
		 */
		$path = self::ServiceUrl . "cdn/thekadeshi";
		$content = file_get_contents($path);
		if($content === false) {
			echo("something wrong");
		} else {
			file_put_contents(self::$TheKadeshiDir . "/.thekadeshi", $content);
		}

		/*
		 * Обновление себя
		 */
		$path = self::ServiceUrl . "cdn/agent";
		$content = file_get_contents($path);
		if($content === false) {
			echo("something wrong");
		} else {
			file_put_contents(__DIR__ . "/thekadeshi.php", $content);
		}
	}

	/**
	 * Функция отправки отчетов фаервола
	 * @return bool
	 */
	private function SendFirewallLogs() {
		$firewallLogContent = '';
		if(file_exists(self::$FirewallLogFile)) {
			$this->setChmod(self::$FirewallLogFile, 'write');
			$firewallLogContent = file_get_contents(self::$FirewallLogFile);

		}
		if($firewallLogContent == '') {
			return false;
		}
		$sendResult = $this->ServiceRequest('sendFirewallLogs', array('data' => $firewallLogContent));
		$resultData = json_decode($sendResult, true);
		if(!empty($resultData) && $resultData['message'] == 'Ok') {
			unlink(self::$FirewallLogFile);
		}
	}

	/**
	 * Функция загрузки сигнатур
	 */
	private function LoadSignatures() {
		if(!file_exists(self::$SignatureFile)) {
			$this->GetRemoteSignatures();
		} else {
			if (isset(self::$Options['lastsignaturecheck']) && self::$Options['lastsignaturecheck'] < (time() - self::configCheckTimer)) {
				$this->GetRemoteSignatures();
			}
		}
		if(file_exists(self::$SignatureFile)) {
			self::$signatureDatabase = json_decode(base64_decode(file_get_contents(self::$SignatureFile)), true);
		}
		if(isset(self::$Options['firewall']) && self::$Options['firewall'] == true) {
			if(file_exists(self::$FirewallFile)) {
				self::$firewallRules = json_decode(base64_decode(file_get_contents(self::$FirewallFile)), true);
			}
		}
	}

	/**
	 * Функция получения списка файлов
	 * @param $dir
	 */
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

	/**
	 * Функция чтения опций их локального файла
	 * @return bool
	 */
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

	/**
	 * Функция получения удаленных сигнатур
	 */
	public function GetRemoteSignatures() {

		/*
		 * Получение массива сигнатур
		 */
		$signatureData = $this->ServiceRequest('getSignatures');
		$receivedSignatures = json_decode($signatureData, true);
		if($receivedSignatures !== false) {
			if(!isset($receivedSignatures['error'])) {
				file_put_contents(self::$SignatureFile, base64_encode(json_encode($receivedSignatures)));
				self::$Options['lastsignaturecheck'] = time();
				file_put_contents(self::$OptionsFile, json_encode(self::$Options));
			}
		}

		/*
		 * Получение массива правил для фаервола
		 */
		$firewallData = $this->ServiceRequest('getFirewallRules');

		$receivedRules = json_decode($firewallData, true);
		if($receivedRules !== false) {
			if(!isset($receivedRules['error'])) {
				file_put_contents(self::$FirewallFile, base64_encode(json_encode($receivedRules)));
			}
		}

		/*
		 * Чистка каталога с контрольными суммами, после получения нового списка сигнатур
		 */

		self::deleteContent(self::$CheckSumDir);
	}

	/**
	 * Функция рекурсивного удаления каталога
	 * @param $path
	 * @return bool
	 */
	public function deleteContent($path) {
		try {
			$iterator = new DirectoryIterator($path);
			foreach ($iterator as $fileinfo) {
				if ($fileinfo->isDot())
					continue;
				if ($fileinfo->isDir()) {
					if ($this->deleteContent($fileinfo->getPathname()))
						@rmdir($fileinfo->getPathname());
				}
				if ($fileinfo->isFile()) {
					@unlink($fileinfo->getPathname());
				}
			}
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

	/**
	 * Функция получения настроек с сервера
	 * @param $siteUrl
	 */
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
		if(self::$Options['prepend'] == 1) {
			$htaccessConfig[] = "php_value auto_prepend_file \"" . __DIR__ . "/thekadeshi.php\"";
		}

		if(self::$Options['firewall'] == 1) {
			$htaccessConfig[] = array(
				"\r\n# \t{Prevent mime based attacks",
				"Header set X-Content-Type-Options \"nosniff\"",
				"\r\n# \tTurn on IE8-IE9 XSS prevention tools",
				"Header set X-XSS-Protection \"1; mode=block\"",
				"\r\n# \tRemove server signature",
				"ServerSignature Off",
				"\r\n# \tChange 403 document",
				"ErrorDocument 403 /thekadeshi.php?block",
				"\r\n# \tBlock xmlrpc",
				"<Files xmlrpc.php>",
					"\tOrder Allow,Deny",
					"\tdeny from all",
				"</Files>",
				"\r\n# \tBlocked .htaccess file",
				"<Files .htaccess>",
					"\torder allow,deny",
					"\tdeny from all",
				"</Files>",
			);
		}
		$this->htaccessModify($htaccessConfig);
		$this->Ping();
	}

	/**
	 * Функция пинга.
	 * Он не особо нужен, на будущее надо:
	 * @todo перенести этот функционал в результаты сканирования
	 */
	public function Ping() {
		$StatusContent['ping'] = array(
			'date' => gmdate("Y-m-d H:i:s"),
			'status' => 'online'
		);

		$pingResult = TheKadeshi::ServiceRequest('sendPing', array('data' => json_encode($StatusContent)));
		if($pingResult) {
			$isErrors = json_decode($pingResult, true);

			if($isErrors['errors'] == false) {

			}
		}

		$this->SendFirewallLogs();
	}

	public function htaccessModify($configArray) {
		$htaccessFile = __DIR__ . "/.htaccess";
		$this->setChmod($htaccessFile, 'write');
		$htaccessContent = mb_convert_encoding(file_get_contents($htaccessFile), "utf-8");
		$newContent = "";
		$startString = "# TheKadeshi # Start #\r\n\r\n";
		$endString = "# TheKadeshi # End #\r\n";

				$startPosition = mb_strpos($htaccessContent, $startString);
				$endPosition = (mb_strpos($htaccessContent, $endString )!=0)?(mb_strpos($htaccessContent, $endString) + mb_strlen($endString)):0;
				$startBlock = mb_substr($htaccessContent, 0, $startPosition);
				$endBlock = mb_substr($htaccessContent, $endPosition);
				$oldContent = $startBlock . $endBlock;

		foreach ($configArray as $configElement) {
			if(is_array($configElement)) {
				foreach ($configElement as $subelement) {
					$newContent .= $subelement . "\r\n";
				}
			} else {
				$newContent .= $configElement . "\r\n";
			}
		}
		$newContent = $startString . $newContent . "\r\n" . $endString;
		$newContent .= $oldContent;
		file_put_contents($htaccessFile, $newContent);
		$this->setChmod($htaccessFile, 'read');
	}

	/**
	 * Функция записи логов фаервола
	 * @param $ip
	 */
	public function WriteFirewallLog($ip) {
		$firewallData = array();
		if(file_exists(self::$FirewallLogFile)) {
			$this->setChmod(self::$FirewallLogFile, 'write');
			$firewallLogContent = file_get_contents(self::$FirewallLogFile);
			$firewallData = json_decode($firewallLogContent, true);
		}
		if(!isset($firewallData['hash'])) {
			$firewallData['hash'] = self::$Options['hash'];
		}
		$firewallData['logs'][] = array(
			'ip' => $ip,
			'time' => gmdate("Y-m-d H:i:s")
		);
		$firewallLogContent = json_encode($firewallData);
		file_put_contents(self::$FirewallLogFile, $firewallLogContent);
		$this->setChmod(self::$FirewallLogFile, 'read');
	}

	/**
	 * Функция установки прав на файлы
	 * @param $fileName
	 * @param string $action
	 */
	private function setChmod($fileName, $action = 'read') {
		if($action == 'read') {
			if (is_file($fileName)) {
				chmod($fileName, 0444);
			}
		} else {
			if (is_file($fileName)) {
				chmod($fileName, 0644);
			}
		}
		if (is_dir($fileName)) {
			chmod($fileName, 0755);
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
		    'Sender: TheKadeshi');

		curl_setopt_array($curl, $curlOptions);
		$pageContent = curl_exec($curl);

		curl_close($curl);

		return $pageContent;
	}
}
$oldErrorReporting = error_reporting();
error_reporting(0);

$theKadeshi = new TheKadeshi();

if(php_sapi_name() !== 'cli') {
	if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'thekadeshi.php')) {

		if(isset($_POST['ping'])) {

			$theKadeshi->Ping();
			exit();
		}

		if(isset($_POST['scan'])) {
			exec("php " . __DIR__ . $_SERVER['PHP_SELF'] . " --scan");
			exit();
		} else {

			/*
			* Инсталляция, если запущен из браузера без параметров
			*/
			$theKadeshi->Install($_SERVER['SERVER_NAME']);
			echo(base64_decode($theKadeshi::ProtectedPage));
			exit();
		}
	}

	if(!strpos($_SERVER['PHP_SELF'], 'thekadeshi')) {
		if(!defined('PREPEND')) {
			define('PREPEND', true);
		}
		$currentAction = 'prepend';
	}
	if(isset($_REQUEST['block'])) {
		$currentAction = "block";
	}
} else {
	$currentAction = 'scan';
}

$scanResults = array();
$needToBlock = false;

switch ($currentAction) {
	case 'prepend':

		if(isset($theKadeshi::$Options['modifyheaders']) && $theKadeshi::$Options['modifyheaders'] == true) {
			@header("Protection: TheKadeshi");
		}

		if(isset($theKadeshi::$Options['firewall']) && $theKadeshi::$Options['firewall'] == true) {

			if(isset($theKadeshi::$Options['block_empty_user_agent']) && $theKadeshi::$Options['block_empty_user_agent'] == true) {

				if(!isset($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] == '') {
					$needToBlock = true;
					break;
				}
			}
			$requestArray = array_merge($_POST, $_GET, $_COOKIE);

			foreach ($theKadeshi::$firewallRules as $firewallRule) {
				if($needToBlock == true) {
					continue;
				}
				foreach ($requestArray as $requestItem) {
					if($needToBlock == true) {
						continue;
					}
					$firewallResult = (bool)preg_match("~" . $firewallRule['rule'] . "~msA", $requestItem);


					if($firewallResult!==false) {

						$needToBlock = true;
						break;
					}
				}

			}
		}

		if(!empty($_FILES)) {
			foreach ($_FILES as $fileToScan) {
				$fileScanResults = $theKadeshi->Scanner->Scan($fileToScan['tmp_name'], false);
				if(!empty($fileScanResults) && isset($fileScanResults['scanner'])) {
					$needToBlock = true;
					$theKadeshi->Scanner->SaveAnamnesis();
					$theKadeshi->Scanner->SendAnamnesis();
				}
			}
		}
		if(isset($_SERVER['SCRIPT_FILENAME'])) {
			$fileToCheck = $_SERVER['SCRIPT_FILENAME'];
			if (method_exists($theKadeshi->Scanner, "Scan")) {
				$fileScanResults = $theKadeshi->Scanner->Scan($fileToCheck, true);

				if(!empty($fileScanResults) && isset($fileScanResults['scanner'])) {
					$needToBlock = true;
					$theKadeshi->Scanner->SaveAnamnesis();
					$theKadeshi->Scanner->SendAnamnesis();
				}
			}
		}

		break;

	case "block":
		$needToBlock = true;
		break;
	default:

		$theKadeshi->GetFileList(__DIR__);

		foreach ($theKadeshi->fileList as $file) {

			$fileScanResults = $theKadeshi->Scanner->Scan($file, true);

		}

		$theKadeshi->Scanner->SaveAnamnesis();
		$theKadeshi->Scanner->SendAnamnesis();


		break;
}
@header('Execute: ' . (microtime(true) - $theKadeshi->executionMicroTimeStart));

if($needToBlock == true) {
	$blockedIp = $_SERVER['REMOTE_ADDR'];
	$theKadeshi->WriteFirewallLog($blockedIp);
	header('HTTP/1.0 403 Forbidden');
	echo(base64_decode($theKadeshi::ProtectedPage));
	die();
}
error_reporting($oldErrorReporting);
unset($theKadeshi);
unset($scanResults);