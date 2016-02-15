<?php
/**
 * Project: antivir
 * User: Bagdad ( https://goo.gl/mRvZBa )
 * Date: 05.02.2016
 * Time: 14:09
 * Created by PhpStorm.
 */

namespace TheKadeshi;

class CMS {
	private $CMS = array();

	private $RulesDir = __DIR__ . '/cms';
	private $RulesFileList = array();
	private $RulesList = array();

	static $cmsVariation = array();

	public $detectedCMS = '';

	function __construct($url)
	{
		self::DetectCMS($url, 1);

		arsort(self::$cmsVariation, SORT_NUMERIC);
		$arraySlice = array_slice(self::$cmsVariation, 0, 2);

		if(array_slice(self::$cmsVariation, 0, 1, true) == array_slice(self::$cmsVariation, 1, 1, true)) {
			self::DetectCMS($url, 2);
		}

		$this->detectedCMS = array_keys(array_slice(self::$cmsVariation, 0, 1, true))[0];
	}


	/**
	 * Функция получения списка правил из каталога
	 *
	 * @return mixed
	 */
	private function GetCMSSignaturesFiles() {
		$list = null;
		$filesList = scandir($this->RulesDir);

		foreach($filesList as $file) {
			if($file != '.' && $file != '..') {
				$list[] = $file;
			}
		}

		$this->RulesFileList = $list;

		return $list;
	}

	/**
	 * Функция загрузки правил определения CMS
	 *
	 * @return array
	 */
	private function LoadRules() {
		$rules = array();

		foreach($this->RulesFileList as $ruleFile) {
			$fileName = $this->RulesDir . '/' . $ruleFile;
			//$xmlContent = file_get_contents($fileName);
			$xml = simplexml_load_file($fileName, 'SimpleXMLElement', LIBXML_NOCDATA);

			$cmsName = trim($xml->name[0]);
			$cmsRules = $xml->rules[0];
			foreach($cmsRules as $cmsRule) {
				$method = trim($cmsRule->method[0]);
				$weight = trim($cmsRule->weight[0]);
				$path = trim($cmsRule->path[0]);
				$regexp = trim($cmsRule->regexp[0]);
				$step = filter_var($cmsRule->step[0], FILTER_SANITIZE_NUMBER_INT);
				$this->CMS[$cmsName][] = array(
					'method' => $method,
					'weight' => $weight,
					'path' => $path,
					'regexp' => $regexp,
				    'step' => $step
				);
			}
		}

		$this->RulesList = $rules;

		return $rules;
	}

	/**
	 * Основная функция определения текущей CMS на сайте
	 * На выходе получается архив с вероятными CMS
	 * @param $url
	 */
	private function DetectCMS($url, $currentStep = 1) {
		self::GetCMSSignaturesFiles();
		self::LoadRules();

		$contentClass = new Content();
		$content = $contentClass->GetContent($url);
		$lastPath = '/';

		//$cmsVariation = array();
		foreach ($this->CMS as $cmsName => $currentCmsRules) {
			self::$cmsVariation[$cmsName] = 0;
			foreach($currentCmsRules as $currentRule) {
				if($currentRule['step'] == $currentStep) {
					if ($currentRule['path'] != $lastPath) {
						$content = $contentClass->GetContent($url . $currentRule['path']);
						$lastPath = $currentRule['path'];
					}
					//print_r($currentRule);
					if ($currentRule['method'] == 'content') {
						preg_match($currentRule['regexp'], $content['content'], $result);

						if (!empty($result)) {
							//echo($currentRule . "\r\n");
							//print_r($result);
							self::$cmsVariation[$cmsName] = self::$cmsVariation[$cmsName] + $currentRule['weight'];
						}
					}
				}
			//print_r($cmsName);
				}
		}

		//print_r($cmsVariation);
	}
}