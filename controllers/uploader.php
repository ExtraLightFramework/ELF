<?php

namespace Elf\Controllers;

use Elf;

define ('UPLOADER_IMAGE_MAX_XSIZE',	4650);
define ('UPLOADER_IMAGE_MAX_YSIZE',	3000);

class Uploader {
	
	protected $model;
	private $params;
	
	function __construct() {
		$this->params = [];
		if (Elf::input()->get('params')) {
			Elf::input()->set('params',Elf::json_decode_to_array(Elf::input()->get('params')));
			$this->params = Elf::input()->get('params');
		}
		if (!Elf::input()->get('model')||(Elf::input()->get('model')=='uploaders'))
			$this->model = new \Elf\Libs\Uploaders;
		else {
			$m = '\\Elf\\App\Models\\'.Elf::input()->get('model');
			$this->model = new $m;
		}
	}
	function get_accept_ext() {
		$tp = '';
		if ($types = $this->model->get_mime_types()) {
			foreach ($types as $v)
				$tp .= ($tp?',':'').$v;
		}
		echo json_encode(['ok'=>1]);//,'accept_ext'=>$tp));
	}
	function remove() {
		$this->rem_file();
	}
	function rem_file() {
		$this->model->remfile(Elf::input()->get('remfile'));
		echo json_encode(['ok'=>1]);
	}
	function crop() {
		echo $this->model->crop();
	}	
	function upload() {
		echo $this->async_upload();
	}
	function async_upload() {
		if (!($field = Elf::input()->get('field'))) {
			echo json_encode(array('error'=>'Input name is wrong or empty'.print_r($_POST, true)));
			exit;
		}
		
		$types = @$this->model->get_upload_types(Elf::input()->get('accept'));
		
		if (!isset($_FILES[$field])) {
			echo json_encode(array('error'=>"Unknown input field: ".$field));
			exit;
		}
		
		$size = $_FILES[$field]['size'];
		if ($size > MAX_UPLOAD_SIZE) {
			echo json_encode(array('error'=>'File wrong size '.$size.' (max. '.MAX_UPLOAD_SIZE.')'));
			exit;
		}

		$tmp_name = $_FILES[$field]['tmp_name'];
		$fname = !isset($this->params['translit'])||$this->params['translit']?str_replace(" ","-",Elf::translit($_FILES[$field]['name'])):$_FILES[$field]['name'];
		if (!is_uploaded_file($tmp_name)) {
			echo json_encode(array('error'=>'Can not load on server '.$_FILES[$field]['name']));
			exit;
		}

		// Set the uploaded data as class variables
		$file_ext = strtolower(pathinfo($fname,PATHINFO_EXTENSION));
		if (!empty($this->params['pasteron']))
			$fname = pathinfo($fname,PATHINFO_FILENAME).rand(1000,9999).'.'.$file_ext;
		if (!isset($this->params['timestamp'])
			|| $this->params['timestamp'])
			$fname = pathinfo($fname,PATHINFO_FILENAME).'_'.time().'.'.$file_ext;
		// Check file type (extension)
		if (!empty($types)) {
			if (!in_array($file_ext,$types)) {
				echo json_encode(array('error'=>'Wrong file type: '.$file_ext));
				exit;
			}
			unset($types);
		}
		
		if (!@copy($tmp_name, $this->model->fpath.$fname))
		{
			if (!@move_uploaded_file($tmp_name, $this->model->fpath.$fname))
			{
				echo json_encode(array('error'=>'Can not uploaded '.$this->model->fpath.$fname));
				exit;
			}
		}
		switch ($file_ext) {
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
				$params = Elf::input()->get('params');
				if (!isset($params['max_size']) && ($size > MAX_IMAGE_SIZE)) {
					echo json_encode(array('error'=>'Image wrong size '.$size.' (max. '.MAX_IMAGE_SIZE.')'));
					exit;
				}
				elseif (isset($params['max_size']) && ($size > (int)$params['max_size'])) {
					echo json_encode(array('error'=>'Image wrong size '.$size.' (max. '.$params['max_size'].')'));
					exit;
				}
				if ($sz = getimagesize($this->model->fpath.$fname)) {
					if ($sz[0]<$sz[1]) {
						$sz1 = $sz[1];
						$sz2 = $sz[0];
					}
					else {
						$sz1 = $sz[0];
						$sz2 = $sz[1];
					}
				}	
				if (!$sz
					|| ($sz1 > UPLOADER_IMAGE_MAX_XSIZE) || ($sz2 > UPLOADER_IMAGE_MAX_YSIZE)) {
					@unlink($this->model->fpath.$fname);
					echo json_encode(array('error'=>'File is not image or wrong max resolution ('.UPLOADER_IMAGE_MAX_XSIZE.'x'.UPLOADER_IMAGE_MAX_YSIZE.')'));
					exit;
				}
				
				break;
			default:
				break;
		}
		// === cleaner data
//		$tmp = new Db('uploader_tmp');
//		$tmp->init($this->app);
//		$tmp->_insert(array('path'=>$this->model->fpath,'fname'=>$fname,'tm'=>time()))->_execute();
//		$tmp->_insert(array('path'=>$this->model->ficons,'fname'=>$fname,'tm'=>time()))->_execute();
//		unset($tmp);
		// ==========
		$this->model->image_formalize($fname);
		$this->model->save_to_db($fname,Elf::input()->get('params'));
		
		if (get_class($this) == __CLASS__)
			echo json_encode(['ok'=>1,'name'=>$fname,
								'src'=>DIR_ALIAS.'/'.$this->model->path.$fname,
								'icon'=>isset($this->model->icons)?DIR_ALIAS.'/'.$this->model->icons.$fname:'',
								'img_w'=>isset($this->model->w)?$this->model->w:(isset($sz1)?$sz1:0),
								'img_h'=>isset($this->model->h)?$this->model->h:(isset($sz2)?$sz2:0),
								'icon_w'=>isset($this->model->icon_w)?$this->model->icon_w:(isset($sz1)?$sz1:0),
								'icon_h'=>isset($this->model->icon_h)?$this->model->icon_h:(isset($sz2)?$sz2:0),
								'params'=>Elf::input()->get('params')]);
		else
			return json_encode(['ok'=>1,'name'=>$fname,
							'src'=>DIR_ALIAS.'/'.$this->model->path.$fname,
							'icon'=>isset($this->model->icons)?DIR_ALIAS.'/'.$this->model->icons.$fname:'',
							'img_w'=>isset($this->model->w)?$this->model->w:(isset($sz1)?$sz1:0),
							'img_h'=>isset($this->model->h)?$this->model->h:(isset($sz2)?$sz2:0),
							'icon_w'=>isset($this->model->icon_w)?$this->model->icon_w:(isset($sz1)?$sz1:0),
							'icon_h'=>isset($this->model->icon_h)?$this->model->icon_h:(isset($sz2)?$sz2:0)]);
	}
	function __log() {
		if ($f = fopen(ROOTPATH.'logs/uploader.log','wb')) {
			fwrite($f, html_entity_decode(Elf::input()->get('log')));
			fclose($f);
		}
	}
}