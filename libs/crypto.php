<?php

namespace Elf\Libs;

use Elf;

class Crypto {
	private $td;
	private $iv;
	private $ks;

	function __construct() {
		if (version_compare(phpversion(), '7.2.0', '>')) {
			throw new \Exception('MCrypt module is depricated and removed in '.phpversion().' PHP version');
		}
		elseif (function_exists('mcrypt_module_open')) {
			$this->td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
			$this->ks = mcrypt_enc_get_key_size ($this->td);
			$this->iv = 'The open crypt keyThe open crypt';
		}
		else {
			throw new \Exception('MCrypt PHP module not installed');
		}
	}
	function __destruct () {
		@mcrypt_generic_deinit ($this->td);
		@mcrypt_module_close ($this->td);
	}
	function encode($text, $key = null) {
		$key = $key===null?SECRET_WORD:$key;
		mcrypt_generic_init($this->td, $this->_encode_key($key), $this->iv);
		return mcrypt_generic($this->td, $text);
	}
	function decode($text, $key = null) {
		$key = $key===null?SECRET_WORD:$key;
		@mcrypt_generic_deinit($this->td);
		mcrypt_generic_init ($this->td, $this->_encode_key($key), $this->iv);
		return mdecrypt_generic ($this->td, $text);
	}
	private function _encode_key($key) {
		return substr(md5($key), 0, $this->ks);
	}
}
