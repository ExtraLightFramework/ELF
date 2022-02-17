<?php
	$tags = new Elf\Libs\Tags;
	if (!($rec = $tags->_get_full((int)Elf::$_data['tid'])))
		$rec = null;
?>
<form action="{site_url}tag/edit/<% offset %>" method="post">
	<input type="hidden" name="id" value="<?=!empty($rec['id'])?$rec['id']:0?>" />
	<table class="dialog">
		<tr>
			<th><% lang:tags:name %>:</th>
			<td><input type="text" name="htag" value="<?=!empty($rec['htag'])?$rec['htag']:''?>" /></td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="cntr">
			<input type="submit" class="cntrl sbmt" value="<% lang:save %>" />
			<input type="button" class="cntrl cncl" value="<% lang:cancel %>" onclick="hideDialog(<% wid %>)" />
			</td>
		</tr>
	</table>
</form>