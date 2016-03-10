<?php
/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 07.02.2016
 * Time: 16:17
 * Created by PhpStorm.
 */

class Scanner {

	/**
	 * Где брать файл сигнатур
	 * @var string
	 */
	public $SignatureFile = 'remote';

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


	function __construct($signaturesUpdate = true) {
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
		//if($signaturesUpdate) {
		//	$this->SignaturesDir = 'http://thekadeshi.bagdad.tmweb.ru/signatures';
		//}
		//print_r($this->SignaturesDir);
		//$this->GetSignaturesFiles();

	}

	/**
	 * Инициализация
	 */
	public function Init() {
		switch($this->SignatureFile) {
			case 'local':
				$this->SignaturesDir = 'signatures';
				break;
			default:
				$this->SignaturesDir = 'http://thekadeshi.bagdad.tmweb.ru/signatures';
				break;
		}
		$this->LoadRules();
	}

	/**
	 * Функция получения списка правил из каталога
	 *
	 * @return mixed
	 */
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

	/**
	 * Функция загрузки правил определения CMS
	 *
	 * @return array
	 *
	 * @todo На какое-то время это можно убрать.
	 */
	private function LoadRules() {
		$rules = array();

		//foreach ($this->SignaturesFileList as $ruleFile) {
		$fileName = $this->SignaturesDir . '/' . 'database.xml';

		$xml = simplexml_load_file($fileName, 'SimpleXMLElement', LIBXML_NOCDATA);

		foreach ($xml[0] as $item){
			$name = trim($item->name[0]);
			$signature = trim($item->signature[0]);
			$action = trim($item->action[0]);

			$this->Signatures[] = array(
				'name' => $name,
				'signature' => $signature,
				'action' => $action
			);
		}
		//}

		$this->RulesList = $rules;

		return $rules;
	}

	public function Scan($fileName) {
		$this->scanResults = array();
		$heuristicScanResult = $this->Heuristic($fileName);
		if($heuristicScanResult > 1) {
			echo($fileName . " infected with " . $heuristicScanResult . "\r\n");
			$content = $this->GetFileContent($fileName);

			if ($content !== false && strlen($content) > 0) {
				$this->ScanContent($content);
			}
		}

		return (!empty($this->scanResults))?$this->scanResults:null;
	}

	/**
	 * Функция получения содержимого файла
	 * @param $fileName
	 * @return string
	 */
	private function GetFileContent($fileName) {
		$this->realFileName = pathinfo($fileName);
		$content = false;

		if(isset($this->realFileName['extension']) && $this->realFileName['extension'] != 'xml') {
			$content = file_get_contents($fileName);
		}
		//print_r($content);
		return $content;
	}

	private function ScanContent($content) {

		$content = mb_convert_encoding($content, "utf-8");
		foreach($this->Signatures as $virusSignature) {

			preg_match($virusSignature['signature'], $content, $results);

			if(isset($results) && !empty($results)) {
				//print_r($results);
				$files[] = array('file'=>'', 'action'=>$virusSignature['action']);
				$this->scanResults = array(
					'file' => $this->realFileName,
					'name' => $virusSignature['name'],
					'positions' => array(
						'start' => mb_strpos($content, $results[0]),
						'length' => mb_strlen($results[0])
					),
					//'content' => $results[0],
					'action' => $virusSignature['action']
				);
			}
			$content = preg_replace($virusSignature['signature'], '', $content);

		}
	}

	public function ContentParser($content) {
		//$contentWords = explode($this->devideSymbols, $content);

		$contentWords = preg_match_all('/\$?\w+/i', $content, $matches);

		print_r($matches);

		return 1;
	}

	/**
	 * Функция эвристического анализа содержимого файла
	 * @param $fileName string Имя файла для анализа
	 * @return float Результат сканирования. Чем больше значение, тем более стремным выглядит файл
	 */
	public function HeuristicFileContent($fileName) {
		$suspicion = 0.0;

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
						if($variableUsages > 3) {
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

/**
 * Класс лекарь
 */
class Healer {

	/**
	 * Анамнез
	 * @var array
	 */
	public $Anamnesis;

	function __construct()
	{
		$this->Anamnesis = array();
		$this->GetAnamnesis();
		if(!empty($this->Anamnesis)) {
			//cure
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
}

class FileList {

	public $fileList = null;

	function __construct() {
		$this->fileList = array();
	}


	public function GetFileList($dir) {

		$dirContent = scandir($dir);
		foreach($dirContent as $directoryElement) {
			if($directoryElement != '..' && $directoryElement != '.') {
				if (is_file($dir . '/' . $directoryElement)) {
					$this->fileList[] = $dir . '/' . $directoryElement;
				}
				if (is_dir($dir . '/' . $directoryElement)) {
					$this->GetFileList($dir . '/' . $directoryElement);
				}
			}
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

/*

//@todo надо отрефакторить эту фигню
$signaturesBase = 'remote';
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
}

$Console = new Console(defined('VERBOSE')?VERBOSE:false);

if($currentAction == 'scan' || $currentAction == null) {

	$Console->Log("Current action: " . $Console->Color['green'] . "Scanning" . $Console->Color['normal'] );
	if($signaturesBase == 'local') {
		$Console->Log("Signature file: " . $Console->Color['blue'] . "local" . $Console->Color['normal'] );
	} else {
		$Console->Log("Signature file: " . $Console->Color['blue'] . "remote" . $Console->Color['normal'] );
	}

	$scanResults = array();
	$scanner = new Scanner();
	$scanner->SignatureFile = $signaturesBase;
	$scanner->Init();

	$filelist = new FileList();

	if(!isset($fileToScan)) {
		$filelist->GetFileList(__DIR__);
	} else {
		$filelist->fileList[] = $fileToScan;
	}

	foreach ($filelist->fileList as $file) {

		$fileScanResults = $scanner->Scan($file);
		if ($fileScanResults != null) {
			$scanResults[] = $fileScanResults;

			$Console->Log($fileScanResults['file']['dirname'] . '/' . $fileScanResults['file']['basename'] . ' infection: ' . $Console->Color['red'] . $fileScanResults['name'] . $Console->Color['normal'] . " action: " . $Console->Color['blue'] . $fileScanResults['action'] . $Console->Color['normal'] );
		}
	}
	if(!empty($scanResults)) {
		//for
		$encodedResults = json_encode($scanResults);
		$resultsFile = file_put_contents("kadeshi.anamnesis.json", $encodedResults);
	}
}
*/