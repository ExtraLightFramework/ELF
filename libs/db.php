<?php

namespace Elf\Libs;

use Elf;

define ('db_tinyint',	1);
define ('db_boolean',	1);
define ('db_smallint',	2);
define ('db_int',		3);
define ('db_float',		4);
define ('db_double',	5);
define ('db_real',		5);
define ('db_timestamp',	7);
define ('db_bigint',	8);
define ('db_serial',	8);
define ('db_mediumint',	9);
define ('db_date',		10);
define ('db_time',		11);
define ('db_datetime',	12);
define ('db_year',		13);
define ('db_bit',		16);
define ('db_decimal',	246);
define ('db_text',		252);
define ('db_tinytext',	252);
define ('db_mediumtext',252);
define ('db_longtext',	252);
define ('db_tinyblob',	252);
define ('db_mediumblob',252);
define ('db_blob',		252);
define ('db_longblob',	252);
define ('db_varchar',	253);
define ('db_varbinary',	253);
define ('db_char',		254);
define ('db_binary',	254);

define ('DB_NOT_NULL_FLAG',		1);                                                                              
define ('DB_PRI_KEY_FLAG',		2);
define ('DB_UNIQUE_KEY_FLAG',	4);
define ('DB_BLOB_FLAG',			16);
define ('DB_UNSIGNED_FLAG',		32);
define ('DB_ZEROFILL_FLAG',		64);
define ('DB_BINARY_FLAG',		128);
define ('DB_ENUM_FLAG',			256);
define ('DB_AUTO_INCREMENT_FLAG',512);
define ('DB_TIMESTAMP_FLAG',	1024);
define ('DB_SET_FLAG',			2048);
define ('DB_NUM_FLAG',			32768);
define ('DB_PART_KEY_FLAG',		16384);
define ('DB_GROUP_FLAG',		32768);
define ('DB_UNIQUE_FLAG',		65536);

class Db {
	
	static $mysqli;
	static $prefix;
	private $_name;
	private $fields;

	private $_sql;
	private $_oper;
	private $_lastoper;
	private $_mainoper;
	private $_what;
	private $_values;
	private $_fields;
	private $_where;
	private $_join;
	private $_limit;
	private $_orderby;
	private $_groupby;
	private $_squeries;
	private $_sqtype;
	private $_talias;
	private $_cond;
	private $_this;
	private $_and_or;
	private $_as;
	private $_asis;
	private $_repaterns = array('/%%what%%/','/%%join%%/','/%%where%%/','/%%orderby%%/','/%%groupby%%/','/%%limit%%/','/%%fields%%/','/%%values%%/','/%%whatsqueries%%/','/%%joinsqueries%%/','/%%wheresqueries%%/');
	
	function __construct($name = '', $sqtype = null) {
		if (empty(self::$mysqli)) {
			self::$mysqli = new \Mysqli(DB_HOST, DB_USER, DB_PASS, DB_BASE);
			if (self::$mysqli->connect_errno) {
				throw new \Exception ("Can't connect to database. <strong>Error:</strong> ". self::$mysqli->connect_error);
			}
			else {
				self::$mysqli->query("SET NAMES utf8");
			}
		}
		if ($name)
			$this->_name = DB_PREFIX . str_replace(DB_PREFIX,'',$name);
		if ($sqtype)
			$this->_sqtype = $sqtype;

		$this->init();
		return $this;
	}

