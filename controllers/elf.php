<?php

namespace Elf\Controllers;

//use Elf;

class Elf {
	
	function reload_captcha() {
		if (\Elf::input()->get('name'))
			echo \Elf::captcha(\Elf::input()->get('name'),\Elf::input()->get('len')?(int)\Elf::input()->get('len'):4,true);
		else
			echo \Elf::captcha('captcha',\Elf::input()->get('len')?(int)\Elf::input()->get('len'):4,true);
		exit;
	}
	function loadtemplate() {
		if (\Elf::input()->get('template')) {
			\Elf::$_data = \Elf::input()->data();
			echo \Elf::load_template(\Elf::input()->get('template'));
		}
	}
	function showdialog() {
		if (\Elf::input()->get('dialog')) {
			\Elf::input()->set('wid', time());
			\Elf::$_data = \Elf::input()->data();
			$out = \Elf::load_template(\Elf::get_data('appearance')?\Elf::get_data('appearance'):'main/dialog');
			if (\Elf::get_data('auth_required')) {
				if (\Elf::is_xml_request())
					echo json_encode(['redirect'=>'/auth/auth']);
				else
					Elf::redirect('/auth/auth');
			}
			else
				echo json_encode(['dialog'=>$out]);
		}
	}
	function showpopup() {
		if (\Elf::input()->get('dialog')) {
			\Elf::input()->set('wid', time());
			\Elf::$_data = \Elf::input()->data();
			echo json_encode(['pid'=>time(),'left'=>\Elf::$_data['left'],'popup'=>\Elf::load_template('main/popup')]);
		}
	}
	function showtooltip() {
		\Elf::input()->set('wid', time());
		\Elf::$_data = \Elf::input()->data();
		echo json_encode(['pid'=>time(),'popup'=>\Elf::load_template('main/tooltip')]);
	}
	function md5hash() {
		echo md5(\Elf::input()->get('v'));
	}
}