<?php

namespace Elf\Libs;

class Lang {

	private $lang;
	private $requestlng;
	
	function init($array) {
		if (!empty($array) && is_array($array) && sizeof($array)) {
			foreach ($array as $k=>$v) {
				$this->lang[$k] = $v;
			}
		}
	}
	function _load($lng, $syslang, $applang) {
		if ($lng && !isset($this->lang[$lng])) {
			if (file_exists($syslang)) {
				include ($syslang); // init lang Array
			}
			if (file_exists($applang)) {
				include ($applang); // init lang Array
			}
			if (!empty($lang) && is_array($lang) && sizeof($lang)) {
				foreach ($lang as $k=>$v) {
					$this->lang[$lng][$k] = $v;
				}
			}
			else {
				throw new \Exception('Lang files <b>'.$syslang.' or '.$applang.'</b> not found');
			}
			unset($lang);
		}
		$this->set_request_lng($lng);
	}
	private function set_request_lng($lng) {
		$this->requestlng = $lng;
	}
	function item($name) {
		if (!$this->requestlng)
			$this->requestlng = 'main';
		if (isset($this->lang[$this->requestlng][$name])) {
			$ret = $this->lang[$this->requestlng][$name];
		}
		elseif (isset($this->lang[$name])) {
			$ret = $this->lang[$name];
		}
		if (!empty($ret)) {
			if (func_num_args() > 1) {
				$args = func_get_args();
				$i = 0;
				while (!empty($args[++$i])) {
					$ret = @str_replace("%%".$i."%%",$args[$i],$ret);
				}
			}
			$ret = preg_replace("/%%\d%%/","",$ret);
		}
		else {
			$ret = (DEBUG_MODE?$this->requestlng.':':'').$name;
		}
		return $ret;
	}
}