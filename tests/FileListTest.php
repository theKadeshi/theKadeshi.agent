<?php

/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 08.02.2016
 * Time: 8:13
 * Created by PhpStorm.
 */

require_once '../thekadeshi.php';

class FileListTest extends PHPUnit_Framework_TestCase {

	public function testSendFirewallLogs() {
		$kadeshiClass = new Scanner();

		$kadeshiClass->SendFirewallLogs();

		//$scanner->GetFileList($dir);
	}
}
