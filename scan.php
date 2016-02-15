<?php
/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 07.02.2016
 * Time: 16:17
 * Created by PhpStorm.
 */

require_once 'classes/scanner.php';
require_once 'classes/filelist.php';

$scanner = new TheKadeshi\Scanner();
$filelist = new TheKadeshi\FileList();

$filelist->GetFileList(__DIR__);

print_r($filelist->fileList);

foreach($filelist->fileList as $file) {

	$scanResults = $scanner->Scan($file);
	if($scanResults != null) {
		$results[] = $scanResults;
	}

}

print_r($results);

