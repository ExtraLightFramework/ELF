<h2 class="adm-tlt"><% lang:route:title %></h2>
<h3 class="red"><% lang:route:donotanything %></h3>
<a href="javascript:;" data-params="dialog=route/edit;rid=0;modal=yes;offset=<% offset %>;caption=<% lang:route:newrule %>" onclick="showDialog(this)"><% lang:route:newrule %></a>
| <a href="/route/sitemap" title="<% lang:route:sitemapcreate %>">SITEMAP.XML</a>
<br /><br />
<?=Elf::$_data['data'][1]?Elf::$_data['data'][1]:''?>
<?php if (!empty(Elf::$_data['data'][0])):?>
	<table class="edit">
		<tr class="top">
			<td width="15">#</td>
			<td width="50">&nbsp;</td>
			<td width="120"><% lang:route:controller %> -&gt;</td>
			<td width="120"><% lang:route:method %> -&gt</td>
			<td width="120">-&gt; <% lang:route:controller %></td>
			<td width="120">-&gt; <% lang:route:method %></td>
			<td width="200">-&gt; <% lang:route:getparams %></td>
			<td><% lang:route:canonicalink %></td>
			<td>SEO</td>
		</tr>
	<?php foreach (Elf::$_data['data'][0] as $v):?>
		<tr class="data">
			<td><?=$v['id']?><br /><?=date('d.m.Y',$v['tm'])?><br /><?=date('H:i:s',$v['tm'])?></td>
			<td>
				<a href="javascript:;" data-params="dialog=route/edit;rid=<?=$v['id']?>;modal=yes;wide=yes;offset=<% offset %>;caption=<% lang:route:ruleseditor %>" onclick="showDialog(this)"><% lang:edt %></a><br />
				<a href="{site_url}<?=$v['controller']?>/<?=$v['method']?>" target="_blank">link</a><br />
				<a href="/route/_del/<?=base64_encode($v['controller'])?>/<?=base64_encode($v['method'])?>/<% offset %>"><% lang:del %></a>
			</td>
			<td><?=$v['controller']?></td>
			<td><?=$v['method']?></td>
			<td><?=$v['controller_to']?></td>
			<td><?=$v['method_to']?></td>
			<td><?=$v['params_to']?></td>
			<td><?=$v['canonical']?'{site_url}'.$v['canonical']:'-'?></td>
			<td>
				<div class="show-top show-top-min" id="show-top-<?=$v['id']?>">
				<a href="javascript:;" onclick="$('#show-top-<?=$v['id']?>').toggleClass('show-top-min show-top-max')" title="<% lang:showall %>">...</a>
				<strong>Title:</strong> <?=$v['title']?$v['title']:'-'?><br />
				<strong>Description:</strong> <?=$v['description']?$v['description']:'-'?>
				</div>
			</td>
		</tr>
	<?php endforeach;?>
	</table>
<?php else:?>
<div class="alert">
<% lang:datanotfound %>
</div>
<?php endif;?>
