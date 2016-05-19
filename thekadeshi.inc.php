<?php

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
	 * Список подозрительных функций
	 * @var array
	 */
	private $dangerousFunctions = array('eval', 'assert', 'base64_decode', 'str_rot13', 'mail',
										'move_uploaded_file', 'is_uploaded_file', 'script',
										'fopen', 'curl_init', 'document.write', '$GLOBAL',
										'passthru', 'system', 'exec');

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
						//print_r($suspicion);
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

		foreach ($this->dangerousFunctions as $dangerousFunction) {
			$functionCount =  mb_substr_count($fileContent, $dangerousFunction);
			$suspicion = $suspicion + (1 * $functionCount);
			if($suspicion > 1) {
				return $suspicion;
			}
		}

		if ($suspicion == 0) {
			//Проверка на длинные слова
			$pregResult = preg_match_all('/\$?\w+/i', $fileContent, $wordMatches);
			if ($pregResult !== false) {;
				foreach (array_unique($wordMatches[0]) as $someWord) {
					if (strlen($someWord) >= 25) {
						if (mb_substr($someWord, 0, 1) != '$') {
							//  Чем длиннее слово, тем больше подозрение
							if ($someWord != strtoupper($someWord)) {
								$suspicion = $suspicion + 0.01 * strlen($someWord);
								//echo($someWord . " " . $suspicion . "\r\n");
							}
						}
					}

					//  Если слово - переменная
					if (mb_substr($someWord, 0, 1) == '$') {
						//  Проверка переменных на стремные именования
						foreach ($this->namePatterns as $namePattern) {
							$checkResult = preg_match($namePattern, mb_substr($someWord, 1));
							if ($checkResult == 1) {
								$suspicion = $suspicion + 0.01;
							}
						}

						//  Проверка переменных на частые использования в виде массивов
						$arrayPattern = '/\\' . $someWord . '\[[\'"]?[\d\S]+[\'"]?\](\[\d+\])?/i';
						//echo($arrayPattern . "\r\n");
						$arrayCheckResult = preg_match_all($arrayPattern, $fileContent, $arrayPatternMatches);
						if ($arrayCheckResult !== false) {

							$variableUsages = count(array_unique($arrayPatternMatches[0]));
							if ($variableUsages > 4) {
								$suspicion = $suspicion + (0.2 + $variableUsages);
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
