<?php
/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 08.02.2016
 * Time: 8:09
 * Created by PhpStorm.
 */

namespace TheKadeshi;

class FileList {

	public $fileList = null;

	function __construct() {
		$this->fileList = array();
	}


	public function GetFileList($dir) {

		$dirContent = scandir($dir);
		foreach($dirContent as $directoryElement) {
			if($directoryElement != '..' && $directoryElement != '.') {
				if (is_file($dir . '/' . $directoryElement)) {
					$this->fileList[] = $dir . '/' . $directoryElement;
				}
				if (is_dir($dir . '/' . $directoryElement)) {
					$this->GetFileList($dir . '/' . $directoryElement);
				}
			}
		}
		//print_r(self::$fileList);
		//print_r($dirContent);
	}
}