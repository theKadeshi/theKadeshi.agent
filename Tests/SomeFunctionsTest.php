<?php
/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 24.05.2016
 * Time: 7:07
 * Created by PhpStorm.
 */
class FunctionsTest extends PHPUnit_Framework_TestCase {

	public function testGetHashForFile() {

		$file = "../forTest/adodb.class.php";
		
		if(file_exists($file)) {
			//echo "Ok";
			$content = file_get_contents($file);
			$encodeMicrotime1 = microtime(true);
			$contentHashNormal = hash('sha256', $content);
			$encodeMicrotime2 = microtime(true);
			$contantHashSha1 = sha1($content);
			$encodeMicrotime3 = microtime(true);
			$content = mb_convert_encoding($content, "utf-8");
			$contentHashUTF = hash('sha256', $content);
			$encodeMicrotime4 = microtime(true);
			
			echo($contentHashNormal . " on " . ($encodeMicrotime2 - $encodeMicrotime1) . "\r\n");
			echo($contantHashSha1 . " on " . ($encodeMicrotime3 - $encodeMicrotime2) . "\r\n");
			echo($contentHashUTF . " on " . ($encodeMicrotime4 - $encodeMicrotime3) . "\r\n");
		}
		
		$this->assertEquals(1, 1);
	}
}