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
	const PROTECTED_PAGE = 'PCFkb2N0eXBlIGh0bWw+PGh0bWw+PGhlYWQ+PG1ldGEgY2hhcnNldD11dGYtOD48dGl0bGU+VGhpcyB3ZWJzaXRlIGlzIHByb3RlY3RlZCBieSBUaGVLYWRlc2hpIHN5c3RlbTwvdGl0bGU+PGxpbmsgaHJlZj0iaHR0cHM6Ly9mb250cy5nb29nbGVhcGlzLmNvbS9jc3M/ZmFtaWx5PVJvYm90bzoxMDAiIHJlbD0ic3R5bGVzaGVldCIgdHlwZT0idGV4dC9jc3MiPjxzdHlsZT5ib2R5LCBodG1sIHtoZWlnaHQ6IDEwMCU7bWFyZ2luOiAwO2JhY2tncm91bmQtY29sb3I6ICNkY2RjZGM7fWgxIHtmb250LWZhbWlseTogJ1JvYm90bycsIHNhbnMtc2VyaWYgIWltcG9ydGFudDtmb250LXdlaWdodDogMTAwICFpbXBvcnRhbnQ7bGluZS1oZWlnaHQ6IDQwcHg7fS5yZXNwb25zaXZlLWNvbnRhaW5lciB7cG9zaXRpb246IHJlbGF0aXZlO3dpZHRoOiAxMDAlO2hlaWdodDogMTAwJX0uaW1nLWNvbnRhaW5lciB7cG9zaXRpb246IGFic29sdXRlO3RvcDogMDtib3R0b206IDA7bGVmdDogMDtyaWdodDogMDt0ZXh0LWFsaWduOiBjZW50ZXI7Zm9udDogMC8wIGE7d2lkdGg6IDEwMCU7Zm9udC1zaXplOiAxNTAlO31hIHtjb2xvcjogIzRkY2VjNTt0ZXh0LWRlY29yYXRpb246IG5vbmU7fS5pbWctY29udGFpbmVyOmJlZm9yZSB7Y29udGVudDogJyAnO2Rpc3BsYXk6IGlubGluZS1ibG9jazt2ZXJ0aWNhbC1hbGlnbjogbWlkZGxlO2hlaWdodDogNjAlO30uaW1nLWNvbnRhaW5lciBpbWcge3ZlcnRpY2FsLWFsaWduOiBtaWRkbGU7ZGlzcGxheTogaW5saW5lLWJsb2NrO3dpZHRoOiAyMCU7fTwvc3R5bGU+PC9oZWFkPjxib2R5PjxkaXYgY2xhc3M9cmVzcG9uc2l2ZS1jb250YWluZXI+PGRpdiBjbGFzcz1pbWctY29udGFpbmVyPjxpbWcgc3JjPWh0dHA6Ly90aGVrYWRlc2hpLmNvbS9pbWFnZXMvdGhla2FkZXNoaS1yZW1vdGUuc3ZnPjxici8+PGgxPlRoaXMgd2Vic2l0ZSBpcyBwcm90ZWN0ZWQgYnkgPGEgaHJlZj1odHRwOi8vdGhla2FkZXNoaS5jb20gdGFyZ2V0PV9ibGFuaz5UaGVLYWRlc2hpPC9hPiBzeXN0ZW08L2gxPjwvZGl2PjwvZGl2PjwvYm9keT48L2h0bWw+';

	/**
	 * Адрес службы
	 */
	const ServiceUrl = 'http://thekadeshi.com/';

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
	 *
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

	/**
	 * @var string Каталог с карантином
	 */
	static $QuarantineDir = '';


	private static $OptionsFile = '';

	private static $SignatureFile = '';

	private static $AnamnesisFile = '';

	public static $Options;

	static $Logs;

	private static $API_Path, $CDN_Path;

	const configCheckTimer = 3600;

	public $executionMicroTimeStart;

	//public static $WorkWithoutSelfFolder = false;

	/**
	 * База сигнатур
	 * @var array
	 */
	private static $signatureDatabase;

	public function __construct() {

		$this->executionMicroTimeStart = microtime(true);

		self::$TheKadeshiDir = __DIR__ . '/.thekadeshi';
		self::$OptionsFile = self::$TheKadeshiDir . '/.options';
		self::$API_Path = self::ServiceUrl . 'api/';
		self::$CDN_Path = self::ServiceUrl . 'cdn/';

		self::setCheckSumDir(self::$TheKadeshiDir . '/checksum');
		if(!is_dir(self::getCheckSumDir())) {
			$folderCreateResult = mkdir(self::getCheckSumDir(), 0755, true);
			if($folderCreateResult === false) {
				self::$WorkWithoutSelfFolder = true;
			}
		}

		if(is_file('/thekadeshi.inc.php')) {
			echo('Engine file: local');
			include_once __DIR__ . '/thekadeshi.inc.php';
		} else {
			echo('Engine file: ');
			$path = self::ServiceUrl . 'cdn/thekadeshi';
			$content = file_get_contents($path . '?dev=1');
			if($content === false) {
				echo('something wrong');
			}
			file_put_contents(self::$TheKadeshiDir . '/.thekadeshi', $content);
			include_once self::$TheKadeshiDir . '/.thekadeshi';
			echo(" received\r\n");
			//echo(strlen($content));
			//die();
		}

		self::$QuarantineDir = self::$TheKadeshiDir . '/.quarantine';

		self::$AnamnesisFile = self::$TheKadeshiDir . '/.anamnesis';


		$this->Scanner = new Scanner();
		//$this->Healer = new Healer();
		self::$Status = new Status();

		$this->LoadSignatures();

	}

	private function LoadSignatures() {
		$remoteSignatures = json_decode(self::ServiceRequest('getSignatures', array('notoken'=>true), false), true);

		self::setSignatureDatabase($remoteSignatures);
		$totalCount = 0;
		foreach (self::getSignatureDatabase() as $subSignature) {
			$totalCount += count($subSignature);
		}
		echo('Load ' . $totalCount . ' remote signatures' . "\r\n");

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
	public static function setSignatureDatabase(array $signatureDatabase) {
		self::$signatureDatabase = $signatureDatabase;
	}

	/**
	 * @return string
	 */
	public static function getCheckSumDir() {
		return self::$CheckSumDir;
	}

	/**
	 * @param string $CheckSumDir
	 */
	public static function setCheckSumDir($CheckSumDir) {
		self::$CheckSumDir = $CheckSumDir;
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
	public static function setAnamnesisFile($AnamnesisFile) {
		self::$AnamnesisFile = $AnamnesisFile;
	}

	/**
	 * @return string
	 */
	public static function getTheKadeshiDir() {
		return self::$TheKadeshiDir;
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
	 * Функция рекурсивного удаления каталога
	 * @param $path
	 * @return bool
	 */
	public function deleteContent($path) {
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

	/**
	 * Эксперементальная функция получения списка файлов
	 * На практике оказалась довольно тормозной
	 * Требует PHP > 5.2
	 * @param $dir
	 */
	public function GetIteratorFileList($dir) {
		$directory = new \RecursiveDirectoryIterator($dir);
		$iterator = new \RecursiveIteratorIterator($directory);

		foreach ($iterator as $info) {

			$fileData = pathinfo($info);
			if ((array_key_exists('extension', $fileData) === true) && (in_array($fileData['extension'], self::$ValidExtensions, true) === true)) {
				$this->fileList[] = $info;
			}
		}

		unset($directory, $iterator);
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

//@todo надо отрефакторить эту фигню
set_time_limit(0);
@ini_set('max_execution_time', 0);
$signaturesBase = 'remote';
define('THEKADESHI_DIR', __DIR__ . '/.thekadeshi');

//$healer = new Healer();

$theKadeshi = new TheKadeshi();

$scanResults = array();

//$Console->Log("Current action: " . "Scanning");

if(!isset($fileToScan)) {

	$theKadeshi->GetFileList(__DIR__);

	//$theKadeshi->GetIteratorFileList(__DIR__);

} else {
	$theKadeshi->fileList = $fileToScan;
}
//die();
//print_r(array($theKadeshi->fileList, __DIR__));
$result_line = '';
$totalFiles = count($theKadeshi->fileList);
echo('Files to scan: ' . $totalFiles . "\r\n");
$fileCounter = 1;
$totalScanTime = 0;
$fileScanTime = 0;
foreach ($theKadeshi->fileList as $file) {
	$fileMicrotimeStart = microtime(true);

	$fileScanResults = $theKadeshi->Scanner->Scan($file, false);

	if ($fileScanResults !== null) {

		if(isset($fileScanResults['heuristic']) && $fileScanResults['heuristic'] > 0) {
			echo('[' . $fileCounter . ' of ' . $totalFiles . ' ~' . number_format(($fileScanTime * $totalFiles - $fileScanTime * $fileCounter), 2) . 's] ');
			echo($file . ' ');

			if(isset($fileScanResults['scanner'])) {

				echo(' ' . $fileScanResults['scanner']['name'] . ' ' . $fileScanResults['scanner']['action']);
				$result_line .= $file . ' ' . $fileScanResults['scanner']['name'] . ' ' . $fileScanResults['scanner']['action'] . "\r\n";
			} else {
				echo('(H:' . $fileScanResults['heuristic'] . ') ');
			}
			echo("\r\n");
		}
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
$theKadeshi->Scanner->SaveAnamnesis();
$theKadeshi->Scanner->SendAnamnesis(false);

if(isset($theKadeshi->Scanner->signatureLog)) {

	arsort($theKadeshi->Scanner->signatureLog);
	file_put_contents($theKadeshi::getTheKadeshiDir() . '/signature.log.json', json_encode($theKadeshi->Scanner->signatureLog));
}
if(file_exists($theKadeshi::getTheKadeshiDir() . '/.thekadeshi')) {
	unlink($theKadeshi::getTheKadeshiDir() . '/.thekadeshi');
}
echo("\r\n" . $result_line . "\r\n");