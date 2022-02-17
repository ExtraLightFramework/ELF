<?php

namespace Elf\Libs;

use Elf;

class Structure extends Uploaders {
	function __construct($tbl = null, $dir = null, $crop_enable = false) {
		parent::__construct($tbl, $dir, $crop_enable);
	}
	protected function _get_rubric_aliases($pid = 0, $onelevel = false) {
		$ret = [];
		if ($res = $this->_select("`id`,`alias`")->_where("`parent_id`=".$pid." AND `type`='rubric'")->_execute()) {
			foreach ($res as $v) {
				$ret[] = $v['alias'];
				if (!$onelevel) {
					$ret = array_merge($ret, (array)$this->_get_rubric_aliases($v['id'],$onelevel));
				}
			}
		}
		return $ret;
	}
	protected function _get_parent_aliases($pid) {
		$ret = [];
		while ($pid && ($rec = parent::get_by_id($pid))) {
			$ret[!empty($rec['title'])?$rec['title']:(!empty($rec['name'])?$rec['name']:$rec['id'])] = $rec['alias'];
			$pid = $rec['parent_id'];
		}
		return sizeof($ret)?array_reverse($ret):null;
	}
	public function _get_root_parent($id) {
		do {
			$ret = $this->get_by_id($id);
			$id = $ret['parent_id'];
		} while ($id);
		return $ret;
	}
	protected function _get_parent_ids($pid) {
		$ret = [];
		while ($pid && ($rec = $this->get_by_id($pid))) {
			$ret[] = $rec['id'];
			$pid = $rec['parent_id'];
		}
		return sizeof($ret)?array_reverse($ret):null;
	}
	protected function _get_parent_alias($cid) {
		$ret = '';
		if (($rec = $this->get_by_id((int)$cid))
			&& $rec['parent_id']
			&& ($rec = $this->get_by_id($rec['parent_id']))) {
			$ret = $rec['alias'];
		}
		return $ret;
	}
	protected function _get_childs_ids($id, $direct = false, $type = 'all') { // $direct = true - прямые потомоки,
													// $direct = false - потомоки в любом поколении
		$ret = '';
		if ($direct
			&& ($res = $this->_select()->_where("`parent_id`=".$id.($type!='all'?" AND `type`='".$type."'":""))->_execute())) {
			foreach ($res as $v)
				$ret .= ($ret?',':'').$v['id'];
		}
		elseif (!$direct
			&& ($res = $this->_select()
						->_subquery($this->_name(true),"t2")->_select("COUNT(t2.`id`)")
							->_where("t2.`parent_id`=t1.`id`")->_closesquery("childs_cnt")
						->_where("t1.`parent_id`=".$id.($type!='all'?" AND t1.`type`='".$type."'":""))->_execute())) {
			foreach ($res as $v) {
				$ret .= ($ret?',':'').$v['id'];
				if ($v['childs_cnt'])
					$ret .= $this->_get_childs_ids($v['id'],$direct,$type);
			}
		}
		return $ret;
	}
	protected function _get_childs($id, $direct = false, $type = 'all') { // $direct = true - прямые потомоки,
													// $direct = false - потомоки в любом поколении
		$ret = [];
		if ($direct) {
			$ret = $this->_select()->_where("`parent_id`=".$id.($type!='all'?" AND `type`='".$type."'":""))->_execute();
		}
		elseif ($res = $this->_select()
						->_subquery($this->_name(true),"t2")->_select("COUNT(t2.`id`)")
							->_where("t2.`parent_id`=t1.`id`")->_closesquery("childs_cnt")
						->_where("t1.`parent_id`=".$id.($type!='all'?" AND t1.`type`='".$type."'":""))->_execute()) {
			foreach ($res as $v) {
				$ret[] = $v;
				if ($v['childs_cnt'])
					$ret = array_merge($ret, (array)$this->_get_childs($v['id'],$direct,$type));
			}
		}
		return sizeof($ret)?$ret:null;
	}
	protected function _is_child($parent_id, $child_id, $direct = false) { // $direct = true - прямой потомок,
																	// $direct = false - потомок в любом поколении
		if ($direct) {
			return $this->get("`id`=".$child_id." AND `parent_id`=".$parent_id)?true:false;
		}
		elseif (($rec = $this->get_by_id($child_id))
				&& ($pids = $this->_get_parent_ids($rec['parent_id']))) {
			return in_array($parent_id, $pids);
		}
		return false;
	}
	protected function _recalc_routing_controllers($alias, $type = 'rubric', $controller_to = '', $content_type = '') {
		$ret = '';
		$exp = explode("/",$alias);
		$method = array_pop($exp);
		switch ($type) {
			case 'rubric':
//				Elf::routing()->_delete()->_where("`controller` LIKE '%".$method."%'")->_execute();
				$ret = Elf::routing()->_edit($alias,'*',$controller_to,'rubric');
				break;
			default:
//				Elf::routing()->_delete()->_where("`method`='".$method."'")->_execute();
				$ret = Elf::routing()->_edit(implode("/",$exp),$method,$controller_to,$content_type?$content_type:$type);
				break;
		}
		$this->_update(['hash'=>$ret])->_where("`alias`='".$method."'")->_execute();
		
		if (($type == 'rubric')
			&& ($exp = explode("/",$alias))
			&& ($rec = $this->get("`alias`='".$exp[sizeof($exp)-1]."'"))
			&& ($res = $this->_select()->_where("`parent_id`=".$rec['id'])->_execute())) {
			foreach ($res as $v)
				$this->_recalc_routing_controllers($alias."/".$v['alias'],$v['type'],$controller_to, $v['content_type']);
		}
	}
	protected function _clear_routing_controllers($alias, $type = 'rubric') {
		switch ($type) {
			case 'rubric':
				Elf::routing()->_delete()->_where("`controller` LIKE '%".$alias."%'")->_execute();
				break;
			case 'item':
				Elf::routing()->_delete()->_where("`method`='".$alias."'")->_execute();
				break;
		}
	}
}