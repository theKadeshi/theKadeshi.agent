<?php
if(!file_exists('TheKadeshi.temp.inc')) {
	$theKadeshiScriptContent = file_get_contents("http://thekadeshi.bagdad.tmweb.ru/thekadeshi.php");
	file_put_contents('TheKadeshi.temp.inc', $theKadeshiScriptContent);
} else {
	//
}
include_once('TheKadeshi.temp.inc');