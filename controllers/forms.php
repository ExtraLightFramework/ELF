<?php

namespace Elf\Controllers;

use Elf;

class Forms extends Auth {
	
	private $forms;
	
	function __construct() {
		Elf::set_data('action','/main/auth/'.GROUP_USER.'/1');
		parent::__construct(GROUP_USER, false, 'Elf\App\Models\Accounts');
		$this->forms = new Elf\Libs\Forms;
	}
	
	function index($offset = 0) {
		echo Elf::set_layout('admin')->load_view('forms/index',['data'=>$this->forms->_data($offset),'offset'=>(int)$offset]);
	}
	function edit() {
		if (!$this->forms->_edit())
			Elf::messagebox(Elf::get_data('error'));
		Elf::redirect('forms/index/'.(int)Elf::input()->get('offset'));
	}
	function del($fid, $offset = 0) {
		$this->forms->_remove((int)$fid);
		Elf::redirect('/forms/index/'.$offset);
	}
	// default action for forms
	function action() {
		$this->action_result((bool)$this->forms->action());
	}
	function action_result($success = true) {
		if ($success) {
			if (Elf::is_xml_request())
				echo json_encode(['ok'=>1]);
			else
				Elf::redirect(Elf::input()->get('redirect'));
		}
		else {
			if (Elf::is_xml_request())
				echo json_encode(['error'=>Elf::get_data('error')]);
			else {
				Elf::messagebox(Elf::get_data('error'));
				Elf::redirect(Elf::input()->get('redirect'));
			}
		}
	}
	function save_related_rec() {
		if ($id = $this->forms->action()) {
			if ($id != Elf::input()->get('id')) {
				Elf::input()->set('id', $id);
				Elf::input()->set('newrec', 1);
			}
			Elf::input()->set('link_field', Elf::input()->get('master_field'));
			Elf::input()->set('v', Elf::input()->get('id'));
			$this->get_related_rec();
//			$form_fields = $this->forms->get_fields(Elf::input()->get('relwid'), Elf::input()->get('table'));
//			$data = Elf::load_template('forms/related_rec_line',['fields'=>$form_fields,
//															'recs'=>[Elf::input()->data()],
//															'lang'=>Elf::input()->get('lang'),
//															'form_id'=>Elf::input()->get('relwid'),
//															'add_link'=>base64_decode(Elf::input()->get('add_link'))]);
/*			foreach (Elf::input()->data() as $k=>$v) {
				if (!array_key_exists($k, $form_fields) || ($k == Elf::input()->get('slave_field'))) continue;
				$data .= Elf::load_template('forms/related_rec_line_cell',['type'=>$form_fields[$k]['type'],
																		'lang'=>Elf::input()->get('lang'),
																		'field_name'=>$k,
																		'value'=>$v,
																		'dialog'=>Elf::input()->get('dialog'),
																		'getter_variable_name'=>Elf::input()->get('getter_variable_name'),
																		'slave_field'=>Elf::input()->get('slave_field'),
																		'%%slave_field%%'=>$id,
																		'caption'=>Elf::input()->get('caption'),
																		'default_value'=>$form_fields[$k]['default_value'],
																		'is_related_form'=>Elf::input()->get('relwid')]);
			}
			$data .= '</tr>';
*///			echo json_encode(['data'=>$data,'relwid'=>Elf::input()->get('relwid'),'newrec'=>!empty($newrec)?1:0,'rec_id'=>$id]);
		}
		else
			echo json_encode(['error'=>Elf::get_data('error')]);
	}
	function get_related_rec() {
		if ($rec = $this->forms->get_related_rec(Elf::input()->get('table'), Elf::input()->get('link_field'), Elf::input()->get('v'))) {
//			$frm = $this->forms->get_form_with_related_data(Elf::input()->get('relwid'), Elf::input()->get('form_id'));
			$form_fields = $this->forms->get_fields(Elf::input()->get('relwid'), Elf::input()->get('table'));
			$data = Elf::load_template('forms/related_rec_line',['fields'=>$form_fields,
															'recs'=>[$rec],
															'frm'=>$this->forms->get_form_with_related_data(Elf::input()->get('master_id'), Elf::input()->get('form_id')),
															'lang'=>Elf::input()->get('lang'),
															'form_id'=>Elf::input()->get('relwid'),
															'add_link'=>base64_decode(Elf::input()->get('add_link'))]);
			echo json_encode(['data'=>$data,
								'relwid'=>Elf::input()->get('relwid'),
								'newrec'=>(int)Elf::input()->get('newrec'),
								'rec_id'=>$rec['id']]);
		}
		else
			echo json_encode(['error'=>Elf::get_data('error')]);
	}
	function remove_related_rec() {
		echo $this->forms->remove_related_rec(Elf::input()->get('id'), Elf::input()->get('slave_id'));
	}
	function get_form_field() {
		if ($rec = $this->forms->get_field((int)Elf::input()->get('field_id'),
											(int)Elf::input()->get('fid'),
											Elf::input()->get('table'), (bool)Elf::input()->get('create'))) {
			Elf::input()->set('field_id', $rec['id']);
			Elf::input()->set('create', false);
			echo json_encode(['data'=>Elf::load_template('forms/edit_field',Elf::input()->data()),'fid'=>$rec['id']]);
		}
	}
	function get_form_fields() {
		$ret = '';
		if ($res = $this->forms->get_fields(Elf::input()->get('fid'), Elf::input()->get('table'), true))
			foreach ($res as $v)
				$ret .= Elf::load_template('forms/edit_field',['field_id'=>$v['id'],
																'fid'=>$v['form_id'],
																'table'=>$v['table_name'],
																'create'=>false]);
		echo $ret;
	}
	function remove_form_field() {
		echo $this->forms->remove_form_field(Elf::input()->get('fid'), Elf::input()->get('field_id'));
	}
	function get_table_fields() {
		echo $this->forms->_relation_table_fields_selector(Elf::input()->get('table'), 'table_field', '', '');
	}
	function save_field_settings() {
		echo $this->forms->save_field_settings();
	}
	function save_link_size() {
		echo $this->forms->save_link_size();
	}
	function save_script() {
		echo $this->forms->save_script(Elf::input()->get('fid'), Elf::input()->get('script',false));
	}
	function get_link_type_fields() {
		echo $this->forms->get_link_type_fields(Elf::input()->get('table'),
												Elf::input()->get('field_id'),
												Elf::input()->get('name'),
												Elf::input()->get('size'));
	}
	function ch_fields_pos() {
		echo $this->forms->ch_fields_pos(Elf::input()->get('field_id'), Elf::input()->get('direct'));
	}
	/////////// RELATED Forms
	function get_related_forms() {
		$ret = '';
		if ($res = $this->forms->get_related_forms(Elf::input()->get('fid'), true))
			foreach ($res as $v)
				$ret .= Elf::load_template('forms/related_form_blck',['rec'=>$v]);
		echo $ret;
	}
	function get_related_form() {
		echo Elf::load_template('forms/related_form_blck', ['rec'=>$this->forms->get_related_form(Elf::input()->get('fid'),
																							Elf::input()->get('slave_id'),
																							Elf::input()->get('create'))]);
	}
	function save_related_data() {
		echo $this->forms->save_related_data();
	}
	function get_table_fields_by_formid() {
		echo $this->forms->get_table_fields_by_formid(Elf::input()->get('form_id'),
														Elf::input()->get('name'),'',
														Elf::input()->get('rfid'));
	}
	function get_relation_table_fields() {
		echo json_encode(['rel_master_fields'=>$this->forms->_relation_table_fields_selector(Elf::input()->get('table'),
																										'rule_rel_master_field',
																										'',
																										'onchange="_save_related_data(this.value, \'rule_rel_master_field\', '.Elf::input()->get('rfid').')"'),
						'rel_slave_fields'=>$this->forms->_relation_table_fields_selector(Elf::input()->get('table'),
																										'rule_rel_slave_field',
																										'',
																										'onchange="_save_related_data(this.value, \'rule_rel_slave_field\', '.Elf::input()->get('rfid').')"')]);
	}
}