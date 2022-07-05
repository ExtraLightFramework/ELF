<?php

namespace Elf\Libs;

use Elf;

class Viewer {
	
	static $DEFAULT_LAYOUT	= "layout";
	static $DEFAULT_VIEW	= "viewer/index";

	function __construct() {
	}
	// PARAMS structure
	//
	// $params = [
	//		'title'		=>	"Title view page",
	//		'layout'	=>	'' (default layout),
	//		'edit_lnk'	=>	['url' => "/controller/edit/%%id%%/%%offset%%
	//									or dialog=view_dir/edit;id=%%id%%;offset=%%offset%%",
	//							'request' => 'get|json'],
	//		'del_lnk'	=>	['url' => "/controller/del/%%id%%/%%offset%%",
	//							'request' => 'get|json'],
	//		'class'		=>	"Add class name for view table",
	//		'fields'	=>	[
	//							'name_in_DB'	=> ['title' => 'Title in view table',
	//												'type' => 'text|integer|float|boolean|image|checkbox',
	//												'lang' => 'lang file for translate',
	//												'editable' => true|false (default false)
	//												]
	//							...
	//						],
	//		'view'		=>	"path/to/alt/view/file",
	//		'id_col'	=>	true|false (default true)
	// ]
	function index($params, $data, $pagi, $offset) {
		return Elf::set_layout(!empty($params['layout'])?$params['layout']:static::$DEFAULT_LAYOUT)
						->load_view(!empty($params['view'])?$params['view']:static::$DEFAULT_VIEW,
								['params'=>$params,'data'=>$data,'pagi'=>$pagi,'offset'=>(int)$offset]);
	}
}