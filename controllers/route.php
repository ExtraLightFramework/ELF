<?php

namespace Elf\Controllers;

use Elf;

class Route extends Admin {
	function index($offset = 0) {
		echo Elf::set_layout('admin')->load_view('route/index',
													['data'=>Elf::routing()->_data($offset),'offset'=>(int)$offset]);
	}
	function edit($offset = 0) {
		if (Elf::routing()->_edit_rec()) {
			Elf::messagebox(Elf::lang('route')->item('save'));
		}
		else {
			Elf::messagebox(Elf::lang('route')->item('unsave',Elf::input()->get('controller'),Elf::input()->get('method')));
		}
		Elf::redirect('route/index/'.$offset);
	}
	function _del($c, $m, $offset = 0) {
		Elf::routing()->_del(base64_decode($c),base64_decode($m));
		$this->index($offset);
	}
	function sitemap() {
		Elf::routing()->sitemap();
		Elf::messagebox(Elf::lang('route')->item('sitemap.success'));
		Elf::redirect('route/index');
	}
}
