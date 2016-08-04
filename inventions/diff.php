<?php
function GetLCSAlgoritm(&$_a, &$_b) {
	$firstArray = explode(' ', $_a);
	$secondArray = explode(' ', $_b);
	$maxLen = array();
	$firstArrayCounter = count($firstArray);
	$secondArrayCounter = count($secondArray);

	for ($firstCounter = 0; $firstCounter <= $firstArrayCounter; $firstCounter++) {
		$maxLen[$firstCounter] = array();

		for ($secondCounter = 0; $secondCounter <= $secondArrayCounter; $secondCounter++) {
			$maxLen[$firstCounter][$secondCounter] = '';
		}
	}
	for ($firstCounter = $firstArrayCounter  - 1; $firstCounter >= 0; $firstCounter--) {
		for ($secondCounter = $secondArrayCounter - 1; $secondCounter >= 0; $secondCounter--) {
			if ($firstArray[$firstCounter] === $secondArray[$secondCounter]) {
				$maxLen[$firstCounter][$secondCounter] = 1 + $maxLen[$firstCounter + 1][$secondCounter + 1];
			} else {
				$maxLen[$firstCounter][$secondCounter] = max($maxLen[$firstCounter + 1][$secondCounter], $maxLen[$firstCounter][$secondCounter + 1]);
			}
		}
	}

	$rez = '';
	for ($firstCounter = 0, $secondCounter = 0; $maxLen[$firstCounter][$secondCounter] !== 0 && $firstCounter < $firstArrayCounter  && $secondCounter < $secondArrayCounter;) {
		if ($firstArray[$firstCounter] === $secondArray[$secondCounter]) {
			$rez .= $firstArray[$firstCounter] . ' ';
			$firstCounter++;
			$secondCounter++;
		} else {
			if ($maxLen[$firstCounter][$secondCounter] === $maxLen[$firstCounter + 1][$secondCounter]) {
				$firstCounter++;
			} else {
				$secondCounter++;
			}
		}
	}
	return trim($rez);
}
	
function GetUniqueStr(&$arr, &$arrUnick) {
	$s='';
	$arrUnickFlip = array_flip($arrUnick);
	foreach($arr as $v) {
		$s .= $arrUnickFlip[$v].' ';
	}
	return trim($s);
}
	
function FromUniqueToArr(&$arrStr, &$arrUnick) {
	$r = array();			
	foreach($arrStr as $v) {
		$buff   = array();
		$buff[] = $arrUnick[$v[0]];
		$buff[] = $v[1];
		$r[]    = $buff;
	}		
	return $r;
}
	
function SelDiffsStr(&$_a, &$_b, &$retA, &$retB) {
	$_longest = GetLCSAlgoritm($_a, $_b);
	$longest = explode(' ', $_longest);

	$a = explode(' ', $_a);
	$b = explode(' ', $_b);
	$rB = array();

	$i1 = 0;
	$i2 = 0;
	for ($i = 0, $iters = count($b); $i < $iters; $i++) {
		$symbol = array();
		if (isset($longest[$i1]) && $longest[$i1] == $b[$i2]) {
			$symbol[] = $longest[$i1];
			$symbol[] = '*';
			$rB[] = $symbol;
			$i1++;
			$i2++;
		} else {
			$symbol[] = $b[$i2];
			$symbol[] = '+';
			$rB[] = $symbol;
			$i2++;
		}
	}
	$retB = $rB;

	$i1 = 0;
	$i2 = 0;
	for ($i = 0, $iters = count($a); $i < $iters; $i++) {
		$symbol = array();
		if (isset($longest[$i1]) && $longest[$i1] == $a[$i2]) {
			$symbol[] = $longest[$i1];
			$symbol[] = '*';
			$rA[] = $symbol;
			$i1++;
			$i2++;
		} else {
			$symbol[] = $a[$i2];
			$symbol[] = '-';
			$rA[] = $symbol;
			$i2++;
		}
	}
	$retA = $rA;
}
	
