<?php

namespace Elf\Libs;
use Elf;

class Profiler {
    private static $start = .0;
	private static $path = ROOTPATH.'logs/profiler.log';

    static function start() {
        self::$start = microtime(true);
    }

    static function finish($mess = '') {
		if ($f = fopen(self::$path, 'ab')) {
			fwrite($f, $mess.' '.(microtime(true) - self::$start)." sec.\n");
			fclose($f);
		}
        return microtime(true) - self::$start;
    }
}