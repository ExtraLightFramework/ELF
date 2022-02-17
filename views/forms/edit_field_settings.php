<% field_type %>
<table class="form-field-sett-editor" id="form-field-sett-editor-<% field_id %>">
	<tr>
		<td><% lang:forms:field.visible.name %></td>
		<td><input type="text" name="name" value="" /></td>
	</tr>
	<tr>
		<td><% lang:forms:field.default.value %></td>
		<td><input type="text" name="default_value" value="" /></td>
	</tr>
	<tr>
		<td><% lang:forms:field.size %></td>
		<td id="field-size-cont-<% field_id %>"></td>
	</tr>
	<tr>
		<td><% lang:forms:field.title %></td>
		<td><input type="text" name="title" value="" /></td>
	</tr>
	<tr>
		<td><% lang:forms:field.required %></td>
		<td><input type="checkbox" name="required" class="nowide" /></td>
	</tr>
	<tr>
		<td><% lang:forms:field.placeholder %></td>
		<td><input type="text" name="placeholder" value="" /></td>
	</tr>
	<tr>
		<td><% lang:forms:field.pattern %></td>
		<td><input type="text" name="pattern" value="" /></td>
	</tr>
	<tr>
		<td><% lang:forms:field.autocomplete %></td>
		<td><input type="checkbox" name="autocomplete" class="nowide" /></td>
	</tr>
	<tr>
		<td><% lang:forms:field.showinrealdata %></td>
		<td><input type="checkbox" name="show_in_related_data" class="nowide" /></td>
	</tr>
</table>
<center>
	<input type="button" value="<% lang:save %>" class="cntrl sbmt" onclick="_save_settings_values()" />
	<input type="button" class="cntrl cncl" value="<% lang:cancel %>" onclick="hideDialog(<% wid %>)" />
</center>
<script>
var _sett_frm_obj = null;
$(function() {
	// Init settings values
	$('.form-fields-general-sett-<% field_id %> input').each(function() {
		if ($('#form-field-sett-editor-<% field_id %> input[name="'+$(this).attr('name')+'"]').attr('type')=='checkbox') {
			if (parseInt($(this).val()))
				$('#form-field-sett-editor-<% field_id %> input[name="'+$(this).attr('name')+'"]').prop('checked',true);
		}
		else
			$('#form-field-sett-editor-<% field_id %> input[name="'+$(this).attr('name')+'"]').attr('value',$(this).val());
	});
	_sett_frm_obj = $('#form-field-sett-editor-<% field_id %>');
	///////////////
	let out = {};
	out['field_id'] = <% field_id %>;
	out['type'] = $('#form-field-type-<% field_id %>').val();
	switch (out['type']) {
		case 'string':
		case 'int':
		case 'float':
		case 'checkbox':
		case 'hidden':
			out['template'] = 'forms/edit_field_size_simple';
			out['size'] = $('#form-field-name-<% field_id %> option:selected').attr('data-size');
			$.post('/elf/loadtemplate',out, data => $('#field-size-cont-<% field_id %>').html(data));
			break;
		case 'text':
		case 'wysiwyg':
			out['template'] = 'forms/edit_field_size_text';
			out['size'] = $('#form-field-name-<% field_id %> option:selected').attr('data-size');
//			out['rows'] = $('#<% field_id %>.params.rows').val() || 3;
			out['rows'] = $('.form-fields-general-sett-<% field_id %> input[name="params[rows]"]').val() || 3;
			$.post('/elf/loadtemplate',out, data => $('#field-size-cont-<% field_id %>').html(data));
			break;
		case 'select_simple':
		case 'select_enum':
		case 'radio':
			out['template'] = 'forms/edit_field_size_select';
			out['size'] = $('.form-fields-general-sett-<% field_id %> input[name=size]').val();
			$.post('/elf/loadtemplate',out, data => $('#field-size-cont-<% field_id %>').html(data));
			break;
		case 'link':
			out['template'] = 'forms/edit_field_size_link';
			out['size'] = $('.form-fields-general-sett-<% field_id %> input[name=size]').val();
			$.post('/elf/loadtemplate',out, data => $('#field-size-cont-<% field_id %>').html(data));
			break;
		case 'picture':
			out['template'] = 'forms/edit_field_size_picture';
			out['size'] = '-';//$('.form-fields-general-sett-<% field_id %> input[name=size]').val();
			out['model'] = $('.form-fields-general-sett-<% field_id %> input[name="params[model]"]').val() || '';
			$.post('/elf/loadtemplate',out, data => $('#field-size-cont-<% field_id %>').html(data));
			break;
	}
});
function _ch_settings_value(obj) {
	return;
	$('.form-fields-general-sett-<% field_id %> input[data-name='+obj.attr('name')+']').attr('value',obj.attr('type')=='checkbox'?(obj.prop('checked')?1:0):obj.val());
}
function _set_settings_values() {
	_sett_frm_obj.find('input,select,textarea').each(function() {
		$('.form-fields-general-sett-<% field_id %> input[name="'+$(this).attr('name')+'"]').attr('value',$(this).attr('type')=='checkbox'?($(this).prop('checked')?1:0):$(this).val());
	});
}
function _save_settings_values() {
	hideDialog(<% wid %>);
	showWW();
	_set_settings_values();
	let out = {};
	_sett_frm_obj.find('input,select,textarea').each(function() {
//		$('.form-fields-general-sett-<% field_id %> input[name="'+$(this).attr('name')+'"]').attr('value',$(this).attr('type')=='checkbox'?($(this).prop('checked')?1:0):$(this).val());
		out[$(this).attr('name')] = $(this).attr('type')=='checkbox'?($(this).prop('checked')?1:0):$(this).val();
	});
//	$('.form-fields-general-sett-<% field_id %> input,.form-fields-general-sett-<% field_id %> select,.form-fields-general-sett-<% field_id %> textarea').each(function() {
//		out[$(this).attr('name')] = $(this).val();
//	});
	out['field_id'] = '<% field_id %>';
	out['fid'] = '<% fid %>';
	out['table_name'] = '<% table_name %>';
	$.post('/forms/save_field_settings',out);//,() => hideWW());
	_change_field_type($('#form-field-type-<% field_id %>').val(), <% field_id %>);
}
</script>