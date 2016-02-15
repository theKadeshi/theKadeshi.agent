<?php
/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 05.02.2016
 * Time: 13:58
 * Created by PhpStorm.
 */

//$url = "http://it-solutions.su/";
$url = "http://xn----itbabresdcddhxdniy5lh.xn--p1ai/";
//$url = "http://gormashsnab.ru/";

require_once('classes/content.php');
require_once('classes/cms.php');

$cms = new TheKadeshi\CMS($url);
echo($cms->detectedCMS);

//$receivedContent = GetContent($url);

//$cms->DetectCMS($url);

/*
function GetContent($url) {
		$curl = curl_init();

		$urlDatails = parse_url($url);

		$curlOptions = array();

		$curlOptions[CURLOPT_RETURNTRANSFER] = true;
		$curlOptions[CURLOPT_URL] = $url;
		$curlOptions[CURLOPT_TIMEOUT] = 300;
		$curlOptions[CURLOPT_FOLLOWLOCATION] = false;
		$curlOptions[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.115 Safari/537.36';

		$curlOptions[CURLOPT_REFERER] = 'http://websolution.pro';

		curl_setopt_array($curl, $curlOptions);
		$pageContent = curl_exec($curl);

		curl_close($curl);

		$headers=get_headers($url, 1);

		//print_r($headers);

		return array('headers' => $headers, 'content' => $pageContent);
}
*/