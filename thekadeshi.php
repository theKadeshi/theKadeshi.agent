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

	public $fileList = array();

	public $Scanner;

	/**
	 * Допустимые расширения для сканера
	 * @var array
	 */
	private $ValidExtensions = array ('php', 'php4', 'php5', 'php7', 'js', 'css', 'html', 'htm', 'tpl');

	/**
	 * Каталог кеша
	 * @var string
	 */
	static $TheKadeshiDir;
	
	static $OptionsFile = '';

	static $CheckSumDir = '';

	static $Options;
	
	static $API_Path;

	const configCheckTimer = 3600;

	function __construct() {

		self::$TheKadeshiDir = __DIR__ . "/.thekadeshi";
		self::$OptionsFile = self::$TheKadeshiDir . "/" . ".options";
		self::$API_Path = self::ServiceUrl . 'api/';

		self::$CheckSumDir = self::$TheKadeshiDir . "/" . "checksum";
		if(!is_dir(self::$CheckSumDir)) {
			mkdir(self::$CheckSumDir);
		}

		$this->Scanner = new Scanner(self::$TheKadeshiDir);

		$this->GetOptions();

		if(!isset(self::$Options['lastconfigcheck']) || (self::$Options['lastconfigcheck'] < time() - configCheckTimer) || (self::$Options['lastconfigcheck'] >= time())) {
			$this->GetRemoteConfig(self::$Options['name']);
		}

	}

	//public function Init() {
		//$this->Scanner = new Scanner();
	//	$this->Scanner->Init();
	//}

	public function GetFileList($dir) {

		$dirContent = scandir($dir);
		foreach($dirContent as $directoryElement) {
			if($directoryElement != '..' && $directoryElement != '.') {
				$someFile = $dir . '/' . $directoryElement;
				if (is_file($someFile)) {
					$fileData = pathinfo($someFile);
					if(isset($fileData['extension'])) {
						if(array_search($fileData['extension'], $this->ValidExtensions)) {
							//if ($fileData['extension'] == 'php') {
							$this->fileList[] = $someFile;
							//}
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
	 * Функция получения содержимого файла
	 * @param $fileName
	 * @return string
	 */
	private function GetFileContent($fileName) {

		$fileInfo = pathinfo($fileName);
		$content = false;

		if(strtolower($_SERVER['PHP_SELF']) != strtolower($fileName)) {
			if(array_search($fileInfo['extension'], $this->ValidExtensions)) {
				//if (isset($this->realFileName['extension']) && $this->realFileName['extension'] != 'xml') {
				$content = file_get_contents($fileName);
				//}
			}
		}
		//print_r($content);
		return $content;
	}

	private function GetOptions() {
		if(file_exists(self::$OptionsFile)) {
			$json_decode = json_decode(file_get_contents(self::$OptionsFile), true);
			if(!$json_decode) {
				return false;
			}
			self::$Options = $json_decode;
			unset($json_decode);
			return true;
		} else {
			return false;
		}
	}
	
	public function GetRemoteConfig($siteUrl) {
		//$remoteToken = file_get_contents(self::$API_Path . "getToken");
		$arguments = array(
			'site' => $siteUrl
		);
		$ConfigData = $this->ServiceRequest('getConfig', $arguments);
		if($ConfigData) {
			self::$Options = json_decode($ConfigData, true);
			self::$Options[] = array('lastconfigcheck' => time());
			file_put_contents(self::$OptionsFile, json_encode(self::$Options));
		}
	}

/*
	private function SaveOptions() {
		$json_encoded = json_encode(self::$Options);
		file_put_contents(self::$OptionsFile, $json_encoded);
		unset($json_encoded);
	}

	private function GetRemoteToken($siteName = null) {
		
		$tokenData = self::$Options['token'];
		
	}
*/
	public function Install($siteUrl) {
		if(!is_dir(self::$TheKadeshiDir)) {
			mkdir(self::$TheKadeshiDir);
		}
		$this->GetRemoteConfig($siteUrl);

		//if(!file_exists(THEKADESHI_DIR . "/.options")) {
		//	file_put_contents(THEKADESHI_DIR . "/.options", json_encode($key));
		//}

		//$siteUrl = $_SERVER['SERVER_NAME'];
		
	}



	private function ServiceRequest($ApiMethod, $arguments = null) {

		$curl = curl_init();

		//$urlDatails = parse_url($url);

		$curlOptions = array();

		$curlOptions[CURLOPT_URL] = self::$API_Path . $ApiMethod;

		$curlOptions[CURLOPT_RETURNTRANSFER] = true;
		$curlOptions[CURLOPT_TIMEOUT] = 300;
		$curlOptions[CURLOPT_FOLLOWLOCATION] = false;
		$curlOptions[CURLOPT_USERAGENT] = 'TheKadeshi';
		//if ($this->siteOptions['referer'] !== false) {
		//	$curlOptions[CURLOPT_REFERER] = $this->siteOptions['referer'];
		//}
		//if ($this->siteOptions['ajax'] !== false) {
		//	$curlOptions[CURLOPT_HTTPHEADER] = array("X-Requested-With: XMLHttpRequest");
		//}

		$curlOptions[CURLOPT_POST] = true;

		//if(isset($this->siteOptions['post']) && $this->siteOptions['post'] === true) {
		if(isset($arguments)) {
			$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($arguments);//$urlDatails['query'];
		}
		$curlOptions[CURLOPT_HTTPHEADER] = array(
			//'Content-length:'.strlen($urlDatails['query']),
			'Content-Type: application/x-www-form-urlencoded',
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
		    'Sender: TheKadeshi'
			/* 'Cookie: Cookie:ASP.NET_SessionId=iwwmkffugdvjsi45s5wmxwmn; __utmt=1; _ym_visorc_7415752=w; _ym_visorc_6333970=w; _ga=GA1.2.1821460200.1427287094; _dc_gtm_UA-51412757-1=1; __utma=69487449.1821460200.1427287094.1427287094.1427348781.2; __utmb=69487449.2.9.1427348783251; __utmc=69487449; __utmz=69487449.1427287094.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)' */);
			//}

		//}
		curl_setopt_array($curl, $curlOptions);
		$pageContent = curl_exec($curl);

		curl_close($curl);


		//if(isset($this->siteOptions['codepage'])) {
		//	$pageContent = mb_convert_encoding($pageContent, 'utf-8', $this->siteOptions['codepage']);
		//}

		return $pageContent;
	}
}

class Scanner {

	/**
	 * Где брать файл сигнатур
	 * @var string
	 */
	public $SignatureFile = 'remote';



	/**
	 * Каталог для контрольных сумм
	 * @var string
	 */
	private $ChekSumDir = "";

	/**
	 * Каталог сигнатур
	 * @var string
	 */
	private $SignaturesDir;

	private $SignaturesFileList = array();

	private $Signatures = array();

	private $realFileName = null;

	private $scanResults = array();

	private $namePatterns = array();

	/**
	 * Гласные буквы
	 * @var array
	 */
	private $vowelsLetters = array('a', 'e', 'i', 'o', 'u', 'y');

	/**
	 * Согласные буквы
	 * @var array
	 */
	private $consonantsLetters = array('q', 'w', 'r', 't', 'p', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm');


	function __construct($TheKadeshiDir) {
		$this->scanResults = array();

		$this->namePatterns = array(
			"/^[" . implode($this->consonantsLetters) . strtoupper(implode($this->consonantsLetters)) . "]{5}/i",
			"/^[a-zA-Z]{3,8}\d{1,8}/i",
			"/^\d{1,}[a-zA-Z]{1,3}\d{1,}/i",
			"/^[a-zA-Z]\d{4,}/i",
			"/^\d[a-zA-Z0-9]{1.}/i",
			"/^[a-zA-Z]\d{3,}[a-zA-Z]$/i",
			"/^\S+\d+\S+\d+\S+?/i"
		);

	}

	/**
	 * Инициализация
	 */
/*
	public function Init() {



		switch($this->SignatureFile) {
			case 'local':
				$this->SignaturesDir = parent::$TheKadeshiDir . '/signatures';
				break;
			default:
				$this->SignaturesDir = 'http://thekadeshi.bagdad.tmweb.ru/signatures';
				break;
		}

		$this->ChekSumDir = parent::$TheKadeshiDir . "/checksum";
		if(!is_dir($this->ChekSumDir)) {
			mkdir($this->ChekSumDir);
		}
		//if(!is_dir(parent::$TheKadeshiDir)) {
		//	mkdir(parent::$TheKadeshiDir);
		//}

		$this->LoadRules();
	}
*/
	/**
	 * Функция получения списка правил из каталога
	 *
	 * @return mixed
	 */
/*
	private function GetSignaturesFiles() {
		$list = null;
		$filesList = scandir($this->SignaturesDir);

		foreach ($filesList as $file) {
			if ($file != '.' && $file != '..') {
				$list[] = $file;
			}
		}

		$this->SignaturesFileList = $list;

		return $list;
	}
*/

	/**
	 * Функция загрузки правил определения CMS
	 *
	 * @return array
	 *
	 * @todo На какое-то время это можно убрать.
	 */
/*
	private function LoadRules() {
		$rules = array();

		$fileName = $this->SignaturesDir . '/' . 'database.xml';

		if(file_exists($fileName)) {

			$xml = simplexml_load_file($fileName, 'SimpleXMLElement', LIBXML_NOCDATA);

			foreach ($xml[0] as $item) {
				$name = trim($item->name[0]);
				$signature = trim($item->signature[0]);
				$action = trim($item->action[0]);

				$this->Signatures[] = array(
					'name' => $name, 'signature' => $signature, 'action' => $action
				);
			}

			$this->RulesList = $rules;
		}

		return $rules;
	}
*/
	public function Scan($fileName, $needChecksum = true) {

		//echo($fileName . "<br />\r\n");

		$this->scanResults = array();
		$heuristicScanResult = 0;
		$fileCheckSum = false;

		if($needChecksum) {
			$fileCheckSum = $this->CompareFileCheckSum($fileName);
		}

		if($fileCheckSum !== true) {
			
			$heuristicScanResult = $this->Heuristic($fileName);

			if ($heuristicScanResult > 1) {

					$this->scanResults = $this->ScanContent($fileName);

			} else {
				if($needChecksum) {
					$this->SetFileCheckSum($fileName);
				}
			}
		}

		return (!empty($this->scanResults))?$this->scanResults:(($heuristicScanResult>1)?$heuristicScanResult:null);
	}

	/**
	 * Функция создания контрольной суммы файла
	 * @param $fileName
	 * @return int
	 */
	public function SetFileCheckSum($fileName) {
		$currentFileCheckSum = md5_file($fileName);
		$currentCheckSumPath = TheKadeshi::$CheckSumDir;

		$realFileName = pathinfo($fileName);
		$subdirSplitPath = mb_split("/", str_replace("\\", "/", $realFileName['dirname']));
		foreach($subdirSplitPath as $pathElement) {
			$catalogCode = mb_substr(strtolower(trim($pathElement, ":;.\\/|'\"?<>,")), 0, 2);
			$currentCheckSumPath .= "/" . $catalogCode;
			if(!is_dir($currentCheckSumPath)) {
				mkdir($currentCheckSumPath);
			}
		}
		$checkSumContent = array(
			'folder'=>$realFileName['dirname'],
			'filename'=>$realFileName['basename'],
			'date' => filemtime($fileName),
		    'size' => filesize($fileName),
			'checksum' => $currentFileCheckSum

		);
		$checkSumFileName = $currentCheckSumPath . "/" . $realFileName['basename'] . ".json";
		if(file_exists($checkSumFileName)) {
			unlink($checkSumFileName);
		}
		$creationResult = file_put_contents($checkSumFileName, json_encode($checkSumContent));
		return $creationResult;
	}

	public function GetFileCheckSum($fileName) {
		$checkSumContent = false;
		$currentCheckSumPath = $this->ChekSumDir;
		$realFileName = pathinfo($fileName);
		$subdirSplitPath = mb_split("/", str_replace("\\", "/", $realFileName['dirname']));
		foreach($subdirSplitPath as $pathElement) {
			$catalogCode = mb_substr(strtolower(trim($pathElement, ":;.\\/|'\"?<>,")), 0, 2);
			$currentCheckSumPath .= "/" . $catalogCode;
		}

		$checkSumFileName = $currentCheckSumPath . "/" . $realFileName['basename'] . ".json";
		if(file_exists($checkSumFileName)) {
			$checkSumContent  = json_decode(file_get_contents($checkSumFileName), true);
		}

		return $checkSumContent;
	}

	/**
	 * Функция сравнения контрольных сумм
	 * @param $fileName
	 * @return bool
	 */
	public function CompareFileCheckSum($fileName) {
		$savedCheckSum = $this->GetFileCheckSum($fileName);
		if($savedCheckSum === false) {
			return false;
		}
		//print_r($savedCheckSum);
		$realFileName = pathinfo($fileName);
		$currentFileCheckSum = md5_file($fileName);
		$checkSumContent = array(
			'folder'=>$realFileName['dirname'],
			'filename'=>$realFileName['basename'],
			'date' => filemtime($fileName),
		    'size' => filesize($fileName),
			'checksum' => $currentFileCheckSum
		);
		$checkDiff = array_diff($savedCheckSum, $checkSumContent);

		if(!empty($checkDiff)) {
			return true;
		}
		return false;
	}

	private function ScanContent($fileName) {
		$scanResults = null;
		$content = $this->GetFileContent($fileName);

		if ($content !== false && strlen($content) > 0) {

			$content = mb_convert_encoding($content, "utf-8");
			foreach ($this->Signatures as $virusSignature) {

				preg_match($virusSignature['signature'], $content, $results);

				if (isset($results) && !empty($results)) {
					//print_r($results);
					$files[] = array('file' => '', 'action' => $virusSignature['action']);
					$scanResults = array(
						'file' => $this->realFileName, 'name' => $virusSignature['name'], 'positions' => array(
							'start' => mb_strpos($content, $results[0]), 'length' => mb_strlen($results[0])
						), //'content' => $results[0],
						'action' => $virusSignature['action']
					);
				}
				$content = preg_replace($virusSignature['signature'], '', $content);

			}
		}
		return $scanResults;
	}

	/**
	 * Функция эвристического анализа содержимого файла
	 * @param $fileName string Имя файла для анализа
	 * @return float Результат сканирования. Чем больше значение, тем более стремным выглядит файл
	 */
	public function HeuristicFileContent($fileName) {
		$suspicion = 0.0;

		//echo($fileName . "<br>\r\n");

		$fileContent = mb_convert_encoding(file_get_contents($fileName), "utf-8");

		//Проверка на длинные слова
		$pregResult = preg_match_all('/\$?\w+/i', $fileContent, $wordMatches);
		if($pregResult !== false) {
			//print_r(array_unique($wordMatches[0]));
			foreach(array_unique($wordMatches[0]) as $someWord) {
				if (strlen($someWord) >= 25) {
					if(mb_substr($someWord, 0, 1) != '$') {
						//  Чем длиннее слово, тем больше подозрение
						if($someWord != strtoupper($someWord)) {
							$suspicion = $suspicion + 0.001 * strlen($someWord);
							//echo($someWord . " " . $suspicion . "\r\n");
						}
					}
				}

				//  Если слово - переменная
				if(mb_substr($someWord, 0, 1) == '$') {
					//print_r($someWord);
					//  Проверка переменных на стремные именования
					foreach ($this->namePatterns as $namePattern) {
						$checkResult = preg_match($namePattern, mb_substr($someWord, 1));
						if ($checkResult == 1) {
							$suspicion = $suspicion + 0.01;
							//echo $someWord . " - " . $namePattern . "\r\n";
						}
					}

					//  Проверка переменных на частые использования в виде массивов
					//$arrayPattern = '/\\' . $someWord . '\[[\'"]?\d+[\'"]?\]/i';
					$arrayPattern = '/\\' . $someWord . '\[[\'"]?[\d\S]+[\'"]?\](\[\d+\])?/i';
					//echo($arrayPattern . "\r\n");
					$arrayCheckResult = preg_match_all($arrayPattern, $fileContent, $arrayPatternMatches);
					if($arrayCheckResult !== false) {

						$variableUsages = count(array_unique($arrayPatternMatches[0]));
						if($variableUsages > 6) {
							$suspicion = $suspicion + (0.2 + $variableUsages);
						}
						//print_r($arrayPatternMatches);
					}

				}
			}
		}

		//  eval в коде выглядит очень подозрительно
		if(mb_strpos($fileContent, "eval")) {
			$evlFileterPattern = '/eval.+?\(/i';
			$evlCheckResult = preg_match_all($evlFileterPattern, $fileContent, $evlMatches);
			if($evlCheckResult !== false) {
				$suspicion = $suspicion + 1 * count($evlMatches[0]);
			}
			unset($evlMatches);
		}

		//  base64 тоже вызывает некоторые подозрения
		if(mb_strpos($fileContent, "base64_decode")) {
			$baseFilterPattern = '/base64_decode.+?\(/i';
			$baseCheckResult = preg_match_all($baseFilterPattern, $fileContent, $baseMatches);
			if($baseCheckResult !== false) {
				$suspicion = $suspicion + 0.4 * count($baseMatches[0]);
			}
			unset($baseMatches);
		}

		//  str_rot13 может использоваться для маскировки
		if(mb_strpos($fileContent, "str_rot13")) {
			$rotFilterPattern = '/str_rot13.+?\(/i';
			$rotCheckResult = preg_match_all($rotFilterPattern, $fileContent, $rotMatches);
			if($rotCheckResult !== false) {
				$suspicion = $suspicion + 0.3 + count($rotMatches[0]);
			}
			unset($rotMatches);
		}


		return $suspicion;
	}

	/**
	 * Эвристический алгоритм проверки файла
	 *
	 * @param $fileName string
	 */
	public function HeuristicFileName($fileName) {
		$suspicion = 0.0;

		$fileData = pathinfo($fileName);

		//  Проверка имени файла, не выглядит ли оно стремным
		foreach ($this->namePatterns as $filenamePattern) {
			$checkResult = preg_match($filenamePattern, $fileData['basename']);
			if ($checkResult == 1) {
				$suspicion = $suspicion + 0.5;
				//echo $fileData['basename'] . " - " . $filenamePattern . "\r\n";
			}
		}

		//print_r($suspicion);

		return $suspicion;
	}

	public function Heuristic($filename) {
		$totalSuspicion = 0;

		$fileNameSuspicion = $this->HeuristicFileName($filename);
		$fileContentSuspicion = $this->HeuristicFileContent($filename);

		$totalSuspicion = $fileNameSuspicion + $fileContentSuspicion;

		return $totalSuspicion;
	}
}

class Status {

	private $kadeshiDir;

	private $selfStatus;

	function __construct($folder) {
		$this->kadeshiDir = $folder;
	}

	public function FirewallEvent() {
		$firewallLogsFile = $this->kadeshiDir . "/" . ".firewall";
		$firewall_logs = array();
		if(is_file($this->kadeshiDir . "/" . ".firewall")) {
			$firewall_logs = json_decode(file_get_contents($firewallLogsFile), true);
		}
		$firewall_logs[] = date("Y-m-d H:i:s.u");
		file_put_contents($firewallLogsFile, json_encode($firewall_logs));
	}
	
	public function ReportDate() {
		$report = array(
			'firewall' => array(),
			'quarantine' => array(),
		);
	}

	public function Ping() {
		$pingStatus = array(
			'date' => date("Y-m-d H:i:s"),
			'status' => 'online'
		);
		$this->selfStatus['ping'] = $pingStatus;
	}

	public function writeStatus() {
		file_put_contents($this->kadeshiDir . "/" . ".status", json_encode($this->selfStatus));
	}

	public function Output() {
		if(is_file($this->kadeshiDir . "/" . ".status")) {
			echo(file_get_contents($this->kadeshiDir . "/" . ".status"));
		} else {
			$this->writeStatus();
		}
	}
}

/**
 * Класс лекарь
 */
class Healer {

	/**
	 * Анамнез
	 * @var array
	 */
	public $Anamnesis;

	/**
	 * Каталог кеша
	 * @var string
	 */
	private $TheKadeshiDir = '';
	
	private $QuarantineDir = "";

	function __construct()
	{
		$this->TheKadeshiDir = __DIR__ . "/.thekadeshi";
		
		$this->QuarantineDir = $this->TheKadeshiDir . "/quarantine";

		$this->Anamnesis = array();
		if(is_file('kadeshi.anamnesis.json')) {
			$this->GetAnamnesis();
			if (!empty($this->Anamnesis)) {
				//cure
			}
		}
	}

	public function GetAnamnesis() {
		$anamnesisContent = file_get_contents('kadeshi.anamnesis.json');
		$this->Anamnesis = json_decode($anamnesisContent, true);
	}

	public function Cure($infectedElement) {
		$filePath = $infectedElement['file']['dirname'] . '/' . $infectedElement['file']['basename'];
		$cureAction = $infectedElement['action'];
		switch(mb_strtolower($cureAction)){
			case 'delete':
				$unlinkResult = unlink($filePath);
				// @todo Поставить полноценную проверку на удаление. Иначе хана :)
				if($unlinkResult === false) {
					chmod($filePath, 0600);
					unlink($filePath);
				}
				break;
			case 'cure':
				// @todo Описать этот момент тестами
				$fileContent = file_get_contents($filePath);
				$fileParts[0] = mb_substr($fileContent, 0, $infectedElement['positions']['start']);
				$fileParts[1] = mb_substr($fileContent, $infectedElement['positions']['start'] + $infectedElement['positions']['length']);
				file_put_contents($filePath, $fileParts[0] . $fileParts[1]);
				break;
		}
	}

	public function Quarantine($sourceFile, $originalFileName = null) {
		if(!is_dir($this->QuarantineDir)) {
			mkdir($this->QuarantineDir);
		}
		if(!is_null($originalFileName)) {
			$fileName = $originalFileName;
		} else {
			$fileInfo = pathinfo($sourceFile);
			$fileName = $fileInfo['basename'];
		}
		$quarantineFileName = date("Y-m-d-H-i-s-u") . ".json";
		//echo($quarantineFileName . "<br/>\r\n");
		$quarantineFile = array(
			'content' => base64_encode(file_get_contents($sourceFile)),
			'original' => $fileName,
			'handler' => $_SERVER['SCRIPT_FILENAME'],
			'date' => date("Y-m-d H:i:s"),
			'request' => array (
				'get' => $_GET,
				'post' => $_POST,
				'files' => $_FILES,
			    'request' => $_REQUEST,
				'cookies' => $_COOKIE,
				'session' => $_SESSION
			)
		);
		$quarantineResult = file_put_contents($this->QuarantineDir . "/" . $quarantineFileName, json_encode($quarantineFile));
		

		if($quarantineResult) {
			unlink($sourceFile);
		}

	}
}

/**
 * Класс для работы с консолью
 */
class Console {

	/**
	 * Цвет текста в консоли
	 * @var array
	 */
	public $Color;

	/**
	 * Флаг, требуется ли вывод
	 * @var boolean
	 */
	private $IsVerbose;

	/**
	 * Console constructor.
	 * @param $Verbose boolean
	 */
	function __construct($Verbose) {

		$this->IsVerbose = $Verbose;

		$this->Color = array(
			'grey'      =>  chr(27) . "[31;30m",
			'blue'      =>  chr(27) . "[30;34m",
			'green'     =>  chr(27) . "[30;32m",
			'red'       =>  chr(27) . "[30;31m",
			'normal'    =>  chr(27) . "[0m",
		);
	}

	/**
	 * Функция вывода текста в консоль
	 * @param $Message string
	 */
	public function Log($Message) {
		if($this->IsVerbose === true) {
			echo($Message . "\r\n");
		}
	}
}

//@todo надо отрефакторить эту фигню
$signaturesBase = 'remote';
define('THEKADESHI_DIR', __DIR__ . "/.thekadeshi");

// Первоначальная установка
/*
if(isset($_SERVER['SERVER_NAME'])) {
	if(isset($_SERVER['REQUEST_URI'])) {
		if(strpos($_SERVER['REQUEST_URI'], 'thekadeshi.php')) {
			//print_r($_SERVER);

			$key = array(
				'url' => $_SERVER['SERVER_NAME'],
			    'installed' => date("Y-m-d H:i:s"),
				'reports' => array(
					'send' => true,
					'timeout' => 60
				)
			);

			if(!is_dir(THEKADESHI_DIR)) {
				mkdir(THEKADESHI_DIR);
			}

			if(!file_exists(THEKADESHI_DIR . "/.options")) {
				file_put_contents(THEKADESHI_DIR . "/.options", json_encode($key));
			}

			if(!isset($_REQUEST['ping'])) {
				header("location: /");
				exit();
			}
		}

	}
}
*/


$healer = new Healer();

// Статистика
$Status = new Status(THEKADESHI_DIR);
$Status->Ping();
$Status->writeStatus();

$theKadeshi = new TheKadeshi();

if(!empty($_REQUEST)) {
	if(isset($_REQUEST['ping'])) {
		$Status->Output();
		exit();
	}
	echo("<!--\r\n");
	print_r($_SERVER);
	echo("-->\r\n");
	if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'thekadeshi.php')) {
		// Инсталляция, если запущен из браузера без параметров

		$theKadeshi->Install($_SERVER['SERVER_NAME']);
		echo(base64_decode($theKadeshi::ProtectedPage));
		exit();
	}
}

if($argc > 1) {
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
	$currentAction = 'scan';
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

		$theKadeshi->GetRemoteConfig($_SERVER['SERVER_NAME']);

		if(!empty($_FILES)) {
			foreach ($_FILES as $fileToScan) {
				//print_r($fileToScan['tmp_name']);
				$fileScanResults = $scanner->Scan($fileToScan['tmp_name'], false);
				if(!empty($fileScanResults)) {
					$healer->Quarantine($fileToScan['tmp_name'], $fileToScan['name']);
					$Status->FirewallEvent();
				}
				
			}
		}

		$fileToCheck = $_SERVER['SCRIPT_FILENAME'];
		//print_r($fileToCheck);
		$fileScanResults = $theKadeshi->Scanner->Scan($fileToCheck);
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
		//print_r($fileScanResults);
		break;

	default:    //  Действие по умолчанию
		$Console->Log("Current action: " . $Console->Color['green'] . "Scanning" . $Console->Color['normal'] );
		if($signaturesBase == 'local') {
			$Console->Log("Signature file: " . $Console->Color['blue'] . "local" . $Console->Color['normal'] );
		} else {
			$Console->Log("Signature file: " . $Console->Color['blue'] . "remote" . $Console->Color['normal'] );
		}


		//$scanner = new Scanner();
		//$scanner->SignatureFile = $signaturesBase;
		//$scanner->Init();

		//$filelist = new FileList();

		if(!isset($fileToScan)) {
			$theKadeshi->GetFileList(__DIR__);
		} else {
			$theKadeshi->fileList = $fileToScan;
		}

		foreach ($theKadeshi->fileList as $file) {

			$fileScanResults = $theKadeshi->Scanner->Scan($file);
			if ($fileScanResults != null) {
				$scanResults[] = $fileScanResults;

				$Console->Log($fileScanResults['file']['dirname'] . '/' . $fileScanResults['file']['basename'] . ' infection: ' . $Console->Color['red'] . $fileScanResults['name'] . $Console->Color['normal'] . " action: " . $Console->Color['blue'] . $fileScanResults['action'] . $Console->Color['normal'] );
			}
		}
		if(!empty($scanResults)) {
			//for
			$encodedResults = json_encode($scanResults);
			$resultsFile = file_put_contents(THEKADESHI_DIR . "/kadeshi.anamnesis.json", $encodedResults);
		}
		break;
}