<?php

namespace Elf\Libs;

use Elf;

class Tags extends Db {
	
	private $ctags;
	
	function __construct() {
		parent::__construct('tags');
		$this->ctags = new Db('tags_content');

	}
	function _data($offset = 0) {
		$ret = $pagi = null;
		if ($ret = $this->_select()->_orderby("`htag`")->_limit(RECS_ON_PAGE,(int)$offset*RECS_ON_PAGE)->_execute()) {
			foreach ($ret as $k=>$v) {
				$ret[$k]['content_items'] = $this->_get_content_items($v['id']);
			}
			$pg = new Pagination;
			$pagi = $pg->create("/tag/index/",
									$this->cnt(),
									(int)$offset,RECS_ON_PAGE,
									3);
		}
		return array($ret,$pagi);
	}
	function _data_by_tag($tag, $offset, $img_path, $icon_path) {
		$ret = $pagi = null;
		if ($ret = $this->_select("t1.`htag`,
								concat('/','".$img_path."',t3.`picture`) as `picture_image`,
								concat('/','".$img_path."',t3.`picture`) as `picture_icon`,
								t1.`freq`,t3.*")
							->_join("tags_content","t2","t2.`tag_id`=t1.`id`")
							->_join("content","t3","t3.`id`=t2.`content_id`")
							->_where("t1.`htag`='".addslashes($tag)."'")
							->_orderby("t1.`htag`,t2.`tag_id`,t3.`hot` DESC,t3.`tm` DESC")
							->_limit(RECS_ON_PAGE,(int)$offset*RECS_ON_PAGE)
							->_execute()) {
			foreach ($ret as $k=>$v) {
				$ret[$k]['tags'] = $this->_get_content_tags($v['id'],5,"`freq` DESC");
			}
			$pg = new Pagination;
			$pagi = $pg->create('/tag/'.$tag.'/',
									$ret[0]['freq'], 
									(int)$offset, RECS_ON_PAGE, 3, 
									'/tag/'.$tag.'/','elf-news');
		}
		return array($ret,$pagi);
	}
	function _edit($tag, $cid = 0, $tid = 0) {
		$ret = false;
		if ((int)$tid && ($rec = $this->get_by_id((int)$tid))) {
			if ($rec['htag'] != $tag) {
				$ret = $this->_update(array('htag'=>addslashes($tag)))->_where("`id`=".$rec['id'])->_orderby("`id`")->_limit(1)->_execute();
				Elf::routing()->_del('tag',$rec['htag']);
			}
		}
		elseif (!($rec = $this->_get($tag))) {
			$rec['id'] = $ret = $this->_insert(array('htag'=>addslashes($tag)))->_execute();
			$rec['freq'] = 0;
		}
		if ($ret) {
			Elf::routing()->_edit('tag',addslashes($tag),'content','tagsearch',null,
				array('title'=>Elf::lang('tags')->item('route.title',$tag),
						'description'=>Elf::lang('tags')->item('route.description',$tag),
						'keywords'=>Elf::lang('tags')->item('route.keywords',$tag)));
		}
		if ((int)$cid 
			&& !$this->ctags->get("`tag_id`=".$rec['id']." AND `content_id`=".(int)$cid,"`tag_id`,`content_id`")) {
			$this->ctags->_insert(array('tag_id'=>$rec['id'],'content_id'=>(int)$cid))->_execute();
			$ret = $this->_update(array('freq'=>$rec['freq']+1))->_where("`id`=".$rec['id'])->_orderby("`id`")->_limit(1)->_execute();
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
	function _del_all_tags_from_content($cid) {
		if ($res = $this->ctags->_select("`tag_id`")->_where("`content_id`=".(int)$cid)->_execute()) {
			$ids = '';
			foreach ($res as $v)
				$ids .= ($ids?',':'').$v['tag_id'];
			if ($ids)
				$this->_update(['freq'=>'`freq`-1'])->_where("`id` IN (".$ids.")")->_execute();
			unset($ids);
			$this->ctags->_delete()->_where("`content_id`=".(int)$cid)->_execute();
		}
	}
	function _del_tag_content($tid, $cid) {
		if (($rec = $this->get_by_id($tid))
			&& ($c = $this->ctags->get("`tag_id`=".$rec['id']." AND `content_id`=".(int)$cid,"`tag_id`,`content_id`"))) {
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
	function _get_content_tags($cid, $limit = null, $sort = null) {
		$ret = null;
		if ($cids = $this->ctags->_select()
							->_where("`content_id`=".(int)$cid)
							->_orderby("`content_id`")->_execute()) {
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
	private function _get_content_items($tid) {
		$ret = null;
		if ($cids = $this->ctags->_select()->_where("`tag_id`=".$tid)->_orderby("`tag_id`")->_execute()) {
			$ids = '';
			foreach ($cids as $v)
				$ids .= ($ids?',':'').$v['content_id'];
			$cont = new \Elf\App\Models\Content;
			$ret = $cont->_select('t1.`id`,t1.`title`')
							->_where("`id` IN (".$ids.")")
							->_orderby("`id`")
							->_execute();
		}
		return $ret;
	}
}
