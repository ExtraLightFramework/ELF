<h2 class="adm-tlt"><% lang:forms:title %></h2>
<a href="javascript:;" data-params="dialog=forms/edit;fid=0;offset=<% offset %>;caption=<% lang:forms:newform %>" onclick="showDialog(this)"><% lang:forms:newform %></a>
<br /><br />
<?=Elf::$_data['data'][1]?Elf::$_data['data'][1]:''?>
<?php if (!empty(Elf::$_data['data'][0])):?>

	<table class="edit">
		<tr class="top">
			<td width="15">#</td>
			<td width="120"><% lang:forms:name %></td>
			<td width="60"><% lang:forms:lang %></td>
			<td><% lang:forms:path %></td>
			<td><% lang:forms:link %></td>
			<td><% lang:forms:table %></td>
		</tr>
	<?php foreach (Elf::$_data['data'][0] as $v):?>
		<tr class="data">
			<td><?=$v['id']?><br />
				<a href="javascript:;" data-params="dialog=forms/edit;appearance=appearances/wide;fid=<?=$v['id']?>;offset=<% offset %>;caption=<% lang:forms:formseditor %>" onclick="showDialog(this)"><% lang:edt %></a><br />
				<a href="/forms/del/<?=$v['id']?>/<% offset %>" title="<% lang:forms:deletetag %>" onclick="return confirm('<% lang:forms:deleteconfirm %>')"><% lang:del %></a>
			</td>
			<td>
				<a href="javascript:;" data-params="dialog=forms/edit;appearance=appearances/wide;fid=<?=$v['id']?>;offset=<% offset %>;caption=<% lang:forms:formseditor %>" title="редактировать" onclick="showDialog(this)">
				<?=$v['name']?></a>
			</td>
			<td><?=$v['lang']?$v['lang'].'.lng':'-'?></td>
			<td><?=Elf::get_app_views_path()?><?=$v['fullpath']?></td>
			<td><?php
					$lnk = '<a href="javascript:;" data-params="dialog='.str_replace(EXT,'',$v['fullpath']).';'.($v['getter_variable_name']?$v['getter_variable_name']:'id').'=0;caption='.$v['name'].'" onclick="showDialog(this)">show</a>';
				?>
				<?=htmlspecialchars($lnk)?><br />
				<?=$lnk?>
			</td>
			<td><?=$v['table']?></td>
		</tr>
	<?php endforeach;?>
	</table>
<?php else:?>
<div class="alert">
<% lang:datanotfound %>
</div>
<?php endif;?>