<?php

namespace Elf\Controllers;

use Elf;
use Elf\Libs\Users;

class Auth extends \Elf\Controllers\Main {
	
	private $model;
	
	function __construct($group = null, $redirect = true, $model = null, $after_auth_redirect = false) {
		$this->model = $model?$model:'Elf\Libs\Users';;
		if (!Elf::input()->get('datain')) {
			if (!Elf::session()->get('group')) { // not logged
				if (Elf::routing()->method_to() != '_404') // page is found 
					$this->auth($group, $redirect, $after_auth_redirect);
			}
			elseif (($group !== null) && !(Elf::session()->get('group')&$group)) {// wrong group!!!
				if ($redirect)
					Elf::redirect($redirect===true?'':base64_decode($redirect));
				else {
					$this->denied();
				}
			}
		// normaly logged
		}
		else {// is AUTH action init
			//print_r(Elf::routing()->params());exit;
//			print_r(Elf::routing()->params());exit;
			$this->auth(isset(Elf::routing()->params()[0])?Elf::routing()->params()[0]:$group,
								isset(Elf::routing()->params()[1])?Elf::routing()->params()[1]:$redirect,
								isset(Elf::routing()->params()[2])?Elf::routing()->params()[2]:$after_auth_redirect);
		}
	}
	function index() {
		echo 'You are auth!';
	}
	function denied() {
		header('HTTP/1.1 403 Forbidden');
		echo 'Access denied';
		exit;
	}
	function auth($group = null, $redirect = true, $after_auth_redirect = false) {
		Elf::no_cache();
		Elf::$_data['title'] = Elf::lang('auth')->item('title');
		if (Elf::input()->get('datain')) {
			$user = new $this->model;
			if ($user->auth(Elf::input()->get('login'),Elf::input()->get('passwd'),Elf::input()->get('remme'))) {
				if (($group === null) || (Elf::session()->get('group')&$group)) {
					if ($after_auth_redirect)
						Elf::redirect($after_auth_redirect===true?'':base64_decode($after_auth_redirect));
					else
						$this->index();
					exit;
				}
				else {
					if ($after_auth_redirect)
						Elf::redirect($after_auth_redirect===true?'':base64_decode($after_auth_redirect));
					else {
						$this->denied();
					}
				}
			}
			else {
				Elf::messagebox(Elf::lang('auth')->item('error.auth','/'.Elf::routing()->controller_inp()));
				if ($redirect)
					Elf::redirect($redirect===true?'':base64_decode($redirect));
				else {
					parent::index();
					exit;
				}
			}
		}
		Elf::show_dialog('auth/index',array_merge(['caption'=>Elf::lang('auth')->item('title'),
													'appearance'=>'appearances/auth',
													'group'=>(int)$group,
													'redirect'=>$redirect?($redirect===true?1:base64_encode($redirect)):'',
													'after_auth_redirect'=>$after_auth_redirect?($after_auth_redirect===true?'':$after_auth_redirect):''],Elf::input()->data(false)));
		if ($redirect)
			Elf::redirect($redirect===true?'':base64_decode($redirect));
		else {
			parent::index();
			exit;
		}
	}
	function logout() {
		$user = new Users;
		$user->logout();
		Elf::redirect();
	}
}
