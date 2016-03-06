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
	 * @var string Каталог сигнатур
	 */
	private $SignaturesDir;

	private $SignaturesFileList = array();

	private $Signatures = array();

	private $realFileName = null;

	private $scanResults = array();

	function __construct($signaturesUpdate = true) {
		$this->scanResults = array();
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
		$content = $this->GetFileContent($fileName);

		if($content !== false && strlen($content) > 0) {
			$this->ScanContent($content);
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
		return $content;
	}

	private function ScanContent($content) {

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



if($argc > 1) {
	foreach ($argv as $argument) {
		if (strtolower($argument) == '--local') {
			if(!defined('SIGNATURE_BASE')) {
				define('SIGNATURE_BASE', 'local');
			}
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
	if(SIGNATURE_BASE == 'local') {
		$Console->Log("Signature file: " . $Console->Color['blue'] . "local" . $Console->Color['normal'] );
	} else {
		$Console->Log("Signature file: " . $Console->Color['blue'] . "remote" . $Console->Color['normal'] );
	}

	$scanResults = array();
	$scanner = new Scanner();
	$scanner->SignatureFile = SIGNATURE_BASE;
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