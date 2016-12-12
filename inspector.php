<?php
/**
 * Скрипт проверки сервера на возможность использования защиты
 * Project: antivir
 * User: Bagdad
 * Date: 25.07.2016
 * Time: 7:35
 * Created by PhpStorm.
 */
$functionList = array(
	'mkdir', 'is_dir', 'is_file', 'time', 'file_get_contents', 'hash', 'file_put_contents', 'file_exists', 'json_decode', 'count', 'unlink', 'base64_decode', 'scandir', 'pathinfo', 'in_array', 'json_encode', 'base64_encode', 'rmdir', 'gmdate', 'mb_convert_encoding', 'mb_strpos', 'mb_substr', 'is_array', 'chmod', 'curl_init', 'http_build_query', 'curl_setopt_array', 'curl_exec', 'curl_close', 'php_sapi_name', 'error_reporting', 'array_key_exists', 'header', 'array_merge', 'preg_match', 'method_exists', 'microtime'
);
$classList = array('DirectoryIterator',);

$mode = 'con';
if (php_sapi_name() !== 'cli') {
	$mode = 'web';
}

function PrintResult($message, $result = false, $mode) {
	echo $message . ' ';
	if($result === true) {
		if ($mode === 'con') {
			echo 'Ok' . "\r\n";
		} else {
			echo '<span style="color: green;">Ok</span><br/>' . "\r\n";
		}
	} else {
		if ($mode === 'con') {
			echo 'False' . "\r\n";
		} else {
			echo '<span style="color: red; font-weight: bold;">False</span><br/>' . "\r\n";
		}
	}
}



if ($mode === 'web') {
	echo '<h4>Checking functions</h4>' . "\r\n";
}
foreach ($functionList as $function) {
	if (function_exists($function)) {
		PrintResult('Checking ' . $function, true, $mode);
	} else {
		PrintResult('Checking ' . $function, false, $mode);
	}
}

foreach ($classList as $className) {
	if (class_exists($className)) {
		PrintResult('Checking ' . $className, true, $mode);
	} else {
		PrintResult('Checking ' . $className, false, $mode);
	}
}

if ($mode === 'web') {
	echo '<h4>Checking file operations</h4>' . "\r\n";
}
$content = 'Test';
$result = file_put_contents('test.txt', $content);
if ($result !== false) {

	PrintResult('File write', true, $mode);

	$newContent = file_get_contents('test.txt');
	if ($newContent !== $content) {
		PrintResult('File read', false, $mode);
	} else {
		PrintResult('File read', true, $mode);
	}
	try {
		unlink('test.txt');
		PrintResult('File delete', true, $mode);
	} catch (Exception $e) {
		PrintResult('File delete', false, $mode);
	}
} else {
	PrintResult('File write', false, $mode);
}

if( ini_get('allow_url_fopen') ) {
	PrintResult('allow_url_fopen enabled', true, $mode);
} else {
	PrintResult('allow_url_fopen enabled', false, $mode);
}

if ($mode === 'web') {
	echo '<h4>Checking curl operations</h4>' . "\r\n";
}

try {
	//$_SERVER['SERVER_NAME']
	$curl = curl_init();

	$curlOptions = array();

	$arguments = array(
		'site' => $_SERVER['SERVER_NAME']
	);

	$curlOptions[CURLOPT_URL] = 'http://thekadeshi.com/api/getConfig';
	//$curlOptions[CURLOPT_URL] = 'http://thekadeshi.com/cdn/agent';

	$curlOptions[CURLOPT_RETURNTRANSFER] = true;
	$curlOptions[CURLOPT_TIMEOUT] = 300;
	$curlOptions[CURLOPT_FOLLOWLOCATION] = false;
	$curlOptions[CURLOPT_USERAGENT] = 'TheKadeshi';
	//$curlOptions[CURLOPT_VERBOSE] =  1;
	$curlOptions[CURLOPT_HEADER] =  false;
	$curlOptions[CURLOPT_POST] = true;

	$curlOptions[CURLOPT_HTTPHEADER] = array(
		'Content-Type: application/x-www-form-urlencoded', 'Sender: TheKadeshi' //, 'Accept: text/html, application/json'
	);

	if (isset($arguments)) {
		$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($arguments);
	}

	curl_setopt_array($curl, $curlOptions);
	$pageContent = curl_exec($curl);

	//$information = curl_getinfo($curl);
/*
	$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
$header = substr($pageContent, 0, $header_size);
$body = substr($pageContent, $header_size);
*/
	curl_close($curl);
	echo("-=");
	//print_r($information);
	print_r($pageContent);
	echo("=-");
	if($pageContent !== '') {
		PrintResult('Curl read', true, $mode);
	}

} catch (Exception $e) {
	PrintResult('Curl read', false, $mode);
}

if ($mode === 'web') {
	echo '<h4>Checking file_get_content operations</h4>' . "\r\n";
}

try {
	$context = stream_context_create(array(
		'http' => array(
			'method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded', 'Sender: TheKadeshi',
		),
	));

	$pageContent = file_get_contents(
        $file = "http://thekadeshi.com/api/getConfig?site=".$_SERVER['SERVER_NAME'],
        $use_include_path = false,
        $context);

	echo("-=");
	print_r($pageContent);
	echo("=-");

	if($pageContent !== '') {
		PrintResult('file_get_contents read', true, $mode);
	}

} catch (Exception $e) {
	PrintResult('file_get_contents read', false, $mode);
}