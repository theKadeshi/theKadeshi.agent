<?php
require_once __DIR__ . '/.thekadeshi/TheKadeshiEngine.php';

/**
 * Project: theKadeshi
 * User: ntorgov ( https://github.com/ntorgov/ )
 * Date: 07.02.2016
 * Time: 16:17
 * Created by PhpStorm.
 */
class Scanner extends TheKadeshi\TheKadeshiEngine
{

	/**
	 * Адрес службы
	 */
	const ServiceUrl = 'https://thekadeshi.com/';

	public $fileList;

	/**
	 * @var TheKadeshi\TheKadeshiEngine $Engine Экземпляр класса сканнера
	 */
	public $Engine;

	/**
	 * @var object Экземпляр класса статуса
	 */
	//public static $Status;

	/**
	 *
	 * @var array Допустимые расширения для сканера
	 */
	private static $ValidExtensions = array('php', 'php4', 'php5', 'php7', 'js', 'css', 'phtml', 'html', 'htm', 'tpl', 'inc');

	/**
	 * Каталоги
	 */

	/**
	 * @var string Каталог Кадеш
	 */
	private $TheKadeshiDir;

	/**
	 * @var string Каталог с контрольными суммами
	 */
	private static $CheckSumDir = '';

	/**
	 * @var string Каталог с карантином
	 */
	// static $QuarantineDir = '';


	// private static $OptionsFile = '';

	// private static $SignatureFile = '';

	private static $AnamnesisFile = '';

	public static $Options;

	// static $Logs;

	// private static $API_Path, $CDN_Path;

	const configCheckTimer = 3600;

	public $executionMicroTimeStart;

	//public static $WorkWithoutSelfFolder = false;

//	/**
//	 * База сигнатур
//	 * @var array
//	 */
//	global $signatureDatabase;

	public function __construct()
	{

		$this->executionMicroTimeStart = microtime(true);

		$this->TheKadeshiDir = __DIR__ . '/.thekadeshi';

		// self::$OptionsFile = self::$TheKadeshiDir . '/.options';
		// self::$API_Path = self::ServiceUrl . 'api/';
		// self::$CDN_Path = self::ServiceUrl . 'cdn/';
//
//		self::setCheckSumDir(self::$TheKadeshiDir . '/checksum');
//		if(!is_dir(self::getCheckSumDir())) {
//
//			$folderCreateResult = mkdir(self::getCheckSumDir(), 0755, true);
//
//			if($folderCreateResult === false) {
//
//				self::$WorkWithoutSelfFolder = true;
//			}
//		}
//
//		 if (is_file($this->TheKadeshiDir . '/thekadeshi.php')) {
//
//		 include_once $this->TheKadeshiDir . '/thekadeshi.php';
//		}
//		} else {
//
//			echo('Engine file: ');
//			$path = self::ServiceUrl . 'cdn/thekadeshi';
//			$content = file_get_contents($path . '?dev=1');
//			if($content === false) {
//				echo('something wrong');
//			}
//			file_put_contents(self::$TheKadeshiDir . '/.thekadeshi', $content);
//			include_once self::$TheKadeshiDir . '/thekadeshi.php';
//			echo(" received" . PHP_EOL);
//			//echo(strlen($content));
//			//die();
//		}
//
		// self::$QuarantineDir = self::$TheKadeshiDir . '/.quarantine';

		self::$AnamnesisFile = $this->TheKadeshiDir . '/.anamnesis';


		$this->Engine = new TheKadeshi\TheKadeshiEngine();
		// $this->Healer = new Healer();
		// self::$Status = new Status();

		$this->LoadSignatures();

	}

	/**
	 * Loading signatures
	 */
	private function LoadSignatures()
	{
		global $signatureDatabase;
		$signatureFile = $this->TheKadeshiDir . '/signatures.json';
		$remoteSignatures = array();
		if (is_file($signatureFile)) {
			$fileContent = file_get_contents($signatureFile, 'r');
			$remoteSignatures = json_decode($fileContent, true);
		}

		$totalCount = 0;
		foreach ((array)$remoteSignatures as $subSignature) {
			$totalCount += count($subSignature);
		}

		//$this->signatureDatabase = $remoteSignatures;
		$signatureDatabase = $remoteSignatures;
		echo('Load ' . $totalCount . ' remote signatures' . PHP_EOL);
	}

//	/**
//	 * @return array
//	 */
//	public function getSignatureDatabase()
//	{
//
//		return $this->signatureDatabase;
//	}
//
//	/**
//	 * @param array $signatureDatabase
//	 */
//	public function setSignatureDatabase(array $signatureDatabase)
//	{
//
//		$this->signatureDatabase = $signatureDatabase;
//	}

