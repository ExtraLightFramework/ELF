<?php

namespace Elf\Libs;

use Elf;

define ('IMAGE_ORIENT_H',	'horizontal');
define ('IMAGE_ORIENT_V',	'vertical');
define ('IMAGE_ORIENT_S',	'square');

class Image {
	
	private $src;
	private $dst;
	private $type;
	private $_w;
	private $_h;
	private $_orient; //horizontal|vertical|square

	function __construct($src, $dst = '') {
		if ($sz = getimagesize($src)) {
			$this->src = $src;
			if (empty($dst))
				$this->dst = $src;
			else
				$this->dst = $dst;
			$this->type = $sz[2];
			$this->_w = $sz[0];
			$this->_h = $sz[1];
			if ($this->_w > $this->_h)
				$this->_orient = IMAGE_ORIENT_H;
			elseif ($this->_w < $this->_h)
				$this->_orient = IMAGE_ORIENT_V;
			else
				$this->_orient = IMAGE_ORIENT_S;
			unset($sz);
			return $this;
		}
		else
			throw new \Exception('Imglib common error for '.$src);
	}
	// задать конечное изображение (приемник)
	function set_dst($dst) {
		$this->dst = $dst;
		return $this;
	}
	// получить ориентацию изображения
	function get_orient() {
		return $this->_orient;
	}
	function get_w() {
		return $this->_w;
	}
	function get_h() {
		return $this->_h;
	}
	// масштабирование
	function _scale($w, $h, $ratio = true) {
		$this->_w = $w;
		$this->_h = $h;

		switch ($this->type) {
			case IMAGETYPE_JPEG:
				$im = @imagecreatefromjpeg($this->src);
				break;
			case IMAGETYPE_PNG:
				$im = @imagecreatefrompng($this->src);
				$tcolor = array('red' => 255, 'green' => 255, 'blue' => 255);
				if (($tidx = imagecolortransparent($im)) >= 0)
					$tcolor = imagecolorsforindex($im, $tidx);
				break;
			case IMAGETYPE_GIF:
				$im = @imagecreatefromgif($this->src);
				break;
		}
		if (!empty($im)) {
			if ($im1 = @imagescale($im, $this->_w, !$ratio?$this->_h:-1)) {
				switch ($this->type)	{
					case IMAGETYPE_JPEG:
						$ret = @imagejpeg($im1, $this->dst);
						break;
					case IMAGETYPE_PNG:
						$ret = @imagepng($im1, $this->dst, 9);
						break;
					case IMAGETYPE_GIF:
						$ret = @imagegif($im1, $this->dst);
						break;
				}
			}
			imagedestroy($im);
			imagedestroy($im1);
		}
		return !empty($ret)?$ret:false;
	}
	// изменение размера изображения на заданный процент
	function _ratio_resize($percent) {
		$this->_w = intval($this->_w/100*$percent);
		$this->_h = intval($this->_h/100*$percent);
		return $this->_resize($this->_w, $this->_h, false);
	}
	// изменение размеров изображения, $ratio - признак сохранения пропорций
	function _resize($w = 0, $h = 0, $ratio = true) {
		$ret = false;
		if (empty($w) || empty($h)) {
			$w = $this->_w;
			$h = $this->_h;
		}
		if ((($this->_orient == IMAGE_ORIENT_H) && ($w < $h))
			|| (($this->_orient == IMAGE_ORIENT_V) && ($w > $h))) {
			$_i = $w;
			$w = $h;
			$h = $_i;
		}
		elseif (($this->_orient == IMAGE_ORIENT_S) && ($w > $h)) {
			$h = $w;
		}
		elseif (($this->_orient == IMAGE_ORIENT_S) && ($w < $h)) {
			$w = $h;
		}
		if ($ratio) {
			$des = $this->_w/$this->_h;
			if ($w/$h != $des) {
				if ($this->_orient == IMAGE_ORIENT_H) {
					$h = intval($w/$des);
				}
				elseif ($this->_orient == IMAGE_ORIENT_V) {
					$w = intval($h*$des);
				}
			}
		}
		$this->_w = $w;
		$this->_h = $h;

		switch ($this->type) {
			case IMAGETYPE_JPEG:
				$im = @imagecreatefromjpeg($this->src);
				break;
			case IMAGETYPE_PNG:
				$im = @imagecreatefrompng($this->src);
				$tcolor = array('red' => 255, 'green' => 255, 'blue' => 255);
				if (($tidx = imagecolortransparent($im)) >= 0)
					$tcolor = imagecolorsforindex($im, $tidx);
				break;
			case IMAGETYPE_GIF:
				$im = @imagecreatefromgif($this->src);
				break;
		}
		if (!empty($im)) {
			if ($im1 = imagecreatetruecolor($w,$h)) {
				if ($this->type == IMAGETYPE_PNG) {
					$c = imagecolorallocate($im1,$tcolor['red'],$tcolor['green'],$tcolor['blue']);
					@imagefill($im1,0,0,$c);
					@imagecolortransparent($im1, $c);
				}
			    if (@imagecopyresampled($im1, $im, 0, 0, 0, 0, $w, $h, imagesx($im), imagesy($im))) {
					switch ($this->type)	{
						case IMAGETYPE_JPEG:
							$ret = @imagejpeg($im1, $this->dst);
							break;
						case IMAGETYPE_PNG:
							$ret = @imagepng($im1, $this->dst, 9);
							break;
						case IMAGETYPE_GIF:
							$ret = @imagegif($im1, $this->dst);
							break;
					}
		    	}
			    imagedestroy($im);
			    imagedestroy($im1);
		    }
		}
		return $ret?$this->dst:false;
	}
	// установка водяного знака на изображение
	// $dst				- изображение приемник
	// $water 			- сам водяной знак
	// $pos_x, $pos_y	- позиция водяного знака, по умолчанию в центре. 
	//						Возможные значения (C|c-center, для Х [L|l-left, R|r-right], для Y [T|t-top, B|b-bottom])
	// $margin 			- смещение водяного знака от краев (берутся ближайшие края относительно положения знака)
	function _set_watermark(/*$water,*/ $pos_x = 'C', $pos_y = 'C', $margin = 0) {
		if ($wsz = @getimagesize($this->dst)) {
			$pos_x = strtolower($pos_x);
			$pos_y = strtolower($pos_y);
			$margin = intval($margin);
			switch ($pos_x) {
				case 'l':
					$pos_x = $margin;
					break;
				case 'r':
					$pos_x = $this->_w-$wsz[0]-$margin;
					break;
				case 'c':
				default:
					$pos_x = floor($this->_w/2)-ceil($wsz[0]/2);
					break;
			}
			switch ($pos_y) {
				case 't':
					$pos_y = $margin;
					break;
				case 'b':
					$pos_y = $this->_h-$wsz[1]-$margin;
					break;
				case 'c':
				default:
					$pos_y = floor($this->_h/2)-ceil($wsz[1]/2);
					break;
			}
			return $this->_concat($pos_x, $pos_y);
		}
	}
	// "склеивает" первое изображение ($this->src) со вторым ($this->dst), 
	// 3 и 4 параметры - местоположение второго изображения в первом, 
	// по умочланию в верхнем левом углу
	function _concat($dst_x = 0, $dst_y = 0) {
		$ret = false;
		if ($dsz = @getimagesize($this->dst)) {
			switch ($this->type)	{
				case IMAGETYPE_JPEG:
					$sim = @imagecreatefromjpeg($this->src);
					break;
				case IMAGETYPE_PNG:
					$sim = @imagecreatefrompng($this->src);
					break;
				case IMAGETYPE_GIF:
					$sim = @imagecreatefromgif($this->src);
					break;
			}
			switch ($dsz[2])	{
				case IMAGETYPE_JPEG:
					$dim = @imagecreatefromjpeg($this->dst);
					break;
				case IMAGETYPE_PNG:
					$dim = @imagecreatefrompng($this->dst);
					break;
				case IMAGETYPE_GIF:
					$dim = @imagecreatefromgif($this->dst);
					break;
			}
			if (!empty($dim) && !empty($sim)) {
				if (($this->_w > $dsz[0]) && ($this->_h > $dsz[1])) {
					$w = $this->_w;
					$h = $this->_h;
				}
				else {
					$w = $dsz[0];
					$h = $dsz[1];
				}
				if ($rim = @imagecreatetruecolor($w, $h)) {
					if (@imagecopy($rim, $sim,  0, 0, 0, 0, $this->_w, $this->_h)
						&& @imagecopy($rim, $dim, $dst_x, $dst_y, 0, 0, $dsz[0], $dsz[1])) {
						switch ($this->type)	{
							case IMAGETYPE_JPEG:
								$ret = @imagejpeg($rim, $this->src);
								break;
							case IMAGETYPE_PNG:
								$ret = @imagepng($rim, $this->src);
								break;
							case IMAGETYPE_GIF:
								$ret = @imagegif($rim, $this->src);
								break;
						}
					}
					@imagedestroy($rim);
					@imagedestroy($sim);
					@imagedestroy($dim);
				}
			}
		}
		return $ret?$this->src:false;
	}
	// обрезка изображения по заданным координатам x y и размерам w h
	// исходное изображение остается нетронутым, 
	// возвращается путь к "обрезанному" изображению
	function _crop($x, $y, $w, $h) {
		$ret = false;
			switch ($this->type)	{
				case IMAGETYPE_JPEG:
					$im = @imagecreatefromjpeg($this->src);
					break;
				case IMAGETYPE_PNG:
					$im = @imagecreatefrompng($this->src);
					$tcolor = array('red' => 255, 'green' => 255, 'blue' => 255);
					if (($tidx = imagecolortransparent($im)) >= 0)
						$tcolor = imagecolorsforindex($im, $tidx);
					break;
				case IMAGETYPE_GIF:
					$im = @imagecreatefromgif($this->src);
					break;
			}
			if (!empty($im)) {
				if ($dim = imagecreatetruecolor($w, $h)) {
					if ($this->type == IMAGETYPE_PNG) {
						$c = imagecolorallocate($dim,$tcolor['red'],$tcolor['green'],$tcolor['blue']);
						@imagefill($dim,0,0,$c);
						@imagecolortransparent($dim, $c);
					}
					if (imagecopyresampled($dim, $im, 0, 0, $x, $y, $w, $h, $w, $h)) {
						switch ($this->type)	{
							case IMAGETYPE_JPEG:
								$ret = @imagejpeg($dim, $this->dst);
								break;
							case IMAGETYPE_PNG:
								$ret = @imagepng($dim, $this->dst);
								break;
							case IMAGETYPE_GIF:
								$ret = @imagegif($dim, $this->dst);
								break;
						}
					}
					@imagedestroy($im);
					@imagedestroy($dim);
				}
			}
		return $ret?$this->dst:false;
	}
	// "превращает" исходное изображение в квадратное изображение. берется наименьшая сторона и изображение обрезается 
	// по центральным координатам. если $dst пустой, то изображение перезаписывается
	// если задано $wh (размер картинки) - ,берется это значение, иначе наименьшая сторона картики
	function _rectangle($wh = 0) {
		if (empty($wh)) {
			if ($this->_w >= $this->_h) {
				$wh = $this->_h;
				$x = intval(($this->_w-$wh)/2);
				$y = 0;
			}
			else {
				$wh = $this->_w;
				$y = intval(($this->_h-$wh)/2);
				$x = 0;
			}
		}
		else {
			$y = intval(($this->_h-$wh)/2);
			$x = intval(($this->_w-$wh)/2);
		}
		return $this->_crop($x, $y, $wh, $wh);
	}
	public function get_file_ext($fname) {
		$fname = pathinfo($fname);
		return '.'.$fname['extension'];
	}
	public function get_file_name($fname) {
		$fname = pathinfo($fname);
		return $fname['filename'];
	}
	
}
