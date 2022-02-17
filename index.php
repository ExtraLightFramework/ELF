<?php
	define ('_VALID_CODE', 1);
	
	if (is_dir('install')) {
		require_once('install/install.php');
	}
	else {
		require_once('elf.php');
	
		$c = Elf::routing()->controller_to();
		$c = new $c;
		if (method_exists($c,Elf::routing()->method_to()))
			call_user_func_array([$c, Elf::routing()->method_to()], Elf::routing()->params());
		else {
			if (!Elf::is_xml_request())
				Elf::redirect('main/_404');
			else
				throw new \Exception('Method '.Elf::routing()->method_to().' in controller '.Elf::routing()->controller_to().' not found');
		}

	}
