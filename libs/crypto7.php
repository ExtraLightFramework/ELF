<?php

namespace Elf\Libs;

use Elf;

define ('CRYPTO_ALG', 'aes-256-cbc');

class Crypto7 {
	private $td;
	private $iv;
	private $ks;

	function __construct() {
		if (function_exists('openssl_random_pseudo_bytes')) {
			$this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(CRYPTO_ALG));
		}
		else {
			throw new \Exception('Open SSL PHP module not installed');
		}
	}
	function encode($data, $key = null) {
		$key = $key===null?SECRET_WORD:$key;
		return base64_encode(openssl_encrypt($data, CRYPTO_ALG, $key, 0, $this->iv) . '::' . $this->iv);
	}
	function decode($data, $key = null) {
		$key = $key===null?SECRET_WORD:$key;
		list($data, $this->iv) = array_pad(explode('::', base64_decode($data), 2),2,null);
		return openssl_decrypt($data, CRYPTO_ALG, $key, 0, $this->iv);
	}
}