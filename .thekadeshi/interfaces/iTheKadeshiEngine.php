<?php
/**
 * Created by PhpStorm.
 * Url: https://github.com/theKadeshi/theKadeshi.agent
 * Home: https://thekadeshi.com/en/
 * Date: 14.08.2017
 * Time: 12:27
 */

namespace TheKadeshi;


interface iTheKadeshiEngine
{
	public function Scan($fileName, $needChecksum);

	public function SetFileCheckSum($fileName);

	public function GetFileCheckSum($fileName);

	public function CompareFileCheckSum($fileName);

	// public function HeuristicFileContent($fileName);

	// public function HeuristicFileName($fileName);

	// public function Heuristic($filename);

	public function SaveAnamnesis();

	public function SendAnamnesis($sendToken);
}