<?php
	$forms = new Elf\Libs\Forms;
	$size = (array)json_decode(base64_decode(Elf::get_data('size')));
	$selector = $forms->link_tables_selector(!empty($size['table'])?$size['table']:'', Elf::get_data('field_id'), Elf::get_data('size'));
	if (!empty($size['table'])) {
		$link_field_selector = $forms->get_link_type_fields($size['table'], Elf::get_data('field_id'), 'link_field', Elf::get_data('size'));
		$search_fields_selector = $forms->get_link_type_fields($size['table'], Elf::get_data('field_id'), 'search_fields', Elf::get_data('size'));
		$display_field_selector = $forms->get_link_type_fields($size['table'], Elf::get_data('field_id'), 'display_field', Elf::get_data('size'));
	}
	echo $selector;
?>
<div class="link-type-size" id="link-type-size-<% field_id %>" <?=empty($size['table'])?'style="display:none;"':''?>>
	<table>
		<tr>
			<td id="link-field-<% field_id %>">
				<h6 class="field-title"><% lang:forms:link.field %></h6>
				<?=!empty($link_field_selector)?$link_field_selector:''?>
			</td>
			<td id="link-display-field-<% field_id %>">
				<h6 class="field-title"><% lang:forms:display.field %></h6>
				<?=!empty($display_field_selector)?$display_field_selector:''?>
			</td>
			<td id="link-search-fields-<% field_id %>">
				<h6 class="field-title"><% lang:forms:search.fields %></h6>
				<?=!empty($search_fields_selector)?$search_fields_selector:''?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<h6 class="field-title"><% lang:forms:field.data.getter %></h6>
				<input type="text" name="getter" value="<?=!empty($size['getter'])?$size['getter']:''?>" onblur="_save_link_size($(this),'getter',<% field_id %>,'<% size %>')" />
				<h6 class="field-title"><% lang:forms:field.data.params %></h6>
				<input type="text" name="params" value="<?=!empty($size['params'])?$size['params']:''?>" onblur="_save_link_size($(this),'params',<% field_id %>,'<% size %>')" />
			</td>
		</tr>
	</table>
</div>