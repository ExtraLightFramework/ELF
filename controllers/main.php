<?php

namespace Elf\Controllers;

use Elf;

class Main {
	function __construct() {
		Elf::no_cache();
	}
	function index() {
		echo Elf::load_view('main/index');
	}
	function _404() {
		header('HTTP/1.1 404 Not Found');
		echo Elf::load_view('main/404');
	}
	function _302() {
		header('HTTP/1.1 302 Moved Temporarily');
		echo Elf::load_view('main/302');
	}
	function unsubscribe($code) {
		if ($code)
			echo Elf::load_view('main/unsubscribe',['code'=>$code]);
		else
			Elf::redirect();
	}
}
