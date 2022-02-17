<?php

namespace Elf\Libs;

use Elf;

class Settings extends Db {
	
	private $data;
	
	function __construct() {
		parent::__construct('settings');
		if ($res = $this->_select()->_execute()) {
			foreach ($res as $v) {
				if (!defined($v['name'])) {
					define ($v['name'], $v['value']);
				}
				$this->data[$v['name']] = $v['value'];
			}
		}
	}
	function _get($key) {
		return isset($this->data[$key])?$this->data[$key]:null;
	}
	function _set($key, $value, $desc = '') {
		if (isset($this->data[$key]))
			$this->_update(array('value'=>$value))->_where("`name`='".$key."'")->_orderby("`name`")->_limit(1)->_execute();
		else
			$this->_insert(array('name'=>$key,'value'=>$value,'desc'=>$desc))->_execute();
		$this->data[$key] = $value;
	}
	function _data($whr = '') {
		return $this->_select()->_where($whr)->_orderby("`name`")->_execute();
	}
	function _edit() {
		$ret = false;
		Elf::set_data('error',Elf::lang('settings')->item('unsave'));
		$data = Elf::input()->data(false);
		if (!empty($data['name'])) {
			$data['name'] = strtoupper(str_replace(' ','_',$data['name']));
			if (isset($data['desc']) && !$data['desc'])
				unset($data['desc']);
			if (!empty($data['expire']))
				$data['expire'] = Elf::date2timestamp($data['expire']);
			else
				$data['expire'] = 0;
			if ((!empty($data['id'])
				&& ($rec = $this->get_by_id((int)$data['id'])))
				|| ($rec = $this->get("`name`='".$data['name']."'","`name`"))) {
				$ret = $this->_update($data)->_where("`id`=".$rec['id'])->_limit(1)->_execute();
			}
			else {
				$ret = $this->_insert($data)->_execute();
			}
			if ($ret)
				Elf::set_data('success',Elf::lang('settings')->item('save'));
		}
		return $ret;
	}
}