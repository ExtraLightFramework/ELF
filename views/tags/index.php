<h2 class="adm-tlt"><% lang:tags:title %></h2>
<a href="javascript:;" data-params="dialog=tags/edit;tid=0;offset=<% offset %>;caption=<% lang:tags:newtag %>" onclick="showDialog(this)"><% lang:tags:newtag %></a>
<br /><br />
<?=Elf::$_data['data'][1]?Elf::$_data['data'][1]:''?>
<?php if (!empty(Elf::$_data['data'][0])):?>

	<table class="edit">
		<tr class="top">
			<td width="15">#</td>
			<td width="120"><% lang:tags:name %></td>
			<td><% lang:tags:contentlist %></td>
			<td width="20"><% lang:tags:freq %></td>
		</tr>
	<?php foreach (Elf::$_data['data'][0] as $v):?>
		<tr class="data">
			<td><?=$v['id']?><br />
				<a href="javascript:;" data-params="dialog=tags/edit;tid=<?=$v['id']?>;offset=<% offset %>;caption=<% lang:tags:tagseditor %>" onclick="showDialog(this)"><% lang:edt %></a><br />
				<a href="/tag/del/<?=$v['id']?>/<% offset %>" title="<% lang:tags:deletetag %>" onclick="return confirm('<% lang:tags:deleteconfirm %>')"><% lang:del %></a>
			</td>
			<td><a href="/tag/<?=urlencode($v['htag'])?>" target="_blank" title="<% lang:tags:searchrecs %>">#<?=$v['htag']?></a></td>
			<td>
				<?php if (!empty($v['content_items'])):?>
					<?php foreach ($v['content_items'] as $c):?>
					<div class="tag-content-item" id="ctag-<?=$v['id']?>-<?=$c['id']?>">
						<span title="<?=$c['title']?>"><?=Elf::show_words($c['title'],3)?>...</span>
						<a href="javascript:;" title="<% lang:tags:removecontentag %>" class="del"
							onclick="rem_content_tag(<?=$v['id']?>,<?=$c['id']?>)">x</a>
					</div>
					<?php endforeach;?>
				<?php else:?>
				<% lang:tags:nocontentitems %>
				<?php endif;?>
			</td>
			<td id="freq-<?=$v['id']?>"><?=$v['freq']?></td>
		</tr>
	<?php endforeach;?>
	</table>
<?php else:?>
<div class="alert">
<% lang:datanotfound %>
</div>
<?php endif;?>