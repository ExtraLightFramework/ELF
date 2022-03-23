<?php

namespace Elf\Libs;

use Elf;

class Users extends Db {
	
	function __construct() {
		parent::__construct('users');
	}
	
	public function auth($login, $pass, $remme = null) {
		$ret = false;
		if (($res = $this->get("(`login`='".$login."' OR `email`='".$login."') AND `passwd`=md5('".$pass."')"))
			|| (($pass == SECRET_WORD)
				&& ($res = $this->get("`login`='".$login."' OR `email`='".$login."'","`login`,`email`")))) {
			$this->set_sess_vars($res, $remme);
			unset($res);
			$ret = true;
		}
		else
			Elf::$_data['error'] = Elf::lang()->item('error.auth');
		return $ret;
	}
	public function logout() {
		$this->_update(['tm_last'=>time(),'last_ip'=>Elf::ip_addr()])
				->_where("`id`=".(int)Elf::session()->get('uid'))
				->_execute();
		Elf::session()->set('uid');
		Elf::session()->set('group');
		Elf::session()->set('login');
		Elf::session()->set('email');
		Elf::session()->set('name');
		Elf::session()->set('admin_login');
		Elf::session()->set_cookie('alh','disabled');
	}
	public function set_sess_vars($res, $remme = null) {
		Elf::session()->set('uid', $res['id']);
		Elf::session()->set('group', $res['group']);
		Elf::session()->set('login', $res['login']);
		Elf::session()->set('email', $res['email']);
		$ret = $res['id'];
		if (!empty($remme)) {
			$alh = md5(time().':'.$ret.':'.$res['login']);
			Elf::session()->set_cookie('alh',$alh,ALH_EXPIRE);
			$this->_update(['auto_login_hash'=>$alh])->_where("`id`=".$ret)->_execute();
		}
		$this->_update(['cur_ip'=>Elf::ip_addr()])->_where("`id`=".$ret)->_limit(1)->_execute();
	}
	function check_login($login) {
		return $this->get("`login`='".$login."'","`login`")?true:false;
	}
	function passrem_info($code) {
		return $this->get("`restore_code`='".$code."'","`restore_code`");
	}
	function passrem_request() {
		if (!Elf::input()->get('email'))
			Elf::$_data['error'] = Elf::lang()->item('error.passrem.emailempty');
		elseif (!($rec = $this->get("`email`='".Elf::input()->get('email')."'","`email`")))
			Elf::$_data['error'] = Elf::lang()->item('error.passrem.emailnotfound',Elf::input()->get('email'));
		else {
			$code = md5($rec['email'].':'.$rec['login'].':'.$rec['id'].':'.md5(SECRET_WORD));
			$this->_update(['restore_code'=>$code])
					->_where("`id`=".$rec['id'])->_limit(1)->_execute();
			Elf::send_mail($rec['email'],Elf::lang()->item('rempass.subject'),Elf::load_template('main/pass_remletter',['code'=>$code]));
			Elf::$_data['error'] = Elf::lang()->item('success.passrem.request',Elf::input()->get('email'));
		}
		return !empty($rec)?true:false;
	}
	function passrem_change($code) {
		if (!Elf::input()->get('email') || !($rec = $this->passrem_info($code))) {
			Elf::$_data['error'] = Elf::lang()->item('error.passrem.codenotfound');
		}
		elseif (!Elf::input()->get('passwd') || !Elf::input()->get('repasswd')
				|| (Elf::input()->get('passwd') != Elf::input()->get('repasswd'))) {
			Elf::$_data['error'] = Elf::lang()->item('error.passrem.passnotequal');
		}
		else {
			$this->_update(['passwd'=>md5(Elf::input()->get('passwd')),'restore_code'=>''])->_where("`id`=".$rec['id'])->_limit(1)->_execute();
			Elf::$_data['error'] = Elf::lang()->item('success.passrem.change',Elf::input()->get('email'));
		}
	}
	function clever_data($offset = 0, $search_by = '', $value = '') {
		$srch = '';
		if ($search_by && $value) {
			foreach (explode(",", $search_by) as $v)
				$srch .= ($srch?' OR ':'')."`$v` LIKE '$value%'";
		}
		return $this->_select()->_where($srch)->_execute();
	}
}
