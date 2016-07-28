<?php /* */

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
		'passthru', 'system', 'exec', 'header', 'preg_replace',
		'fromCharCode', '$_COOKIE', '$_POST', 'copy', 'navigator',
		'$_REQUEST', 'array_filter', 'str_replace');

	public $signatureLog = array();

	private $AnamnesisContent = array();

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

		if($needChecksum) {
			$fileCheckSum = $this->CompareFileCheckSum($fileName);
		}

		if($fileCheckSum !== true) {

			if(is_array($fileCheckSum)) {
				$suspicion['checksum'] = $fileCheckSum;
			}

			$heuristicScanResult = $this->Heuristic($fileName);

			if ($heuristicScanResult >= 1) {

				$suspicion['heuristic'] = $heuristicScanResult;

				if(count(TheKadeshi::getSignatureDatabase()) !== 0) {
					$scanResults = $this->ScanContent($fileName);
					if(count($scanResults) !== 0) {
						$suspicion['scanner'] = $scanResults;
						$filePermits = decoct(fileperms($fileName) & 0777);

						chmod($fileName, 0666);

						if($suspicion['scanner']['action'] === 'cure') {
							// @todo Описать этот момент тестами
							$fileContent = file_get_contents($fileName);
							$fileParts[0] = mb_substr($fileContent, 0, $suspicion['scanner']['positions']['start']);
							$fileParts[1] = mb_substr($fileContent, $suspicion['scanner']['positions']['start'] + $suspicion['scanner']['positions']['length']);
							$fixedContent = $fileParts[0] . $fileParts[1];
							$suspicion['file_fixed'] = base64_encode(gzcompress($fixedContent, 9));
							file_put_contents($fileName, $fixedContent);
							/*
							chmod($fileName, $filePermits);
							*/
						}
						if($suspicion['scanner']['action'] === 'delete') {
							unlink($fileName);
						}
						if($suspicion['scanner']['action'] === 'quarantine') {
							rename($fileName, $fileName . '.kdsh.suspected');
							/*
							chmod($fileName, $filePermits);
							*/
						}
						//print_r($scanResults);
						$this->AnamnesisContent[] = $scanResults;
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
			//$this->SaveAnamnesis($fileName, $suspicion);
			//print_r();

		}

		return (!empty($suspicion))?$suspicion:(($heuristicScanResult>1)?$heuristicScanResult:null);
	}

	/**
	 * Функция создания контрольной суммы файла
	 * @param $fileName
	 * @return int
	 */
	public function SetFileCheckSum($fileName) {
		$currentFileCheckSum = md5_file($fileName);
		$currentCheckSumPath = TheKadeshi::getCheckSumDir();

		$realFileName = pathinfo($fileName);
		$subdirSplitPath = mb_split('/', str_replace("\\", '/', strtolower($realFileName['dirname'])));
		foreach($subdirSplitPath as $pathElement) {
			$catalogCode = mb_substr(trim($pathElement, ":;.\\/|'\"?<>,"), 0, 2);
			$currentCheckSumPath .= '/' . $catalogCode;

		}
		if(!is_dir($currentCheckSumPath)) {
			mkdir($currentCheckSumPath, 0755, true);
		}
		$checkSumContent = array(
			'folder'=>$realFileName['dirname'],
			'filename'=>$realFileName['basename'],
			'checksum' => $currentFileCheckSum

		);
		$checkSumFileName = $currentCheckSumPath . '/' . $realFileName['basename'] . '.json';
		if(file_exists($checkSumFileName)) {
			unlink($checkSumFileName);
		}
		$creationResult = file_put_contents($checkSumFileName, json_encode($checkSumContent));
		return $creationResult;
	}

	public function GetFileCheckSum($fileName) {
		$checkSumContent = false;
		$currentCheckSumPath = TheKadeshi::getCheckSumDir();
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

			$signatureArray = TheKadeshi::getSignatureDatabase();
			if(function_exists('hash')) {
				$contentHash = hash('sha256', $content);

				if(array_key_exists('h', $signatureArray)) {
					foreach ($signatureArray['h'] as $virusSignature) {
						if ($contentHash === $virusSignature['expression']) {
							$scanResults = array(
								'file' => $fileName, 'name' => $virusSignature['title'], 'id' => $virusSignature['id'], 'action' => $virusSignature['action']
							);
						}
					}
				}
			}

			if(count($scanResults) === 0) {
				$content = mb_convert_encoding($content, 'utf-8');

				if(array_key_exists('r', $signatureArray)) {
					foreach ($signatureArray['r'] as $virusSignature) {

						$scanStartTime = microtime(true);

						preg_match($virusSignature['expression'], $content, $results);

						if (($results !== null) && (count($results) !== 0)) {

							//$files[] = array('file' => '', 'action' => $virusSignature['action']);
							$scanResults = array(
								'file' => $fileName, 'name' => $virusSignature['title'], 'id' => $virusSignature['id'], 'date' => gmdate('Y-m-d H:i:s'), 'positions' => array(
									'start' => mb_strpos($content, $results[0]), 'length' => mb_strlen($results[0])
								), 'action' => $virusSignature['action']
							);
							//  Сомнительная фича
							break;
						}

						$scanEndTime = microtime(true);
						$timeDifference = $scanEndTime - $scanStartTime;
						if (isset($this->signatureLog[$virusSignature['title']])) {
							$this->signatureLog[$virusSignature['title']] = $this->signatureLog[$virusSignature['title']] + $timeDifference;
						} else {
							$this->signatureLog[$virusSignature['title']] = $timeDifference;
						}
					}
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
		$wordSplitPattern = array('/\$?\w+/i', '~[\'"]([\S\s]+)?[\'"]~iuU');
		$suspicion = 0.0;

		$fileContent = mb_convert_encoding(file_get_contents($fileName), 'utf-8');

		foreach ($this->dangerousFunctions as $dangerousFunction) {
			$functionCount =  mb_substr_count($fileContent, $dangerousFunction);
			$suspicion = $suspicion + (1 * $functionCount);
			if($suspicion > 1) {
				return $suspicion;
			}
		}

		if ($suspicion == 0) {
			//Проверка на длинные слова
			$wordMatches = array();
			foreach ($wordSplitPattern as $wordPattern) {
				$pregResult = preg_match_all($wordPattern, $fileContent, $currentWordMatches);
				if(!empty($currentWordMatches)) {
					$newWordMatches = array_merge($wordMatches, $currentWordMatches[0]);
					$wordMatches = $newWordMatches;
					unset($newWordMatches);
					unset($currentWordMatches);
					//print_r($wordMatches);
				}
			}
			if (!empty($wordMatches)) {;
				foreach (array_unique($wordMatches) as $someWord) {
					if (strlen($someWord) >= 25) {
						if (mb_substr($someWord, 0, 1) !== '$') {
							//  Чем длиннее слово, тем больше подозрение
							if ($someWord != strtoupper($someWord)) {
								$suspicion = $suspicion + 0.01 * strlen($someWord);
								//echo($someWord . " " . $suspicion . "\r\n");
							}
						}
					}

					//  Если слово - переменная
					if (mb_substr($someWord, 0, 1) === '$') {
						//  Проверка переменных на стремные именования
						foreach ($this->namePatterns as $namePattern) {
							$checkResult = preg_match($namePattern, mb_substr($someWord, 1));
							if ($checkResult == 1) {
								$suspicion = $suspicion + 0.02;
							}
						}

						//  Проверка переменных на частые использования в виде массивов
						$arrayPattern = '/\\' . $someWord . '\[[\'"]?[\d\S]+[\'"]?\](\[\d+\])?/i';
						//echo($arrayPattern . "\r\n");
						$arrayCheckResult = preg_match_all($arrayPattern, $fileContent, $arrayPatternMatches);
						if ($arrayCheckResult !== false) {

							$variableUsages = count(array_unique($arrayPatternMatches[0]));
							if ($variableUsages > 4) {
								$suspicion = $suspicion + (0.3 + $variableUsages);
							}
						}
					}
				}
			}
		}

		//echo($fileName . " " . $suspicion . "\r\n");
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
	 */
	public function SaveAnamnesis() {

		if(file_exists(TheKadeshi::getAnamnesisFile())) {
			unlink(TheKadeshi::getAnamnesisFile());
		}
		//print_r($this->AnamnesisContent);
		if(0 !== count($this->AnamnesisContent)) {
			file_put_contents(TheKadeshi::getAnamnesisFile(), json_encode($this->AnamnesisContent));
		}
	}

	public function SendAnamnesis($sendToken = true) {
		if(file_exists(TheKadeshi::getAnamnesisFile())) {

			$anamnesisContent = json_decode(file_get_contents(TheKadeshi::getAnamnesisFile()), true);
			$sendResult = TheKadeshi::ServiceRequest('sendAnamnesis', array('anamnesis' => $anamnesisContent), $sendToken);

			$jsonResult = json_decode($sendResult, true);
			if($jsonResult['success'] == true) {
				unlink(TheKadeshi::getAnamnesisFile());
			}
		}
	}
}

class Status {

	private $StatusFile;

	private $StatusContent;

	//private $AnamnesisFile;

	function __construct() {
		$this->StatusFile = TheKadeshi::getCheckSumDir() . '/' . '.status';

		if(file_exists($this->StatusFile)) {
			$this->StatusContent = json_decode(file_get_contents($this->StatusFile), true);
		}

		$this->Action();
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