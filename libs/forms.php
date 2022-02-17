<?php

namespace Elf\Libs;

use Elf;

define ('FORM_DEFAULT_ACTION',	'/forms/action');
define ('FORM_TEMPLATE_FILE',	ROOTPATH.'views/forms/form_template%%relation_type%%.php');

class Forms extends Db {
	
	private $fields;
	private $relforms;
	
	function __construct() {
		parent::__construct('elf_forms');
		$this->fields = new Db('elf_forms_fields');
		$this->relforms = new Db('elf_forms_related');
	}
	private function _table_exists($table) {
		return $table?(bool)$this->query("show tables where Tables_in_".DB_BASE."='".addslashes($table)."'"):false;
	}
	private function _get_table_fields($table) {
		$ret = [];
		if ($res = $this->query("show columns from ".addslashes($table))) {
			foreach ($res as $v) {
				$ret[$v['Field']] = [];
				$ret[$v['Field']]['table_type'] = preg_replace(["/(int)\((\d+)\).*/",
													"/(float)\((\d+,\d+)\).*/",
													"/(decimal)\((\d+,\d+)\).*/",
													"/(varchar)\((\d+)\).*/",
													"/(enum)\(('\w+',?)+\)/",
													"/(tiny|medium|long)?(text)/"],['${1}','${1}','${1}','${1}','${1}','${2}'],$v['Type']);
				$ret[$v['Field']]['size'] = preg_replace(["/(int)\((\d+)\).*/",
													"/(float)\((\d+,\d+)\).*/",
													"/(decimal)\((\d+,\d+)\).*/",
													"/(varchar)\((\d+)\).*/",
													"/(enum)\((('\w+',?)+)\)/",
													"/(tiny|medium|long)?(text)/"],['${2}','${2}','${2}','${2}','${2}','${1}${2}'],$v['Type']);
				if ($ret[$v['Field']]['table_type'] == 'enum')
					$ret[$v['Field']]['size'] = str_replace(["'",","],["","\n"],$ret[$v['Field']]['size']);
				elseif ($ret[$v['Field']]['table_type'] == 'text')
					$ret[$v['Field']]['size'] = str_replace(["tinytext","mediumtext","longtext","text"],[255,16777215,4294967295,65535],$ret[$v['Field']]['size']);
			}
		}
		return $ret;
	}
	function _relation_table_fields_selector($table, $name, $sel = '', $add = '') {
		$ret = '';
		if ($res = $this->_get_table_fields($table)) {
			$ret = '<select name="'.$name.'" '.$add.'>';
			$ret .= '<option value="">'.Elf::lang('forms')->item('select.field.name').'</option>';
			foreach ($res as $k=>$v) {
				$ret .= '<option value="'.$k.'" '.((is_array($sel)&&in_array($k,$sel))||$k==$sel?'selected="selected"':'').'>'.$k.'</option>';
			}
			$ret .= '</select>';
		}
		return $ret;
	}
	private function _table_fields_selector($table, $sel = '', $field_id) {
		$ret = '';
		if ($res = $this->_get_table_fields($table)) {
			$ret = '<select name="field_name" data-old="'.$sel.'" data-field-id="'.$field_id.'" class="form-field-selector" id="form-field-name-'.$field_id.'" onchange="_change_field_sett_names(this.value, '.$field_id.')">';
			$ret .= '<option value="">'.Elf::lang('forms')->item('select.field.name').'</option>';
			foreach ($res as $k=>$v) {
				$ret .= '<option data-size="'.$v['size'].'" value="'.$k.'" '.($k==$sel?'selected="selected"':'').'>'.$k.'</option>';
			}
			$ret .= '</select>';
		}
		return $ret;
	}
	function _tables_selector($sel = '', $name = 'table', $add = 'onchange="_set_form_path($(this).find(\'option:selected\'))" required="required"') {
		$ret = "-";
		if ($res = $this->query("SHOW TABLES")) {
			$ret = '<select name="'.$name.'" '.$add.'>';
			$ret .= '<option value="">'.Elf::lang('forms')->item('select.table').'</option>';
			foreach ($res as $v) {
				$ret .= '<option value="'.$v['Tables_in_'.DB_BASE].'" '.($v['Tables_in_'.DB_BASE]==$sel?'selected="selected"':'').'>'.str_replace(DB_PREFIX,"",$v['Tables_in_'.DB_BASE]).'</option>';
			}
			$ret .= '</select>';
		}
		return $ret;
	}
	function get_table_fields_by_formid($fid, $name, $sel = '', $rfid) {
		$ret = '';
		if (($rec = parent::get_by_id($fid))
			&& ($res = $this->_get_table_fields($rec['table']))) {
			$ret = '<select name="'.$name.'" class="nowide" onchange="_save_related_data(this.value,\''.$name.'\','.$rfid.')">';
			$ret .= '<option value="">'.Elf::lang('forms')->item('select.field.name').'</option>';
			foreach ($res as $k=>$v) {
				$ret .= '<option value="'.$k.'" '.($k==$sel?'selected="selected"':'').'>'.$k.'</option>';
			}
			$ret .= '</select>';
		}
		return $ret;
	}
	function link_tables_selector($sel = '', $field_id = 0, $size = null) {
		$ret = "";
		if ($res = $this->query("SHOW TABLES")) {
			$ret = '<select name="table" onchange="_save_link_size($(this),\'table\','.$field_id.',\''.$size.'\')">';
			$ret .= '<option value="">'.Elf::lang('forms')->item('select.table').'</option>';
			foreach ($res as $v) {
				$ret .= '<option value="'.$v['Tables_in_'.DB_BASE].'" '.($v['Tables_in_'.DB_BASE]==$sel?'selected="selected"':'').'>'.str_replace(DB_PREFIX,"",$v['Tables_in_'.DB_BASE]).'</option>';
			}
			$ret .= '</select>';
		}
		return $ret;
	}
	function _data($offset = 0) {
		$ret = $pagi = null;
		if ($ret = $this->_select()->_limit(RECS_ON_PAGE,(int)$offset*RECS_ON_PAGE)->_execute()) {
			$pg = new Pagination;
			$pagi = $pg->create("/forms/index/",
									$this->cnt(),
									(int)$offset, RECS_ON_PAGE,
									3);
		}
		return [$ret,$pagi];
	}
	function get_by_id($id, $ext = null) {
		if ($ret = parent::get_by_id($id))
			$ret['tables'] = $this->_tables_selector($ret['table']);
		else
			$ret['tables'] = $this->_tables_selector();
		if (!empty($ret['getter']))
			$ret['getter'] = base64_decode($ret['getter']);
		if (!empty($ret['redirect'])) {
			$ret['redirect_orig'] = $ret['redirect'];
			$ret['redirect'] = base64_decode($ret['redirect']);
		}
		if ($ret && $ext)
			$ret['fields'] = $this->get_fields($ret['id'], $ret['table']);
		return $ret;
	}
	function get_field($field_id, $fid = 0, $table = '', $create = false) {
		$ret = [];
//		if ($this->_table_exists($table)) {
			if ((int)$field_id
				&& ($rec = $this->fields->get_by_id($field_id))
//				&& ($rec['table_name'] == $table)
				&& (($rec['form_id'] == (int)$fid) || !(int)$fid)) {
				$ret = $rec;
			}
			elseif ($create) {// new field rec
				if ($ret = $this->fields->_insert(['form_id'=>(int)$fid,
													'table_name'=>addslashes($table),
													'sessid'=>!(int)$fid?Elf::session()->sessid():''])->_execute()) {
					$this->fields->_update(['pos'=>$this->get_free_pos($ret)])->_where("`id`=".$ret)->_execute();
					$ret = $this->fields->get_by_id($ret);
				}
			}
			$ret['field_name_selector'] = $this->_table_fields_selector($table, !empty($rec)?$rec['field_name']:'', $ret['id']);
			$ret['type_selector'] = $this->fields->create_select('type', !empty($rec)?$rec['type']:'', 'id="form-field-type-'.$ret['id'].'" data-field-id="'.$ret['id'].'" class="form-field-type-selector" onchange="_change_field_type(this.value,'.(!empty($ret['id'])?$ret['id']:0).')"', 'forms');
			$ret['params'] = !empty($ret['params'])?(array)json_decode(base64_decode($ret['params'])):null;
//		}
//		else
//			Elf::set_data('error', Elf::lang('forms')->item('error.table.exists'));
		return $ret;
	}
	function get_fields($fid, $table, $session = false) {
		$ret = [];
		if ((int)$fid
			&& ($rec = parent::get_by_id($fid))) {
			$ret = $this->fields->_select()->_where("`form_id`=".$rec['id'])->_orderby("`pos`")->_execute();
		}
		if ($session && ($res = $this->fields->_select()->_where("`sessid`='".Elf::session()->sessid()."'")->_orderby("`pos`")->_execute())) {
			if ($ret)
				$ret = array_merge($ret, $res);
			else
				$ret = $res;
		}
		if ($ret) {
			foreach ($ret as $k=>$v) {
				if (!empty($v['params']))
					$v['params'] = (array)json_decode(base64_decode($v['params']));
				$ret[$v['field_name']] = $v;//$v['table_name'].'.'.
				unset($ret[$k]);
			}
		}
		return $ret;
	}
	function remove_form_field($fid, $field_id) {
		return $this->fields->_delete()->_where("`form_id`=".(int)$fid." AND `id`=".(int)$field_id)->_execute();
	}
	function ch_fields_pos($field_id, $direct) {
		if ($rec = $this->fields->get_by_id((int)$field_id)) {
			switch ($direct) {
				case 'up':
					$repl = $this->fields->get(($rec['form_id']?"`form_id`=".$rec['form_id']:"`sessid`='".$rec['sessid']."'")." AND `pos`<".$rec['pos'],"`pos` DESC");
					break;
				case 'down':
					$repl = $this->fields->get(($rec['form_id']?"`form_id`=".$rec['form_id']:"`sessid`='".$rec['sessid']."'")." AND `pos`>".$rec['pos'],"`pos` ASC");
					break;
			}
			if (!empty($repl)) {
				$this->fields->_update(['pos'=>$rec['pos']])->_where("`id`=".$repl['id'])->_execute();
				$this->fields->_update(['pos'=>$repl['pos']])->_where("`id`=".$rec['id'])->_execute();
			}
		}
	}
	function get_free_pos($field_id) {
		$ret = 0;
		if (($rec = $this->fields->get_by_id((int)$field_id))
			&& ($repl = $this->fields->get(($rec['form_id']?"`form_id`=".$rec['form_id']:"`sessid`='".$rec['sessid']."'"),"`pos` DESC"))) {
			$ret = $repl['pos'] + 1;
		}
		return $ret;
	}
	function save_script($fid, $script) {
		return $this->_update(['script'=>base64_encode($script)])->_where("`id`=".(int)$fid)->_execute();
	}
	function save_field_settings() {
		if ($rec = $this->get_field(Elf::input()->get('field_id'),Elf::input()->get('fid'),Elf::input()->get('table_name'))) {
			if (Elf::input()->get('params'))
				Elf::input()->set('params', base64_encode(json_encode(Elf::input()->get('params', false))));
			$this->fields->_update(Elf::input()->data())->_where("`id`=".$rec['id'])->_execute();
		}
	}
	function save_link_size() {
		$ret = '';
		if ((Elf::input()->get('table')
			|| Elf::input()->get('link_field')
			|| Elf::input()->get('search_fields')
			|| Elf::input()->get('getter')
			|| Elf::input()->get('params')
			|| Elf::input()->get('display_field'))
			&& ($rec = $this->get_field(Elf::input()->get('field_id'),Elf::input()->get('fid'),Elf::input()->get('table_name')))) {
			$size = (array)json_decode(base64_decode($rec['size']));
			if (Elf::input()->get('table'))
				$size['table'] = Elf::input()->get('table');
			if (Elf::input()->get('link_field'))
				$size['link_field'] = Elf::input()->get('link_field');
			if (Elf::input()->get('search_fields'))
				$size['search_fields'] = Elf::input()->get('search_fields');
			if (Elf::input()->get('getter'))
				$size['getter'] = Elf::input()->get('getter');
			if (Elf::input()->get('params'))
				$size['params'] = Elf::input()->get('params');
			if (Elf::input()->get('display_field'))
				$size['display_field'] = Elf::input()->get('display_field');
			$ret = base64_encode(json_encode($size));
			$this->fields->_update(['size'=>$ret])->_where("`id`=".$rec['id'])->_execute();
		}
		return $ret;
	}
	function get_link_type_fields($table, $field_id, $name, $size = '') {
		$ret = '';
		if ($res = $this->_get_table_fields($table)) {
			if ($size)
				$size = (array)json_decode(base64_decode($size));
			if (!empty($size['search_fields'])) {
				$size['search_fields'] = explode(',',$size['search_fields']);
			}
			if (!empty($size['link_field']))
				$size['link_field'] = [$size['link_field']];
			if (!empty($size['display_field']))
				$size['display_field'] = [$size['display_field']];
			$ret = '<select name="'.$name.'" class="link-type-fields-selector" size="5" '.($name=='search_fields'?'multiple="multiple"':'').' onchange="_save_link_size($(this),\''.$name.'\','.$field_id.',\'\')">';
			foreach ($res as $k=>$v) {
				$ret .= '<option value="'.$k.'" '.(!empty($size[$name]) && in_array($k, $size[$name])?'selected="selected"':'').'>'.$k.'</option>';
			}
			$ret .= '</select>';
		}
		return $ret;
	}
	function get_visible_field_value_of_link($field_id, $v) {
		if (($f = $this->fields->get_by_id($field_id))
			&& ($f['type'] == 'link')
			&& $f['size']) {
			$f['size'] = (array)json_decode(base64_decode($f['size']));
			$model = new Db($f['size']['table']);
			$v = $model->_select("`{$f['size']['display_field']}`")->_where("`{$f['size']['link_field']}`='{$v}'")->_limit(1)->_execute()[0];
			$v = $v[$f['size']['display_field']];
		}
		return $v;
	}
	function action() {
		$data = Elf::input()->data();
		if ($ret = $this->_action($data)) {
			// related recs proceed
			if (!empty($data['related_data'])) {
				foreach ((array)json_decode(base64_decode($data['related_data'])) as $k=>$v) {
					if ($frm = $this->get_related_form((int)$data['form_id'], (int)$k)) {
						$v = (array)$v;
						switch ($frm['relation_type']) {
							case 'single':
								$v['form_id'] = $frm['slave_id'];
								$v[$frm['slave_field']] = $ret;
								$this->_action($v);
								break;
							case 'multi':
								$model = new Db($this->_get_table_by_form_id($frm['slave_id']));
								foreach ($v as $kk=>$vv) {
									$model->_update([$frm['slave_field'] => $ret])->_where("`id`=".(int)$vv)->_execute(null, true);
								}
								break;
							case 'widemulti':
								if ($frm['rule']) {
//									$frm['rule'] = (array)json_decode(base64_decode($frm['rule']));
									$model = new Db($frm['rule']['relation_table']);
									foreach ($v as $kk=>$vv) {
										$model->_insert([$frm['rule']['rel_master_field'] => $ret,
															$frm['rule']['rel_slave_field'] => $vv])->_execute(null, true);
									}
								}
								break;
						}
					}
				}
			}
		}
		return $ret;
	}
	private function _action($data) {
		$ret = true;
		if (!empty($data['form_id'])
			&& ($rec = $this->get_by_id((int)$data['form_id'], true))
			&& ($tfields = $this->_get_table_fields($rec['table']))) {
//			$model = "Elf\\App\\Models\\".(!empty($data['model'])?$data['model']:str_replace(DB_PREFIX, '', $rec['table']));
//			$model = new $model;
			$model = new Db($rec['table']);
			$out = [];
			$log = fopen(ROOTPATH.'logs/log.log','wb');
			fwrite($log, print_r($data, true));
			//
			if (!empty($data['id']))
				$upd = $model->get_by_id((int)$data['id']);
			foreach ($rec['fields'] as $k=>$v)
				if (!isset($data[$k])) $data[$k] = null;
			foreach ($data as $k=>$v) {
//				print_r($data);
//				print_r($rec['fields']);
//				print_r($tfields);
//				exit;
				if (!empty($rec['fields'][$k]) && !empty($tfields[$k])) {
					if ($rec['fields'][$k]['required'] && empty($v)) {
						$ret = false;
						Elf::set_data('error', Elf::lang('forms')->item('error.required.field', $k));
						break;
					}
					if ($rec['fields'][$k]['pattern'] && !preg_match("/".$rec['fields'][$k]['pattern']."/", $v)) {
						$ret = false;
						Elf::set_data('error', Elf::lang('forms')->item('error.pattern.field', $k));
						break;
					}
					if ($rec['fields'][$k]['type'] == 'checkbox') {
						if (!empty($v))
							$v = 1;
						else
							$v = 0;
					}
					elseif ($rec['fields'][$k]['type'] == 'eval') {
						switch ($rec['fields'][$k]['default_value']) {
							case 'CURRENT_TIMESTAMP':
								$v = time();
								break;
							case 'CURRENT_USERID':
								$v = (int)Elf::session()->get('uid');
								break;
							case 'ONCE_TIMESTAMP':
								if (empty($upd))
									$v = time();
								else
									$v = false;
								break;
							case 'ONCE_USERID':
								if (empty($upd))
									$v = (int)Elf::session()->get('uid');
								else
									$v= false;
								break;
							case 'CURRENT_SESSID':
								$v = Elf::session()->sessid();
								break;
						}
					}
					elseif ($rec['fields'][$k]['type'] == 'date') {
						switch ($tfields[$k]['table_type']) {
							case 'int':
								$v = Elf::date2timestamp($v);
								break;
							case 'date':
								$v = preg_replace("/(\d{2})\.(\d{2})\.(\d{4})/","${3}-${2}-${1}",$v);
								break;
						}
					}
					elseif ($rec['fields'][$k]['type'] == 'password') {
						if ($v)
							$v = md5($v);
						else
							$v = false;
					}
					elseif ($rec['fields'][$k]['type'] == 'picture') {
						if ($v)
							$v = basename($v);
						else
							$v = '';
					}
					elseif (in_array($rec['fields'][$k]['type'],['h','hm','hms'])) {
						$v = 0;
						if (isset($data[$k.'[hours]']))
							$v += (int)$data[$k.'[hours]']*3600;
						if (isset($data[$k.'[mins]']))
							$v += (int)$data[$k.'[mins]']*60;
						if (isset($data[$k.'[sec]']))
							$v += (int)$data[$k.'[sec]'];
					}
					if ($v !== false)
						switch ($tfields[$k]['table_type']) {
							case 'varchar':
							case 'text':
							case 'wysiwyg':
							case 'enum':
							case 'date':
								$out[$k] = $v;
								break;
							case 'int':
								$out[$k] = (int)$v;
								break;
							case 'decimal':
							case 'float':
								$out[$k] = (float)$v;
								break;
						}
				}
			}
			if ($ret) {
				if (!empty($upd)) {
					unset($out['id']);
					if ($model->_update($out)->_where("`id`=".$upd['id'])->_execute(null, true))
						$ret = $upd['id'];
				}
				else {
					if (!empty($data['id'])) unset($data['id']);
					$ret = $model->_insert($out)->_execute(null, true);
				}
			}
		}
		else {
			$ret = false;
			Elf::set_data('error', Elf::lang('forms')->item('error.form.id'));
		}
		fclose($log);
		return $ret;
	}
	function remove_related_rec($id, $fid) {
		if ($t = $this->get_by_id($fid)) {
			$model = new Db($t['table']);
			return $model->_delete()->_where("`id`=".(int)$id)->_execute(null, true);
		}
	}
	function _remove($fid) {
		if ($rec  = parent::get_by_id($fid)) {
			if (is_file(Elf::get_app_views_path().$rec['fullpath'])) {
				@unlink(Elf::get_app_views_path().$rec['fullpath']);
			}
			return $this->_delete()->_where("`id`=".(int)$fid)->_execute();
		}
	}
	function _edit() {
		$ret = true;
		$data = Elf::input()->data();
		if (empty($data['name'])) {
			Elf::set_data('error', Elf::lang('forms')->item('error.name'));
			$ret = false;
		}
		if ($ret && (empty($data['path']) || empty($data['filename']))) {
			Elf::set_data('error', Elf::lang('forms')->item('error.path'));
			$ret = false;
		}
		if ($ret && !$this->_table_exists($data['table'])) {
			Elf::set_data('error', Elf::lang('forms')->item('error.table'));
			$ret = false;
		}
		if ($ret) {
//			echo 'pos:'.strrpos($data['filename'],EXT).' len:'.(strlen($data['filename'])-strlen(EXT));exit;
			$data['fullpath'] = $data['path'].'/'.$data['filename'].(strrpos($data['filename'],EXT)!==false && strrpos($data['filename'],EXT)==(strlen($data['filename'])-strlen(EXT))?'':EXT);
			$data['ajax_request'] = !empty($data['ajax_request'])?1:0;
			$data['getter'] = $data['getter']?base64_encode(Elf::input()->get('getter', false)):'';
			$data['redirect'] = $data['redirect']?base64_encode(Elf::input()->get('redirect', false)):'';
			if ((int)$data['id'] && ($rec = $this->get_by_id($data['id']))) { // exsits rec
				unset($data['id']);
				$this->_update($data)->_where("`id`=".$rec['id'])->_execute();
				$rec = $rec['id'];
			}
			else { // new rec
				unset($data['id']);
				$rec = $this->_insert($data)->_execute();
			}
			if (!empty($rec)) {
				$data['table'] = '';
				if (!is_dir(Elf::get_app_views_path().$data['path'])) {
					@mkdir(Elf::get_app_views_path().$data['path']);
					chmod(Elf::get_app_views_path().$data['path'], 0777);
				}
				if (!is_file(Elf::get_app_views_path().$data['path'].'/'.$data['filename'].EXT)) {
					@touch(Elf::get_app_views_path().$data['path'].'/'.$data['filename'].EXT);
					chmod(Elf::get_app_views_path().$data['path'].'/'.$data['filename'].EXT, 0666);
				}
				if ($fields = $this->get_fields(0, $data['table'], true)) {
					$ids = '';
					foreach ($fields as $v)
						$ids .= ($ids?',':'').$v['id'];
					$this->fields->_update(['form_id'=>$rec,'sessid'=>''])->_where("`id` IN (".$ids.")")->_execute();
				}
				$ret = $this->creator($rec);
			}
		}
		return $ret;
	}
	function creator($id) {
		$ret = true;
		if (($rec = $this->get_by_id((int)$id, true)) && $rec['fullpath']
			&& ($f = fopen(Elf::get_app_views_path().$rec['fullpath'],'wb'))) {
			$file = $this->_creator($rec);
			$rel_forms = '';
			if ($rfs = $this->get_related_forms($rec['id'])) {
				foreach ($rfs as $v) {
					if ($rf = $this->get_by_id($v['slave_id'], true)) {
						$rel_forms .= $this->_creator(array_merge($v, $rf), $v['relation_type']);
					}
				}
			}
			$file = str_replace('%%related_forms%%', $rel_forms, $file);
			fwrite($f, $file);
			fclose($f);
		}
		else {
			$ret = false;
			Elf::set_data('error', Elf::lang('forms')->item('error.writetodisk'));
		}
		return $ret;
//		exit;
	}
	private function _creator($rec, $related = '') {
		$file = '';
		$fname = str_replace('%%relation_type%%',$related?'_'.$related:$related,FORM_TEMPLATE_FILE);
		if ($f = fopen($fname, 'rb')) {
			$file = fread($f, filesize($fname));
			fclose($f);
			////////////////////
			$file = str_replace('%%table%%',ucfirst(str_replace(DB_PREFIX,'',$rec['table'])),$file);
			$file = str_replace('%%model%%',$rec['model']?$rec['model']:ucfirst(str_replace(DB_PREFIX,'',$rec['table'])),$file);
			$file = str_replace('%%full_table%%',$rec['table'],$file);
			////////////////////
			$str = '';
			if ($rec['getter']) {
				$str = "<?php\n";
				$str .= "\t\$rec = ".$rec['getter'].";\n";
				$str .= "?>\n";
			}
			$file = str_replace('%%getter%%', $str, $file);
			////////////////////////////
			$file = str_replace('%%lang%%', $rec['lang'], $file);
			////////////////////////////
			$file = str_replace('%%redirect%%', $rec['redirect'], $file);
			////////////////////////////
			$file = str_replace('%%form_id%%', $rec['id'], $file);
			////////////////////////////
			$file = str_replace('%%name%%', $rec['name'], $file);
			////////////////////////////
			$file = str_replace('%%script%%', $rec['script']?base64_decode($rec['script']):'', $file);
			////////////////////////////
			if (isset($rec['slave_field']))
				$file = str_replace('%%slave_field%%', $rec['slave_field'], $file);
			////////////////////////////
			if (isset($rec['master_field']))
				$file = str_replace('%%master_field%%', $rec['master_field'], $file);
			////////////////////////////
			if (isset($rec['slave_id']))
				$file = str_replace('%%slave_id%%', $rec['slave_id'], $file);
			////////////////////////////
			if (isset($rec['master_id']))
				$file = str_replace('%%master_id%%', $rec['master_id'], $file);
			////////////////////////////
			if ($rec['action'])
				$file = str_replace('%%action%%', $rec['action'], $file);
			else
				$file = str_replace('%%action%%', FORM_DEFAULT_ACTION, $file);
			////////////////////////////
			if ($rec['method'])
				$file = str_replace('%%method%%', $rec['method'], $file);
			else
				$file = str_replace('%%method%%', 'post', $file);
			////////////////////////////
			if ($rec['ajax_request'])
				$file = str_replace('%%ajax_request%%', 'ajax-request', $file);
			else
				$file = str_replace('%%ajax_request%%', '', $file);
			////////////////////////////
			if ($rec['js_callback'])
				$file = str_replace('%%js_callback%%', $rec['js_callback'], $file);
			else
				$file = str_replace('%%js_callback%%', '', $file);
			///////////////////////
			$str = '';
			$pictures = [];
			if ($rec['fields']) {
				$another = false;
				foreach ($rec['fields'] as $field_name=>$f) {
					if ($f['type'] == 'hidden') {
						$str .= "\t<input class=\"%%related_class%%\" %%related_input%% type=\"hidden\" name=\"{$field_name}\" value=\"<?=!empty(\$rec['{$field_name}'])?\$rec['{$field_name}']:'{$f['default_value']}'?>\" />\n";
					}
					else
						$another = true;
				}
				if ($another) {
					if ($related && isset($rec['rule']['type']) && ($rec['rule']['type'] == 'condition')) {
//						$s = '$("#elf-form-'.$rec['master_id'].' input[name='.$rec['rule']['field'].'],#elf-form-'.$rec['master_id'].' textarea[name='.$rec['rule']['field'].']").on("input",function() {if($(this).val()'.($rec['rule']['condition_oper']?$rec['rule']['condition_oper']:'==').'"'.$rec['rule']['value'].'") _sw_related_form('.$rec['slave_id'].',"show"); else _sw_related_form('.$rec['slave_id'].',"hide");});';
//						$s .= '$("#elf-form-'.$rec['master_id'].' select[name='.$rec['rule']['field'].']").on("change",function() {if($(this).val()'.($rec['rule']['condition_oper']?$rec['rule']['condition_oper']:'==').'"'.$rec['rule']['value'].'") _sw_related_form('.$rec['slave_id'].',"show"); else _sw_related_form('.$rec['slave_id'].',"hide");});';
//						$s .= "\n_sw_related_form({$rec['slave_id']},\"hide\");\nif ('{$rec['rule']['value']}'".($rec['rule']['condition_oper']?$rec['rule']['condition_oper']:'==')."$(\"#elf-form-{$rec['master_id']} input[name={$rec['rule']['field']}],#elf-form-{$rec['master_id']} textarea[name={$rec['rule']['field']}],#elf-form-{$rec['master_id']} select[name={$rec['rule']['field']}]\").val()) _sw_related_form({$rec['slave_id']},\"show\");\n";
						$file = str_replace('%%condition_rule%%', Elf::load_template('forms/condition_rule_template',
																						['master_id'=>$rec['master_id'],
																							'slave_id'=>$rec['slave_id'],
																							'field'=>$rec['rule']['field'],
																							'value'=>$rec['rule']['value'],
																							'condition_oper'=>$rec['rule']['condition_oper']]), $file);
					}
					else
						$file = str_replace('%%condition_rule%%', '', $file);
					if (!$related || ($related=='single')) {
						$str .= "\t<table>\n";
						foreach ($rec['fields'] as $field_name=>$f) {
							if ($related && ($field_name == $rec['slave_field']))
								continue;
							if (!in_array($f['type'], ['hidden','eval'])) {
								$attrs = '';
								if ($f['placeholder'])
									$attrs .= ' placeholder="'.$f['placeholder'].'"';
								if ($f['title'])
									$attrs .= ' title="'.$f['title'].'"';
								if ($f['required'])
									$attrs .= ' required="required"';
								if ($f['autocomplete'])
									$attrs .= ' autocomplete="off"';
								$str .= "\t\t<tr id=\"elf-form-field-{$rec['id']}-{$field_name}\">\n";
								$str .= "\t\t\t<th>".($f['name']?$f['name'].':':'--- name not set ---')."</th>\n";
								switch ($f['type']) {
									case 'string':
									case 'float':
										$str .= "\t\t\t<td><input class=\"%%related_class%%\" %%related_input%% type=\"text\" ".((int)$f['size']?'maxlength="'.(int)$f['size'].'"':'')." name=\"{$field_name}\" value=\"<?=!empty(\$rec['{$field_name}'])?\$rec['{$field_name}']:'{$f['default_value']}'?>\" $attrs /></td>\n";
										break;
									case 'password':
										$str .= "\t\t\t<td><input class=\"%%related_class%%\" %%related_input%% type=\"password\" ".((int)$f['size']?'maxlength="'.(int)$f['size'].'"':'')." name=\"{$field_name}\" value=\"\" $attrs /></td>\n";
										break;
									case 'text':
										$str .= "\t\t\t<td><textarea class=\"%%related_class%%\" %%related_input%% ".((int)$f['size']?'maxlength="'.(int)$f['size'].'"':'')." name=\"{$field_name}\" $attrs rows=\"".(!empty($f['params']['rows'])?$f['params']['rows']:3)."\"><?=!empty(\$rec['{$field_name}'])?\$rec['{$field_name}']:'{$f['default_value']}'?></textarea></td>\n";
										break;
									case 'wysiwyg':
										$str .= "\t\t\t<td><textarea id=\"elf-form-wysiwyg-{$field_name}\" class=\"ckeditor %%related_class%%\" %%related_input%% ".((int)$f['size']?'maxlength="'.(int)$f['size'].'"':'')." name=\"{$field_name}\" $attrs rows=\"".(!empty($f['params']['rows'])?$f['params']['rows']:3)."\"><?=!empty(\$rec['{$field_name}'])?\$rec['{$field_name}']:'{$f['default_value']}'?></textarea></td>\n";
										break;
									case 'picture':
										$str .= "\t\t\t<td><div id=\"elf-form-picture-{$field_name}\" class=\"elf-uploader-cont\"></div></td>\n";
										$pictures[$field_name] = !empty($f['params']['model'])?$f['params']['model']:$rec['model'].'_'.$field_name;
										break;
									case 'h':
									case 'hm':
									case 'hms':
										$str .= "\t\t\t<td>";
										$str .= $this->_create_hms_input($f['type'], $field_name, $f['default_value'], $attrs);
										$str .= "</td>\n";
										break;
									case 'date':
										$str .= "\t\t\t<td>										
										<input class=\"nowide date %%related_class%%\" %%related_input%% size=\"12\" type=\"text\" name=\"{$field_name}\" $attrs
											value=\"<?=!empty(\$rec['{$field_name}'])?date('d.m.Y',\$rec['{$field_name}']):'{$f['default_value']}'?>\" onfocus=\"this.select();lcs(this)\" onclick=\"event.cancelBubble=true;this.select();lcs(this)\" /></td>\n";
										break;
									case 'checkbox':
										$str .= "\t\t\t<td class=\"inputs-no-wide\"><input class=\"%%related_class%%\" %%related_input%% type=\"checkbox\" name=\"{$field_name}\" <?=!empty(\$rec['{$field_name}']) || (!isset(\$rec['{$field_name}']) && (int)'{$f['default_value']}'!=0)?'checked=\"checked\"':''?> $attrs /></td>\n";
										break;
									case 'int':
										$str .= "\t\t\t<td><input class=\"%%related_class%%\" %%related_input%% type=\"number\" ".((int)$f['size']?'maxlength="'.(int)$f['size'].'"':'')." name=\"{$field_name}\" value=\"<?=!empty(\$rec['{$field_name}'])?\$rec['{$field_name}']:'{$f['default_value']}'?>\" $attrs /></td>\n";
										break;
									case 'radio':
										$str .= "\t\t\t<td class=\"inputs-no-wide\"><div $attrs >";
										foreach (explode("\n", $f['size']) as $v)
											$str .= "<input class=\"%%related_class%%\" %%related_input%% type=\"radio\" name=\"{$field_name}\" value=\"$v\" <?=isset(\$rec['{$field_name}']) && (\$rec['{$field_name}']=='$v')?'checked=\"checked\"':''?>> - ".($rec['lang']?Elf::lang($rec['lang'])->item($v):$v)."&nbsp;";
										$str .= "</div>\n";
										break;
									case 'select_simple':
										$str .= "\t\t\t<td><select class=\"%%related_class%%\" %%related_input%% name=\"{$field_name}\" $attrs >";
										foreach (explode("\n", $f['size']) as $v)
											$str .= "<option value=\"$v\" <?=isset(\$rec['{$field_name}']) && (\$rec['{$field_name}']=='$v')?'selected=\"selected\"':''?>>".($rec['lang']?Elf::lang($rec['lang'])->item($v):$v)."</option>";
										$str .= "</select>\n";
										break;
									case 'select_enum':
										$str .= "\t\t\t<td><?=\$model->create_select('{$field_name}',!empty(\$rec['{$field_name}'])?\$rec['{$field_name}']:'','class=\"%%related_class%%\" %%related_input%% $attrs','{$rec['lang']}')?></td>\n";
										break;
									case 'link':
										$size = (array)json_decode(base64_decode($f['size']));
										$str .= "\t\t\t<td><select class=\"elf-advs-input elf-clever-selector %%related_class%%\" %%related_input%% name=\"{$field_name}\" data-step=\"1\" data-getter=\"".(!empty($size['getter'])?$size['getter']:'')."\" data-selected=\"<?=!empty(\$rec['{$field_name}'])?\$rec['{$field_name}']:''?>\" data-link-field=\"".(!empty($size['link_field'])?$size['link_field']:'')."\" data-search-fields=\"".(!empty($size['search_fields'])?$size['search_fields']:'')."\" data-display-field=\"".(!empty($size['display_field'])?$size['display_field']:'')."\" data-params=\"".(!empty($size['params'])?$size['params']:'').";ajax_request_forced=1\" $attrs>\n";
										$str .=	"\t\t\t\t<option value=\"\"><% lang:forms:set.value %></option>\n";
										$str .= "\t\t\t</select></td>\n";
										break;
								}
								$str .= "\t\t</tr>\n";
							}
						}
						$str .= "\t</table>\n";
						if (sizeof($pictures)) {
							$str .= "\t<script>\n";
							foreach ($pictures as $pk=>$pv)
								$str .= "\t\tlet  elf_form_picture_{$pk}= new ELF_Uploader('elf-form-picture-{$pk}','{$pv}','{$pk}',<?=!empty(\$rec['{$pk}'])?json_encode([\$rec['{$pk}']]):'false'?>,false,<?=json_encode(['rem_func'=>'/uploader/rem_file','upload_func'=>'/uploader/async_upload','crop_func'=>'/uploader/crop','editable'=>true,'crop_ratio'=>true])?>);\n";
							$str .= "\t</script>";
						}
					}
					else {//($related == 'multi') { // $related == 'multi'
						$str = '';
						$file = str_replace('%%fullpath%%', str_replace(EXT,'',$rec['fullpath']), $file);
						$file = str_replace('%%getter_variable_name%%', $rec['getter_variable_name'], $file);
						if ($related == 'widemulti') {
							$file = str_replace('%%rel_slave_field%%', $rec['rule']['rel_slave_field'], $file);
							$file = str_replace('%%rel_getter%%', $rec['rule']['rel_getter'], $file);
							$file = str_replace('%%rel_display_field%%', $rec['rule']['rel_display_field'], $file);
							$file = str_replace('%%pparams%%', $rec['rule']['rel_params'], $file);
							$file = str_replace('%%rel_params%%', base64_encode($rec['rule']['rel_params']), $file);
							$file = str_replace('%%rel_search_field%%', implode(",",$rec['rule']['rel_search_field']), $file);
						}
					}
				}
			}
			$file = str_replace('%%condition_rule%%', '', $file);
//			$file = str_replace('%%top%%', !empty($top)?$top:'[]', $file);
			$file = str_replace('%%fields%%', $str, $file);
			if ($related) {
				$file = str_replace('%%related_input%%', 'data-related-form-id="'.$rec['slave_id'].'"', $file);
				$file = str_replace('%%related_class%%', 'elf-form-related-data', $file);
			}
			else {
				$file = str_replace('%%related_input%%', '', $file);
				$file = str_replace('%%related_class%%', '', $file);
			}
		}
		return $file;
	}
	/////////////// RELATED FORMS
	function get_related_forms($fid, $session = false) {
		$ret = [];
		if ((int)$fid
			&& ($rec = parent::get_by_id($fid))) {
			$ret = $this->relforms->_select("t1.*,'".$rec['name']."' AS `master_name`")
									->_subquery("elf_forms","t2")
										->_select("t2.`name`")->_where("t1.`slave_id`=t2.`id`")->_closesquery("slave_name")
									->_where("t1.`master_id`=".$rec['id'])->_orderby("pos")->_execute();
		}
		if ($session
			&& ($res = $this->relforms->_select("t1.*".(!empty($rec)?",'".$rec['name']."' AS `master_name`":""))
									->_subquery("elf_forms","t2")
										->_select("t2.`name`")->_where("t1.`slave_id`=t2.`id`")->_closesquery("slave_name")
									->_where("t1.`sessid`='".Elf::session()->sessid()."'")->_execute())) {
			if ($ret)
				$ret = array_merge($ret, $res);
			else
				$ret = $res;
		}
		if ($ret) foreach ($ret as $k=>$v) $ret[$k] = array_merge($v, $this->_init_related_vars($v));
		return $ret;
	}
	function get_related_form($fid, $slave_id = 0, $create = false) {
		$ret = [];
		if ((int)$fid
			&& ($rec = $this->relforms->get("`master_id`=".(int)$fid." AND `slave_id`=".(int)$slave_id))) {
			$ret = $rec;
		}
		elseif ($create) {// new related form rec
			if ($ret = $this->relforms->_insert(['master_id'=>(int)$fid,
												'slave_id'=>(int)$slave_id,
												'sessid'=>!(int)$fid?Elf::session()->sessid():''])->_execute(null, true)) {
				$ret = $this->relforms->_select()
								->_subquery("elf_forms","t2")
									->_select("t2.`name`")->_where("t1.`master_id`=t2.`id`")->_closesquery("master_name")
								->_subquery("elf_forms","t3")
									->_select("t3.`name`")->_where("t1.`slave_id`=t3.`id`")->_closesquery("slave_name")
								->_subquery("elf_forms","t3")
									->_select("t3.`table`")->_where("t1.`slave_id`=t3.`id`")->_closesquery("slave_table")
								->_where("t1.`id`=".$ret)->_execute()[0];
			}
		}
		$ret = array_merge($ret, $this->_init_related_vars($ret));
		return $ret;
	}
	private function _init_related_vars($ret) {
		if ($ret['rule'])
			$ret['rule'] = (array)json_decode(base64_decode($ret['rule']));
		$ret['form_selector'] = $this->_slave_form_selector(!empty($ret['slave_id'])?$ret['slave_id']:0,!empty($ret['master_id'])?$ret['master_id']:0,$ret['id']);
		
		$ret['relation_table_selector'] = $this->_tables_selector(!empty($ret['rule']['relation_table'])?$ret['rule']['relation_table']:'','rule_relation_table','onchange="_save_related_data(this.value, \'rule_relation_table\', '.$ret['id'].');_get_relation_table_fields(this.value, '.$ret['id'].')"');
		if (!empty($ret['rule']['relation_table'])) {
			$ret['rel_master_fields'] = $this->_relation_table_fields_selector($ret['rule']['relation_table'],'rule_rel_master_field',!empty($ret['rule']['rel_master_field'])?$ret['rule']['rel_master_field']:'','onchange="_save_related_data(this.value, \'rule_rel_master_field\', '.$ret['id'].')"');
			$ret['rel_slave_fields'] = $this->_relation_table_fields_selector($ret['rule']['relation_table'],'rule_rel_slave_field',!empty($ret['rule']['rel_slave_field'])?$ret['rule']['rel_slave_field']:'','onchange="_save_related_data(this.value, \'rule_rel_slave_field\', '.$ret['id'].')"');
			if (!empty($ret['slave_id']) && ($stbl = $this->_get_table_by_form_id($ret['slave_id']))) {
				$ret['rel_display_fields'] = $this->_relation_table_fields_selector($stbl,'rule_rel_display_field',!empty($ret['rule']['rel_display_field'])?$ret['rule']['rel_display_field']:'','size="5" onchange="_save_related_data(this.value, \'rule_rel_display_field\', '.$ret['id'].')"');
				$ret['rel_search_fields'] = $this->_relation_table_fields_selector($stbl,'rule_rel_search_field',!empty($ret['rule']['rel_search_field'])?$ret['rule']['rel_search_field']:'','size="5" multiple="multiple" onchange="_save_related_data($(this).val(), \'rule_rel_search_field\', '.$ret['id'].')"');
			}
		}
		
		$ret['master_fields'] = $this->get_table_fields_by_formid($ret['master_id'],'master_field',$ret['master_field'], $ret['id']);
		$ret['rule_fields'] = $this->get_table_fields_by_formid($ret['master_id'],'rule_field',!empty($ret['rule']['field'])?$ret['rule']['field']:'', $ret['id']);
		if ($ret['slave_id'])
			$ret['slave_fields'] = $this->get_table_fields_by_formid($ret['slave_id'],'slave_field',$ret['slave_field'], $ret['id']);
		$ret['relation_type_selector'] = $this->relforms->create_select('relation_type', $ret['relation_type'], 'onchange="_save_related_data(this.value, \'relation_type\', '.$ret['id'].')"', 'forms');
		return $ret;
	}
	function save_related_data() {
		$data = Elf::input()->data();
		if (!empty($data['id']) && ($rec = $this->relforms->get_by_id((int)$data['id']))) {
			$rule = (array)json_decode(base64_decode($rec['rule']));
			foreach ($data as $k=>$v) {
				if (strpos($k,'rule_') !== false) {
					$rule[str_replace('rule_','',$k)] = $v;
				}
			}
			if ($rule)
				$data['rule'] = base64_encode(json_encode($rule));
			$this->relforms->_update($data)->_where("`id`=".$rec['id'])->_execute();
		}
		return true;
	}
	function get_related_widemulti_data($master_id, $slave_id, $master_val) {
		$ret = null;
		if (!empty($master_val)
			&& ($rec = $this->relforms->get("`master_id`=".(int)$master_id." AND `slave_id`=".(int)$slave_id))) {
			if (!empty($rec['rule'])) {
				$rec['rule'] = (array)json_decode(base64_decode($rec['rule']));
				if (!empty($rec['rule']['relation_table'])) {
					$rt = new Elf\Libs\Db($rec['rule']['relation_table']);
					if ($ret = $rt->_select()->_where("`{$rec['rule']['rel_master_field']}`='{$master_val}'")->_execute()) {
						$slave_vals = '';
						foreach ($ret as $v)
							$slave_vals .= ($slave_vals?',':'')."'".$v[$rec['rule']['rel_slave_field']]."'";
						$table = new Elf\Libs\Db($this->_get_table_by_form_id($rec['slave_id']));
						$ret = $table->_select()->_where("`{$rec['slave_field']}` IN ({$slave_vals})")->_execute();
					}
				}
			}
		}
		return $ret;
	}
	function get_related_rec($table, $link_field, $val) {
		$t = new Elf\Libs\Db($table);
		return $t->get("`{$link_field}`='{$val}'");
	}
	function get_form_with_related_data($master_id, $slave_id) {
		if (($ret = parent::get_by_id($slave_id))
			&& ($rel = $this->relforms->get("`master_id`=".(int)$master_id." AND `slave_id`=".(int)$slave_id))) {
			unset($rel['id']);
			$ret = array_merge($ret, $rel);
		}
		return $ret;
	}
	private function _slave_form_selector($sel, $dis, $rfid) {
		$ret = Elf::lang('forms')->item('forms.not.found');
		if ($res = $this->_select()->_execute()) {
			$ret = '<select name="slave_id" onchange="_set_slave_form(this.value,'.$rfid.')"><option value="">'.Elf::lang('forms')->item('select.form').'</option>';
			foreach ($res as $v)
				$ret .= '<option value="'.$v['id'].'" '.($sel==$v['id']?'selected="selected"':'').' '.($dis==$v['id']?'disabled="disabled"':'').'>'.$v['name'].'</option>';
			$ret .= '</select>';
		}
		return $ret;
	}
	private function _get_table_by_form_id($form_id) {
		if ($ret = parent::get_by_id($form_id))
			$ret = $ret['table'];
		return $ret;
	}
	private function _create_hms_input($type, $fname, $defval, $attrs) {
		$ret = "
		<?php
		\$val = (int)(!empty(\$rec['{$fname}'])?\$rec['{$fname}']:'{$defval}');
		\$h = (int)(\$val/3600);
		\$m = (int)((\$val-\$h*3600)/60);
		\$s = \$val-\$h*3600-\$m*60;
		?>
		";
		$ret .= "<select class=\"nowide %%related_class%%\" %%related_input%% name=\"{$fname}[hours]\" {$attrs}>";
		$ret .= "<option value=\"-1\">hours</option>";
		for ($i = 0; $i <= 23; $i++)
			$ret .= "<option value=\"{$i}\" <?={$i}==\$h?'selected=\"selected\"':''?>>".str_pad($i, 2, '0', STR_PAD_LEFT)."</option>";
		$ret .= '</select>';
		if (($type == 'hm') || ($type == 'hms')) {
			$ret .= "<select class=\"nowide %%related_class%%\" %%related_input%% name=\"{$fname}[mins]\" {$attrs}>";
			$ret .= "<option value=\"-1\">mins</option>";
			for ($i = 0; $i <= 59; $i++)
				$ret .= "<option value=\"{$i}\" <?={$i}==\$m?'selected=\"selected\"':''?>>".str_pad($i, 2, '0', STR_PAD_LEFT)."</option>";
			$ret .= '</select>';
		}
		if ($type == 'hms') {
			$ret .= "<select class=\"nowide %%related_class%%\" %%related_input%% name=\"{$fname}[sec]\" {$attrs}>";
			$ret .= "<option value=\"-1\">sec</option>";
			for ($i = 0; $i <= 59; $i++)
				$ret .= "<option value=\"{$i}\" <?={$i}==\$s?'selected=\"selected\"':''?>>".str_pad($i, 2, '0', STR_PAD_LEFT)."</option>";
			$ret .= '</select>';
		}
		return $ret;
	}
}