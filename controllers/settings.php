<?php

namespace Elf\Controllers;

use Elf;

class Settings extends Admin {
	
	function index() {
		echo Elf::set_layout('admin')->load_view('settings/index',array('setts'=>Elf::settings()->_data()));
	}
	function _edit() {
		if (Elf::settings()->_edit()) {
			Elf::messagebox(Elf::lang('settings')->item('save'));
		}
		else {
			Elf::messagebox(Elf::lang('settings')->item('unsave'));
		}
		Elf::redirect('settings');
	}
	function md5hash() {
		echo md5(Elf::input()->get('v'));
	}
}
