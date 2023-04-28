<?php

namespace Elf\Libs;

use Elf;

class Structure extends Uploaders {
	function __construct($tbl = null, $dir = null, $crop_enable = false) {
		parent::__construct($tbl, $dir, $crop_enable);
	}
	function get_name() {
		return 'structure';
	}
	function get_alias() {
		return 'structure_alias';
	}
	function get_controller() {
		return 'structure';
	}
	function get_method() {
		return 'index';
	}
	function get_iop() {
		return 10;
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
	protected function _get_parent_aliases($pid, $child_name = null) {
		$ret = [];
		if ($child_name)
			$ret[$child_name] = '';
		while ($pid && ($rec = parent::get_by_id($pid))) {
//			echo $pid.'<br />';
			$ret[!empty($rec['title'])?$rec['title']:(!empty($rec['name'])?trim($rec['name'].' '.$rec['brand'].' '.$rec['brand_type']):$rec['id'])] = $rec['alias'];
			$pid = isset($rec['pids'])?(int)$rec['pids']:$rec['parent_id'];
		}
		$ret[$this->get_name()] = $this->get_alias();//.'/'.$this->get_method();
//		print_r($ret);
//		exit;
		return sizeof($ret)?array_reverse($ret):null;
	}
	public function _get_root_parent($id) {
		do {
			$ret = $this->get_by_id($id);
			$id = $ret['parent_id'];
		} while ($id);
		return $ret;
	}
	protected function _get_parent_ids($pids) {
		$ret = [];
		while ($pids && ($res = $this->_select("`id`,`pids`")->_where("`id` IN ({$pids})")->_execute())) {
			$pids = '';
			foreach ($res as $v) {
				$ret[] = $v['id'];
				$pids .= ($pids?',':'').$v['pids'];
			}
		}
		return sizeof($ret)?array_reverse($ret):null;
	}
	protected function _get_content_parent_ids($pid) {
		$ret = [];
		while ($pid && ($res = $this->_select("`id`")->_where("`id` IN ({$pid})")->_execute()[0])) {
			$ret[] = $res['id'];
		}
		return sizeof($ret)?array_reverse($ret):null;
	}
	public function _get_parent_alias($cid) {
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
				if ($v['childs_cnt'] && ($childs = $this->_get_childs_ids($v['id'],$direct,$type)))
					$ret .= ','.$childs;
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
			return $this->_select()
					->_where("(SELECT t2.`catalog_id` FROM `".DB_PREFIX."catalog_rels` t2
								WHERE t2.`catalog_id`={$child_id} AND t2.`parent_id`={$parent_id})")
					->_execute()?true:false;
//			return $this->get("`id`=".$child_id." AND `parent_id`=".$parent_id)?true:false;
		}
		elseif (($rec = $this->get_by_id($child_id))
				&& ($pids = $this->_get_parent_ids($rec['pids']))) {
			return in_array($parent_id, $pids);
		}
		return false;
	}
	protected function _is_content_child($parent_id, $child_id) {
		if (($rec = $this->get_by_id($child_id))
			&& ($pids = $this->_get_content_parent_ids($rec['parent_id']))) {
			return in_array($parent_id, $pids);
		}
		return false;
	}
/*	protected function _recalc_routing_controllers($controller, $method, $controller_to, $method_to) {
		if ($ret = Elf::routing()->_edit($controller, $method, $controller_to, $method_to))
			$this->_update(['hash'=>$ret])->_where("`alias`='{$method}'")->_execute();
	}
*/	
	protected function _replace_routing_controllers($controller, $repl_alias) {
		// записи с controller=$repl_alias заменяются зачением $controller
		if ($rec = Elf::routing()->get("`controller`='{$controller}' AND `method`='{$repl_alias}'")) {
			Elf::routing()->_update(['controller'=>$rec['controller']])->_where("`controller`='{$repl_alias}'")->_execute();
			Elf::routing()->_delete()->_where("`id`={$rec['id']}")->_execute();
		}
	}
	public function _recalc_routing_controllers($pids, $alias, $type,
													$old_pids = null, $old_alias = null, $old_type = null,
													$seo = [], $root_pid = null) {
		// при update $old_ значения всегда должны быть заполнены старыми данными из БД
		// при добавлении новой записи, данные значения всегда должны быть null
		if (($old_alias !== null) && ($alias != $old_alias)) {
			Elf::routing()->_update(['controller'=>$alias])->_where("`controller`='{$old_alias}'")->_execute();
			Elf::routing()->_update(['method'=>$alias])->_where("`method`='{$old_alias}'")->_execute();
			if (($root_pid && ($parent = $this->get_by_id((int)$root_pid)))
				|| (!$root_id && ($parent['alias'] = $this->get_alias()))) {
				Redirector::_add("\/{$parent['alias']}\/{$old_alias}","\/{$parent['alias']}\/{$alias}");
				// если элемент "рубрика" - редиректим все дочерние элементы
				if (($type == 'rubric')
					&& ($childs = Elf::routing()->_select("`method`")->_where("`controller`='{$old_alias}'")->_execute())) {
					foreach ($childs as $ch) {
						Redirector::_add("\/{$old_alias}\/{$ch['method']}","\/{$alias}\/{$ch['method']}");
					}
				}
			}
		}
		if (($old_pids === null) || ($pids == $old_pids)) {
			$this->_add_routing_rules($pids, $alias, $type, $seo, $root_pid);
		}
		elseif ($pids != $old_pids) {
//			$this->_rem_routing_rules($old_pids, $alias);
			$this->_add_routing_rules($pids, $alias, $type, $seo, $root_pid);
		}
		if (($old_type !== null) && ($type != $old_type)) {
			Elf::routing()->_update(['method_to'=>$type])->_where("`method`='{$alias}'")->_execute();
		}
	}
	private function _add_routing_rules($pids, $alias, $type, $seo, $root_pid) {
		if (array_search('0', explode(',',$pids)) !== false) { // add in root 
			if ($ret = Elf::routing()->_edit($this->get_alias(), $alias, $this->get_controller(), $type, null, (int)$root_pid===0?$seo:null)) {
				$this->_update(['hash'=>$ret])->_where("`alias`='{$alias}'")->_execute();
			}
		}
		if ($res = $this->_select("`id`,`alias`")->_where("`id` IN ({$pids})")->_execute()) { // add in levels
			foreach ($res as $v) {
				if ($ret = Elf::routing()->_edit($v['alias'], $alias, $this->get_controller(), $type, null, $root_pid==$v['id']?$seo:null))
					$this->_update(['hash'=>$ret])->_where("`alias`='{$alias}'")->_execute();
			}
		}
	}
	private function _rem_routing_rules($pids, $alias) {
		if (array_search('0', explode(',',$pids)) !== false) { // rem from root 
			$this->_clear_routing_controllers($this->get_alias(), $alias);
		}
		if ($res = $this->_select("`alias`")->_where("`id` IN ({$pids})")->_execute()) { // rem from levels
			foreach ($res as $v)
				$this->_clear_routing_controllers($v['alias'], $alias);
		}
	}
	protected function _clear_routing_controllers($controller, $method) {
		Elf::routing()->_delete()->_where("`controller`='{$controller}' AND `method`='{$method}'")->_execute();
	}
}