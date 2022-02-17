	<td>
		<?php
			switch (Elf::get_data('type')) {
				case 'select_simple':
				case 'select_enum':
				case 'radio':
					echo Elf::lang(Elf::get_data('lang'))->item(Elf::get_data('value'));
					break;
				case 'checkbox':
					echo Elf::get_data('value')?'<i class="far fa-check-square"></i>':'<i class="far fa-square"></i>';
					break;
				case 'eval':
					switch (Elf::get_data('default_value')) {
						case 'CURRENT_TIMESTAMP':
							echo (int)Elf::get_data('value')?date('d.m.Y', (int)Elf::get_data('value')):'&nbsp;';
							break;
						default:
							echo Elf::get_data('value');
							break;
					}
					break;
				default:
					echo Elf::get_data('value');
					break;
			}
		?>
		<?php if (Elf::get_data('field_name') == 'id'):?>
		<input type="hidden" name="id[<% value %>]" value="<% value %>" class="elf-form-related-data" data-related-form-id="<% is_related_form %>" />
		<br /><a href="javascript:;" data-params="dialog=<% dialog %>;getter_variable_name=<% getter_variable_name %>;<% getter_variable_name %>=<% value %>;slave_field=<% slave_field %>;<% slave_field %>=<?=Elf::get_data(Elf::get_data('slave_field'))?>;caption=<% name %>;is_related_form=<% is_related_form %>" onclick="showDialog(this)"><% lang:edt %></a>
		<?php endif;?>
	</td>
