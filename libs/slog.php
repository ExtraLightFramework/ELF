<?php

namespace Elf\Libs;

use Elf;

class Slog extends Db {
	
	function __construct() {
		parent::__construct('syslog');
	}
	function _add($model, $action, $tm_start, $tm_end, $desc=null, $user_id = 0) {
		$ret = null;
		if ($user_id || ($user_id = (int)Elf::session()->get('uid'))) {
			$ret = $this->_insert(['user_id'=>(int)$user_id,
									'model'=>$model,
									'action'=>$action,
									'desc'=>$desc,
									'tm_start'=>(int)$tm_start,
									'tm_end'=>(int)$tm_end])->_execute();
		}
		return $ret;
	}
	function manager_selector($sel = 0) {
		$ret = '-';
		$user = new Users;
		if ($res = $user->_select()->_where("`group`&".GROUP_TECH."=".GROUP_TECH)->_execute()) {
			$ret = '<select onchange="location.href=\'/slog/index/\'+this.value">';
			$ret .= '<option value="0">выберите оператора</option>';
			foreach ($res as $v)
				$ret .= '<option value="'.$v['id'].'" '.($sel==$v['id']?'selected="selected"':'').'>'.$v['name'].'</option>';
			$ret .= '</select>';
		}
		return $ret;
	}
	function _stat($manager_id = 0) {
		$ret = [];
		if ($manager_id = (int)$manager_id) {
			$tm_end = new \DateTime(date('Y-m-d'));
			$tm_start =  new \DateTime(date('Y-m-d'));
			$tm_start->modify('-7 days');
			$start = $tm_start->getTimestamp();
			$end = $tm_end->getTimestamp();
			$ret['settings']['model_actions'] = [];
			$ret['settings']['period'] = date('d.m.y', $start).' - '.date('d.m.y', $end);
			if ($res = $this->_select('model,action')->_where("`user_id`={$manager_id}")->_groupby("`model`,`action`")->_execute())
				foreach ($res as $v)
					if (!in_array($v['model'].':'.$v['action'],$ret['settings']['model_actions']))
						$ret['settings']['model_actions'][] = $v['model'].':'.$v['action'];
			while ($start <= $end) {
				$model = $action = '';
				foreach ($ret['settings']['model_actions'] as $ma) {
					$ret[$start][$ma]['duration'] = 0;
					$ret[$start][$ma]['cnt'] = 0;
				}
				if ($res = $this->_select()
								->_where("`user_id`={$manager_id} AND `tm_start`>={$start} AND `tm_end`<".($start+SECONDS_IN_DAY))
								->_execute()) {
					$ret['settings']['data_found'] = true;
					foreach ($res as $v) {
//						if (!isset($ret[$start][$v['model'].':'.$v['action']])) {
//							$ret[$start][$v['model'].':'.$v['action']]['duration'] = 0;
//							$ret[$start][$v['model'].':'.$v['action']]['cnt'] = 0;
//						}
						$ret[$start][$v['model'].':'.$v['action']]['duration'] += ($v['tm_end']-$v['tm_start']);
						$ret[$start][$v['model'].':'.$v['action']]['cnt'] ++;
					}
				}
				$tm_start->modify('+1 day');
				$start = $tm_start->getTimestamp();
			}
		}
		return $ret;
	}
}