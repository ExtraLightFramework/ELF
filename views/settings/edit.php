<?php
	$sett = new \Elf\Libs\Settings;
	if (!Elf::get_data('sid') 
		|| !($rec = $sett->get_by_id((int)Elf::get_data('sid')))) {
		$rec = null;
	}
?>

<form action="{site_url}settings/_edit" method="post">
	<input type="hidden" name="id" value="<?=!empty($rec['id'])?$rec['id']:0?>" />
	<table class="dialog">
		<tr>
			<th><% lang:name %>:</th>
			<td><input type="text" required="required" name="name" value="<?=!empty($rec['name'])?$rec['name']:''?>" /></td>
		</tr>
		<tr>
			<th><% lang:value %>:</th>
			<td><input type="text" id="settings-value" name="value" value="<?=!empty($rec['value'])?$rec['value']:''?>" data-value="" /><br />
				<label><input type="checkbox" class="m-top nowide" id="md5hash" name="encrypt" onclick="_cryptuncrypt($('#md5hash').prop('checked'),'settings-value')" /> <% lang:settings:encyptvalmd5 %></label>
			</td>
		</tr>
		<tr>
			<th><% lang:settings:desc %>:</th>
			<td><textarea name="desc" rows="5"><?=!empty($rec['desc'])?$rec['desc']:''?></textarea></td>
		</tr>
		<tr>
			<th><% lang:settings:expireto %>:</th>
			<td><input type="text" class="date nowide" value="<?=!empty($rec['expire'])?date('d.m.Y',$rec['expire']):''?>" placeholder="<% lang:ddmmyyyy %>" id="sett_expire" name="expire" onfocus="this.select();lcs(this)"
    				onclick="event.cancelBubble=true;this.select();lcs(this)" readonly="readonly" /> <a href="javascript:;" onclick="$('#sett_expire').attr('value','')">неогр.</a></td>
		</tr>
		<tr>
			<td colspan="2" class="cntr">
			<input type="submit" class="cntrl sbmt" value="<% lang:save %>" />
			<input type="button" class="cntrl cncl" value="<% lang:cancel %>" onclick="hideDialog(<% wid %>)" />
			</td>
		</tr>
	</table>
</form>
