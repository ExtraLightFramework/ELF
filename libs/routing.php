<?php

namespace Elf\Libs;

use Elf;

class Routing extends Db {
	
	private $uri;
	private $controller;
	private $method;
	private $controller_to;
	private $method_to;
	private $controller_inp; // controller from intuo uri
	private $method_inp; // method from input uri
	private $params;
	private $title;
	private $description;
	private $canonical;
	
	function __construct() {
		if (DIR_ALIAS)
			$this->uri = substr($_SERVER['REQUEST_URI'],0,strlen(DIR_ALIAS))==DIR_ALIAS?substr($_SERVER['REQUEST_URI'],strlen(DIR_ALIAS)):$_SERVER['REQUEST_URI'];
		else
			$this->uri = $_SERVER['REQUEST_URI'];
		if (($pos = strpos($this->uri,'?')) !== false) {
			$this->uri = substr($this->uri,0,$pos);
		}
		$this->uri = preg_replace(["/('|%22)/","/(\:)/","/\?/"],["","%3A","/"],$this->uri);
//		echo $this->uri;exit;
		parent::__construct('routing');
	}
	function init() {
		parent::init();
		$param_ptr = 1;
		$last_res = null;
		$this->controller = '';//DEFAULT_CONTROLLER;
		$this->method = '';
		
		// determ begin controller alias
		$sal = $esal = explode("/",substr($this->uri,1));

		$sz = sizeof($sal);
		do {
			if ($sz) {
				$this->controller = $this->controller_inp = implode("/",$sal);
				unset($sal[--$sz]);
				if ($this->get("`controller`='".addslashes(urldecode($this->controller))."'"))
					break;
			}
			else
				$this->controller = $this->controller_inp = $sal[0];
		}
		while ($sz);
		unset($sal);
		$param_ptr += $sz;
		$this->method = $this->method_inp = !empty($esal[$sz+1])?$esal[$sz+1]:'';

		$this->controller_to = $this->controller;
		$this->method_to = $this->method;
		while (($res = $this->get("`controller`='".addslashes(urldecode($this->controller_to))."'
									AND ".($this->method_to?"`method` IN ('".addslashes(urldecode($this->method_to))."')":
											"`method` IS NULL"),
									"`controller`,`method` DESC"))
				|| ($res = $this->get("`controller`='".addslashes(urldecode($this->controller_to))."'
									AND `method` IN ('*')",
									"`controller`,`method` DESC"))) {
			$this->controller_to = $res['controller_to'];
			$this->method_to = $res['method_to'];
			$last_res = $res;
			if ($res['is_last'])
				break;
		}
//		echo $this->controller_to.' '.$this->method_to;
//print_r($last_res);exit;
		if ($last_res) {
			if (($last_res['params_to'])
				&& ($last_res['params_to'] = explode("&amp;",$last_res['params_to']))) {
				foreach ($last_res['params_to'] as $v) {
					if (($v = explode("=",$v)) && isset($v[0]) && isset($v[1])) {
						$_GET[$v[0]] = $v[1];
//						$this->app->input()->set($v[0],$v[1]);
					}
				}
			}

//			Init global SEO variables
			if ($this->title = $last_res['title'])
				Elf::$_data['title'] = $last_res['title'];
			if ($this->description = $last_res['description'])
				Elf::$_data['description'] = $last_res['description'];
			if ($this->description = $last_res['keywords'])
				Elf::$_data['keywords'] = $last_res['keywords'];
			if ($this->canonical = $last_res['canonical'])
				Elf::$_data['canonical'] = $last_res['canonical'];
		}
//		throw new \Exception(print_r($last_res, true).' '.$last_res['method'].' '.$this->method);
		if (empty($this->controller_to)) {
			$this->controller_to = $this->determ_def_controller();
			$this->method_to = DEFAULT_METHOD;
		}
		elseif ((!file_exists(ROOTPATH.APP_DIR.'/'.CONTROLLERS_DIR.'/'.strtolower($this->controller_to).EXT)
			&& !file_exists(ROOTPATH.CONTROLLERS_DIR.'/'.strtolower($this->controller_to).EXT))
			|| (!empty($last_res) && !$last_res['method'] && $this->method)) {
			if (!Elf::is_xml_request()) {
				$this->controller_to = $this->determ_def_controller();
				$this->method_to = METHOD_404;
			}
			else
				throw new \Exception('Controller '.$this->controller_to.' not found');
		}
		else {
			if (file_exists(ROOTPATH.APP_DIR.'/'.CONTROLLERS_DIR.'/'.strtolower($this->controller_to).EXT))
				$this->controller_to = 'Elf\\'.APP_DIR.'\\'.CONTROLLERS_DIR.'\\'.$this->controller_to;
			else
				$this->controller_to = 'Elf\\'.CONTROLLERS_DIR.'\\'.$this->controller_to;
			$this->method_to = !empty($this->method_to)?$this->method_to:DEFAULT_METHOD;
		}
		$this->params = [];
		while (isset($esal[++$param_ptr])) {
			$this->params[] = $esal[$param_ptr];
		}
//		echo $this->controller.' '.$this->method.' '.$this->controller_to.' '.$this->method_to;exit;
		unset($esal);
	}
	private function determ_def_controller() {
		if (file_exists(ROOTPATH.APP_DIR.'/'.CONTROLLERS_DIR.'/'.strtolower(DEFAULT_CONTROLLER).EXT))
			return 'Elf\\'.APP_DIR.'\\'.CONTROLLERS_DIR.'\\'.DEFAULT_CONTROLLER;
		elseif (file_exists(ROOTPATH.CONTROLLERS_DIR.'/'.strtolower(DEFAULT_CONTROLLER).EXT))
			return 'Elf\\'.CONTROLLERS_DIR.'\\'.DEFAULT_CONTROLLER;
		else
			throw new \Exception('Default controller <b>'.DEFAULT_CONTROLLER.'</b> not found');
	}
	public function controller() {
		return $this->controller;
	}
	public function method() {
		return $this->method;
	}
	public function controller_to() {
		return $this->controller_to;
	}
	public function method_to() {
		return $this->method_to;
	}
	public function controller_inp() {
		return $this->controller_inp;
	}
	public function method_inp() {
		return $this->method_inp;
	}
	public function set_method_to($method) {
		$this->method_to = $method;
	}
	public function params() {
		return $this->params;
	}
	public function title() {
		return $this->title;
	}
	public function description() {
		return $this->description;
	}
	public function canonical() {
		return $this->canonical;
	}
	function _edit($c, $m, $cto = null, $mto = null, $params_to = null, $seo = null) {
		$cto = $cto?$cto:DEFAULT_CONTROLLER;
		$mto = $mto?$mto:DEFAULT_METHOD;
		$hash = null;
		$data['controller'] = $c;
		$data['method'] = $m;
		$data['controller_to'] = $cto;
		$data['method_to'] = $mto;
		$data['params_to'] = $params_to;
		$data['tm'] = time();
		if (!empty($seo['title']))
			$data['title'] = $seo['title'];
		if (!empty($seo['description']))
			$data['description'] = $seo['description'];
		if (!empty($seo['keywords']))
			$data['keywords'] = $seo['keywords'];
		
		if ($rec = $this->get("`controller`='".$c."' AND `method`='".$m."'","`controller`,`method`")) {
			unset($data['controller'], $data['method']);
			$this->_update($data)->_where("`id`=".$rec['id'])->_limit(1)->_execute();
			$ret = $rec['id'];
		}
		else {
			$ret = $this->_insert($data)->_execute();
		}
		if (!empty($ret)) {
			$hash = md5($ret.':'.$c.':'.$m);
			$this->_update(['hash'=>$hash])->_where("`id`=".$ret)->_execute();
		}
		return $hash;
	}
	function _del($c, $m = '*') {
		return $this->_delete()
						->_where("`controller`='".$c."'")
							->_and("`method`='".$m."'")
						->_limit(1)->_execute();
	}
	function _del_by_hash($hash) {
		return $this->_delete()
						->_where("`hash`='".addslashes($hash)."'")
						->_limit(1)->_execute();
	}
	function _data($offset) {
		$ret = $pagi = null;
		if ($ret = $this->_select()
				->_orderby("`id` DESC")->_limit(RECS_ON_PAGE,(int)$offset*RECS_ON_PAGE)
				->_execute()) {
			$pg = new Pagination;
			$pagi = $pg->create("/route/index/", 
								$this->cnt(),
								(int)$offset,
								RECS_ON_PAGE,
								3);
			unset($pg);
		}
		return array($ret, $pagi);
	}
	function _edit_rec() {
		$ret = false;
		$data = Elf::input()->data(false);
		foreach ($data as $k=>$v)
			$data[$k] = htmlspecialchars_decode(urldecode($v));
		$data['tm'] = time();
		if (!empty($data['is_last']))
			$data['is_last'] = 1;
		else
			$data['is_last'] = 0;
		if (!empty($data['id'])
			&& ($rec = $this->get_by_id((int)$data['id']))) {
			$ret = $this->_update($data)->_where("`id`=".$rec['id'])->_limit(1)->_execute();
		}
		else {
			if (isset($data['id']))
				unset($data['id']);
			$ret = $this->_insert($data)->_execute();
		}
		return $ret;
	}
	
// ====== SITEMAP.XML	
	function sitemap() {
		$items = Elf::load_template('route/sitemap_item',
											['url'=>Elf::site_url(),
													'date'=>date('Y-m-d'),
													'freq'=>'daily',
													'priority'=>'1.0']);
		if ($ret = $this->_select()->_orderby("`id`")->_execute()) {
			foreach ($ret as $v) {
				$items .= Elf::load_template('route/sitemap_item',
											['url'=>Elf::site_url().$v['controller'].($v['method']!='*'?'/'.$v['method'].'/':'/'),
													'date'=>date('Y-m-d',$v['tm']),
													'freq'=>'weekly',
													'priority'=>'0.5']);
			}
		}
		if ($f = fopen(ROOTPATH.'sitemap.xml','w')) {
			fwrite($f,"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".Elf::load_template('route/sitemap',['items'=>$items]));
			fclose($f);
		}
	}
}