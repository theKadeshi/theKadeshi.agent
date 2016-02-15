<?php
/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 07.02.2016
 * Time: 13:28
 * Created by PhpStorm.
 */

namespace TheKadeshi;


class Content {

	public function GetContent($url) {
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

		return array('headers' => $headers, 'content' => mb_convert_encoding($pageContent, 'utf-8'));
}

}