<?php

namespace Elf\Controllers;

use Elf;

class Slog extends Admin {
	
	function index($manager_id = 0) {
		echo Elf::set_layout('admin')->load_view('slog/index',['stat'=>Elf::slog()->_stat($manager_id),
																'manager_id'=>(int)$manager_id]);
	}
}
