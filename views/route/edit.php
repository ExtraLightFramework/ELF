<?php
	$route = new \Elf\Libs\Routing;
	if (!Elf::input()->get('rid') 
		|| !($rec = $route->get_by_id((int)Elf::input()->get('rid'))))
		$rec = null;
?>
<form action="{site_url}route/edit/<% offset %>" method="post">
	<input type="hidden" name="id" value="<?=!empty($rec['id'])?$rec['id']:0?>" />
	<table class="dialog">
		<tr>
			<th><% lang:route:controller.source %>:</th>
			<td><input type="text" name="controller" value="<?=!empty($rec['controller'])?$rec['controller']:''?>" /></td>
		</tr>
		<tr>
			<th><% lang:route:method.source %>:</th>
			<td><input type="text" name="method" value="<?=!empty($rec['method'])?$rec['method']:''?>" />
			</td>
		</tr>
		<tr>
			<th><% lang:route:controller.acceptor %>:</th>
			<td><input type="text" name="controller_to" value="<?=!empty($rec['controller_to'])?$rec['controller_to']:''?>" /></td>
		</tr>
		<tr>
			<th><% lang:route:method.acceptor %>:</th>
			<td><input type="text" name="method_to" value="<?=!empty($rec['method_to'])?$rec['method_to']:''?>" /></td>
		</tr>
		<tr>
			<th><% lang:route:getparams %>:</th>
			<td><input type="text" name="params_to" value="<?=!empty($rec['params_to'])?$rec['params_to']:''?>" /></td>
		</tr>
		<tr>
			<th><% lang:route:canonicalink %>:</th>
			<td>{site_url}<input type="text" name="canonical" class="nowide" size="25" value="<?=!empty($rec['canonical'])?$rec['canonical']:''?>" /></td>
		</tr>
		<tr>
			<th><% lang:route:lastroute %>:</th>
			<td><input type="checkbox" name="is_last" <?=!empty($rec['is_last'])?'checked="checked"':''?> class="nowide" /></td>
		</tr>
	</table>
	
	<fieldset>
	<legend>SEO</legend>
	<table class="dialog">
		<tr>
			<th>Title:</th>
			<td><input type="text" name="title" value="<?=!empty($rec['title'])?$rec['title']:''?>" /></td>
		</tr>
		<tr>
			<th>Description:</th>
			<td><textarea name="description" rows="5"><?=!empty($rec['description'])?$rec['description']:''?></textarea></td>
		</tr>
		<tr>
			<th>Keywords:</th>
			<td><textarea name="keywords" rows="4"><?=!empty($rec['keywords'])?$rec['keywords']:''?></textarea></td>
		</tr>
	</table>
	</fieldset>
	<table class="dialog">
		<tr>
			<td class="cntr">
			<input type="submit" class="cntrl sbmt" value="<% lang:save %>" />
			<input type="button" class="cntrl cncl" value="<% lang:cancel %>" onclick="hideDialog(<% wid %>)" />
			</td>
		</tr>
	</table>
			
</form>