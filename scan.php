<?php
/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 07.02.2016
 * Time: 16:17
 * Created by PhpStorm.
 */

class Scanner {

	private $SignaturesDir = '/signatures';

	private $SignaturesFileList = array();

	private $Signatures = array();

	private $realFileName = null;

	static $scanResults = array();

	function __construct() {
		self::$scanResults = array();
		$this->SignaturesDir = 'http://thekadeshi.bagdad.tmweb.ru/signatures';
		//$this->GetSignaturesFiles();
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
		$content = $this->GetFileContent($fileName);
		$this->ScanContent($content);

		return (!empty(self::$scanResults))?self::$scanResults:null;
	}

	private function GetFileContent($fileName) {
		$content = file_get_contents($fileName);
		$this->realFileName = pathinfo($fileName);
		return $content;
	}

	private function ScanContent($content) {

		foreach($this->Signatures as $virusSignature) {

			preg_match($virusSignature['signature'], $content, $results);
			if(!empty($results)) {
				$files[] = array('file'=>'', 'action'=>$virusSignature['action']);
				self::$scanResults[] = array(
					'file' => $this->realFileName,
				    'name' => $virusSignature['name'],
					'content' => $results[0],
					'action' => $virusSignature['action']
				);
			}
			$content = preg_replace($virusSignature['signature'], '', $content);

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

$scanner = new Scanner();
$filelist = new FileList();

$filelist->GetFileList(__DIR__);

//print_r($filelist->fileList);

foreach($filelist->fileList as $file) {

	$scanResults = $scanner->Scan($file);
	if($scanResults != null) {
		$results[] = $scanResults;
	}

}

print_r($results);