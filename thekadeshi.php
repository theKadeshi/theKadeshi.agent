<?php
/**
 * Project: theKadeshi
 * User: Bagdad
 * Date: 03.08.2016
 * Time: 13:44
 * Created by PhpStorm.
 */

class TheKadeshi {

	/**
	 * Содержимое страницы блокировки
	 */
	const ProtectedPage = 'PCFkb2N0eXBlIGh0bWw+PGh0bWw+PGhlYWQ+PG1ldGEgY2hhcnNldD11dGYtOD48dGl0bGU+VGhpcyB3ZWJzaXRlIGlzIHByb3RlY3RlZCBieSBUaGVLYWRlc2hpIHN5c3RlbTwvdGl0bGU+PGxpbmsgaHJlZj0iaHR0cHM6Ly9mb250cy5nb29nbGVhcGlzLmNvbS9jc3M/ZmFtaWx5PVJvYm90bzoxMDAiIHJlbD0ic3R5bGVzaGVldCIgdHlwZT0idGV4dC9jc3MiPjxzdHlsZT5ib2R5LCBodG1sIHtoZWlnaHQ6IDEwMCU7bWFyZ2luOiAwO2JhY2tncm91bmQtY29sb3I6ICNkY2RjZGM7fWgxIHtmb250LWZhbWlseTogJ1JvYm90bycsIHNhbnMtc2VyaWYgIWltcG9ydGFudDtmb250LXdlaWdodDogMTAwICFpbXBvcnRhbnQ7bGluZS1oZWlnaHQ6IDQwcHg7fS5yZXNwb25zaXZlLWNvbnRhaW5lciB7cG9zaXRpb246IHJlbGF0aXZlO3dpZHRoOiAxMDAlO2hlaWdodDogMTAwJX0uaW1nLWNvbnRhaW5lciB7cG9zaXRpb246IGFic29sdXRlO3RvcDogMDtib3R0b206IDA7bGVmdDogMDtyaWdodDogMDt0ZXh0LWFsaWduOiBjZW50ZXI7Zm9udDogMC8wIGE7d2lkdGg6IDEwMCU7Zm9udC1zaXplOiAxNTAlO31hIHtjb2xvcjogIzRkY2VjNTt0ZXh0LWRlY29yYXRpb246IG5vbmU7fS5pbWctY29udGFpbmVyOmJlZm9yZSB7Y29udGVudDogJyAnO2Rpc3BsYXk6IGlubGluZS1ibG9jazt2ZXJ0aWNhbC1hbGlnbjogbWlkZGxlO2hlaWdodDogNjAlO30uaW1nLWNvbnRhaW5lciBpbWcge3ZlcnRpY2FsLWFsaWduOiBtaWRkbGU7ZGlzcGxheTogaW5saW5lLWJsb2NrO3dpZHRoOiAyMCU7fTwvc3R5bGU+PC9oZWFkPjxib2R5PjxkaXYgY2xhc3M9cmVzcG9uc2l2ZS1jb250YWluZXI+PGRpdiBjbGFzcz1pbWctY29udGFpbmVyPjxpbWcgc3JjPWh0dHA6Ly90aGVrYWRlc2hpLmNvbS9pbWFnZXMvdGhla2FkZXNoaS1yZW1vdGUuc3ZnPjxici8+PGgxPlRoaXMgd2Vic2l0ZSBpcyBwcm90ZWN0ZWQgYnkgPGEgaHJlZj1odHRwOi8vdGhla2FkZXNoaS5jb20gdGFyZ2V0PV9ibGFuaz5UaGVLYWRlc2hpPC9hPiBzeXN0ZW08L2gxPjwvZGl2PjwvZGl2PjwvYm9keT48L2h0bWw+';

	/**
	 * Адрес службы
	 */
	const ServiceUrl = 'http://thekadeshi.com/';

	/**
	 * @var array Список файлов для сканирования
	 */
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
	private static $ValidExtensions = array ('php', 'php4', 'php5', 'php7', 'js', 'css', 'phtml', 'html', 'htm', 'tpl', 'inc');

