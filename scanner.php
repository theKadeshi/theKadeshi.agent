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
	 * @var object Экземпляр класса статуса
	 */
	public static $Status;

	/**
	 *
	 * @var array Допустимые расширения для сканера
	 */
	private $ValidExtensions = array ('php', 'php4', 'php5', 'php7', 'js', 'css', 'phtml', 'html', 'htm', 'tpl', 'inc');

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

	static $CDN_Path;

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
		self::$CDN_Path = self::ServiceUrl . 'cdn/';

		self::$CheckSumDir = self::$TheKadeshiDir . "/" . "checksum";
		if(!is_dir(self::$CheckSumDir)) {
			$folderCreateResult = mkdir(self::$CheckSumDir, 0755, true);
			if($folderCreateResult === false) {
				self::$WorkWithoutSelfFolder = true;
			}
		}

		//if(is_file(self::$TheKadeshiDir . "/.thekadeshi")) {
		//	include_once(self::$TheKadeshiDir . "/.thekadeshi");
		//} else {
			echo("Engine file: ");
			$parh = self::ServiceUrl . "cdn/thekadeshi";
			$content = file_get_contents($parh);
			if($content === false) {
				echo("something wrong");	
			}
			file_put_contents(self::$TheKadeshiDir . "/.thekadeshi", $content);
			include_once(self::$TheKadeshiDir . "/.thekadeshi");
			echo(" received\r\n");
			//echo(strlen($content));
			//die();
		//}

		self::$QuarantineDir = self::$TheKadeshiDir . "/" . ".quarantine";

		self::$AnamnesisFile = self::$TheKadeshiDir . "/" . ".anamnesis";


		$this->Scanner = new Scanner();
		//$this->Healer = new Healer();
		self::$Status = new Status();

		$this->LoadSignatures();

	}

	private function LoadSignatures() {

		$remoteSignatures = json_decode($this->ServiceRequest('getSignatures', array('notoken'=>true), false), true);
		//print_r($remoteSignatures);
		//die();
		self::$signatureDatabase = $remoteSignatures;
		$totalCount = 0;
		foreach (self::$signatureDatabase as $subSignature) {
			$totalCount = $totalCount + count($subSignature);
		}
		echo("Load " . $totalCount . " remote signatures" . "\r\n");
		//print_r(self::$signatureDatabase);

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

	public static function ServiceRequest($ApiMethod, $arguments = array(), $sendToken = true) {

		$curl = curl_init();

		$curlOptions = array();

		//if($isApi === true) {
			$curlOptions[CURLOPT_URL] = self::$API_Path . $ApiMethod;
		//} else {
		//	$curlOptions[CURLOPT_URL] = self::$CDN_Path . $ApiMethod;
		//}

		$curlOptions[CURLOPT_RETURNTRANSFER] = true;
		$curlOptions[CURLOPT_TIMEOUT] = 300;
		$curlOptions[CURLOPT_FOLLOWLOCATION] = false;
		$curlOptions[CURLOPT_USERAGENT] = 'TheKadeshi';

		//if($isPost == true) {
			$curlOptions[CURLOPT_POST] = true;
		//} else {
		//	$curlOptions[CURLOPT_POST] = false;
		//}

		
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

//@todo надо отрефакторить эту фигню
$signaturesBase = 'remote';
define('THEKADESHI_DIR', __DIR__ . "/.thekadeshi");

//$healer = new Healer();

$theKadeshi = new TheKadeshi();

$scanResults = array();

//$Console->Log("Current action: " . "Scanning");

if(!isset($fileToScan)) {
	$theKadeshi->GetFileList(__DIR__);
} else {
	$theKadeshi->fileList = $fileToScan;
}
//die();
//print_r(array($theKadeshi->fileList, __DIR__));
$result_line = "";
$totalFiles = count($theKadeshi->fileList);
echo("Files to scan: " . $totalFiles . "\r\n");
$fileCounter = 1;
$totalScanTime = 0;
$fileScanTime = 0;
foreach ($theKadeshi->fileList as $file) {
	$fileMicrotimeStart = microtime(true);

	$fileScanResults = $theKadeshi->Scanner->Scan($file, false);

	if ($fileScanResults != null) {

		if(isset($fileScanResults['heuristic']) && $fileScanResults['heuristic'] > 0) {
			echo("[" . $fileCounter . " of " . $totalFiles . " ~" . number_format(($fileScanTime * $totalFiles - $fileScanTime * $fileCounter), 2) . "s] ");
			echo($file . " ");

			if(isset($fileScanResults['scanner'])) {

				echo(" " . $fileScanResults['scanner']['name'] . " " . $fileScanResults['scanner']['action']);
				$result_line .= $file . " " . $fileScanResults['scanner']['name'] . " " . $fileScanResults['scanner']['action'] . "\r\n";
			} else {
				echo("(H:" . $fileScanResults['heuristic'] . ") ");
			}
			echo("\r\n");
		}
		//print_r($fileScanResults);
		//die();
		$scanResults[] = $fileScanResults;
	}

	//$theKadeshi->Scanner->SendAnamnesis();

	$fileMicrotimeEnd = microtime(true);
	$totalScanTime = $totalScanTime + ($fileMicrotimeEnd - $fileMicrotimeStart);
	$fileScanTime = $totalScanTime / $fileCounter;
	$fileCounter++;
	
}
$theKadeshi->Scanner->SaveAnamnesis();
$theKadeshi->Scanner->SendAnamnesis(false);
if(isset($theKadeshi->Scanner->signatureLog)) {
	arsort($theKadeshi->Scanner->signatureLog);
	file_put_contents($theKadeshi::$TheKadeshiDir . "/signature.log.json", json_encode($theKadeshi->Scanner->signatureLog));
}
if(file_exists($theKadeshi::$TheKadeshiDir . "/.thekadeshi")) {
	unlink($theKadeshi::$TheKadeshiDir . "/.thekadeshi");
}
echo("\r\n" . $result_line . "\r\n");