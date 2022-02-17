<?php

namespace Elf\Libs;

use Elf;

class Session extends Db {
	// removeable - значение куки будет заменяться на новое, если таковое будет получено во входных параментрах запроса
	// strong - значение куки будет установлено единожды, и не будет заменяться на новое, если будет получено во входном запросе
	// false - значение куки не будет сохраняться, а также удалится существующее, если таковое было установлено ранее
	private $saved_cookies = ['refid'=>'removeable'];

	function __construct() {
		session_start();
		if ($sess = $this->get_cookie('PHPSESSID')) {
			$this->set_cookie('PHPSESSID', $sess, SESSION_EXPIRE);
		}
		if ($inp = Elf::input()->data()) {
			foreach ($inp as $k=>$v) {
				if (isset($this->saved_cookies[$k])) {
					if (($this->saved_cookies[$k] == 'removeable')
						|| (($this->saved_cookies[$k] == 'strong') && ($this->get_cookie($k) === null)))
							$this->set_cookie($k,$v);
					elseif ($this->saved_cookies[$k] === false)
						$this->set_cookie($k);
				}
			}
		}
		unset($inp);
		// system sessions update
		parent::__construct('sessions');
		if ($res = parent::get("`sessid`='".$this->sessid()."'")) {
			$this->_update(['tm_updated'=>time()])->_where("`sessid`='".$res['sessid']."'")->_execute();
		}
		else {
			$this->_insert(['sessid'=>$this->sessid(),'tm_created'=>time(),'tm_updated'=>time()])->_execute();
		}
		// remove old sessions
		if ($res = $this->_select()->_where("`tm_updated`<".(time() - SESSION_EXPIRE))->_execute()) {
			foreach ($res as $v)
				@unlink(SESSION_PATH.'sess_'.$v['sessid']);
			$this->_delete()->_where("`tm_updated`<".(time() - SESSION_EXPIRE))->_execute();
		}
		unset($res);
	}
	function cookie_agreement() {
		if (!$this->get_cookie('cookie_agreement')) {
			return Elf::load_template('main/cookie_agreement');
		}
		else
			return '';
	}
	function check_auto_login() {
		$users = new Elf\Libs\Users;
		if (!$this->get('uid')
			&& ($alh = $this->get_cookie('alh'))
			&& ($alh != 'disabled')
			&& ($u = $users->get("`auto_login_hash`='".$alh."'","`auto_login_hash`"))) {
			// если пользователь не авторизован, и высталена кука автологина, и найден юзер с установленным хешем автологина
			$users->set_sess_vars($u);
		}
		elseif (!$this->get('uid')
			&& ($alh = $this->get_cookie('alh'))
			&& ($alh != 'disabled')) {
			$this->set_cookie('alh','disabled');
		}
		// GeoIP checker
		if ($this->get_cookie('geoip_city') && !$this->get('geoip_city'))
			$this->set('geoip_city',$this->get_cookie('geoip_city'));
	}
	function sessid() {
		return session_id();
	}
	function get($key, $ext = null) {
		return isset($_SESSION[$key])?$_SESSION[$key]:null;
	}
	function set($key, $val = null) {
		$ret = $this->get($key);
		if ($val !== null)
			$_SESSION[$key] = $val;
		else {
			$_SESSION[$key] = null;
			unset($_SESSION[$key]);
		}
		return $ret;
	}
	function set_cookie($key, $val = null, $expire = null) {
		if ($val !== null) {
			setcookie($key, $val, time() + ($expire?$expire:COOKIE_EXPIRE), "/");
			$_COOKIE[$key] = $val;
		}
		elseif (isset($_COOKIE[$key])) {
			setcookie($key, false, time()-1, "/");
			$_COOKIE[$key] = null;
			unset($_COOKIE[$key]);
		}
	}
	function get_cookie($key) {
		return isset($_COOKIE[$key])?$_COOKIE[$key]:null;
	}
}