function SelDiffsText(&$aText, &$bText, &$retAText, &$retBText) {
	$arrA = str_replace("\r", '', $aText);
	$arrB = str_replace("\r", '', $bText);
	$arrA = explode("\n", $arrA);
	$arrB = explode("\n", $arrB);
	$uniqueTable = array_unique(array_merge($arrA, $arrB));

	$strA = GetUniqueStr($arrA, $uniqueTable);
	$strB = GetUniqueStr($arrB, $uniqueTable);

	SelDiffsStr($strA, $strB, $retA, $retB);
	$retAText = FromUniqueToArr($retA, $uniqueTable);
	$retBText = FromUniqueToArr($retB, $uniqueTable);
}
/*
function SelDiffsColor(&$rdyAText, &$rdyBText, &$strRetA, &$strRetB) {
	$strRetA = '';
	$strRetB = '';

	foreach($rdyAText as $v) {
		if($v[1] === '+') 		$strRetA.='<font color="#00cc33">'.$v[0].'</font>';
		elseif($v[1] == '-') 	$strRetA.='<font color="#7c7b7c"><s>'.$v[0].'</s></font>';
		elseif($v[1] == 'm')	$strRetA.='<font color="#e64444">'.$v[0].'</font>';
		elseif($v[1] == '*')	$strRetA.=$v[0];
	}

	foreach($rdyBText as $v) {
		if($v[1] === '+')		$strRetB.='<font color="#00cc33">'.$v[0].'</font>';
		elseif($v[1] == '-')	$strRetB.='<font color="#7c7b7c"><s>'.$v[0].'</s></font>';
		elseif($v[1] == 'm')	$strRetB.='<font color="#e64444">'.$v[0].'</font>';
		elseif($v[1] == '*')	$strRetB.=$v[0];
	}
}
*/
function MergeInsertAndDelete(&$rdyAText, &$rdyBText) {
	$max = count($rdyAText)>count($rdyBText)?count($rdyAText):count($rdyBText);
/*
	for($i1=0,$i2=0; $i1<$max && $i2<$max; ) 	{
		if($rdyAText[$i1][1]=="-" && $rdyBText[$i2][1]=="+" && $rdyBText[$i2][0]!="") {
			$rdyAText[$i1][1]="*";
			$rdyBText[$i2][1]="m";
		}
		elseif($rdyAText[$i1][1]!="-" && $rdyBText[$i2][1]=="+") $i2++;
		elseif($rdyAText[$i1][1]=="-" && $rdyBText[$i2][1]!="+") $i1++;
		
		$i1++;
		$i2++;
	}
*/
//print_r($rdyAText);
	//$diff = array_diff($rdyAText, $rdyBText);
//print_r($diff);
}

// ***********************************************************
// 					Main function
// ***********************************************************
// string  $sA, $sB 	= 	strings where try find differences
// string  $retA, $retB	=	strings for return result of work
function SelectedDiffs(&$sA, &$sB, &$retA, &$retB) {
	SelDiffsText($sA,$sB,$retAText,$retBText);
	//print_r(array($retAText, $retBText));
	MergeInsertAndDelete($retAText,$retBText);
	//print_r(array($retAText, $retBText));
	//SelDiffsColor($retAText,$retBText,$retA,$retB);
	foreach ($retAText as $key => $value) {
		if($value[1] !== '*') {
			echo($key . " [".$value[1]."] " . $value[0] . "\r\n");
		}
	}
	foreach ($retBText as $key => $value) {
		if($value[1] !== '*') {
			echo($key . " [".$value[1]."] " . $value[0] . "\r\n");
		}
	}
}

$firstString = 'Мать, мать, мать...
Привычно отозвалось эхо
После чего грязно выругалось
И пошло бухать';
$secondString = 'Мать, мать, мать...
Но молчание было ему ответом
Само собой грязно выругалось
Вытащило голову из жопы
И пошло бухать';
$ret1 = '';
$ret2 = '';
$startTime = microtime(true);
SelectedDiffs($firstString, $secondString, $ret1, $ret2);
$endTime = microtime(true);
echo($endTime - $startTime) . "\r\n";
//print_r(array($ret1, $ret2));