	/**
	 * Каталоги
	 */

	/**
	 * @var string Каталог Кадеш
	 */
	private static $TheKadeshiDir;

	/**
	 * @var string Каталог с контрольными суммами
	 */
	private static $CheckSumDir = '';

	private static $OptionsFile = '';

	private static $SignatureFile = '';

	private static $FirewallFile = '';

	private static $AnamnesisFile = '';

	private static $FirewallLogFile = '';

	private static $SnifferLogDir;

	private static $snifferLogFile;

	public static $Options;

	static $Logs;

	private static $API_Path, $CDN_Path;

	const configCheckTimer = 3600;

	public $executionMicroTimeStart;

	/**
	 * База сигнатур
	 * @var array
	 */
	private static $signatureDatabase;

	/**
	 * База правил фаервола
	 * @var string
	 */
	public $firewallRules = '';

	public function __construct() {

		$this->executionMicroTimeStart = microtime(true);

	    $currentIp = '127.0.0.1';
	    if(array_key_exists('REMOTE_ADDR', $_SERVER)) {
		    $currentIp = $_SERVER['REMOTE_ADDR'];
	    }
	    $currentMinuteMin = str_pad(floor(gmdate('i') / 10) * 10, '0', STR_PAD_LEFT);
	    $currentMinuteMax = str_pad(ceil(((gmdate('i') / 10) < 1) ? 1 : (gmdate('i') / 10)) * 10, '0', STR_PAD_LEFT);
	    $currentSnifferLogFile = gmdate('H') . '-' . $currentMinuteMin . '-' . $currentMinuteMax . '~' . $currentIp . '.log.json';

		self::$TheKadeshiDir = __DIR__ . '/.thekadeshi';
		self::$OptionsFile = self::$TheKadeshiDir . '/.options';
		self::$FirewallFile = self::$TheKadeshiDir . '/.firewall';
		self::$SnifferLogDir = self::$TheKadeshiDir . '/.sniffer/' . gmdate('Y/m/d/H');
		self::$FirewallLogFile = self::$TheKadeshiDir . '/.firewall.log';
		self::$snifferLogFile =  self::$SnifferLogDir . '/' . $currentSnifferLogFile;
		self::$API_Path = self::ServiceUrl . 'api/';
		self::$CDN_Path = self::ServiceUrl . 'cdn/';

		self::setCheckSumDir(self::$TheKadeshiDir . '/checksum');

		if(!is_dir(self::getCheckSumDir())) {
			$folderCreateResult = mkdir(self::getCheckSumDir(), 0755, true);
			if($folderCreateResult === false) {
				self::$WorkWithoutSelfFolder = true;
			}
		}

		if($this->GetOptions() !== false) {

			self::setAnamnesisFile(self::$TheKadeshiDir . '/.anamnesis');

			self::$SignatureFile = self::$TheKadeshiDir . '/.signatures';

			if (!is_file(self::$TheKadeshiDir . '/.thekadeshi')) {
				$this->Update();
			}
			if (file_exists(self::$TheKadeshiDir . '/.thekadeshi')) {
				include_once self::$TheKadeshiDir . '/.thekadeshi';

				$this->Scanner = new Scanner();

				self::$Status = new Status();

			}

			if (!isset(self::$Options['lastconfigcheck']) || (self::$Options['lastconfigcheck'] < (time() - self::configCheckTimer)) || (self::$Options['lastconfigcheck'] >= time())) {

				$this->SendFirewallLogs();
				$this->GetRemoteConfig(self::$Options['name']);
				$this->GetRemoteSignatures();
				$this->Update();
			}

			$this->LoadSignatures();
		}
	}

