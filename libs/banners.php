<?php

namespace Elf\Libs;

use Elf;

class Banners {
	
	static $UPLOADIR= ROOTPATH.APP_DIR.'/views/banners/';
	static $EXT 	= 'ban';
	static $TMPL 	= 'banners/tmpl';
	private $path;
	private $files;
	
	function __construct($subdir = '') {
		if (!is_dir(static::$UPLOADIR.$subdir))
			throw new \Exception('ELF Banners: module directory ('.static::$UPLOADIR.$subdir.') is not exists');
		
		$this->path = static::$UPLOADIR.$subdir.'/';
		$this->files = [];
		
		if (!scandir($this->path))
			throw new \Exception('ELF Banners: directory (/'.$subdir.') can`t be read');
	}
	private function _init_data() {
		if ($files = scandir($this->path)) {
			$cnt = 0;
			foreach($files as $f) {
				if ($f != '.'
					&& $f != '..'
					&& (pathinfo($this->path.$f, PATHINFO_EXTENSION) == static::$EXT)
					&& filesize($this->path.$f)) {
					$cnt ++;
					$this->files[] = $this->path.$f;
				}
			}
			if (!$cnt)
				throw new \Exception('ELF Banners: directory (/'.$subdir.') is empty. Files *.ban not found, or all .ban files are empty');
		}
	}
	function _data() {
		$this->_init_data();
		$ret = [];
		foreach ($this->files as $v) {
			if (($sz = filesize($v))
				&& ($f = fopen($v, 'rb'))
				&& ($data = fread($f, $sz))) {
				$ret[] = $data;
				fclose($f);
			}
		}
		return $ret;
	}
	function _upload($name, $out) {
		$fname = $this->path.$name.'.'.static::$EXT;
		if ($f = fopen($fname, 'wb')) {
			fwrite($f, $out);
			fclose($f);
		}
	}
	function _remove($name) {
		@unlink($this->path.$name.'.'.static::$EXT);
	}
	
	// $data DATA FORMAT
	// смотреть необходимые поля для баннера в файлах views/$TMPL или app/views/$TMPL
    function _standart_create($name, $data) {
		$fname = $this->path.$name.'.'.static::$EXT;
		$tmpl = Elf::load_template(static::$TMPL, $data);
		if (file_exists($fname))
			@unlink($fname);
		if ($f = fopen($fname, "wb")) {
			fwrite($f, $tmpl);
			fclose($f);
		}
	}
}
