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
	private $ValidExtensions = array ('php', 'php4', 'php5', 'php7', 'js', 'css', 'html', 'htm', 'tpl');

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
		echo("Signatures request\r\n");
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

class Scanner {

	/**
	 * Где брать файл сигнатур
	 * @var string
	 */
	public $SignatureFile = 'remote';

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


	/**
	 * Scanner constructor.
	 */
	function __construct() {
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
	 * Функция управления сканированием
	 * @param $fileName
	 * @param bool $needChecksum
	 * @return array|float|int|null
	 */
	public function Scan($fileName, $needChecksum = true) {

		$suspicion = array();

		//$scanResults = array();
		$heuristicScanResult = 0;
		$fileCheckSum = false;

		//if(TheKadeshi::$WorkWithoutSelfFolder == true) {
			//$needChecksum = false;
		//}

		if($needChecksum) {
			$fileCheckSum = $this->CompareFileCheckSum($fileName);
		}

		if($fileCheckSum !== true) {

			if(is_array($fileCheckSum)) {
				$suspicion['checksum'] = $fileCheckSum;
			}
			
			$heuristicScanResult = $this->Heuristic($fileName);
			//echo($fileName . " : " . $heuristicScanResult . "; \r\n");

			if ($heuristicScanResult >= 1) {

				$suspicion['heuristic'] = $heuristicScanResult;
				$suspicion['file_original'] = base64_encode(gzcompress(file_get_contents($fileName), 9));

				if(!empty(TheKadeshi::$signatureDatabase)) {
					$scanResults = $this->ScanContent($fileName);
					if(!empty($scanResults)) {
						$suspicion['scanner'] = $scanResults;

						if($suspicion['scanner']['action'] == 'cure') {
							// @todo Описать этот момент тестами
							$fileContent = file_get_contents($fileName);
							$fileParts[0] = mb_substr($fileContent, 0, $suspicion['scanner']['positions']['start']);
							$fileParts[1] = mb_substr($fileContent, $suspicion['scanner']['positions']['start'] + $suspicion['scanner']['positions']['length']);
							$fixedContent = $fileParts[0] . $fileParts[1];
							$suspicion['file_fixed'] = base64_encode(gzcompress($fixedContent, 9));
							file_put_contents($fileName, $fixedContent);
						}
						if($suspicion['scanner']['action'] == 'delete') {
							unlink($fileName);
						}
					}

					//print_r($this->scanResults);
				}


			} else {
				if($needChecksum) {
					$this->SetFileCheckSum($fileName);
				}
			}
		}

		if(!empty($suspicion)) {
			//print_r(array($suspicion, $fileName));
			$this->SaveAnamnesis($fileName, $suspicion);
			//print_r();

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
		$subdirSplitPath = mb_split("/", str_replace("\\", "/", strtolower($realFileName['dirname'])));
		foreach($subdirSplitPath as $pathElement) {
			$catalogCode = mb_substr(trim($pathElement, ":;.\\/|'\"?<>,"), 0, 2);
			$currentCheckSumPath .= "/" . $catalogCode;

		}
		if(!is_dir($currentCheckSumPath)) {
			mkdir($currentCheckSumPath, 0755, true);
		}
		$checkSumContent = array(
			'folder'=>$realFileName['dirname'],
			'filename'=>$realFileName['basename'],
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
		$currentCheckSumPath = TheKadeshi::$CheckSumDir;
		$realFileName = pathinfo($fileName);
		$subdirSplitPath = mb_split("/", str_replace("\\", "/", strtolower($realFileName['dirname'])));
		foreach($subdirSplitPath as $pathElement) {
			$catalogCode = mb_substr(trim($pathElement, ":;.\\/|'\"?<>,"), 0, 2);
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

		$currentFileCheckSum = md5_file($fileName);
		
		if($savedCheckSum['checksum'] == $currentFileCheckSum) {
			return true;
		}
		return array(
			'old' => $savedCheckSum['checksum'],
			'current' => $currentFileCheckSum
		);
	}

	/**
	 * Основная фнкция сканирования по базе сигнатур
	 * @param $fileName
	 * @return array|null
	 */
	private function ScanContent($fileName) {
		$scanResults = null;
		$content = file_get_contents($fileName);

		if ($content !== false && strlen($content) > 0) {

			$content = mb_convert_encoding($content, "utf-8");
			foreach (TheKadeshi::$signatureDatabase as $virusSignature) {

				preg_match($virusSignature['expression'], $content, $results);

				if (isset($results) && !empty($results)) {

					//$files[] = array('file' => '', 'action' => $virusSignature['action']);
					$scanResults = array(
						'file' => $this->realFileName,
						'name' => $virusSignature['title'],
						'id' => $virusSignature['id'],
						'positions' => array(
							'start' => mb_strpos($content, $results[0]),
							'length' => mb_strlen($results[0])
						),
						'action' => $virusSignature['action']
					);
				}
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

		$fileContent = mb_convert_encoding(file_get_contents($fileName), "utf-8");

		//  eval в коде выглядит очень подозрительно
		$evalCount = mb_substr_count($fileContent, 'eval');
		$suspicion = $suspicion + 1 * $evalCount;

		if($suspicion == 0) {

			//  base64 тоже вызывает некоторые подозрения
			$baseCount = mb_substr_count($fileContent, 'base64_decode');
			$suspicion = $suspicion + 1 * $baseCount;

			if ($suspicion == 0) {

				//  str_rot13 может использоваться для маскировки
				$rotCount = mb_substr_count($fileContent, 'str_rot13');
				$suspicion = $suspicion + 1 * $rotCount;

				if ($suspicion == 0) {
					//Проверка на длинные слова
					$pregResult = preg_match_all('/\$?\w+/i', $fileContent, $wordMatches);
					if ($pregResult !== false) {
						//print_r(array_unique($wordMatches[0]));
						foreach (array_unique($wordMatches[0]) as $someWord) {
							if (strlen($someWord) >= 25) {
								if (mb_substr($someWord, 0, 1) != '$') {
									//  Чем длиннее слово, тем больше подозрение
									if ($someWord != strtoupper($someWord)) {
										$suspicion = $suspicion + 0.001 * strlen($someWord);
										//echo($someWord . " " . $suspicion . "\r\n");
									}
								}
							}

							//  Если слово - переменная
							if (mb_substr($someWord, 0, 1) == '$') {
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
								if ($arrayCheckResult !== false) {

									$variableUsages = count(array_unique($arrayPatternMatches[0]));
									if ($variableUsages > 6) {
										$suspicion = $suspicion + (0.2 + $variableUsages);
									}
									//print_r($arrayPatternMatches);
								}

							}
						}
					}
				}
			}
		}
		
		return $suspicion;
	}

	/**
	 * Эвристический алгоритм проверки файла
	 *
	 * @param $fileName string
	 * @return float
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
		$fileNameSuspicion = 0;
		
		$fileContentSuspicion = $this->HeuristicFileContent($filename);
		if($fileContentSuspicion == 0) {
			$fileNameSuspicion = $this->HeuristicFileName($filename);
		}
		
		$totalSuspicion = $fileNameSuspicion + $fileContentSuspicion;

		return $totalSuspicion;
	}

	/**
	 * Функция записи анамнеза в файл
	 * @param $fileName
	 * @param $suspicion
	 */
	public function SaveAnamnesis($fileName, $suspicion) {
		$anamnesisContent = array();

		if(is_file(TheKadeshi::$AnamnesisFile)) {
			$anamnesisContent = json_decode(file_get_contents(TheKadeshi::$AnamnesisFile), true);
		}

		$anamnesisContent[] = array(
			'date' => date("Y-m-d H:i:s"),
			'file' => $fileName,
			'suspiction' => $suspicion,
		);

		file_put_contents(TheKadeshi::$AnamnesisFile, json_encode($anamnesisContent));
	}

	public function SendAnamnesis() {
		if(file_exists(TheKadeshi::$AnamnesisFile)) {

			$anamnesisContent = json_decode(file_get_contents(TheKadeshi::$AnamnesisFile), true);
			$sendResult = TheKadeshi::ServiceRequest('sendAnamnesis', array('anamnesis' => $anamnesisContent));

			$jsonResult = json_decode($sendResult, true);
			if($jsonResult['success'] == true) {
				unlink(TheKadeshi::$AnamnesisFile);
			}
			//print_r($jsonResult);
			//file_put_contents("result.html", $sendResult);
			//print_r($sendResult);
		}
	}
}

class Status {

	private $StatusFile;

	private $StatusContent;

	//private $AnamnesisFile;

	function __construct() {
		$this->StatusFile = TheKadeshi::$TheKadeshiDir . '/' . '.status';
		//$this->AnamnesisFile = TheKadeshi::$TheKadeshiDir . '/' . '.anamnesis';
		if(file_exists($this->StatusFile)) {
			$this->StatusContent = json_decode(file_get_contents($this->StatusFile), true);
		}
		
		$this->Action();
	}

	public function FirewallEvent() {
		$firewallLogsFile = TheKadeshi::$TheKadeshiDir . "/" . ".firewall";
		$firewall_logs = array();
		if(is_file(TheKadeshi::$TheKadeshiDir . "/" . ".firewall")) {
			$firewall_logs = json_decode(file_get_contents($firewallLogsFile), true);
		}
		$firewall_logs[] = date("Y-m-d H:i:s.u");
		file_put_contents($firewallLogsFile, json_encode($firewall_logs));
	}
	
	public function Ping() {
		$this->StatusContent['ping'] = array(
			'date' => date("Y-m-d H:i:s"),
			'status' => 'online'
		);

		$this->writeStatus();

		$pingResult = TheKadeshi::ServiceRequest('sendPing', array('data' => $this->StatusContent));
		if($pingResult) {
			$isErrors = json_decode($pingResult, true);

			if($isErrors['errors'] == false) {
				unlink($this->StatusFile );
			}
		}
	}

	/**
	 * Функция записи счетчика вызова скрипта
	 *
	 */
	private function Action() {
		$currentHit = isset($this->StatusContent['action']['hit'])?$this->StatusContent['action']['hit']:0;
		$currentHit++;
		$this->StatusContent['action'] = array('date' => date("Y-m-d H:i:s"), 'hit' => $currentHit);
		$this->writeStatus();
	}

	public function writeStatus() {
		file_put_contents($this->StatusFile, json_encode($this->StatusContent));
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
	//private $TheKadeshiDir = '';
	
	private $QuarantineDir = "";

	function __construct() {
		//$this->TheKadeshiDir = __DIR__ . "/.thekadeshi";
		
		//$this->QuarantineDir = $this->TheKadeshiDir . "/quarantine";
/*
		$this->Anamnesis = array();
		if(is_file(TheKadeshi::$AnamnesisFile)) {
			$this->GetAnamnesis();
			if (!empty($this->Anamnesis)) {
				$this->Cure();
			}
		}
*/
	}

	public function GetAnamnesis() {
		$anamnesisContent = file_get_contents(TheKadeshi::$AnamnesisFile);
		$this->Anamnesis = json_decode($anamnesisContent, true);
	}

	public function Cure($infectedElement) {
		foreach ($this->Anamnesis as $anamnesisElement) {
			//
		}
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

	/**
	 * Функция карантина
	 * @param $sourceFile
	 * @param null $originalFileName
	 */
	public function Quarantine($sourceFile, $originalFileName = null) {
		if(!is_dir($this->QuarantineDir)) {
			mkdir($this->QuarantineDir, 0755, true);
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

$healer = new Healer();

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
				$fileScanResults = $scanner->Scan($fileToScan['tmp_name'], false);
				if(!empty($fileScanResults)) {
					$healer->Quarantine($fileToScan['tmp_name'], $fileToScan['name']);
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
		$Console->Log("Current action: " . $Console->Color['green'] . "Scanning" . $Console->Color['normal'] );
		if($signaturesBase == 'local') {
			$Console->Log("Signature file: " . $Console->Color['blue'] . "local" . $Console->Color['normal'] );
		} else {
			$Console->Log("Signature file: " . $Console->Color['blue'] . "remote" . $Console->Color['normal'] );
		}

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