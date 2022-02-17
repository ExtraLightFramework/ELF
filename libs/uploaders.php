<?php

namespace Elf\Libs;

use Elf;

define ('UPLOADER_PATH',		'img/galery/%%albumdir%%/');
define ('UPLOADER_ICONS',		'img/galery/%%albumdir%%/icons/');
define ('UPLOADER_FPATH',		ROOTPATH.UPLOADER_PATH);
define ('UPLOADER_FICONS',		ROOTPATH.UPLOADER_ICONS);
define ('UPLOADER_IMAGE_MAX_XSIZE',	4650);
define ('UPLOADER_IMAGE_MAX_YSIZE',	3000);
define ('UPLOADER_IMAGE_XSIZE',	600);
define ('UPLOADER_IMAGE_YSIZE',	480);
define ('UPLOADER_ICON_XSIZE',	360);
define ('UPLOADER_ICON_YSIZE',	270);

class Uploaders extends Db {
	
	private $upltypes;
	public $path;
	public $icons;
	public $fpath;
	public $ficons;
	public $orient;
	public $w;
	public $h;
	public $icon_w;
	public $icon_h;
	protected $image_xsize;
	protected $image_ysize;
	protected $icon_xsize;
	protected $icon_ysize;
	protected $resize_ratio;

	function __construct($tbl = null, $dir = null) {
		parent::__construct($tbl);
		$this->resize_ratio = true;
		$this->upltypes = array('jpg','jpeg','gif','png');
//		if ($dir)
			$this->post_init($dir);
	}
	public function get_upload_types() {
		return $this->upltypes;
	}
	public function get_mime_types() {
		return array('image/jpeg','image/jpg','image/png','image/gif');
	}
	protected function crop_data() {
		return null;
	}
	protected function post_init($dir) {
		$this->path = str_replace("%%albumdir%%/",$dir?$dir.'/':'__tmp/',UPLOADER_PATH);
		$this->icons = str_replace("%%albumdir%%/",$dir?$dir.'/':'__tmp/',UPLOADER_ICONS);
		$this->fpath = str_replace("%%albumdir%%/",$dir?$dir.'/':'__tmp/',UPLOADER_FPATH);
		$this->ficons = str_replace("%%albumdir%%/",$dir?$dir.'/':'__tmp/',UPLOADER_FICONS);
		if (!is_dir($this->fpath))
			@mkdir($this->fpath);
		if (!is_dir($this->ficons))
			@mkdir($this->ficons);
		$this->image_xsize = $this->w = UPLOADER_IMAGE_XSIZE;
		$this->image_ysize = $this->h = UPLOADER_IMAGE_YSIZE;
		$this->icon_xsize = $this->icon_w = UPLOADER_ICON_XSIZE;
		$this->icon_ysize = $this->icon_h = UPLOADER_ICON_YSIZE;
	}
	
