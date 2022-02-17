<?php

namespace Elf\Libs;

use Elf;

class History extends Db {
	
	function __construct() {
		parent::__construct('history');
	}
	function _add($uid, $mess, $status = 'new') {
		return $this->_insert(['user_id'=>(int)$uid,'mess'=>$mess,'ip_addr'=>Elf::ip_addr(),
										'tm'=>time(),'status'=>addslashes($status)])->_execute();
	}
	function get_user_new_events_cnt($uid = 0) {
		if (!($uid = (int)$uid))
			$uid = (int)Elf::session()->get('uid');
		return $this->cnt("`user_id`=".$uid." AND `status`='new'");
	}
	function get_user_events($uid = 0) {
		if (!($uid = (int)$uid))
			$uid = (int)Elf::session()->get('uid');
		if ($ret = $this->_select()
						->_where("`user_id`=".$uid)
							->_and("`status` IN ('new','showed')")
						->_orderby("`user_id`,`status`,`tm` DESC")
						->_limit(30)
						->_execute()) {
			$ids = '';
			foreach ($ret as $v)
				if ($v['status'] == 'new')
					$ids .= ($ids?',':'').$v['id'];
			if ($ids)
				$this->_update(['status'=>'showed'])->_where("`id` IN (".$ids.")")->_execute();
		}
		return $ret;
	}
	function remove($eid, $admin = false) {
		return $this->_delete()->_where("`id`=".(int)$eid." AND `status` IN ('new','showed')".(!$admin?" AND `user_id`=".Elf::session()->get('uid'):""))
						->_limit(1)->_execute();
	}
}
