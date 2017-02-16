<?php

/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 07.02.2016
 * Time: 16:26
 * Created by PhpStorm.
 */
require_once '../thekadeshi.php';

class ScannerTest extends PHPUnit_Framework_TestCase {

	public function testGetCatalogContent() {
		
	}

	public function testSetFileCheckSum() {
		$scanner = new Scanner();
		$fileName = "../site_info.php";

		$result = $scanner->SetFileCheckSum($fileName);
		$this->assertEquals(1, $result);
	}
}
