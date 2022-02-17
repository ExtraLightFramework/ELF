<?php if (!defined('_VALID_CODE')) {header('HTTP/1.1 404 Not Found');echo '<h2>Page Not Found</h2>';exit;}

require_once ("_ini.php");

// ==== !!!!!!!
Elf::init();
// ==== !!!!!!!

class Elf {
	static private $views_path;
	static private $models_path;
	static private $controllers_path;
	static private $libs_path;
	static private $lang_path;

	static private $app_views_path;
	static private $app_models_path;
	static private $app_controllers_path;
	static private $app_lang_path;
	
	static private $layout;

	static private $input;
	static private $routing;
	static private $session;
	static private $settings;
	static private $history;
	static private $lang;
	static private $loaded_classes;
	static public $_data;

	static public function init() {
		self::$loaded_classes = self::$_data = [];

		self::$controllers_path = ROOTPATH.CONTROLLERS_DIR.'/';
		self::$views_path = ROOTPATH.VIEWS_DIR.'/';
		self::$models_path = ROOTPATH.MODELS_DIR.'/';
		self::$libs_path = ROOTPATH.LIBS_DIR.'/';
		self::$app_controllers_path = ROOTPATH.APP_DIR.'/'.CONTROLLERS_DIR.'/';
		self::$app_views_path = ROOTPATH.APP_DIR.'/'.VIEWS_DIR.'/';
		self::$app_models_path = ROOTPATH.APP_DIR.'/'.MODELS_DIR.'/';
		self::$lang_path = ROOTPATH.LANGS_DIR.'/'.SYSTEM_LANGUAGE.'/';
		self::$app_lang_path = ROOTPATH.APP_DIR.'/'.LANGS_DIR.'/'.SYSTEM_LANGUAGE.'/';
//		echo self::$app_views_path;exit;

		set_exception_handler('Elf::exception_handler');
		spl_autoload_register('Elf::_autoload');
		
		Elf::$_data = [];
		
		self::$settings = new Elf\Libs\Settings;
		self::$input = new Elf\Libs\Input;
		self::$routing = new Elf\Libs\Routing;
		self::$session = new Elf\Libs\Session;
		self::$history = new Elf\Libs\History;
		
		if (DEBUG_MODE) {
			ini_set('display_errors', true);
			error_reporting(E_ALL);
		}
		if (AUTO_LOGIN_ENABLED) {
			self::$session->check_auto_login();
		}
		if (COOKIE_AGREEMENT_ENABLED)
			Elf::$_data['cookie.agreement'] = self::session()->cookie_agreement();

		date_default_timezone_set(TIME_ZONE);
		self::$layout = DEFAULT_LAYOUT;
		
		// Init flash data
		if (Elf::session()->get('flashdata'))
			Elf::$_data = array_merge(Elf::$_data, Elf::session()->get('flashdata'));
	}
	static private function is_loaded_class($class) {
		return isset(self::$loaded_classes[$class]);
	}
	static public function exception_handler($exception) {
		if (self::is_xml_request()) {
			echo json_encode(['exception'=>((int)self::settings()->_get('DEBUG_MODE')?"System exception with message \"":"").$exception->getMessage().((int)self::settings()->_get('DEBUG_MODE')?"\" in file \"".$exception->getFile()."\" in line ".$exception->getLine():'')]);
			exit;
		}
		else {
			if (!(int)self::settings()->_get('DEBUG_MODE')) {
				header('HTTP/1.1 404 Not Found');
				header('Content-Type: text/html; charset=utf-8');
				echo '<h1>Страница не найдена</h1>';
				echo '<p>Запрошенная страница не найдена</p>';
				exit;
			}
			else {
				echo "System exception:<br />";
				echo "<strong>Message:</strong> ".$exception->getMessage()."<br />";
				echo "<strong>File:</strong> ".$exception->getFile()."<br />";
				echo "<strong>Line:</strong> ".$exception->getLine()."<br />";
				echo "<strong>Trace:</strong> ".$exception->getTraceAsString()."<br />";
			}
		}
	}
	static public function _autoload($class = '') {
		$path = '';
		if (empty($class)) {
			throw new Exception('Class name is empty');
		}
		$path = str_replace("Elf\\","",$class);
		$path = strtolower(str_replace("\\","/",$path));
		$path = strpos($path,"/")===0?substr($path,1):$path;
		$classld = $class;
		$class = explode("\\",$class);
		$class = $class[sizeof($class)-1];
		if (!self::is_loaded_class($class)) {
			if (file_exists(ROOTPATH.$path.EXT)) { // any class
				require_once(ROOTPATH.$path.EXT);
				self::$loaded_classes[$class] = $classld;
			}
			else
				throw new \Exception('Class <b>'.$classld.'</b> not found');
		}
	}
// ========= PUBLIC GETTERS =========	
	static public function input() {
		return self::$input;
	}
	static public function routing() {
		return self::$routing;
	}
	static public function session() {
		return self::$session;
	}
	static public function settings() {
		return self::$settings;
	}
	static public function history() {
		return self::$history;
	}
// =========== LANGS =================
	static public function lang($lang = 'main') {
		if (empty(self::$lang))
			self::$lang = new Elf\Libs\Lang;
		self::$lang->_load($lang,self::$lang_path.$lang.'.lng',self::$app_lang_path.$lang.'.lng');
		return self::$lang;
	}
	
// =========== VIEWS/TEMPLATES BLOCK =================
	static public function set_layout($layout) {
		self::$layout = $layout;
		return new static();
	}
	static public function load_view($view = '', $data = null, $show = true) {
		if ($data) {
			$pdata = self::$_data;
			self::$_data = array_merge(self::$_data,$data);
		}
		if (empty($view)) {
			throw new \Exception ('View name is empty!');
		}
		ob_start(null,0,PHP_OUTPUT_HANDLER_REMOVABLE|PHP_OUTPUT_HANDLER_CLEANABLE);
		if (file_exists(self::$app_views_path.strtolower($view).EXT))
			include (self::$app_views_path.strtolower($view).EXT);
		elseif (file_exists(self::$views_path.strtolower($view).EXT))
			include (self::$views_path.strtolower($view).EXT);
		else {
			@ob_end_clean();
			throw new \Exception ('View file <strong>'.$view.'</strong> not found.');
		}
				
		self::$_data['content'] = ob_get_contents();
		self::$_data['content'] = self::_parse(self::$_data['content']);
		@ob_end_clean();
		ob_start();
		if (file_exists(self::$app_views_path.strtolower(self::$layout).EXT))
			include (self::$app_views_path.strtolower(self::$layout).EXT);
		elseif (file_exists(self::$views_path.strtolower(self::$layout).EXT))
			include (self::$views_path.strtolower(self::$layout).EXT);
		else {
			@ob_end_clean();
			throw new \Exception ('Layout file not found.');
		}
		$buffer = ob_get_contents();
		$buffer = self::_parse($buffer);
		@ob_end_clean();
		if (!empty($pdata)) {
			self::$_data = $pdata;
			unset($pdata);
		}
		if ($show) {
			echo $buffer;
			exit;
		}
		else
			return $buffer;
	}
	static public function load_template($view = '', $data = null) {
		if ($data) {
			if (!is_array($data))
				throw new Exception("Data is not array");
			$pdata = self::$_data;
			self::$_data = array_merge(self::$_data,$data);
		}
		if (empty($view)) {
			throw new \Exception ('Template name is empty!');
		}
		ob_start(null,0,PHP_OUTPUT_HANDLER_REMOVABLE|PHP_OUTPUT_HANDLER_CLEANABLE);
		if (file_exists(self::$app_views_path.strtolower($view).EXT))
			include (self::$app_views_path.strtolower($view).EXT);
		elseif (file_exists(self::$views_path.strtolower($view).EXT))
			include (self::$views_path.strtolower($view).EXT);
		else {
			@ob_end_clean();
			throw new \Exception ('Template file <strong>'.$view.'</strong> not found.');
		}
				
		$buffer = ob_get_contents();
		$buffer = self::_parse($buffer);
		@ob_end_clean();
		if (!empty($pdata)) {
			self::$_data = $pdata;
			unset($pdata);
		}
		return $buffer;
	}
	static private function _parse($buffer) {
		$buffer = preg_replace_callback("/<% ([a-zA-Z0-9_.]+) %>/",'Elf::_prc_data', $buffer);
		$buffer = preg_replace_callback("/<% lang:(([a-z0-9_.]+):)?([a-zA-Z0-9_.]+) %>/",'Elf::_prc_lang', $buffer);
		$buffer = preg_replace_callback("/<% session:([a-zA-Z0-9_.]+) %>/",'Elf::_prc_session', $buffer);
		return preg_replace_callback("'{([a-zA-Z_]+[a-zA-Z0-9_]*)}'",'Elf::_prc_exec', $buffer);
	}
	static private function _prc_data($matches) {
		return self::get_data($matches[1]);
	}
	static private function _prc_lang($matches) {
		return self::lang($matches[2]?$matches[2]:'main')->item($matches[3]);
	}
	static private function _prc_session($matches) {
		return self::session()->get($matches[1]);
	}
	static private function _prc_exec($matches) {
		return call_user_func('Elf::'.$matches[1]);
	}
	static public function isset_data($k) {
		return isset(self::$_data[$k]);
	}
	static public function get_data($k) {
		return self::isset_data($k)?self::$_data[$k]:null;
	}
	static public function set_data($k, $v = null, $rewrite = true) {
		$ret = self::get_data($k);
		self::$_data[$k] = $rewrite?$v:(self::isset_data($k)?self::$_data[$k].$v:$v);
		return $ret;
	}
	static public function show_dialog($view, $params = null) {
		$data['dialog'] = $view;
		$data['wid'] = time();
		if (!empty($params) && is_array($params)) {
			foreach ($params as $k=>$v)
				$data[$k] = $v;
		}
		self::$_data['preloadialog'] = $data['preloadialog'] = self::load_template(!empty($data['appearance'])?$data['appearance']:'main/dialog',$data);
		self::session()->set('flashdata',array_merge(self::$_data,$data));
		if (!empty(self::$_data['title']) || !empty($params['title']))
			self::session()->set('title',!empty(self::$_data['title'])?self::$_data['title']:$params['title']);
		if (!empty(self::$_data['description']) || !empty($params['description']))
			self::session()->set('description',!empty(self::$_data['description'])?self::$_data['description']:$params['description']);
		if (!empty(self::$_data['canonical']) || !empty($params['canonical']))
			self::session()->set('canonical',!empty(self::$_data['canonical'])?self::$_data['canonical']:$params['canonical']);
	}
	static public function messagebox($message) {
		$data['message'] = $message;
		//$data['dialog'] = 'main/messagebox';
		$data['wid'] = time();
		self::$_data['preloadialog'] = $data['preloadialog'] = self::load_template('main/messagebox',$data);
		self::session()->set('flashdata',array_merge(self::$_data,$data));
	}
	static public function no_cache() {
		// ==== NO CACHE ======
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Expires: " . date("r"));
		// ====================
	}

// ========= NAVIGATION METHODS ==================
	static public function redirect($url = '', $hide = null) { // $hide [null|true|'app']
		if (strpos($url,'/') === 0)
			$url = substr($url,1);
		if ($hide === null) {
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: '.($url?DIR_ALIAS.'/'.$url:self::site_url()));
		}
		else {
			$ex = explode("/",$url);
			if (!empty($ex[0]) && !empty($ex[1])) {
				$c = "\\Elf\\".($hide==='app'?'':'App\\')."Controllers\\".$ex[0];
				$c = new $c;
				call_user_func_array([$c, $ex[1]],[]);
			}
		}
		exit;
	}
	static public function site_url($end_slash = true) {
		return SITE_PROTOCOL."://".SITE.($end_slash?"/":"");
	}
	static public function ip_addr() {
		return !empty($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];
	}
	static private function get_x_request() {
		return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])?$_SERVER['HTTP_X_REQUESTED_WITH']:SITE_PROTOCOL;
	}
	static public function is_xml_request() {
		return (self::get_x_request() == XML_STRING);
	}
