<?php

namespace Elf\Libs;
use Elf;

class Redirector {
	static private $rules;
	static private $entry_points_enable = false;
	
	static function redirect() {
//		print_r($_SERVER);
//		echo http_response_code();
//		exit;
		if (!self::$rules)
			self::$rules = new Db('redirector');
		if (($pos = strrpos($_SERVER['REQUEST_URI'], '?')) !== false) {
			$origin = substr($_SERVER['REQUEST_URI'],0,$pos);
			$get = substr($_SERVER['REQUEST_URI'],$pos);
		}
		else
			$origin = $_SERVER['REQUEST_URI'];
		if (self::$entry_points_enable
			&& !empty($_SERVER['HTTP_REFERER'])
			&& self::is_valid_referer($_SERVER['HTTP_REFERER'])) {
			$headers = @get_headers("https://xn---56-qddbsapokcix6ab3c.xn--p1ai".$origin);
			$code = substr($headers[0], 9, 3);
			if ((int)$code) {
				$entry = new Db('entry_points');
				$entry->_insert(['request_uri'=>$origin,
									'referer'=>$_SERVER['HTTP_REFERER'],
									'code'=>$code,
									'get_params'=>!empty($get)?$get:'',
									'tm'=>date('Y-m-d H:i:s')])->_execute(null, true);
			}
		}
		if (strrpos($origin, '/') === (strlen($origin)-1))
			$uri = substr($origin,0,strlen($origin)-1);
		else
			$uri = $origin;
		$uri_slash = $origin;
		if (!empty($_GET['ref']) && ($_GET['ref'] == 'zdt')) {
			if ($rec = self::$rules->get("`request_uri` IN ('{$uri}','{$uri_slash}','{$uri}.html','/zapchasti{$uri}','/zapchasti{$uri}.html')")) {
				//print_r($rec);
				header("Location: ".Elf::site_url(false).$rec['redirect_to'].'/', true, 301);
				exit;
			}
		}
		else {
			if ($rec = self::$rules->get("`request_uri` IN ('{$uri}','{$uri_slash}')")) { 
	//				|| ($rec = self::$rules->get("`request_uri` LIKE '%{$uri}%'")))) {
				if ($rec['redirect_to'] != $rec['request_uri']) {
					header("Location: ".Elf::site_url(false).$rec['redirect_to'].'/'.(!empty($get)?$get:''), true, 301);
					exit;
				}
			}
		}
	}
	static function _add($request_uri, $redirect_to) {
		if (!self::$rules)
			self::$rules = new Db('redirector');
		self::$rules->_insert(['request_uri'=>$request_uri,
								'redirect_to'=>$redirect_to])->_execute(null, true);
	}
	static function is_valid_referer($referer) {
		$chck_list = [
			"https://xn---56-qddbsapokcix6ab3c.xn--p1ai",
			"https://xn-----6kcavhikeavi2au5aqf9c1a1o.xn--p1ai",
			"http://go.mail.ru/search_images",
			"https://kupit-v-internet-magazine.ru",
			"https://3ds-ds1.mirconnect.ru",
			"https://lens.google.com/",
			"https://rokosta.webstab.ru/"
		];
		$ret = true;
		foreach ($chck_list as $v)
			if (substr($referer,0,strlen($v)) == $v) {
				$ret = false;
				break;
			}
		return $ret;
	}
}