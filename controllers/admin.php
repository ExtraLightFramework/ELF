<?php

namespace Elf\Controllers;

use Elf;

class Admin extends Auth {
	function __construct($redirect = null) {
		Elf::set_data('action',Elf::site_url().'admin/index');
		parent::__construct(GROUP_ADMIN|GROUP_TECH, true, null, $redirect?$redirect:'/admin/index');
		Elf::set_layout('admin');
	}
	function index() {
		echo Elf::set_layout('admin')->load_view('admin/index');
	}
}
