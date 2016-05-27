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
	//static $QuarantineDir = '';

	static $OptionsFile = '';

	static $SignatureFile = '';

	static $AnamnesisFile = '';

	static $FirewallFile = '';

	public static $Options;

	static $Logs;
	
	static $API_Path;

	const configCheckTimer = 3600;

	public $executionMicroTimeStart;

	private $needToSendLogs = false;
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
		self::$FirewallFile = self::$TheKadeshiDir . "/" . ".firewall";
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
			$this->Healer = new Healer();
			self::$Status = new Status();

			$this->LoadSignatures();
		}

		$this->Ping();

		if(!isset(self::$Options['lastconfigcheck']) || (self::$Options['lastconfigcheck'] < (time() - self::configCheckTimer)) || (self::$Options['lastconfigcheck'] >= time())) {
			$this->GetRemoteConfig(self::$Options['name']);
			$this->SendFirewallLogs();
		}
	}

	private function Update() {
		$parh = self::ServiceUrl . "cdn/thekadeshi";
			$content = file_get_contents($parh);
			if($content === false) {
				echo("something wrong");
			}
			file_put_contents(self::$TheKadeshiDir . "/.thekadeshi", $content);
	}

	private function SendFirewallLogs() {
		$firewallLogContent = '';
		if(file_exists(self::$FirewallFile)) {
			$this->setChmod(self::$FirewallFile, 'write');
			$firewallLogContent = file_get_contents(self::$FirewallFile);
			//$firewallData = json_decode($firewallLogContent, true);
		}
		if($firewallLogContent == '') {
			return false;
		}
		$sendResult = $this->ServiceRequest('sendFirewallLogs', array('data' => $firewallLogContent));
		$resultData = json_decode($sendResult, true);
		if(!empty($resultData) && $resultData['message'] == 'Ok') {
			unlink(self::$FirewallFile);
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
				"\r\n# \tBlock MySQL injections, RFI, base64, etc.",
				"<IfModule mod_rewrite.c>",
					"\tRewriteEngine On",
					"\tRewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=http:// [OR]",
					"\tRewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=(\.\.//?)+ [OR]",
					"\tRewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=/([a-z0-9_.]//?)+ [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} \=PHP[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12} [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} (\.\./|\.\.) [OR]",
					"\tRewriteCond %{QUERY_STRING} ftp\: [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} http\: [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} https\: [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} \=\|w\| [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} ^(.*)/self/(.*)$ [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} ^(.*)cPath=http://(.*)$ [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} (\<|%3C).*script.*(\>|%3E) [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} (<|%3C)([^s]*s)+cript.*(>|%3E) [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} (\<|%3C).*iframe.*(\>|%3E) [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} (<|%3C)([^i]*i)+frame.*(>|%3E) [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} base64_encode.*\(.*\) [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} base64_(en|de)code[^(]*\([^)]*\) [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]",
					"\tRewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2}) [OR]",
					"\tRewriteCond %{QUERY_STRING} ^.*(\[|\]|\(|\)|<|>).* [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} (NULL|OUTFILE|LOAD_FILE) [OR]",
					"\tRewriteCond %{QUERY_STRING} (\./|\../|\.../)+(motd|etc|bin) [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} (localhost|loopback|127\.0\.0\.1) [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} (<|>|'|%0A|%0D|%27|%3C|%3E|%00) [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} concat[^\(]*\( [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} union([^s]*s)+elect [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} union([^a]*a)+ll([^s]*s)+elect [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} (;|<|>|'|\"|\)|%0A|%0D|%22|%27|%3C|%3E|%00).*(/\*|union|select|insert|drop|delete|update|cast|create|char|convert|alter|declare|order|script|set|md5|benchmark|encode) [NC,OR]",
					"\tRewriteCond %{QUERY_STRING} (sp_executesql) [NC]",
					"\tRewriteRule ^(.*)$ - [F,L]",
				"</IfModule>",
			);
		}
		$this->htaccessModify($htaccessConfig);
		//if(self::$WorkWithoutSelfFolder === false) {
			$this->Ping();
		//}
	}

	public function Ping() {
		$StatusContent['ping'] = array(
			'date' => date("Y-m-d H:i:s"),
			'status' => 'online'
		);

		//$this->writeStatus();

		$pingResult = TheKadeshi::ServiceRequest('sendPing', array('data' => json_encode($StatusContent)));
		if($pingResult) {
			$isErrors = json_decode($pingResult, true);

			if($isErrors['errors'] == false) {
				//unlink($this->StatusFile );
			}
		}
		//print_r($pingResult);
		$this->SendFirewallLogs();
	}

	public function htaccessModify($configArray) {
		$htaccessFile = __DIR__ . "/.htaccess";
		$this->setChmod($htaccessFile, 'write');
		$htaccessContent = file_get_contents($htaccessFile);
		$newContent = "";
		$startString = "# TheKadeshi # Start #\r\n\r\n";
		$endString = "# TheKadeshi # End #\r\n";

				$startPosition = mb_strpos($htaccessContent, $startString);
				$endPosition = mb_strpos($htaccessContent, $endString )+ mb_strlen($endString);
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
		//		break;
		//}
		$this->setChmod($htaccessFile, 'read');
	}

	public function WriteFirewallLog($ip) {
		$firewallData = array();
		if(file_exists(self::$FirewallFile)) {
			$this->setChmod(self::$FirewallFile, 'write');
			$firewallLogContent = file_get_contents(self::$FirewallFile);
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
		file_put_contents(self::$FirewallFile, $firewallLogContent);
		$this->setChmod(self::$FirewallFile, 'read');
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
//print_r(php_sapi_name());
if(php_sapi_name() !== 'cli') {
	if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'thekadeshi.php')) {

		if(isset($_POST['ping'])) {
			//echo("ping");
			$theKadeshi->Ping();
			exit();
		}

		if(isset($_POST['scan'])) {
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
	if(isset($_REQUEST['block'])) {
		$currentAction = "block";
	}
}
//print_r($_GET);
//$Console = new Console(defined('VERBOSE')?VERBOSE:false);
$scanResults = array();
$needToBlock = false;
//print_r($currentAction);
switch ($currentAction) {
	case 'prepend':

		//$theKadeshi->GetRemoteConfig($_SERVER['SERVER_NAME']);

		if(!empty($_FILES) && ($_FILES['size'] > 0)) {
			foreach ($_FILES as $fileToScan) {
				//echo("file<br/>\r\n");
				//print_r($_FILES);
				$fileScanResults = $theKadeshi->Scanner->Scan($fileToScan['tmp_name'], false);
				if(!empty($fileScanResults)) {
					//print_r($fileScanResults);
					$theKadeshi->Healer->Quarantine($fileToScan['tmp_name'], $fileToScan['name']);
					//$Status->FirewallEvent();
				}
			}
		}

		//if($Options['firewall']) {
//
//			$sqlPattern = "#(;|\*/)\s*(SELECT|INSERT\s+INTO|UPDATE|DELETE\s+FROM|DROP\s+(TABLE|DATABASE|VIEW)|TRUNCATE\s+TABLE)\s#i";
//			$xssPattern = "#base64_?(de|en)code\s*\(#i";
//			if (!empty($_POST)) {
//				foreach ($_POST as $postVar) {
//					preg_match_all($sqlPattern, $postVar, $matches);
//					if (!empty($matches)) {
//						$needToBlock = true;
//					}
//				}
//			}
//		}

		if($theKadeshi::$Options['modifyheaders']) {
			@header("Protection: TheKadeshi");
		}
		if(isset($_SERVER['SCRIPT_FILENAME'])) {
			//echo($_SERVER['SCRIPT_FILENAME']);
			$fileToCheck = $_SERVER['SCRIPT_FILENAME'];
			//print_r($fileToCheck);
			if (method_exists($theKadeshi->Scanner, "Scan")) {
				$fileScanResults = $theKadeshi->Scanner->Scan($fileToCheck, true);
			}
		}

		//print_r($fileScanResults);
		break;

	case "block":
		$blockedIp = $_SERVER['REMOTE_ADDR'];
		$theKadeshi->WriteFirewallLog($blockedIp);
		$needToBlock = true;
		header('HTTP/1.0 403 Forbidden');
		//print_r($_SERVER);
		break;
	default:

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
if($needToBlock == true) {
	echo(base64_decode($theKadeshi::ProtectedPage));
	die();
}