	function image_formalize($src, $w = 0, $h = 0, $icon = true) {
		// Picture
		$img = new Image($this->fpath.$src);
		$this->orient = $img->get_orient();
		$img->_scale($w?$w:$this->image_xsize, $h?$h:$this->image_ysize, $this->resize_ratio);
		$this->w = $img->get_w();
		$this->h = $img->get_h();
		unset($img);
		// Icon
		if ($icon) {
			$img = new Image($this->fpath.$src,$this->ficons.$src);
			$img->_scale($this->icon_xsize, $this->icon_ysize, $this->resize_ratio);
			$this->icon_w = $img->get_w();
			$this->icon_h = $img->get_h();
			unset($img);
		}
		else {
			@unlink($this->ficons.$src);
		}
	}
	function crop($name = 'picture') {
		$ret = true;
		$data = Elf::input()->data();
		if (empty($data[$name])
			|| !isset($data['x'])
			|| !isset($data['y'])
			|| !isset($data['w'])
			|| !isset($data['h']))
			$ret = json_encode(array('error'=>'Input params wrong or invalid: '.$data[$name].' '.(int)$data['x'].' '.(int)$data['y'].' '.(int)$data['w'].' '.(int)$data['h']));
		else {
			$data[$name] = $this->fpath.pathinfo($data[$name],PATHINFO_BASENAME);
			if (!is_file($data[$name]))
				$ret = json_encode(array('error'=>'Input file name not found'));
		}
		if ($ret === true) {
			$img = new Image($data[$name]);
			$data['w'] = (int)$data['w'];
			$data['h'] = (int)$data['h'];
			if (!$data['w'] || $data['w'] > $img->get_w())
				$data['w'] = $img->get_w();
			if (!$data['h'] || $data['h'] > $img->get_h())
				$data['h'] = $img->get_h();
			$data['x'] = (int)$data['x'];
			$data['y'] = (int)$data['y'];
			if ($data['x'] < 0)
				$data['x'] = 0;
			elseif (($data['x']+$data['w'])>$img->get_w())
				$data['x'] = $img->get_w()-$data['w'];
			if ($data['y'] < 0)
				$data['y'] = 0;
			elseif (($data['y']+$data['h'])>$img->get_h())
				$data['y'] = $img->get_h()-$data['h'];
			$img->_crop($data['x'],$data['y'],$data['w'],$data['h']);
			$img->_scale($this->image_xsize, $this->image_ysize);
			$img->set_dst($this->ficons.pathinfo($data[$name],PATHINFO_BASENAME))->_scale($this->icon_xsize, $this->icon_ysize);
			$this->save_to_db($data[$name], null);
			$ret = json_encode(array('icon'=>DIR_ALIAS.'/'.$this->icons.pathinfo($data[$name],PATHINFO_BASENAME),
									'src'=>DIR_ALIAS.'/'.$this->path.pathinfo($data[$name],PATHINFO_BASENAME),
									'name'=>DIR_ALIAS.'/'.$this->path.pathinfo($data[$name],PATHINFO_BASENAME)));
		}
		return $ret;
	}
	function remfile($remfile, $icon = true) {
		@unlink($this->fpath.basename($remfile));
		if ($icon)
			@unlink($this->ficons.basename($remfile));
	}
	function save_to_db($fname, $params) {
		return null;
	}
	protected function _repl_cont_images($text, $alias) {
		// fomalize IMAGES
		if (preg_match_all("/<img([^>]+)src=\"([^>\"]+)\"([^>]+)\/>/",$text,$matches)) {
			if (!empty($matches[2]) && sizeof($matches[2])) {
				if (!is_dir($this->fpath.$alias))
					@mkdir($this->fpath.$alias, 0777);
				foreach ($matches[2] as $v) {
					$nv = pathinfo($v);
					$unremfiles[] = $nv['basename'];
					$oalias = explode("/",$nv['dirname']);
					if ($alias != $oalias[sizeof($oalias)-1]) {
						if (file_exists(ROOTPATH.substr($v,1)))
							rename(ROOTPATH.substr($v,1),$this->fpath.$alias.'/'.$nv['basename']);
						$text = str_replace($v,'/'.$this->path.$alias.'/'.$nv['basename'],$text);
					}
				}
			}
		}
		if (is_dir($this->fpath.$alias) && ($files = scandir($this->fpath.$alias))) {
			if (!empty($unremfiles)) {
				foreach ($files as $file) {
					if (($file != '.')
						&& ($file != '..')
						&& file_exists($this->fpath.$alias.'/'.$file)
						&& !in_array($file,$unremfiles))
							@unlink($this->fpath.$alias.'/'.$file);
				}
			}
			else {
				foreach ($files as $file) {
					if (($file != '.')
						&& ($file != '..')
						&& file_exists($this->fpath.$alias.'/'.$file))
							@unlink($this->fpath.$alias.'/'.$file);
				}
			}
		}
		return $text;
	}
}
