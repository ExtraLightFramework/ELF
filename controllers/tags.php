<?php

namespace Elf\Controllers;

use Elf;

class Tags extends Admin {
	
	private $tags;
	
	function __construct() {
		parent::__construct();
		$this->tags = new Elf\Libs\Tags;
	}
	
	function index($offset = 0) {
		echo Elf::set_layout('admin')->load_view('tags/index',['data'=>$this->tags->_data($offset),'offset'=>(int)$offset]);
	}
	function edit($offset = 0) {
		if ($this->tags->_edit(Elf::input()->get('htag'),0,(int)Elf::input()->get('id'))) {
			Elf::messagebox(Elf::lang('tags')->item('save'));
		}
		else {
			Elf::messagebox(Elf::lang('tags')->item('unsave'));
		}
		Elf::redirect('tags/index/'.$offset);
	}
	function del($tid, $offset = 0) {
		$this->tags->_del($tid);
		Elf::redirect('tags/index/'.$offset);		
	}
	function del_tag_content() {
		if (($freq = $this->tags->_del_tag_content(Elf::input()->get('tid'),
												Elf::input()->get('cid'))) !== false) {
			echo json_encode(['ok'=>1,'freq'=>$freq]);
		}
		else
			echo json_encode(['error'=>Elf::lang('tags')->item('cantremtag')]);
	}
}