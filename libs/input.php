<?php

namespace Elf\Libs;

use Elf;

class Input {
	private $data;

	function __construct() {
		$this->data = $_GET;
		$this->data = array_merge($this->data,$_POST);
	}
	function get($key, $safe = true) {
		if (isset($this->data[$key])) {
			if ($safe) {
				if (is_array($this->data[$key])) {
					foreach ($this->data[$key] as $k=>$v)
						$this->data[$key][$k] = htmlspecialchars($v);
					return $this->data[$key];
				}
				else
					return htmlspecialchars($this->data[$key]);
			}
			else {
				if (is_array($this->data[$key])) {
					foreach ($this->data[$key] as $k=>$v)
						$this->data[$key][$k] = html_entity_decode($v);
					return $this->data[$key];
				}
				else
					return $this->data[$key];
			}
		}
		else
			return null;
	}
	function get_email($key) {
		if (!($ret = $this->get($key))
			|| !filter_var($ret, FILTER_VALIDATE_EMAIL)) {
			$ret = null;
		}
		return $ret;
	}
	function set($key, $val = null) {
		if ($val !== null) {
			$this->data[$key] = $val;
		}
		elseif (isset($this->data[$key])) {
			$this->data[$key] = null;
			unset($this->data[$key]);
		}
	}
	function is_set($key) {
		return isset($this->data[$key]);
	}
	function un_set($key) {
		if ($this->is_set($key))
			unset($this->data[$key]);
	}
	function data($safe = true) {
		$ret = null;
		if ($safe) {
			foreach ($this->data as $k=>$v) {
				if (!is_array($v))
					$ret[$k] = htmlspecialchars($v);
				else {
					foreach ($v as $kk=>$vv) {
						$ret[$k][$kk] = !is_array($vv)?htmlspecialchars($vv):$vv;
					}
				}
			}
		}
		else
			$ret = $this->data;
		return $ret;
	}
}