	/**
	 * @return string
	 */
	public static function getCheckSumDir()
	{

		return self::$CheckSumDir;
	}

	/**
	 * @param string $CheckSumDir
	 */
	public static function setCheckSumDir($CheckSumDir)
	{

		self::$CheckSumDir = $CheckSumDir;
	}

	/**
	 * @return string
	 */
	public static function getAnamnesisFile()
	{

		return self::$AnamnesisFile;
	}

	/**
	 * @param string $AnamnesisFile
	 */
	public static function setAnamnesisFile($AnamnesisFile)
	{

		self::$AnamnesisFile = $AnamnesisFile;
	}

	/**
	 * @return string
	 */
	public function getTheKadeshiDir()
	{

		return $this->TheKadeshiDir;
	}

	/**
	 * Функция получения содержимого каталога
	 *
	 * @param $dir
	 */
	public function GetFileList($dir)
	{

		$dirContent = scandir($dir, SCANDIR_SORT_NONE);

		foreach ($dirContent as $directoryElement) {

			if ($directoryElement !== '..' && $directoryElement !== '.') {

				$someFile = $dir . '/' . $directoryElement;
				if (is_file($someFile)) {

					$fileData = pathinfo($someFile);
					if (array_key_exists('extension', $fileData) && in_array($fileData['extension'], self::$ValidExtensions, true) === true) {

						$this->fileList[] = $someFile;
					}
				} else {

					$this->GetFileList($someFile);
				}
			}
		}
	}

	/**
	 * Функция рекурсивного удаления каталога
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	public function deleteContent($path)
	{

		try {

			$iterator = new DirectoryIterator($path);
			foreach ($iterator as $fileinfo) {

				if ($fileinfo->isDot() === true)
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

//	/**
//	 * Эксперементальная функция получения списка файлов
//	 * На практике оказалась довольно тормозной
//	 * Требует PHP > 5.2
//	 *
//	 * @param $dir
//	 */
//	public function GetIteratorFileList($dir)
//	{
//
//		$directory = new \RecursiveDirectoryIterator($dir);
//		$iterator = new \RecursiveIteratorIterator($directory);
//
//		foreach ($iterator as $info) {
//
//			$fileData = pathinfo($info);
//			if ((array_key_exists('extension', $fileData) === true) && (in_array($fileData['extension'], self::$ValidExtensions, true) === true)) {
//
//				$this->fileList[] = $info;
//			}
//		}
//
//		unset($directory, $iterator);
//	}
//
//	public static function ServiceRequest($ApiMethod, $arguments = null, $sendToken = true, $source = 'api') {
//
//		if(function_exists('curl_exec') && function_exists('curl_init') && function_exists('curl_close')) {
//
//			$curl = curl_init();
//
//			$curlOptions = array();
//
//			if($source === 'api') {
//				$curlOptions[CURLOPT_URL] = self::$API_Path . $ApiMethod;
//			} elseif($source === 'cdn') {
//				$curlOptions[CURLOPT_URL] = self::$CDN_Path . $ApiMethod;
//			}
//			if(array_key_exists('SERVER_NAME', $_SERVER)) {
//				$arguments['site'] = $_SERVER['SERVER_NAME'];
//			}
//
//			$curlOptions[CURLOPT_RETURNTRANSFER] = true;
//			$curlOptions[CURLOPT_TIMEOUT] = 300;
//			$curlOptions[CURLOPT_FOLLOWLOCATION] = false;
//			$curlOptions[CURLOPT_USERAGENT] = 'TheKadeshi';
//
//			$curlOptions[CURLOPT_POST] = true;
//
//
//			if(isset($arguments)) {
//				if($sendToken === true) {
//					$arguments['token'] = self::$Options['token'];
//				}
//				$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($arguments);
//			}
//			$curlOptions[CURLOPT_HTTPHEADER] = array(
//				'Content-Type: application/x-www-form-urlencoded', 'Sender: TheKadeshi'
//			);
//
//			curl_setopt_array($curl, $curlOptions);
//			$pageContent = curl_exec($curl);
//
//			curl_close($curl);
//
//			return $pageContent;
//		} else {
//
//			$context = stream_context_create(array(
//				'http' => array(
//					'method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded', 'Sender: TheKadeshi',
//				),
//			));
//
//			if($source === 'api') {
//				$url = self::$API_Path . $ApiMethod;
//			} elseif($source === 'cdn') {
//				$url = self::$CDN_Path . $ApiMethod;
//			}
//
//			if($sendToken === true) {
//				$arguments['token'] = self::$Options['token'];
//			}
//
//			$pageContent = file_get_contents($file = $url . '?' . http_build_query($arguments), $use_include_path = false, $context);
//
//			return $pageContent;
//		}
//
//	}
}