// ============= HELPERS ====================
	static public function date2timestamp($str) {
		$ret = 0;
		$str = explode(".", $str);
		if (sizeof($str) == 3) {
			switch ($str[1]) {
				case '01':
				case '1':
					$str[1] = 'January';
					break;
				case '02':
				case '2':
					$str[1] = 'February';
					break;
				case '03':
				case '3':
					$str[1] = 'March';
					break;
				case '04':
				case '4':
					$str[1] = 'April';
					break;
				case '05':
				case '5':
					$str[1] = 'May';
					break;
				case '06':
				case '6':
					$str[1] = 'June';
					break;
				case '07':
				case '7':
					$str[1] = 'July';
					break;
				case '08':
				case '8':
					$str[1] = 'August';
					break;
				case '09':
				case '9':
					$str[1] = 'September';
					break;
				case '10':
					$str[1] = 'October';
					break;
				case '11':
					$str[1] = 'November';
					break;
				case '12':
					$str[1] = 'December';
					break;
				default:
					$str[1] = 'January';
					break;
			}
			$ret = strtotime($str[0]." ".$str[1]." ".$str[2]);
		}
		return $ret>=0?$ret:0;
	}
	static public function bigdate2timestamp ($str) {
		$ret = 0;
		$str = explode(".", $str);
		if (sizeof($str) == 3) {
			$str[1] = (int)$str[1];
			$str[2] = (int)$str[2];
			$d = (int)$str[0];
			if (($d < 1)
				|| (($d > 31) && in_array($str[1],[1,3,5,7,8,10,12]))
				|| (($d > 30) && in_array($str[1],[4,6,9,11]))
				|| (($d > 28) && in_array($str[1],[2]) && $str[2]%4)
				|| (($d > 29) && in_array($str[1],[2]) && !$str[2]%4))
				throw new \Exception('Wrong date format <b>'.($str[0].'.'.$str[1].'.'.$str[2]).'</b>');
			while (--$str[1]) {
				switch ($str[1]) {
					case 1:
					case 3:
					case 5:
					case 7:
					case 8:
					case 10:
					case 12:
						$d += 31;
						break;
					case 4:
					case 6:
					case 9:
					case 11:
						$d += 30;
						break;
					case 2:
						if ($str[2]%4) {
							$d += 28;
						}
						else {
							$d += 29;
						}
						break;
				}
			}
			$d += $str[2]*365 + (int)($str[2]/4);
			$d += $str[2]%4?1:0;
			$ret = $d * SECONDS_IN_DAY - SECONDS_IN_DAY;
		}
		return $ret;
	}
	static public function bigtimestamp2date (int $tm) {
		$d = (int)(($tm+SECONDS_IN_DAY) / SECONDS_IN_DAY);
		$y = (int)(4*$d / 1461);
		$d -= ($y*365) + (int)($y/4);
		$d -= $y%4?1:0;
		if ($d <= 0) {
			$d = 31;
			$m = 12;
			$y -= 1;
		}
		else {
			$m = 1;
			$i = $d;
			while ($i > 0) {
				switch ($m) {
					case 1:
					case 3:
					case 5:
					case 7:
					case 8:
					case 10:
					case 12:
						$i -= 31;
						break;
					case 4:
					case 6:
					case 9:
					case 11:
						$i -= 30;
						break;
					case 2:
						if ($y%4)
							$i -= 28;
						else
							$i -= 29;
						break;
				}
				if ($i > 0) {
					$d = $i;
					$m ++;
				}
			}
		}
		return @str_repeat('0',2-strlen($d)).$d.'.'.@str_repeat('0',2-strlen($m)).$m.'.'.@str_repeat('0',4-strlen($y)).$y;
	}
	static public function daysinmonth2bigdate (int $tm) {
		$d = (int)(($tm+SECONDS_IN_DAY) / SECONDS_IN_DAY);
		$y = (int)(4*$d / 1461);
		$d -= ($y*365) + (int)($y/4);
		$d -= $y%4?1:0;
		if ($d <= 0) {
			$d = 31;
			$m = 12;
			$y -= 1;
		}
		else {
			$m = 1;
			$i = $d;
			while ($i > 0) {
				switch ($m) {
					case 1:
					case 3:
					case 5:
					case 7:
					case 8:
					case 10:
					case 12:
						$i -= 31;
						$d = 31;
						break;
					case 4:
					case 6:
					case 9:
					case 11:
						$i -= 30;
						$d = 30;
						break;
					case 2:
						if ($y%4) {
							$i -= 28;
							$d = 28;
						}
						else {
							$i -= 29;
							$d = 29;
						}
						break;
				}
				$m ++;
			}
		}
		return $d;
	}
	static public function translit($st) {
	// Сначала заменяем "односимвольные" фонемы.
		$st = iconv('UTF-8','WINDOWS-1251', $st);
		if ($st) {
			$st=strtr($st,"абвгдеёзийклмнопрстуфхъыьэ ",
					"abvgdeeziyklmnoprstufh_yye-");
			$st=strtr($st,"АБВГДЕЁЗИЙКЛМНОПРСТУФХЪЫЬЭ ",
					"ABVGDEEZIYKLMNOPRSTUFH_YYE-");
	// Затем - "многосимвольные".
			$st=strtr($st, 
					array(
					"ж"=>"zh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh", 
					"щ"=>"shch","ь"=>"", "ю"=>"yu", "я"=>"ya",
					"Ж"=>"ZH", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH", 
					"Щ"=>"SHCH","Ь"=>"", "Ю"=>"YU", "Я"=>"YA",
					"ї"=>"i", "Ї"=>"Yi", "є"=>"ie", "Є"=>"Ye")
				);
			$st = iconv('windows-1251','utf-8',$st);
		}
		return $st;
	}
	static public function json_decode_to_array($json) {
		if (!empty($json))
			return (array)json_decode(htmlspecialchars_decode($json));
		else
			return null;
	}
	static public function show_words($s, $col) {
		$i = 0;
		$pos = mb_strpos($s, ' ');
		while ((++$i < $col) && ($pos !== false)) {
			$pos = mb_strpos($s, ' ', $pos+1);
		}
		if ($pos === false)
			$pos = strlen($s);
		return mb_substr($s, 0, $pos);
	}
	static public function gen_alias($text, $add = '') {
		return self::gen_chpu($text).($add?'-'.$add:'');
	}
	static public function gen_chpu($text) {
		return strtolower(preg_replace('/[^a-zA-Z0-9\-]+/i','',str_replace(" ","-",self::translit(self::show_words($text,12)))));
	}
	static public function captcha($name = 'captcha', $len = 4, $force = false) {
		if ((self::session()->get($name) && $force)
			|| !self::session()->get($name)) {
			$rndint = self::gen_password($len);
			self::session()->set($name,$rndint);
		}
		else
			$rndint = self::session()->get($name);		
		$height=30; 
		$width=100;  
		$img = imagecreate($width, $height);
		
		$black = imagecolorallocate($img, 0, 0, 0);
		$white = imagecolorallocate($img, 255, 255, 255);
		$gray = imagecolorallocate($img, 249, 249, 249);
		$orange = imagecolorallocate($img, 255, 128, 64);
		$lightorange = imagecolorallocate($img, 255, 220, 164);
		$green = imagecolorallocate($img, 63, 166, 150);
		$darkgreen = imagecolorallocate($img, 14, 63, 56);
		$red = imagecolorallocate($img, 255, 0, 0);
		
		imagefilledrectangle($img, 0, 0, $width, $height, $gray);
	//	imagerectangle($img, 0, 0, $width-1, $height-1, $darkgreen);
		
		imagettftext($img, 24, 0, 10, 25, $red, ROOTPATH.'img/fonts/opensans.ttf', $rndint);
		
		for ($i=1; $i<=36; $i++) {
				$int1=rand(5,$width-4);
				$int2=rand(0,$height);
				imagesetpixel($img, $int1, $int2, $black);
				$int3=rand(0,15);
				$int4=rand(0,15);
				imageline($img, $int1, $int2, $int1+$int3, $int2+$int4, $red);
		}
		
		$str = '';
		if (!is_dir(ROOTPATH."img/captcha")) {
			mkdir(ROOTPATH."img/captcha");
		}
		$t = time();
		if (imagepng($img,ROOTPATH."img/captcha/captcha".$t.".png")) {
			if ($f = fopen(ROOTPATH.'img/captcha/captcha'.$t.'.png','rb')) {
				$str = fread($f,filesize(ROOTPATH.'img/captcha/captcha'.$t.'.png'));
				$str = base64_encode($str);
				fclose($f);
				@unlink(ROOTPATH.'img/captcha/captcha'.$t.'.png');
			}
		}
		return $str;
	}
	static public function gen_password($number) {
/*		'a','b','c','d','e','f',
        'g','h','i','j','k','l',
        'm','n','o','p','r','s',
        't','u','v','x','y','z',
		'A','B','C','D','E','F',
         'G','H','I','J','K','L',
         'M','N','O','P','R','S',
         'T','U','V','X','Y','Z',
*/
    	$arr = array(
                'a','b','c','d','e','f',
        		'g','h','i','j','k','l',
        		'm','n','o','p','q','r','s',
		        't','u','v','w','x','y','z');//, 
//                 '1','2','3','4','5','6',
//                '7','8','9','0');
	    $pass = "";

	    for($i = 0; $i < $number; $i++)
	    {
			$index = rand(0, count($arr) - 1);
			$pass .= $arr[$index];
		}
    	return $pass;
	}
	static public function send_mail($to, $subject, $text, $sign='', $from = '') {
		$text = stripslashes(wordwrap($text, 70));
		$to = trim($to);
		$subject = trim($subject);
		$from = $from?$from:MAIL_SENDER;
		$sign = $sign?$sign:$from;

	// Заголовки письма === >>>
	$headers = "Return-Path: ".$from."\r\nReply-to: ".$from."\r\n";
	$headers .= "From: =?utf-8?B?".base64_encode($sign)."?= <".$from.">\r\n";
	$headers .= "Date: " . date("r") . "\r\n";
//	$headers .= "X-Mailer: Php-mailer(".phpversion().")\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "List-Unsubscribe: <".self::site_url()."main/unsubscribe/".base64_encode($to).">\r\n";
	$headers .= "Content-Type: multipart/alternative;\r\n";
	$baseboundary = "------------" . strtoupper(md5(uniqid(rand(), true)));
	$headers .= "  boundary=\"$baseboundary\"\r\n";
	// <<< ====================

	// Тело письма === >>>
	$message  =  "--$baseboundary\r\n";
	$message .= "Content-Type: text/plain;\r\n";
	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$message .= strip_tags($text)."\nUnsubscribe - ".self::site_url()."main/unsubscribe/".base64_encode($to)."\r\n";
	$message .= "--$baseboundary\r\n";
	$message .= "Content-Type: text/html; charset=utf-8\r\n";
	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$message .= $text . "<br /><br /><p style=\"font-size:10px;color:#999;\">Unsubscribe <a href=\"".self::site_url()."main/unsubscribe/".base64_encode($to)."\">link</a></p>\r\n\r\n";
	// <<< ==============
//
//		$header = "Return-Path: ".MAIL_SENDER."\r\nSender: ".MAIL_SENDER."\r\nReply-to: %%from%%\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=utf-8\r\nContent-Transfer-Encoding: 8bit\r\nFrom: =?utf-8?B?".base64_encode($sign)."?= <".MAIL_SENDER.">\r\nX-Mailer: PHP/".phpversion()."\r\n\r\n";
//		$header = str_replace("%%from%%",$from,$header);
		if (REAL_SEND_MAIL) {
			mail($to, $subject, $message, $headers, "-f ".$from);
/*			$sendmail = '/usr/sbin/exim -i -f '.$from.' '.$to;
			$fd = popen($sendmail, "w"); 
//			fputs($fd, "To: ".$to."\r\n"); 
			fputs($fd, "Subject: =?utf-8?B?".base64_encode($subject)."?=\r\n"); 
			fputs($fd, $headers);
			fputs($fd, $message); 
			pclose($fd);
			unset($sendmail);
*/		}
		else {
			if ($log = fopen(ROOTPATH.'logs/lastmail.html','wb')) {
				fputs($log, "To: ".$to."\r\n"); 
				fputs($log, "Subject: =?utf-8?B?".base64_encode($subject)."?=\r\n"); 
				fputs($log, $headers);
				fputs($log, $message); 
				fclose($log);
			}
		}
		return true;
	}
	static public function curl_request($url, $dt = null, $method = "POST", $cookie = '', $headers = '') {
		if (!function_exists('curl_init'))
			throw new \Exception('CURL module not installed');
		$ch = curl_init();
		if ($headers) {
			if (!is_array($headers))
				$headers = explode("\r\n",$headers);
			if ($dt)
				$headers[] = 'Content-Length: '.strlen($dt);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, 0);
		}
		if ($cookie) {
			if (!is_array($cookie))
				$cookie = explode("\r\n",$cookie);
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if ($dt) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $dt);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		if (($ret = curl_exec($ch)) === false) {
			$error['error'] = curl_error($ch);
			$error = json_encode($error);
		}
		curl_close($ch);
		return isset($error)?(array)json_decode($error):(array)json_decode($ret);
	}
	static public function get_app_views_path() {
		return self::$app_views_path;
	}
}