	/**
	 * Функция создания лога траффика
	 * @return null
	 */
	private function GenerateSnifferLog() {
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

	/**
	 * Функция записи в лог перехваченного трафика
	 * @return bool
	 */
	public function WriteSnifferLog() {
		$data = $this->GenerateSnifferLog();
    	if($data !== null) {

    		if(!@mkdir(self::$SnifferLogDir, 0755, true)  && !is_dir(self::$SnifferLogDir )){
                return false;
	        } else {

			    if (file_exists(self::$snifferLogFile)) {
				    $currentFileContent = file_get_contents(self::$snifferLogFile);
				    if (!($currentjsonContent = json_decode($currentFileContent, true))) {
					    $currentjsonContent = array();
				    }
			    }
			    $currentJsonContent[gmdate('Y-m-d H:i:s')][] = $data;
			    file_put_contents(self::$snifferLogFile, json_encode($currentJsonContent));

			    return true;
		    }
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
		$fileHash = null;
		if(file_exists(self::$TheKadeshiDir . '/.thekadeshi')) {
			$fileContent = file_get_contents(self::$TheKadeshiDir . '/.thekadeshi');
			$fileHash = hash('sha256', $fileContent);
		}
		if(array_key_exists('kernelhash', self::$Options) === false || (self::$Options['kernelhash'] !== $fileHash)) {

			$arguments = array();
			if(is_array(self::$Options) && array_key_exists('developer_mode', self::$Options) && (int)self::$Options['developer_mode'] === 1) {
				$arguments['dev'] = 1;
			}
			$content = self::ServiceRequest('thekadeshi', $arguments, false, 'cdn');
			if ($content !== false) {
				file_put_contents(self::$TheKadeshiDir . '/.thekadeshi', $content);
			}

			unset($fileContent, $fileHash, $arguments);
		}

		/*
		 * Обновление агента
		 */
		$fileContent = file_get_contents(__DIR__ . '/thekadeshi.php');
		$fileHash = hash('sha256', $fileContent);
		if(array_key_exists('agenthash', self::$Options) === false || (self::$Options['agenthash'] !== $fileHash)) {
			$arguments = array();
			if(is_array(self::$Options) && array_key_exists('developer_mode', self::$Options) && (int)self::$Options['developer_mode'] === 1) {
				$arguments['dev'] = 1;
			}
			$content = self::ServiceRequest('agent', $arguments, false, 'cdn');
			if ($content !== false) {
				file_put_contents(__DIR__ . '/thekadeshi.php', $content);
			}

			unset($fileContent, $fileHash, $arguments);
		}
	}

	/**
	 * Функция отправки отчетов фаервола
	 * @return bool
	 */
	public function SendFirewallLogs() {
		$firewallLogContent = '';
		if(file_exists(self::$FirewallLogFile)) {
			$this->setChmod(self::$FirewallLogFile, 'write');
			$firewallLogContent = file_get_contents(self::$FirewallLogFile);

		}

		if($firewallLogContent === '') {
			return false;
		}
		$sendResult = self::ServiceRequest('sendFirewallLogs', array('data' => $firewallLogContent));

		$resultData = json_decode($sendResult, true);
		if((count($resultData) !== 0) && $resultData['message'] === 'Ok') {
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
			if(array_key_exists('lastsignaturecheck', self::$Options) === true && (self::$Options['lastsignaturecheck'] < (time() - self::configCheckTimer))) {
				$this->GetRemoteSignatures();
			}
		}
		if(file_exists(self::$SignatureFile)) {
			self::setSignatureDatabase(json_decode(base64_decode(file_get_contents(self::$SignatureFile)), true));
		}
		if(array_key_exists('firewall', self::$Options) === true && (self::$Options['firewall'] === 1)) {
			if(file_exists(self::$FirewallFile)) {
				$this->firewallRules = json_decode(base64_decode(file_get_contents(self::$FirewallFile)), true);
			}
		}
	}

	/**
	 * Getter для $CheckSumDir
	 * @return string
	 */
	public static function getCheckSumDir() {
		return self::$CheckSumDir;
	}

	/**
	 * Setter для $CheckSumDir
	 * @param string $CheckSumDir
	 */
	private static function setCheckSumDir($CheckSumDir) {
		self::$CheckSumDir = $CheckSumDir;
	}

	/**
	 * @return array
	 */
	public static function getSignatureDatabase() {
		return self::$signatureDatabase;
	}

	/**
	 * @param array $signatureDatabase
	 */
	private static function setSignatureDatabase($signatureDatabase) {
		self::$signatureDatabase = $signatureDatabase;
	}

	/**
	 * @return string
	 */
	public static function getAnamnesisFile() {
		return self::$AnamnesisFile;
	}

	/**
	 * @param string $AnamnesisFile
	 */
	private static function setAnamnesisFile($AnamnesisFile) {
		self::$AnamnesisFile = $AnamnesisFile;
	}

	/**
	 * Функция получения содержимого каталога
	 * @param $dir
	 */
	public function GetFileList($dir) {

		$dirContent = scandir($dir);
		foreach($dirContent as $directoryElement) {
			if($directoryElement !== '..' && $directoryElement !== '.') {
				$someFile = $dir . '/' . $directoryElement;
				if (is_file($someFile)) {
					$fileData = pathinfo($someFile);
					if (array_key_exists('extension',$fileData) && in_array($fileData['extension'], self::$ValidExtensions, true) === true) {
						$this->fileList[] = $someFile;
					}
				} else {
					$this->GetFileList($someFile);
				}
			}
		}
	}

	/**
	 * Функция чтения опций их локального файла
	 * @param null $OptionName
	 * @return mixed
	 */
	public function GetOptions($OptionName = null) {
		if($OptionName === null) {
			if (file_exists(self::$OptionsFile)) {
				$json_decode = json_decode(file_get_contents(self::$OptionsFile), true);
				if (!$json_decode) {
					return false;
				}
				self::$Options = $json_decode;
				return true;
			} else {
				return false;
			}
		} else {
			if(array_key_exists($OptionName, self::$Options)) {
				return self::$Options[$OptionName];
			} else {
				return false;
			}
		}
	}

	/**
	 * Функция получения удаленных сигнатур
	 */
	public function GetRemoteSignatures() {

		/*
		 * Получение массива сигнатур
		 */
		$signatureData = self::ServiceRequest('getSignatures');
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
		$firewallData = self::ServiceRequest('getFirewallRules');

		$receivedRules = json_decode($firewallData, true);
		if($receivedRules !== false) {
			if(!isset($receivedRules['error'])) {
				file_put_contents(self::$FirewallFile, base64_encode(json_encode($receivedRules)));
			}
		}

		/*
		 * Чистка каталога с контрольными суммами, после получения нового списка сигнатур
		 */
		$this->deleteContent(self::getCheckSumDir());
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
		$htaccessConfig = array();

		$ConfigData = self::ServiceRequest('getConfig', $arguments, false);

		if($ConfigData !== '') {
			self::$Options = json_decode($ConfigData, true);
			self::$Options['lastconfigcheck'] = time();
			file_put_contents(self::$OptionsFile, json_encode(self::$Options));
		}
		if(array_key_exists('prepend', self::$Options) === true && self::$Options['prepend'] === 1) {
			$htaccessConfig[] = array(
				"\r\n<IfModule mod_suphp.c>",
				"\tsuPHP_ConfigPath \"" . __DIR__ . "\"",
				'</IfModule>',
				"\r\n<IfModule mod_php5.c>",
				"\tphp_value auto_prepend_file \"" . __DIR__ . "/thekadeshi.php\"",
				'</IfModule>',
				"\r\n<IfModule lsapi_module>",
				"\tphp_value auto_prepend_file \"" . __DIR__ . "/thekadeshi.php\"",
				'</IfModule>',
				"\r\n# \tLightSpeed",
				'<IfModule LiteSpeed>',
				"\tphp_value auto_prepend_file \"" . __DIR__ . "/thekadeshi.php\"",
				'</IfModule>',

				"\r\n<Files \".user.ini\">",
				"\t<IfModule mod_authz_core.c>",
				"\t\tRequire all denied",
				"\t</IfModule>",
				"\t<IfModule !mod_authz_core.c>",
				"\t\tOrder deny,allow",
				"\t\tDeny from all",
				"\t</IfModule>",
				'</Files>',
				"\r\n# \tChange 403 document",
				'ErrorDocument 403 /thekadeshi.php?block',
				);
		}

		if(array_key_exists('firewall', self::$Options) === true && self::$Options['firewall'] === 1) {
			$htaccessConfig[] = array(
				"\r\n# \tPrevent mime based attacks",
				"Header set X-Content-Type-Options \"nosniff\"",
				"\r\n# \tTurn on IE8-IE9 XSS prevention tools",
				"Header set X-XSS-Protection \"1; mode=block\"",
				"\r\n# \tRemove server signature",
				'ServerSignature Off',
				"\r\n# \tBlock xmlrpc",
				'<Files xmlrpc.php>',
				"\tOrder Allow,Deny",
				"\tdeny from all",
				'</Files>',
				"\r\n# \tBlocked .htaccess file",
				'<Files .htaccess>',
				"\torder allow,deny",
				"\tdeny from all",
				'</Files>',
			);
		}
		$this->htaccessModify($htaccessConfig);
	}

	/**
	 * Функция пинга.
	 * Он не особо нужен, на будущее надо:
	 * @todo перенести этот функционал в результаты сканирования
	 */
	public function Ping() {

		$StatusContent['ping'] = array(
			'date' => gmdate('Y-m-d H:i:s'),
			'status' => 'online'
		);

		$pingResult = self::ServiceRequest('sendPing', array('data' => json_encode($StatusContent)));

		if($pingResult) {
			$isErrors = json_decode($pingResult, true);

			if($isErrors['errors'] === false) {

			}
		}

		$this->SendFirewallLogs();
		$this->GetRemoteConfig(self::$Options['name']);
		$this->GetRemoteSignatures();
		$this->Update();
	}

	public function htaccessModify($configArray) {
		$htaccessFile = __DIR__ . '/.htaccess';
		$this->setChmod($htaccessFile, 'write');
		$htaccessContent = mb_convert_encoding(file_get_contents($htaccessFile), 'utf-8');
		$newContent = '';
		$startString = "# TheKadeshi # Start #\r\n\r\n";
		$endString = "# TheKadeshi # End #\r\n";

		$startPosition = mb_strpos($htaccessContent, $startString);
		$endPosition = (mb_strpos($htaccessContent, $endString ) !== 0 && mb_strpos($htaccessContent, $endString ) !== false)?(mb_strpos($htaccessContent, $endString) + mb_strlen($endString)):0;
		$startBlock = mb_substr($htaccessContent, 0, $startPosition);
		$endBlock = mb_substr($htaccessContent, $endPosition);
		$oldContent = $startBlock . $endBlock;

		if(count($configArray) !== 0) {
			foreach ($configArray as $configElement) {
				if (is_array($configElement) && (count($configElement) !== 0)) {
					foreach ($configElement as $subelement) {
						$newContent .= $subelement . "\r\n";
					}
				} else {
					$newContent .= $configElement . "\r\n";
				}
			}
		}
		$newContent = $startString . $newContent . "\r\n" . $endString;
		$newContent .= $oldContent;
		file_put_contents($htaccessFile, $newContent);
		$this->setChmod($htaccessFile, 'read');
		unset($startString, $endString, $startPosition, $endPosition);

		if(self::$Options['prepend'] === 1) {
			$userIniFile = __DIR__ . '/.user.ini';

			$this->setChmod($userIniFile, 'write');
			$userIniContent = mb_convert_encoding(file_get_contents($userIniFile), 'utf-8');
			$newContent = '';
			$startString = "; TheKadeshi # Start #\r\n\r\n";
			$endString = "; TheKadeshi # End #\r\n";

			$startPosition = mb_strpos($userIniContent, $startString);
			$endPosition = (mb_strpos($userIniContent, $endString ) !== 0)?(mb_strpos($userIniContent, $endString) + mb_strlen($endString)):0;
			$startBlock = mb_substr($userIniContent, 0, $startPosition);
			$endBlock = mb_substr($userIniContent, $endPosition);
			$oldContent = $startBlock . $endBlock;

			$newContent = "auto_prepend_file = \"" . __DIR__ . "/thekadeshi.php\"\r\n";

			$newContent = $startString . $newContent . "\r\n" . $endString;
			$newContent .= $oldContent;
			file_put_contents($userIniFile, $newContent);
			$this->setChmod($userIniFile, 'read');
		}
	}

	/**
	 * Функция записи логов фаервола
	 * @param $ip
	 * @param null $ruleId
	 * @param null $script
	 * @param null $query
	 */
	public function WriteFirewallLog($ip, $ruleId = null, $script = null, $query = null) {
		$firewallData = array();
		if(file_exists(self::$FirewallLogFile)) {
			$this->setChmod(self::$FirewallLogFile, 'write');
			$firewallLogContent = file_get_contents(self::$FirewallLogFile);
			$firewallData = json_decode($firewallLogContent, true);
		}
		if(array_key_exists('hash', $firewallData) === false) {
			$firewallData['hash'] = self::$Options['hash'];
		}
		$firewallData['logs'][] = array(
			'ip' => $ip,
			'time' => gmdate('Y-m-d H:i:s'),
			'rule' => $ruleId,
			'script' => $script,
			'query' => $query
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
		if($action === 'read') {
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

	public static function ServiceRequest($ApiMethod, $arguments = null, $sendToken = true, $source = 'api') {

		if(function_exists('curl_exec') && function_exists('curl_init') && function_exists('curl_close')) {

			$curl = curl_init();

			$curlOptions = array();

			if ($source === 'api') {
				$curlOptions[CURLOPT_URL] = self::$API_Path . $ApiMethod;
			} elseif ($source === 'cdn') {
				$curlOptions[CURLOPT_URL] = self::$CDN_Path . $ApiMethod;
			}
			if(array_key_exists('SERVER_NAME', $_SERVER)) {
				$arguments['site'] = $_SERVER['SERVER_NAME'];
			}

			$curlOptions[CURLOPT_RETURNTRANSFER] = true;
			$curlOptions[CURLOPT_TIMEOUT] = 300;
			$curlOptions[CURLOPT_FOLLOWLOCATION] = false;
			$curlOptions[CURLOPT_USERAGENT] = 'TheKadeshi';

			$curlOptions[CURLOPT_POST] = true;


			if (isset($arguments)) {
				if ($sendToken === true) {
					$arguments['token'] = self::$Options['token'];
				}
				$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($arguments);
			}
			$curlOptions[CURLOPT_HTTPHEADER] = array(
				'Content-Type: application/x-www-form-urlencoded', 'Sender: TheKadeshi'
			);

			curl_setopt_array($curl, $curlOptions);
			$pageContent = curl_exec($curl);

			curl_close($curl);

			return $pageContent;
		} else {

			$context = stream_context_create(array(
				'http' => array(
					'method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded', 'Sender: TheKadeshi',
				),
			));

			if ($source === 'api') {
				$url = self::$API_Path . $ApiMethod;
			} elseif ($source === 'cdn') {
				$url = self::$CDN_Path . $ApiMethod;
			}

			if ($sendToken === true) {
				$arguments['token'] = self::$Options['token'];
			}

			$pageContent = file_get_contents($file = $url . '?' . http_build_query($arguments), $use_include_path = false, $context);

			return $pageContent;
		}

	}
}
$oldErrorReporting = error_reporting();
error_reporting(0);

$theKadeshi = new TheKadeshi();

if(php_sapi_name() !== 'cli') {
	if(array_key_exists('REQUEST_URI', $_SERVER) && strpos($_SERVER['REQUEST_URI'], 'thekadeshi.php')) {

		if(array_key_exists('ping', $_POST)) {

			$theKadeshi->Ping();
			exit();
		}

		if(array_key_exists('scan', $_POST)) {
			exec('php ' . __DIR__ . $_SERVER['PHP_SELF'] . ' --scan');
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
	if(array_key_exists('block', $_REQUEST)) {
		$currentAction = 'block';
	}
} else {
	$currentAction = 'scan';
}

$scanResults = array();
$needToBlock = false;

switch ($currentAction) {
	case 'prepend':

		if($theKadeshi->GetOptions('modifyheaders') === 1) {
			@header('Protection: TheKadeshi');
		}

		if($theKadeshi->GetOptions('sniffer') === 1) {
			$theKadeshi->WriteSnifferLog();
		}

		if($theKadeshi->GetOptions('firewall') === 1) {

			if($theKadeshi->GetOptions('block_empty_user_agent') === 1) {

				if(array_key_exists('HTTP_USER_AGENT', $_SERVER) === false || $_SERVER['HTTP_USER_AGENT'] === '') {
					$needToBlock = true;
					break;
				}
			}
			$requestArray = array_merge($_POST, $_GET, $_COOKIE);

			foreach ((array)$theKadeshi->firewallRules as $firewallRule) {
				if($needToBlock === true) {
					continue;
				}

				foreach ($requestArray as $requestKey => $requestItem) {

					if ($needToBlock === true) {
						continue;
					}
					/*
					  Woocomerce использует кривые куки, что срабатывают как SQL инъекция
					*/
					if(mb_strpos($requestKey, 'wp_woocommerce_session') === false) {

						$firewallResult = (bool)preg_match('`' . $firewallRule['rule'] . '`msA', $requestItem);

						if ($firewallResult !== false) {

							$requestScript = $_SERVER['PHP_SELF'];
							$requestQuery = base64_encode($requestItem);
							$ruleId = $firewallRule['id'];
							$needToBlock = true;
							break;

						}
					}
				}
			}
		}

		if(count($_FILES) === 0) {
			foreach ($_FILES as $fileToScan) {
				$fileScanResults = $theKadeshi->Scanner->Scan($fileToScan['tmp_name'], false);
				if(array_key_exists('scanner', $fileScanResults) === true) {
					$needToBlock = true;
					$theKadeshi->Scanner->SaveAnamnesis();
					$theKadeshi->Scanner->SendAnamnesis();
				}
			}
		}
		if(array_key_exists('SCRIPT_FILENAME' , $_SERVER) === true) {
			$fileToCheck = $_SERVER['SCRIPT_FILENAME'];
			if (method_exists($theKadeshi->Scanner, 'Scan')) {
				$fileScanResults = $theKadeshi->Scanner->Scan($fileToCheck, true);

				if(array_key_exists('scanner', $fileScanResults) === true) {
					$needToBlock = true;
					$theKadeshi->Scanner->SaveAnamnesis();
					$theKadeshi->Scanner->SendAnamnesis();
				}
			}
		}

		break;

	case 'block':
		$needToBlock = true;
		break;
	default:

		set_time_limit(0);
		@ini_set('max_execution_time', 0);

		$theKadeshi->GetFileList(__DIR__);

		foreach ($theKadeshi->fileList as $file) {

			$fileScanResults = $theKadeshi->Scanner->Scan($file, true);

		}

		$theKadeshi->Scanner->SaveAnamnesis();
		$theKadeshi->Scanner->SendAnamnesis();

		break;
}
@header('Execute: ' . (microtime(true) - $theKadeshi->executionMicroTimeStart));

if($needToBlock === true) {
	$blockedIp = $_SERVER['REMOTE_ADDR'];
	$theKadeshi->WriteFirewallLog($blockedIp, (isset($ruleId)?$ruleId:0), (isset($requestScript)?$requestScript:''), (isset($requestQuery)?$requestQuery:''));
	header('HTTP/1.0 403 Forbidden');
	echo(base64_decode($theKadeshi::ProtectedPage));
	die();
}
error_reporting($oldErrorReporting);
unset($theKadeshi, $scanResults);