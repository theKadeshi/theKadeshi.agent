<?php
/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 07.02.2016
 * Time: 15:50
 * Created by PhpStorm.
 */
echo "Prepend";
print_r($_SERVER);
if(isset($_SERVER['SCRIPT_FILENAME'])) {
	require_once('classes/ScannerController.inc.php');
    $controller = new ScannerController();
}