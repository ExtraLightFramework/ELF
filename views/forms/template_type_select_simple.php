<select name="default_value" onchange="_save_field_data(this.value, 'default_value', <% field_id %>)">
	<?php if (Elf::get_data('size')) foreach (explode("\n",Elf::get_data('size')) as $v) echo '<option value="'.$v.'">'.$v.'</option>';?>
</select>
