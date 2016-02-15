<?php

/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 07.02.2016
 * Time: 16:26
 * Created by PhpStorm.
 */
require_once '../classes/scanner.php';

class ScannerTest extends PHPUnit_Framework_TestCase {

	public function testScan() {
		$scanner = new TheKadeshi\Scanner();
		$fileName = 'virus3.php';

		$scanner->Scan($fileName);
	}
}
