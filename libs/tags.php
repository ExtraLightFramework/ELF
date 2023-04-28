<?php

namespace Elf\Libs;

use Elf;

class Tags extends Db {
	
	private $ctags;
	private $type;
	
	function __construct($type = 'content') {
		parent::__construct('tags');
//		$type = 'content';
		$this->type = $type;
		switch ($type) {
			case 'content':
				$this->ctags = new Db('tags_content');
				break;
			case 'catalog':
				$this->ctags = new Db('tags_catalog');
				break;
		}
	}
	function _data($offset = 0) {
		$ret = $pagi = null;
		if ($ret = $this->_select()->_orderby("`htag`")->_limit(RECS_ON_PAGE,(int)$offset*RECS_ON_PAGE)->_execute()) {
			foreach ($ret as $k=>$v) {
				$ret[$k]['content_items'] = $this->_get_items($v['id']);
			}
			$pg = new Pagination;
			$pagi = $pg->create("/tags/index/",
									$this->cnt(),
									(int)$offset,RECS_ON_PAGE,
									3);
		}
		return [$ret,$pagi];
	}
	function _data_by_tag($tag, $offset, $img_path, $icon_path) {
		$ret = $pagi = null;
		if ($ret = $this->_select("t1.`htag`,
								concat('/','".$img_path."',t3.`picture`) as `picture_image`,
								concat('/','".$img_path."',t3.`picture`) as `picture_icon`,
								t1.`freq`,t3.*")
							->_join("tags_".$this->type,"t2","t2.`tag_id`=t1.`id`")
							->_join($this->type,"t3","t3.`id`=t2.`child_id`")
							->_where("t1.`htag`='".addslashes($tag)."'")
							->_orderby("t1.`freq`,t1.`htag`")
							->_limit(RECS_ON_PAGE,(int)$offset*RECS_ON_PAGE)
							->_execute()) {
			foreach ($ret as $k=>$v) {
				$ret[$k]['tags'] = $this->_get_tags($v['id'],5,"`freq` DESC");
			}
			$pagi = \Elf\Libs\Pagination::createcreate('/tag_'.$this->type.'/'.$tag.'/',
									$ret[0]['freq'], 
									(int)$offset, RECS_ON_PAGE, 3, 
									'/tag/'.$tag.'/');
		}
		return array($ret,$pagi);
	}
	function _get_block_data($limit = 100) {
		return $this->_select("concat('/tag_{$this->type}/',t1.`htag`,'/') AS `uri`, t1.*")
					->_where("(SELECT COUNT(t2.`id`) FROM ".DB_PREFIX."tags_{$this->type} t2 WHERE t2.`tag_id`=t1.`id`)>0")
					->_orderby("t1.`freq` DESC")->_limit((int)$limit)->_execute();
	}
	function _selector_data() {
		return $this->_select("t1.*")
					->_where("(SELECT COUNT(t2.`id`) FROM ".DB_PREFIX."tags_{$this->type} t2 WHERE t2.`tag_id`=t1.`id`)>0")
					->_orderby("t1.`htag`")->_execute();
	}
	function _edit($tag, $cid = 0) {
		$ret = false;
/*		if ((int)$tid && ($rec = $this->get_by_id((int)$tid))) {
			if ($rec['htag'] != $tag) {
				$ret = $this->_update(array('htag'=>addslashes($tag)))->_where("`id`=".$rec['id'])->_orderby("`id`")->_limit(1)->_execute();
				Elf::routing()->_del('tag',$rec['htag']);
			}
		}
*/		
		if ($tags = explode(',', $tag)) {
		
			$existing_tags = $this->_get_tags($cid);
			foreach ($tags as $tag) {
				$ret = false;
				if ($tag = trim($tag)) {
					if (!($rec = $this->_get($tag))) {
						$rec['id'] = $ret = $this->_insert(['htag'=>addslashes($tag)])->_execute();
						$rec['freq'] = 0;
					}
					elseif ($existing_tags)
						foreach ($existing_tags as $k=>$t)
							if ($t['htag'] == $tag)
								unset($existing_tags[$k]);
						
					if ($ret) {
						Elf::routing()->_edit('tag_'.$this->type,addslashes($tag),$this->type,'tagsearch',null,
							['title'=>Elf::lang('tags')->item('route.title',$tag),
								'description'=>Elf::lang('tags')->item('route.description',$tag),
								'keywords'=>Elf::lang('tags')->item('route.keywords',$tag)]);
					}
					if ((int)$cid && !empty($rec['id'])
						&& !$this->ctags->get("`tag_id`=".$rec['id']." AND `child_id`=".(int)$cid,"`tag_id`,`child_id`")) {
						$this->ctags->_insert(['tag_id'=>$rec['id'],'child_id'=>(int)$cid])->_execute();
						$ret = $this->_update(['freq'=>$rec['freq']+1])->_where("`id`=".$rec['id'])->_execute();
					}
				}
			}
			if ($existing_tags) {
				foreach ($existing_tags as $t) {
					if ($t = $this->get_by_id($t['id'])) {
						$this->ctags->_delete()->_where("`tag_id`={$t['id']} AND `child_id`={$cid}")->_execute();
						$t['freq'] --;
						if ($t['freq'] <= 0) {
							$this->_delete()->_where("`id`={$t['id']}")->_execute();
							Elf::routing()->_del('tag_'.$this->type, $t['htag']);
						}
						else
							$this->_update(['freq'=>$t['freq']])->_where("`id`={$t['id']}")->_execute();
					}
				}
			}
		}
		return $ret;
	}
	function _get($tag) {
		return $this->get("`htag`='".addslashes($tag)."'","`htag`");
	}
	function _del($tid) {
		if ($rec = $this->get_by_id((int)$tid)) {
			$this->_delete()->_where("`id`=".$rec['id'])->_orderby("`id`")->_limit(1)->_execute();
			$this->ctags->_delete()->_where("`tag_id`=".$rec['id'])->_execute();
		}
	}
	function _del_all_tags($cid) {
		if ($res = $this->ctags->_select("`tag_id`")->_where("`child_id`=".(int)$cid)->_execute()) {
			$ids = '';
			foreach ($res as $v)
				$ids .= ($ids?',':'').$v['tag_id'];
			if ($ids)
				$this->_update(['freq'=>'`freq`-1'])->_where("`id` IN (".$ids.")")->_execute();
			unset($ids);
			$this->ctags->_delete()->_where("`child_id`=".(int)$cid)->_execute();
		}
	}
	function _del_tag($tid, $cid) {
		if (($rec = $this->get_by_id($tid))
			&& ($c = $this->ctags->get("`tag_id`=".$rec['id']." AND `child_id`=".(int)$cid,"`tag_id`,`child_id`"))) {
			$this->ctags->_delete()->_where("`id`=".$c['id'])->_orderby("`id`")->_limit(1)->_execute();
			$this->_update(array('freq'=>$rec['freq']-1))->_where("`id`=".$rec['id'])->_orderby("`id`")->_limit(1)->_execute();
			return $rec['freq']-1;
		}
		return false;
	}
	function _get_full($tid) {
		if ($ret = $this->get_by_id((int)$tid)) {
//			$ret['content_items'] = $this->_get_content_items($ret['id']);
		}
		return $ret;
	}
	function _get_tags($cid, $limit = null, $sort = null) {
		$ret = null;
		if ($cids = $this->ctags->_select()
							->_where("`child_id`=".(int)$cid)
							->_orderby("`child_id`")->_execute()) {
			$ids = '';
			foreach ($cids as $v)
				$ids .= ($ids?',':'').$v['tag_id'];
			$ret = $this->_select('t1.`id`,t1.`htag`')
								->_where("`id` IN (".$ids.")")
								->_orderby(!$sort?"`id`":$sort)
								->_limit($limit!==null?$limit:0)
								->_execute();
		}
		return $ret;
	}
	function _get_items_by_tag($tag) {
		$ret = null;
		if ($res = $this->_select("t2.`child_id`")
							->_join("tags_".$this->type,"t2","t2.`tag_id`=t1.`id`")
							->_where("t1.`htag`='{$tag}'")
							->_execute()) {
			switch ($this->type) {
				case 'catalog':
					$cat = new Elf\App\Models\Catalog;
					break;
			}
			if (!empty($cat)) {
				$ids = '';
				foreach ($res as $v) {
					$ids .= ($ids?',':'').$v['child_id'];
				}
				$ret = $cat->_items_with_all_parents($ids);
			}
		}
		return $ret;
	}
	private function _get_items($tid) {
		$ret = null;
		if ($cids = $this->ctags->_select()->_where("`tag_id`=".$tid)->_orderby("`tag_id`")->_execute()) {
			$ids = '';
			foreach ($cids as $v)
				$ids .= ($ids?',':'').$v['child_id'];
			switch ($this->type) {
				case 'content':
					$cont = new \Elf\App\Models\Content;
					$ret = $cont->_select('t1.`id`,t1.`title`')
							->_where("`id` IN (".$ids.")")
							->_orderby("`id`")
							->_execute();
					break;
				case 'catalog':
					break;
			}
		}
		return $ret;
	}
}
