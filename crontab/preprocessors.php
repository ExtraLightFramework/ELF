#!/usr/bin/php
<?php
// cron shedule:	*	*	*	*	*	<PATH_TO_PROJECT>/crontab/preprocessors.php > /dev/null

// ---------- common DEFINES -------------
define ('ROOT', implode('/', array_slice( explode('/',__DIR__), 0, sizeof(explode('/',__DIR__)) - 1)) . '/');
define ('DIRNAME', '.preprocessors');
define ('COUNTER',	12);
define ('DELAY',	5);

// ----------- LESS defines ---------
define ('LESS', 'less');
define ('LESS_DIR', ROOT.DIRNAME.'/.'.LESS);
define ('LESS_MONITOR', LESS_DIR.'/'.LESS.'.monitor');
define ('LESS_EXT', LESS);
define ('LESS_PERMANENT_EXT', 'pless');
define ('LESS_EXT_OUT', 'css');
define ('LESS_CMD', '/usr/local/bin/lessc %s %s > '.LESS_DIR.'.log');

// ------- MAIN LOOP ----------
$start = COUNTER;
while (--$start) {

	// ----------- LESS -------------
	if (is_dir(LESS_DIR)
		&& ($files = get_files(LESS_DIR))) {
		$chk = [];
		if (file_exists(LESS_MONITOR) && ($monitor = fopen(LESS_MONITOR, 'rb'))) {
			$chk = (array)json_decode(fread($monitor, filesize(LESS_MONITOR)));
			fclose($monitor);
		}
		foreach ($files as $file) {
			switch (pathinfo($file, PATHINFO_EXTENSION)) {
				case LESS_EXT:
					if ((array_key_exists($file, $chk)
							&& (filemtime($file) > $chk[$file]))
						|| !array_key_exists($file, $chk)) {
						less($file);
					}
					$chk[$file] = filemtime($file);
					break;
				case LESS_PERMANENT_EXT:
					less($file);
					break;
			}
		}
		
		if ($monitor = fopen(LESS_MONITOR, 'wb')) {
			fwrite($monitor, json_encode($chk));
			fclose($monitor);
		}
	}
	// ------- LESS END --------------------------------------
	
	sleep(DELAY);
} // --- END MAIN LOOP

function get_files($dir) {
	$ret = [];
	if ($dh = opendir($dir)) {
		while ($s = readdir($dh)) {
			if ($s == '.' || $s == '..') continue;
			if (is_dir($dir.'/'.$s)) $ret = array_merge($ret, get_files($dir.'/'.$s));
			if (is_file($dir.'/'.$s)) {
				$ret[] = $dir.'/'.$s;
			}
		}
	}
	return $ret;
}

function less($file) {
	$to = str_replace(DIRNAME.'/.'.LESS.'/','',pathinfo($file, PATHINFO_DIRNAME)) . 
								'/'.pathinfo($file, PATHINFO_FILENAME).'.'.LESS_EXT_OUT;
	exec(sprintf(LESS_CMD, escapeshellcmd($file), $to));
	echo $to."\n\n";
	chmod($to, 0666);
}
