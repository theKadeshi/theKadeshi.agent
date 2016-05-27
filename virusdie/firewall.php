<?php if (!defined('FW_FILEPATH'))
	return TRUE; fw_init(dirname(__FILE__), TRUE); define('FW_IP', trim(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? implode('', array_slice(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'], 2), 0, 1)) : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''))); define('FW_IPF', fw_iptofile(FW_IP)); if ((PHP_SAPI === 'cli') || !strlen(FW_IP) || preg_match('/(^127\.|^192\.168\.|^::1?$|^[Ff][CcDd]00:)/', FW_IP) || is_file(FW_PATH_IPWL . '/' . FW_IPF))
	return fw_deinit(TRUE); if (!((is_dir(FW_PATH_LOGDB) || mkdir(FW_PATH_LOGDB, 0771)) && (is_dir(FW_PATH_IPDB) || mkdir(FW_PATH_IPDB, 0771))))
	return fw_deinit(TRUE); if (is_file(FW_PATH . '/.updated') || !is_dir(FW_PATH_SCOREDB)) {
	is_dir(FW_PATH_BLOCKDB) ? fw_removedir(FW_PATH_BLOCKDB, TRUE) : mkdir(FW_PATH_BLOCKDB, 0771, TRUE);
	is_dir(FW_PATH_SCOREDB) ? fw_removedir(FW_PATH_SCOREDB, TRUE) : mkdir(FW_PATH_SCOREDB, 0771, TRUE);
	is_file(FW_PATH . '/.updated') && unlink(FW_PATH . '/.updated');
} elseif (($_ = filemtime(__FILE__)) && (($_ < FW_TIME - 3600 * 12) || ($_ > FW_TIME))) {
	touch(__FILE__);
	is_string($_ = file_get_contents(FW_UPDATE_HOST . '?' . http_build_query(array('fromversion' => FW_VERSION, 'cfn' => FW_PATH_SVCDIR)))) && (strlen($_) >= 4096) && (substr($_, 0, 5) === '<' . '?' . 'php') && strpos($_, 'FW_FILEPATH') && fw_file_safe_rewrite(__FILE__, $_, TRUE) && file_put_contents(FW_PATH . '/.updated', '');
	$mtime = FW_TIME - 600;
	foreach (array(FW_PATH_IPDB, FW_PATH_SCOREDB) as $dir)
		if (is_dir($dir) && is_array($files = scandir($dir)))
			foreach ($files as $file)
				if (($file[0] != '.') && is_file($file = $dir . '/' . $file) && ((int)filemtime($file) < $mtime))
					unlink($file);
	$mtime = FW_TIME - 86400 * 31;
	foreach (array(FW_PATH_BLOCKDB) as $dir)
		if (is_dir($dir) && is_array($files = scandir($dir)))
			foreach ($files as $file)
				if (($file[0] != '.') && is_dir($file = $dir . '/' . $file) && ((int)filemtime($file) < $mtime))
					fw_removedir($file);
	unset($_, $mtime, $dir, $files, $file);
}; $fw_daystat = fw_daystat(); $fw_blockreason = 0; if (is_file(FW_PATH_IPBL . '/' . FW_IPF)) {
	define('FW_BLOCK', 2);
	define('FW_SCORE', 0);
	FW_BLOCK && fw_logblocked(FW_IPF, array(microtime(TRUE), $fw_blockreason = 10)) && ++$fw_daystat[7];
} else {
	$fw_vdb = array(
		'scores' => array(
			'freq' => array(array(0, 0.15, 20, 10),
			                array(0.15, 0.3, 10, 5),
			                array(0.3, 0.7, 5, 2),
			                array(0.7, 2.5, 0, -3),
			                array(2.5, 8, -15, -5),
			                array(8, 20, -40, -15),
			                array(20, 0),),
			'noagent' => 30,
			'badheaders' => 20,
			'extpost' => 40,
			'extfiles' => 100,
			'get' => 100,
			'post' => 40,
			'files' => 100,
			'maxscore' => 150,),
		'files' => array('#\.(php.?|phar|pl|py|asp.?|rb|inc|cgi|htaccess|exe|bat|cmd|(ba|w)?sh)(\.|$)#',),
		'request' => array(
			'#</?(script|iframe)[\s>]#i',
			'#([^a-z\d]|^)document\.[a-z\d\_]+\s*\(#',
			'#base64_?(de|en)code\s*\(#i',
			'#(_GET|_POST|_REQUEST|GLOBALS)[=\[]#',
			'#/s?bin/(ba)?sh#',
			'#(;|\*/)\s*(SELECT|INSERT\s+INTO|UPDATE|DELETE\s+FROM|DROP\s+(TABLE|DATABASE|VIEW)|TRUNCATE\s+TABLE)\s#i',),
		);
	$fw_stat = array('time' => microtime(TRUE), 'ispost' => isset($_SERVER['REQUEST_METHOD']) && (strtolower($_SERVER['REQUEST_METHOD']) === 'post') || isset($_POST) && $_POST, 'isfiles' => isset($_FILES) && $_FILES, 'isajax' => isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'), 'host' => strtolower(isset($_SERVER['SERVER_NAME']) && strlen($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : (isset($_SERVER['HTTP_HOST']) && strlen($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '')), 'agent' => isset($_SERVER['HTTP_USER_AGENT']) && (strlen($_SERVER['HTTP_USER_AGENT']) > 3), 'headers' => array('accept' => isset($_SERVER['HTTP_ACCEPT']) ? 1 : 0, 'acceptlanguage' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? 1 : 0, 'acceptencoding' => isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? 1 : 0, 'connection' => isset($_SERVER['HTTP_CONNECTION']) ? 1 : 0,), 'referer' => isset($_SERVER['HTTP_REFERER']) ? strtolower($_SERVER['HTTP_REFERER']) : '', 'extreferer' => NULL,);
	if (strlen($fw_stat['host']))
		if (substr($fw_stat['host'], 0, 4) === 'www.')
			$fw_stat['host'] = substr($fw_stat['host'], 4);
	if (strlen($fw_stat['referer'])) {
		$_ = parse_url($fw_stat['referer']);
		if (isset($_['host'])) {
			if (substr($_['host'], 0, 4) === 'www.')
				$_['host'] = substr($_['host'], 4);
			$fw_stat['extreferer'] = strlen($_['host']) && strlen($fw_stat['host']) && ($_['host'] != $fw_stat['host']);
		};
	};
	$fw_rating = fw_rating(FW_IPF);
	if ($fw_rating[1]) {
		$_ = $fw_rating[0];
		$diff = abs($fw_stat['time'] - $fw_rating[1]);
		foreach ($fw_vdb['scores']['freq'] as $freq)
			if (($diff >= $freq[0]) && ($diff < $freq[1])) {
				$fw_rating[0] += $fw_stat['isajax'] ? $freq[3] : $freq[2];
				if ($fw_rating[0] < 0)
					$fw_rating[0] = 0;
				break;
			} elseif (!$freq[1]) {
				$fw_rating[0] = 0;
				break;
			};
		if (!$fw_rating[0])
			fw_logscorediff(FW_IPF, array()); elseif ($fw_rating[0] > $_) {
			if ($fw_rating[0] >= 100)
				++$fw_daystat[3];
			fw_logscorediff(FW_IPF, array($fw_stat['time'], $fw_rating[0], $fw_blockreason = 3));
		};
		unset($diff, $freq);
	};
	if (!$fw_stat['agent'])
		++$fw_daystat[6] && ($fw_rating[0] += $fw_vdb['scores']['noagent']) && fw_logscorediff(FW_IPF, array($fw_stat['time'], $fw_rating[0], $fw_blockreason = 6)); elseif (array_sum($fw_stat['headers']) / count($fw_stat['headers']) < 0.75)
		++$fw_daystat[6] && ($fw_rating[0] += $fw_vdb['scores']['badheaders']) && fw_logscorediff(FW_IPF, array($fw_stat['time'], $fw_rating[0], $fw_blockreason = 6));
	if ($fw_stat['extreferer'])
		if ($fw_stat['isfiles'])
			++$fw_daystat[5] && ($fw_rating[0] += $fw_vdb['scores']['extfiles']) && fw_logscorediff(FW_IPF, array($fw_stat['time'], $fw_rating[0], $fw_blockreason = 5)); elseif ($fw_stat['ispost'])
			++$fw_daystat[6] && ($fw_rating[0] += $fw_vdb['scores']['extpost']) && fw_logscorediff(FW_IPF, array($fw_stat['time'], $fw_rating[0], $fw_blockreason = 6));
	foreach ($_GET as $value)
		foreach ($fw_vdb['request'] as $pcre)
			if (is_string($value) && preg_match($pcre, $value)) {
				++$fw_daystat[4] && ($fw_rating[0] += $fw_vdb['scores']['get']) && fw_logscorediff(FW_IPF, array($fw_stat['time'], $fw_rating[0], $fw_blockreason = 4));
				break;
			};
	reset($_GET);
	foreach ($_POST as &$value)
		foreach ($fw_vdb['request'] as $pcre)
			if (is_string($value) && preg_match($pcre, $value)) {
				++$fw_daystat[4] && ($fw_rating[0] += $fw_vdb['scores']['post']) && fw_logscorediff(FW_IPF, array($fw_stat['time'], $fw_rating[0], $fw_blockreason = 4));
				break;
			};
	reset($_POST);
	unset($value, $pcre);
	if ($fw_stat['isfiles']) {
		$names = array();
		foreach ($_FILES as &$file) {
			if (is_array($file['name'])) {
				foreach ($file['name'] as $name)
					$names[] = $name;
				reset($file['name']);
			} else $names[] = $file['name'];
		};
		unset($file);
		reset($_FILES);
		foreach ($names as $name)
			foreach ($fw_vdb['files'] as $pcre)
				if (preg_match($pcre, $name)) {
					++$fw_daystat[5] && ($fw_rating[0] += $fw_vdb['scores']['files']) && fw_logscorediff(FW_IPF, array($fw_stat['time'], $fw_rating[0], $fw_blockreason = 5));
					break 2;
				};
		unset($names, $name, $pcre);
	};
	if ($fw_rating[0] > $fw_vdb['scores']['maxscore'])
		$fw_rating[0] = $fw_vdb['scores']['maxscore'];
	define('FW_BLOCK', ($fw_rating[0] >= 100) ? 1 : 0);
	define('FW_SCORE', $fw_rating[0]);
	FW_BLOCK && fw_logblocked(FW_IPF, array($fw_stat['time'], $fw_blockreason)) && ++$fw_daystat[7];
	$fw_rating[1] = $fw_stat['time'];
	fw_rating(FW_IPF, $fw_rating);
}; if (!FW_BLOCK) {
	++$fw_daystat[1];
	fw_daystat($fw_daystat);
	unset($fw_stat, $fw_vdb, $fw_rating, $fw_daystat, $_);
	return fw_deinit(TRUE);
}; ++$fw_daystat[2]; fw_daystat($fw_daystat); function_exists('http_response_code') ? http_response_code(403) : header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden'); header('Content-Type: text/html; charset=UTF-8'); ?><?php function fw_rating($ipf, $rating = NULL) {
	$f = FW_PATH_IPDB . '/' . $ipf;
	if (is_array($rating))
		return file_put_contents($f, implode(',', $rating), LOCK_EX);
	if (is_file($f) && is_string($r = file_get_contents($f)) && (count($r = explode(',', $r)) === 2) && ($r[1] > 0)) {
		$r[0] = (int)$r[0];
		$r[1] = (float)$r[1];
		return $r;
	};
	return array(0, 0.0);
}; function fw_daystat($stat = NULL) {
	$date = date('Ymd');
	$file = FW_PATH_LOGDB . '/' . substr($date, -2);
	if (is_array($stat) && $stat) {
		file_put_contents($file, implode(',', $stat), LOCK_EX);
		return $stat;
	} else {
		is_file($file) && is_string($r = file_get_contents($file)) && ($r = explode(',', $r)) && ($r[0] == $date) && (count($r) === 8) || ($r = array($date, 0, 0, 0, 0, 0, 0, 0));
		return $r;
	};
}; function fw_init($base, $setEnv) {
	define('FW_VERSION', '1.1.4');
	define('FW_UPDATE_HOST', 'http://cdn.virusdie.ru/data/firewall/');
	define('FW_PATH', $base);
	define('FW_PATH_SVCDIR', basename(dirname($base)));
	define('FW_PATH_BLOCKDB', FW_PATH . '/blocked');
	define('FW_PATH_SCOREDB', FW_PATH . '/scoredb');
	define('FW_PATH_LOGDB', FW_PATH . '/logdb');
	define('FW_PATH_IPDB', FW_PATH . '/ipdb');
	define('FW_PATH_IPWL', FW_PATH . '/ipwl');
	define('FW_PATH_IPBL', FW_PATH . '/ipbl');
	define('FW_TIME', time());
	if ($setEnv) {
		define('FW_ERRORLEVEL', (int)error_reporting(0));
		@date_default_timezone_set(is_string($_ = date_default_timezone_get()) && strlen($_) ? $_ : 'UTC');
		ini_set('pcre.backtrack_limit', 10000000);
	} else {
		define('FW_ERRORLEVEL', (int)error_reporting());
	};
}; function fw_deinit($ret) {
	error_reporting(FW_ERRORLEVEL);
	return $ret;
}; function fw_iptofile($ip) {
	return is_string($ip) ? 
			background-position: center top;
			background-repeat: no-repeat;
			text-align: center;
			box-sizing: border-box;
			-moz-box-sizing: border-box;
			-webkit-box-sizing: border-box;
		}

		p {
			padding: 0;
			margin: 0 0 20px 0;
		}

		.col-1, .col-2 {
			width: 45%;
			margin: 0 0 20px 0;
			font-size: 12px;
			text-align: justify;
		}

		.col-1 {
			float: left;
		}

		.col-2 {
			float: right;
		}

		.col-1 p, .col-2 p {
			margin: 0 0 10px 0;
		}

		.copy {
			font-size: 14px;
		}

		@media only screen and (max-width: 500px) {
			.col-1, .col-2 {
				width: 100%;
			}
		}
	</style>
</head>
<body>
<div id="vcenter">
	<div id="vdsync">

		<p>Извините, вас заблокировали<br/>
			Вы не можете получить доступ к <?= $fw_stat['host']; ?></p>

		<div class="col-1">
			<p><strong>1. Почему ваш IP заблокирован</strong></p>
			Этот сайт использует систему безопасности для защиты от интернет атак.
			Действе, которое вы совершили привело к срабатыванию системы безопасности.
			Существует множество факторов, на которые система безопасности могла среагировать, например множественные
			запросы или SQL инъекция или что-либо другое, вызвавшее у системы безопасности подозрение.
		</div>
		<div class="col-2">
			<p><strong>2. Как получить доступ к <?= $fw_stat['host']; ?></strong></p>
			Вы можете попробовать зайти на этот сайт чуть позже, когда, возможно, система безопасности сама снимет
			ограничения, наложенные на ваш адрес. Вы можете также обратиться к администрации данного сайта для получения
			более подробной информации.
		</div>
		<div class="clr"></div>

		<p><a href="https://virusdie.ru/" target="_blank" alt="Облачный антивирус для сайтов">Вирусдай &mdash; сервис,
				который помогает вашим сайтам быть здоровыми</a></p>
		<p class="copy">&copy; Virusdie.ru</p>

	</div>
</div>
</body>
</html>
<?php exit(); ?>