	function init() {
		self::$prefix = DB_PREFIX;
		if ($this->_name() && ($res = self::$mysqli->query("SELECT * FROM ".$this->_name()." LIMIT 0"))
			&& !self::$mysqli->errno) {
			foreach ($res->fetch_fields() as $v) {
				$this->fields[$v->name]['flags'] = $v->flags;
				$this->fields[$v->name]['type'] = $v->type;
				$this->fields[$v->name]['length'] = $v->length;
			}
			$res->free();
			unset($res);
		}
		elseif ($this->_name())
			throw new \Exception ("Table <strong>".$this->_name()."</strong> init error: ".self::$mysqli->error);
	}
	function _name($clear = false) {
		return $clear?str_replace(DB_PREFIX,'',$this->_name):$this->_name;
	}
	function _talias() {
		return $this->_talias;
	}
	function _select($what = '*', $talias = 't1') {
		$this->_oper = "SELECT %%what%% %%whatsqueries%% FROM ".$this->_name()." ".($this->_talias()?$this->_talias():$talias)." %%join%% %%joinsqueries%% %%where%% %%wheresqueries%% %%groupby%% %%orderby%% %%limit%%";
		$this->_what = $what;
		$this->_lastoper = $this->_mainoper = 'select';
		return $this;
	}
	function _update($values = null, $safe = true) {
		if (!empty($values) && is_array($values) && sizeof($values)) {
			$this->_values = $this->_update_values($values,$safe);
			$this->_oper = "UPDATE ".$this->_name()." SET %%values%% %%where%% %%orderby%% %%limit%%";
		}
		else
			throw new \Exception('UPDATE query input params wrong [is not ARRAY].');
		$this->_lastoper = $this->_mainoper = 'update';
		return $this;
	}
	function _insert($values = null, $safe = true) {
		if (!empty($values) && is_array($values) && sizeof($values)) {
			$this->_values = $this->_insert_values($values,$safe);
			$this->_fields = $this->_insert_fields($values);
			$this->_oper = "INSERT INTO ".$this->_name()." (%%fields%%) VALUES(%%values%%)";
		}
		else
			throw new \Exception('INSERT query input params wrong [is not ARRAY].');
		$this->_lastoper = $this->_mainoper = 'insert';
		return $this;
	}
	function _delete() {
		$this->_oper = "DELETE FROM ".$this->_name()." %%where%% %%orderby%% %%limit%%";
		$this->_lastoper = $this->_mainoper = 'delete';
		return $this;
	}
	function _where($cond = null) {
		if ($cond) {
			$this->_where = 'WHERE '.$cond;
			$this->_lastoper = 'where';
		}
		return $this;
	}
	function _and($cond = null) {
		if ($cond) {
			switch ($this->_lastoper) {
				case 'select':
					$this->_where = 'WHERE '.$cond;
					$this->_lastoper = 'where';
					break;
				case 'where':
					$this->_where .= ' AND '.$cond;
					break;
				case 'join':
					$this->_join .= ' AND '.$cond;
					break;
			}
		}
		else
			$this->_and_or = " AND ";
		return $this;
	}
	function _or($cond = null) {
		if ($cond) {
			switch ($this->_lastoper) {
				case 'select':
					$this->_where = 'WHERE '.$cond;
					$this->_lastoper = 'where';
					break;
				case 'where':
					$this->_where .= ' OR '.$cond;
					break;
				case 'join':
					$this->_join .= ' OR '.$cond;
					break;
			}
		}
		else
			$this->_and_or = " OR ";
		return $this;
	}
	function _limit($limit = 0, $offset = 0) {
		if ((int)$limit)
			$this->_limit = "LIMIT ".((int)$offset?(int)$offset.",":"").(int)$limit;
		$this->_lastoper = 'limit';
		return $this;
	}
	function _orderby($cond = null) {
		if ($cond)
			$this->_orderby = 'ORDER BY '.$cond;
		else
			throw new \Exception('ORDER BY condition is wrong [is EMPTY].');
		$this->_lastoper = 'orderby';
		return $this;
	}
	function _groupby($cond) {
		if ($cond)
			$this->_groupby = 'GROUP BY '.$cond;
		else
			throw new \Exception('GROUP BY condition is wrong [is EMPTY].');
		$this->_lastoper = 'groupby';
		return $this;
	}
	function _join($table, $talias, $cond) {
		$this->_join[] = "LEFT JOIN ".DB_PREFIX.$table." ".$talias." ON ".$cond;
		$this->_lastoper = 'join';
		return $this;
	}
	function _as($alias) {
		$this->_as = $alias;
		return $this;
	}
	function _subquery($table, $talias, $cond = null) {
		switch ($this->_lastoper) {
			case 'select':
				$squery = new Db($table, 'select');
				break;
			case 'join':
				$squery = new Db($table, 'join');
				break;
			case 'where':
				$squery = new Db($table, 'where');
				break;
			default:
				throw new \Exception('Subquery can not write in the place SQL query.');
				break;
		}
		if (!empty($squery)) {
			$squery->_talias = $talias;
			$squery->_cond = $cond;
			$squery->_this = $this;
			return $squery;
		}
		else
			throw new \Exception('Can not create SUBQUERY to SQL query.');
	}
	function _closesquery($as = null) {
		if ($as)
			$this->_as($as);
		$this->_prepare();
		$this->_sql = $this->_and_or.'('.$this->_sql().') '.($this->_as?'AS `'.$this->_as.'` ':'').$this->_cond;
		$this->_this->_squeries[] = $this;
		return $this->_this;
	}
	function _prepare() {
		$sq1 = $sq2 = $sq3 = '';
		if (!empty($this->_squeries) && sizeof($this->_squeries)) {
			foreach ($this->_squeries as $k => $obj) {
				switch ($obj->_sqtype) {
					case 'select':
						$sq1 .= ($sq1||$this->_what?',':'').$obj->_sql();
						break;
					case 'join':
						$sq2 .= ($sq2?',':'').$obj->_sql();
						break;
					case 'where':
						$sq3 .= ($sq3?',':'').$obj->_sql();
						break;
				}
				unset($this->_squeries[$k]);
			}
			$this->_squeries = null;
		}
		if (!empty($this->_join[0])) {
			$_joins = '';
			foreach ($this->_join as $v)
				$_joins .= ($_joins?' ':'').$v;
		}
		$this->_sql = preg_replace($this->_repaterns,array($this->_what,
															!empty($_joins)?$_joins:'',
															$this->_where,
															$this->_orderby,
															$this->_groupby,
															$this->_limit,
															$this->_fields,
															$this->_values,
															$sq1,$sq2,$sq3),$this->_oper);
		return $this;
	}
	function _setsafe($set = true) {
		$this->_asis = $set;
		return $this;
	}
	function _execute($sql = null, $soft = false, $log = false) {
		$ret = null;
		$sql = $sql?$sql:($this->_sql()?$this->_sql():$this->_prepare()->_sql());
		if ($log && ($f = @fopen(ROOTPATH.'logs/mysql.log','ab'))) {
			fwrite($f, "------------------------\n");
			fwrite($f, date('d.m.y H:i:s')."\n");
			$btrace = debug_backtrace(0,3);
			@fwrite($f, !empty($btrace[0])?"From: {$btrace[0]['file']}, line: {$btrace[0]['line']}\n":"From: undefined\n");
			@fwrite($f, !empty($btrace[1])?"From: {$btrace[1]['file']}, line: {$btrace[1]['line']}\n":"From: undefined\n");
			@fwrite($f, !empty($btrace[2])?"From: {$btrace[2]['file']}, line: {$btrace[2]['line']}\n":"From: undefined\n");
			fwrite($f, $sql."\n");
			fwrite($f, "------------------------\n");
			fclose($f);
		}
		$res = self::$mysqli->query($sql);
		if (self::$mysqli->errno) {
			if (!$soft)
				throw new \Exception("MySQL query error. ERRNO: ".self::$mysqli->errno .", ERROR: ". self::$mysqli->error.", SQL: ".$sql);
			else {
				if ($log)
					Elf::set_data('error',self::$mysqli->error);
			}
		}
		else {
			if (preg_match("/^(select|show)/i", $sql)) {
				if (@$res->num_rows) {
					while ($r = $res->fetch_assoc()) {
						foreach ($r as $k=>$v)
							$r[$k] = $this->_asis?stripslashes($v):$v;
						$ret[] = $r;
					}
					unset($r);
					@$res->free_result();
				}
			}
			elseif (preg_match("/^(insert)/i", $sql)) {
				$ret = self::$mysqli->insert_id;
			}
			else
				$ret = true;
		}
		$this->_clear_vars();
		return $ret;
	}
	private function _clear_vars() {
		$this->_sql = null;
		$this->_oper = null;
		$this->_lastoper = null;
		$this->_mainoper = null;
		$this->_what = null;
		$this->_values = null;
		$this->_fields = null;
		$this->_where = null;
		$this->_join = array();
		$this->_limit = null;
		$this->_orderby = null;
		$this->_groupby = null;
		$this->_squeries = null;
		$this->_sqtype = null;
		$this->_talias = null;
		$this->_cond = null;
		$this->_this = null;
		$this->_and_or = null;
		$this->_as = null;
		$this->_asis = true;
	}
	function query($sql) {
		return $this->_execute($sql);
	}
	function _sql() {
		return $this->_sql;
	}
	private function _update_values($data, $safe) {
		$ret = '';
		foreach ($data as $k=>$v) {
			if (isset($this->fields[$k])) {
				$ret .= ($ret?",":"")."`".$k."`=";
				if (empty($v) && !($this->fields[$k]['flags']&DB_NOT_NULL_FLAG)) {
					$v = null;
				}
				switch ($this->fields[$k]['type']) {
					case 1:
					case 2:
					case 3:
					case 7:
					case 8:
					case 9:
						$v = $v===null?'NULL':(int)$v;
						break;
					case 4:
					case 5:
						$v = $v===null?'NULL':(float)$v;
						break;
					default:
						$v = $v===null?'NULL':"'".($safe?htmlspecialchars($v):addslashes($v))."'";
						break;
				}
				$ret .= $v;
			}
		}
		return $ret;
	}
	private function _insert_fields($data) {
		$ret = '';
		foreach ($data as $k=>$v) {
			if (isset($this->fields[$k])) {
				$ret .= ($ret?",":"")."`".$k."`";
			}
		}
		return $ret;
	}
	private function _insert_values($data, $safe) {
		$ret = '';
		foreach ($data as $k=>$v) {
			if (isset($this->fields[$k])) {
				if (!empty($v) || (empty($v) && $this->fields[$k]['flags']&DB_NOT_NULL_FLAG)) {
					if ((!empty($v) && !($this->fields[$k]['flags']&DB_AUTO_INCREMENT_FLAG))
						|| empty($v)) {
						switch ($this->fields[$k]['type']) {
							case 1:
							case 2:
							case 3:
							case 7:
							case 8:
							case 9:
								$v = (int)$v;
								break;
							case 4:
							case 5:
								$v = (float)$v;
								break;
							default:
								$v = "'".($safe?htmlspecialchars($v):addslashes($v))."'";
								break;
						}
					}
					else
						$v = 'NULL';
				}
				else
					$v = 'NULL';
				$ret .= ($ret!==''?',':'').$v;
			}
		}
		return $ret;
	}
	function get_by_id($id, $ext = null) {
		return ($ret = $this->query("SELECT * FROM ".$this->_name()." WHERE `id`=".(int)$id." ORDER BY `id` LIMIT 1"))?$ret[0]:null;
	}
	function data($offset = null, $limit = null, $where = null, $orderby = null, $groupby = null) {
		$ret = $this->_select();
		if ($where)
			$ret = $ret->_where($where);
		if ($offset !== null || $limit !== null)
			$ret = $ret->_limit((int)$limit,(int)$offset);
		if ($orderby)
			$ret = $ret->_orderby($orderby);
		if ($groupby)
			$ret = $ret->_groupby($groupby);
		return $ret->_execute();
	}
	function edit($where = '', $data = null, $safe = true) {
		$ret = null;
		if (empty($data))
			$data = Elf::input()->data($safe);
		if ($where) {
			return $this->_update($data,$safe)->_where($where)->_prepare()->_execute();
		}
		else {
			return $this->_insert($data,$safe)->_prepare()->_execute();
		}
	}
	function del_by_id($id) {
		return $this->_delete()->_where("`id`=".(int)$id)->_orderby("`id`")->_limit(1)->_prepare()->_execute();
	}
	function get($where, $orderby = '') {
		$obj = $this->_select()->_where($where);
		$obj = $orderby?$obj->_orderby($orderby)->_limit(1):$obj->_limit(1);
		return ($ret = $obj->_prepare()->_execute())?$ret[0]:null;
	}
	function cnt($where = null, $orderby = '') {
		$ret = 0;
		$obj = $where?$this->_select("COUNT(*) AS `cnt`")->_where($where):$this->_select("COUNT(`id`) AS `cnt`");
		$obj = $orderby?$obj->_orderby($orderby)->_limit(1):$obj->_limit(1);
		if ($ret = $obj->_prepare()->_execute()) {
			$ret = $ret[0]['cnt'];
		}
		return (int)$ret;
	}
	function sum($field, $where, $orderby = '') {
		$ret = 0;
		$obj = $this->_select("SUM(`".$field."`) AS `summ`")->_where($where);
		$obj = $orderby?$obj->_orderby($orderby)->_limit(1):$obj->_limit(1);
		if ($ret = $this->_prepare()->_execute()) {
			$ret = $ret[0]['summ'];
		}
		return (float)$ret;
	}
	private function get_enum_values($f) {
		$ret = null;
		if ($res = $this->query("SHOW COLUMNS FROM ".$this->_name()." where field like '".$f."'")) {
			if (substr($res[0]['Type'], 0, 4) === 'enum') {
				$res = explode(',',str_replace("'","",substr($res[0]['Type'],5,-1)));
				foreach ($res as $v)
					$ret[] = $v;
			}
		}
		return $ret;
	}
	function create_select($f, $sel, $add = '', $lang = '') {
		$ret = '';
		if ($res = $this->get_enum_values($f)) {
			$ret = '<select name="'.$f.'" '.$add.'>';
			foreach ($res as $k=>$v) {
				$ret .= '<option value="'.$v.'"'.($sel == $v?' selected="selected" ':' ').'title="'.($lang?Elf::lang($lang)->item($v):$v).'">'.($lang?Elf::lang($lang)->item($v):$v).'</option>';
			}
			$ret .= '</select>';
		}
		return $ret;
	}
	function selector($f, $sel = null, $add = '', $lang = '') {
		$ret = '';
		if ($res = $this->_select()->_orderby($f)->_execute()) {
			$ret = '<select '.$add.'>';
			$ret .= '<option value=""'.(!$sel?' selected="selected"':'').'>'.Elf::lang($lang?$lang:'')->item('default.selector.mess').'</option>';
			foreach ($res as $v)
				$ret .= '<option value="'.$v['id'].'"'.($sel?!is_array($sel)&&$v['id']==$sel?' selected="selected"':(is_array($sel)&&in_array($v['id'],$sel)?' selected="selected"':''):'').'>'.Elf::lang($lang?$lang:'')->item($v[$f]).'</option>';
			$ret .= '</select>';
		}
		return $ret;
	}
	protected function prepare_search_data($shash) {
		if ($shash) {
			if (($res = json_decode(base64_decode($shash)))
				&& is_object($res)
				&& sizeof((array)$res)) {
				foreach ((array)$res as $k=>$v) {
					if (is_object($v) && sizeof($v)) {
						foreach ((array)$v as $kk=>$vv)
							$data[$k][$kk] = $vv;
					}
					else
						$data[$k] = $v;
					Elf::input()->set($k,$data[$k]);
				}
			}
		}
		else {
			$data = Elf::input()->data();
			$shash = base64_encode(json_encode($data));
		}
		return !empty($data)?array($data,$shash):array(null,null);
	}
	/****************************************************
	STRUCTURE $fields array
	$fields = [
		'field1' => [
						'name'			=> name field (req),
						'required'		=> true | false (def),
						'alert'			=> alert message | null (def.)
						'regexp'		=> regvalue | null (def),
						'regexp_alert'	=> alert message | null (def),
						'unique'		=> `field name in table DB` | false (def),
						'ununique_id'=> => ID value | 0 (def),
						'equal'			=> value | null {def},
						'equal_name'	=> equal field name
					],
		'field2' => [
						...
					],
		...
	]
	Some RegExp:
	email - ^([a-zA-Z0-9_]|\-|\.)+@(([a-z0-9]|\-)+\.)+[a-z]{2,6}$
	phone - ^\+((\d{1,2})[\- ]?)?(\(?\d{2,4}\)?[\- ]?)?[\d\- ]{7,10}$
			или ^\+?(\d{1,3})?[- ]?\(?(?:\d{2,3})\)?[- ]?\d{3}[- ]?\d{2}[- ]?\d{2}$
	password - ^[a-zA-Z0-9_]{6,12}$
	****************************************************/
	protected function chk_req_fields($fields = []) {
		$ret = true;
		if ($fields) {
			$data = Elf::input()->data();
			Elf::$_data['error'] = '';
			Elf::$_data['names_with_error'] = [];
			foreach ($fields as $k=>$v) {
				if (!empty($v['required'])
					&& (!isset($data[$k]) || empty($data[$k]))) {
					Elf::$_data['error'] .= empty($v['alert'])?Elf::lang()->item('error.field.is.empty',$v['name'])."\n":$v['alert']."\n";
					Elf::$_data['names_with_error'][] = $k;
					$ret = false;
				}
				elseif (!empty($v['regexp'])
					&& (!isset($data[$k]) || !preg_match("/".$v['regexp']."/", $data[$k]))) {
					Elf::$_data['error'] .= (!empty($v['regexp_alert'])?$v['regexp_alert']:Elf::lang()->item('error.field.regexp',$v['name']))."\n";
					Elf::$_data['names_with_error'][] = $k;
					$ret = false;
				}
				elseif (!empty($v['unique'])
					&& !empty($data[$k])
					&& $this->get("`{$v['unique']}`='{$data[$k]}'".(!empty($v['ununique_id'])?" AND `id`!={$v['ununique_id']}":""))) {
					Elf::$_data['error'] .= Elf::lang()->item('error.field.unique',$v['name'])."\n";
					Elf::$_data['names_with_error'][] = $k;
					$ret = false;
				}
				elseif (isset($v['equal']) && ($v['equal'] !== null)
					&& isset($data[$k]) && ($v['equal'] != $data[$k])) {
					Elf::$_data['error'] .= Elf::lang()->item('error.field.equal',$v['name'],isset($v['equal_name'])?$v['equal_name']:'undefined')."\n";
					Elf::$_data['names_with_error'][] = $k;
					$ret = false;
				}
			}
		}
		return $ret;
	}
}