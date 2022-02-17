<script>
function _cryptuncrypt(chk, iid) {
	let obj = document.getElementById(iid);
	if (chk) {
		$.post('/settings/md5hash',{v:obj.value},function(data) {
			obj.setAttribute('data-value', obj.value);
			obj.value = data;
		});
	}
	else {
		obj.value = obj.getAttribute('data-value');
	}
}
</script>
<h2 class="adm-tlt"><% lang:settings:title %></h2>

<a href="javascript:;" data-params="dialog=settings/edit;sid=0;modal=yes;caption=<% lang:settings:titledit %>" onclick="showDialog(this)"><% lang:newrec %></a>
<br /><br />
<?php if (!empty(Elf::$_data['setts'])):?>
	<table class="edit">
		<tr class="top">
			<td>#</td>
			<td>&nbsp;</td>
			<td><% lang:desc %></td>
			<td><% lang:name %></td>
			<td><% lang:value %></td>
			<td><% lang:settings:expireto %></td>
		</tr>
	<?php foreach (Elf::$_data['setts'] as $v):?>
		<tr class="data">
			<td><?=$v['id']?></td>
			<td><a href="javascript:;" data-params="dialog=settings/edit;sid=<?=$v['id']?>;modal=yes;caption=<% lang:settings:titledit %>" onclick="showDialog(this)"><% lang:edt %></a></td>
			<td><?=nl2br($v['desc'])?></td>
			<td><strong><?=$v['name']?></strong></td>
			<td title="<?=$v['value']?>"><?=substr(str_replace(",",",<br />",$v['value']),0,50)?></td>
			<td <?=$v['expire']&&($v['expire']<(time()+SECONDS_IN_DAY*7))?'style="background:#800;color:#fff;"':''?>><?=$v['expire']?date('d.m.Y',$v['expire']):'<% lang:unexp %>'?></td>
		</tr>
	<?php endforeach;?>
	</table>
<?php else:?>
<div class="alert">
<% lang:datanotfound %>
</div>
<?php endif;?>
