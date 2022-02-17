<select name="default_value" onchange="_save_field_data(this.value, 'default_value', <% field_id %>)">
	<option value="ONCE_TIMESTAMP" <?=Elf::get_data('default_value')=='ONCE_TIMESTAMP'?'selected="selected"':''?>>Текущее время (статич.)</option>
	<option value="ONCE_USERID" <?=Elf::get_data('default_value')=='ONCE_USERID'?'selected="selected"':''?>>Текущий польз-ль (статич.)</option>
	<option value="CURRENT_TIMESTAMP" <?=Elf::get_data('default_value')=='CURRENT_TIMESTAMP'?'selected="selected"':''?>>Текущее время</option>
	<option value="CURRENT_USERID" <?=Elf::get_data('default_value')=='CURRENT_USERID'?'selected="selected"':''?>>Текущий польз-ль</option>
	<option value="CURRENT_SESSID" <?=Elf::get_data('default_value')=='CURRENT_SESSID'?'selected="selected"':''?>>Текущая сессия (ИД сессии)</option>
</select>