//@todo надо отрефакторить эту фигню
set_time_limit(0);
@ini_set('max_execution_time', 0);
// $signaturesBase = 'remote';
// define('THEKADESHI_DIR', __DIR__ . '/.thekadeshi');

//$healer = new Healer();

$theKadeshi = new Scanner();

$scanResults = array();

//$Console->Log("Current action: " . "Scanning");

if (!isset($fileToScan)) {

	$theKadeshi->GetFileList(__DIR__);

	//$theKadeshi->GetIteratorFileList(__DIR__);

} else {
	$theKadeshi->fileList = $fileToScan;
}
//die();
//print_r(array($theKadeshi->fileList, __DIR__));
$result_line = '';
$totalFiles = count($theKadeshi->fileList);
echo('Files to scan: ' . $totalFiles . PHP_EOL);
$fileCounter = 1;
$totalScanTime = 0;
$fileScanTime = 0;
foreach ((array)$theKadeshi->fileList as $file) {
	$fileMicrotimeStart = microtime(true);

	$fileScanResults = $theKadeshi->Engine->Scan($file, false);

	if ($fileScanResults !== null) {

		// print_r($fileScanResults);
		// exit();
		// if (isset($fileScanResults['heuristic']) && $fileScanResults['heuristic'] > 0) {
		echo('[' . $fileCounter . ' of ' . $totalFiles . ' ~' . number_format($fileScanTime * $totalFiles - $fileScanTime * $fileCounter, 2) . 's] ');
		echo($file . ' ');

		if (isset($fileScanResults['TheKadeshi'])) {

			echo(' ' . $fileScanResults['TheKadeshi']['name'] . ' ' . $fileScanResults['TheKadeshi']['action']);
			$result_line .= $file . ' ' . $fileScanResults['TheKadeshi']['name'] . ' ' . $fileScanResults['TheKadeshi']['action'] . PHP_EOL;
		}
		//  else {
		// 	echo('(H:' . $fileScanResults['heuristic'] . ') ');
		// }
		echo(PHP_EOL);
		// }
		//print_r($fileScanResults);
		//die();
		$scanResults[] = $fileScanResults;
	}

	//$theKadeshi->Scanner->SendAnamnesis();

	$fileMicrotimeEnd = microtime(true);
	$totalScanTime += $fileMicrotimeEnd - $fileMicrotimeStart;
	$fileScanTime = $totalScanTime / $fileCounter;
	$fileCounter++;

}
// $theKadeshi->Scanner->SaveAnamnesis();
// $theKadeshi->Scanner->SendAnamnesis(false);
//
//if ($theKadeshi->Engine->signatureLog !== null) {
//
//	arsort($theKadeshi->Engine->signatureLog);
//	file_put_contents($theKadeshi->getTheKadeshiDir() . '/signature.log.json', json_encode($theKadeshi->Engine->signatureLog));
//}
//if(file_exists($theKadeshi->getTheKadeshiDir() . '/.thekadeshi')) {
//	unlink($theKadeshi->getTheKadeshiDir() . '/.thekadeshi');
//}
echo(PHP_EOL . $result_line . PHP_EOL);