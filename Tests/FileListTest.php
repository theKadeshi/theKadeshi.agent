<?php

/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 08.02.2016
 * Time: 8:13
 * Created by PhpStorm.
 */

require_once '../classes/filelist.php';

class FileListTest extends PHPUnit_Framework_TestCase {

	public function testScan() {
		$scanner = new TheKadeshi\FileList();
		$dir = __DIR__ . '/..';

		$scanner->GetFileList($dir);
	}
}
