<?php

namespace Elf\Controllers;

use Elf;

class Banner extends Main {
	
	function load($subdir = '') {
		$b = new Elf\Libs\Banners($subdir);
		if (Elf::is_xml_request()) {
			echo json_encode($b->_data());
		}
		else
			return $b->_data();
	}
}
