<?php

namespace Elf\Libs;

use Elf;

if (!defined('PAGI_MAX_LINKS_CNT')) define('PAGI_MAX_LINKS_CNT',5);

class Pagination {
	
	static function create($url, $cnt, $offset, $limit, $pos, $firsturl = null, $show_more = null, $show_more_insert_type = 'insertin') {
		// $url 	- урл пагинатора
		// $cnt 	- общее кол-во элементов
		// $offset 	- активное смещение (ссылка по которой кликнули)
		// $limit 	- кол-во отображаемых элементов на странице
		// $pos 	- позиция в урле для указания значения смещения
		// $firsturl- урл для подстановки в первую ссылку пагинатора, чтобы не приклеивалось сзади /page/0
		// $show_more-если не null, то это контейнер для вывода результатов ajax-запроса "Показать еще"
		// $show_more_insert_type - тип вставки insertin вставить в элемент, например в div, insertafter - вставить после, например, элемента с ID заданного в предыдущем параметре
		$ret = '';

		if (!empty($cnt) && ($cnt > $limit)) {
			$exp_path = '';
			$exp = parse_url($url);
			$scheme = !empty($exp['scheme'])&&($exp['scheme']=='javascript')?'javascript:':'';
			if ($scheme == 'javascript:')
				$add_page_postfix = false;
			else
				$add_page_postfix = true;
			if (!empty($exp['path']) && !empty($exp['query']))
				$exp_path = $exp['path'].'?';
			elseif (empty($exp['query']))
				$exp['query'] = $exp['path'];
			$exp = explode("/", $exp['query']);
			
			if (isset($exp[$pos-1])) {
				$colpages = ceil($cnt/$limit);

				if (($offset - ceil(PAGI_MAX_LINKS_CNT/2)) > 0) {
					$counterbeg = $offset - floor(PAGI_MAX_LINKS_CNT/2);
					$firstoffset = 0;
				}
				else {
					$counterbeg = 1;
				}
				if (($offset + ceil(PAGI_MAX_LINKS_CNT/2)) < $colpages) {
					$lastoffset = $colpages-1;
				}
				if ($offset != 0)
					$prevlink = $offset-1;
				if ($offset+1 != $colpages)
					$nextlink = $offset+1;
				$counterend = (($counterbeg + PAGI_MAX_LINKS_CNT)>$colpages)?$colpages+1:$counterbeg + PAGI_MAX_LINKS_CNT;

				$ret = '<div class="pagination">';
				
				if (isset($firstoffset)) {
					$exp[$pos] = $firstoffset;
					$ret .= "<a href='".($firsturl?$firsturl:$scheme.$exp_path.implode("/", $exp)).($add_page_postfix?"/?page=".$exp[$pos]:"")."' title='<% lang:pagination:tobegin %>'><i class='fas fa-chevron-circle-left'></i></a>";
				}
				else
					$ret .= '<span class="pagination-clear"><i class="fas fa-chevron-circle-left"></i></span>';
				if (isset($prevlink)) {
					$exp[$pos] = $prevlink;
					$ret .= "<a href='".(!$prevlink&&$firsturl?$firsturl:$scheme.$exp_path.implode("/", $exp)).($add_page_postfix?"/?page=".$exp[$pos]:"")."' title='<% lang:pagination:reward %>'><i class='fas fa-chevron-left'></i></a>";
				}
				else
					$ret .= '<span class="pagination-clear"><i class="fas fa-chevron-left"></i></span>';
				for ($i = $counterbeg; $i < $counterend; $i ++) {
					$exp[$pos] = $i-1;
					if ($exp[$pos] != $offset)
						$ret .= "<a class='pagination-num-lnk' href='".(!$exp[$pos]&&$firsturl?$firsturl:$scheme.$exp_path.implode("/", $exp)).($add_page_postfix?"/?page=".$exp[$pos]:"")."' title='<% lang:pagination:topage %> ".$i."'>".$i."</a>";
					else
						$ret .= "<span class='pagination-num-lnk pagination-selected'>".$i."</span>";
				}
				if (isset($nextlink)) {
					$exp[$pos] = $nextlink;
					$ret .= "<a href='".$scheme.$exp_path.implode("/", $exp).($add_page_postfix?"/?page=".$exp[$pos]:"")."' title='<% lang:pagination:forward %>'><i class='fas fa-chevron-right'></i></a>";
				}
				else
					$ret .= '<span class="pagination-clear"><i class="fas fa-chevron-right"></i></span>';
				if (isset($lastoffset)) {
					$exp[$pos] = $lastoffset;
					$ret .= "<a href='".$scheme.$exp_path.implode("/", $exp).($add_page_postfix?"/?page=".$exp[$pos]:"")."' title='<% lang:pagination:toend %>'><i class='fas fa-chevron-circle-right'></i></a>";
				}
				else
					$ret .= '<span class="pagination-clear"><i class="fas fa-chevron-circle-right"></i></span>';
				$ret .= '</div>';
				if (!$offset)
					Elf::$_data['pagination.seo'] = '<link rel="canonical" href="'.(Elf::site_url(false).str_replace('page/','',$url)).'/" /><link rel="next" href="'.Elf::site_url(false).$url.'/1/?page=1" />';
				elseif (($offset+1) == $colpages)
					Elf::$_data['pagination.seo'] = '<link rel="canonical" href="'.($firsturl?Elf::site_url(false).$firsturl:Elf::site_url(false).str_replace('page/','',$url)).'/" /><link rel="prev" href="'.Elf::site_url(false).$url.'/'.($offset-1).'/?page='.($offset-1).'" />';
				else 
					Elf::$_data['pagination.seo'] = '<link rel="canonical" href="'.($firsturl?Elf::site_url(false).$firsturl:Elf::site_url(false).str_replace('page/','',$url)).'/" /><link rel="next" href="'.Elf::site_url(false).$url.'/'.($offset+1).'/?page='.($offset+1).'" /><link rel="prev" href="'.Elf::site_url(false).$url.'/'.($offset-1).'/?page='.($offset-1).'" />';
				if ($show_more && isset($nextlink)) {
					$exp[$pos] = $nextlink;
					$lnk = $scheme.$exp_path.implode("/", $exp);
					Elf::$_data['pagination.showmorebutt'] = "<button onclick=\"ELF_pagination_showmore('".$show_more."','".$lnk."','".$show_more_insert_type."')\"><i class=\"fas fa-redo-alt\"></i> ".Elf::lang('pagination')->item('showmore')."</button>";
					Elf::$_data['pagination.showmore'] = "<div id=\"elf-pagination-showmore\">".Elf::$_data['pagination.showmorebutt']."</div>";
				}
			}
			else
				$ret = 'Pagination uri explode error!';
		}
		return $ret;
	}
}
