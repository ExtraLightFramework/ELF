<?php
	$forms = new Elf\Libs\Forms;
	$rec = $forms->get_by_id((int)Elf::$_data['fid']);
?>
<div class="flex">
<div style="width:100%;">
	<form action="{site_url}forms/edit/<% offset %>" method="post" id="frm-edit-form">
		<input type="hidden" name="id" value="<?=!empty($rec['id'])?$rec['id']:0?>" />
		<table class="dialog">
			<tr>
				<th><% lang:forms:name %>:</th>
				<td><input type="text" name="name" value="<?=!empty($rec['name'])?$rec['name']:''?>" required="required" /> <sup class="red">*</sup></td>
			</tr>
			<tr>
				<th><% lang:forms:method %>:</th>
				<td><?=$forms->create_select('method',!empty($rec['method'])?$rec['method']:'','required="required"')?></td>
			</tr>
			<tr>
				<th><% lang:forms:getter %>:</th>
				<td><input type="text" name="getter" value="<?=!empty($rec['getter'])?$rec['getter']:''?>" /></td>
			</tr>
			<tr>
				<th><% lang:forms:getter_vari_name %>:</th>
				<td><input type="text" name="getter_variable_name" value="<?=!empty($rec['getter_variable_name'])?$rec['getter_variable_name']:''?>" /></td>
			</tr>
			<tr>
				<th><% lang:forms:action %>:</th>
				<td><input type="text" name="action" value="<?=!empty($rec['action'])?$rec['action']:''?>" /></td>
			</tr>
			<tr>
				<th><% lang:forms:redirect %>:</th>
				<td><input type="text" name="redirect" value="<?=!empty($rec['redirect'])?$rec['redirect']:''?>" /></td>
			</tr>
			<tr>
				<th><% lang:forms:ajax_request %>:</th>
				<td><input type="checkbox" class="nowide" name="ajax_request" <?=!empty($rec['ajax_request'])?'checked="checked"':''?> /></td>
			</tr>
			<tr id="js-callback-cont" <?=''//empty($rec['ajax_request'])?'style="display:none;"':''?>>
				<th><% lang:forms:js_callback %>:</th>
				<td><input type="text" name="js_callback" value="<?=!empty($rec['js_callback'])?$rec['js_callback']:''?>" /></td>
			</tr>
			<tr>
				<th><% lang:forms:model %>:</th>
				<td><input type="text" name="model" value="<?=!empty($rec['model'])?$rec['model']:''?>" /></td>
			</tr>
			<tr>
				<th><% lang:forms:table %>:</th>
				<td><?=!empty($rec['tables'])?$rec['tables']:''?> <sup class="red">*</sup></td>
			</tr>
			<tr>
				<th><% lang:forms:path %>:</th>
				<td><input type="text" name="path" value="<?=!empty($rec['fullpath'])?pathinfo($rec['fullpath'],PATHINFO_DIRNAME):''?>" required="required" />/ <sup class="red">*</sup></td>
			</tr>
			<tr>
				<th><% lang:forms:filename %>:</th>
				<td><input type="text" name="filename" value="<?=!empty($rec['fullpath'])?str_replace(EXT,'',basename($rec['fullpath'])):''?>" required="required" />.php<sup class="red">*</sup></td>
			</tr>
			<tr>
				<th><% lang:forms:lang %>:</th>
				<td><input type="text" name="lang" value="<?=!empty($rec['lang'])?$rec['lang']:''?>" />.lng</td>
			</tr>
		</table>
	</form>
		<fieldset id="form-fields" class="form-proc-blck" style="<?=empty($rec['id'])?'display:none;':''?>">
			<i class="form-fields-cntrl fas fa-minus-square" title="<% lang:forms:wrap %>" onclick="_block_wrapper($(this))"></i>
			<legend><% lang:forms:fieldslist %>
			<a href="javascript:;" onclick="_add_edit_field(<?=!empty($rec['id'])?$rec['id']:0?>)" title="<% lang:forms:addfield.tlt %>"><i class="fas fa-plus-circle"></i></a>
			<a href="javascript:;" data-params="dialog=forms/select_table_field_popup;table=<?=!empty($rec['table'])?$rec['table']:''?>;form_id=<?=!empty($rec['id'])?$rec['id']:0?>" onclick="showPopup($(this))" title="<% lang:forms:addfield.another.tlt %>"><i class="far fa-object-ungroup"></i></a>
			</legend>
			<div id="form-fields-cont">
			</div>
		</fieldset>
		<fieldset id="form-related-forms" class="form-proc-blck">
			<i class="form-fields-cntrl fas fa-minus-square" title="<% lang:forms:wrap %>" onclick="_block_wrapper($(this))"></i>
			<legend><% lang:forms:relatedforms %>
			<a href="javascript:;" onclick="_add_related_form(<?=!empty($rec['id'])?$rec['id']:0?>)" title="<% lang:forms:addrelform.tlt %>"><i class="fas fa-plus-circle"></i></a>
			</legend>
			<div id="form-relforms-cont">
			</div>
		</fieldset>
		<table>
			<tr>
				<td class="cntr">
				<input type="button" class="cntrl sbmt" value="<% lang:save %>" onclick="$('#frm-edit-form').submit()" />
				<input type="button" class="cntrl cncl" value="<% lang:cancel %>" onclick="hideDialog(<% wid %>)" />
				</td>
			</tr>
		</table>
</div>
<?php if (!empty($rec['id'])):?>
<div style="width:100%;">
	<h5>Скрипт:</h5>
	<textarea name="script" id="elf-form-script" onblur="_save_script(<?=$rec['id']?>)" rows="15"><?=!empty($rec['script'])?base64_decode($rec['script']):''?></textarea>
</div>
<?php endif;?>
</div>
<script>
$(function() {
//	$('#frm-edit-form input[name=ajax_request]').on('click',function() {
//		if ($(this).prop('checked'))
//			$('#js-callback-cont').show();
//		else
//			$('#js-callback-cont').hide();
//	});
	_get_form_fields(<?=!empty($rec['id'])?$rec['id']:0?>);
	_get_related_forms(<?=!empty($rec['id'])?$rec['id']:0?>);
	<?php if (!empty($rec['redirect_orig'])):?>
	setTimeout(() => $('#frm-edit-form input[name=redirect]').attr('value',Base64.decode('<?=$rec['redirect_orig']?>')), 500);
	<?php endif;?>
	$('#elf-form-script').keydown(function(event) {
		if (event.keyCode == 9) {
			event.preventDefault();
			let textarea     = event.target
			let selStart     = textarea.selectionStart
			let selEnd       = textarea.selectionEnd
			let before       = textarea.value.substring( 0, selStart )
			let selection    = textarea.value.substring( selStart, selEnd )
			let after        = textarea.value.substr( selEnd )
			let selection_new= ''
			selStart++

			selection_new = selection.replace( /^/gm, ()=>{
				selEnd++
				return '\t'
			})

			textarea.value = before + selection_new + after

			// cursor
			textarea.setSelectionRange( selStart, selEnd )
		}
	});
});

/////////////// FIELDS functions
function _add_edit_field(fid,table) {
	showWW();
	let out = {};
	out['table'] = table?table:$('#frm-edit-form select[name=table]').val();
	out['field_id'] = 0;
	out['fid'] = fid?parseInt(fid):0;
	out['create'] = 1;
	$.post('/forms/get_form_field',out,function(data){
		hideWW();
		$('#form-fields-cont').append(data.data);
		_disabled_field_selector_opt(data.fid, false);
	}, 'json');
}
function _set_form_path(opt) {
	let _p = opt.text();
	let _s = $('#frm-edit-form input[name=path]').val().split('/');
	if (_s.length >= 2) {
		for (let i=1; i < _s.length; i ++) {
			_p += (_p?'/':'')+_s[i];
		}
	}
	
	_get_form_fields(<?=!empty($rec['id'])?$rec['id']:0?>);
	if (opt.val())
		$("#form-fields").show();
	else
		$("#form-fields").hide();
	$('#frm-edit-form input[name=path]').attr('value', _p);		
}
function _get_form_fields(fid) {
	if ($('#frm-edit-form select[name=table]').val()) {
		showWW();
		$.post('/forms/get_form_fields',{fid:fid,table:$('#frm-edit-form select[name=table]').val()}, function(data) {
			hideWW();
			$('#form-fields-cont').html(data);
			$('.form-field-selector').each(function() {
				if (!$('#form-field-sett-cont-'+$(this).attr('data-field-id')+' input[name=size]').val())
					$('#form-field-sett-cont-'+$(this).attr('data-field-id')+' input[name=size]').attr('value',$(this).find('option:selected').attr('data-size'));
			});
			$('.form-field-selector').each(function() {
				_disabled_field_selector_opt($(this).attr('data-field-id'), false);
			});
		});
	}
	else
		$('#form-fields-cont').html('');
}
function _remove_form_field(fid, field_id) {
	if (confirm('Подтвердите удаление')) {
		showWW();
		$.post('/forms/remove_form_field',{fid:fid,field_id:field_id},function(data) {
			hideWW();
			showBaloon('Поле удалено');
			$('#form-fields-editor-blck-'+field_id).remove();
		});
	}
}
function _disabled_field_selector_opt(fid, all) {
	if (!all) {
		$('#form-field-name-'+fid+' option').prop('disabled',false);
		$('.form-field-selector option:selected').each(function() {
			if ($(this).val() && ($(this).parent().attr('id') != 'form-field-name-'+fid))
				$('#form-field-name-'+fid+' option[value='+$(this).val()+']').prop('disabled',true);
		});
	}
	else {
		$('.form-field-selector option[value='+$('#form-field-name-'+fid).attr('data-old')+']').prop('disabled', false);
		$('.form-field-selector option[value='+$('#form-field-name-'+fid+' option:selected').val()+']').each(function() {
			if ($(this).parent().attr('id') != 'form-field-name-'+fid) {
				$(this).prop('disabled',true);
			}
		});
	}
}
function _save_field_data(v, sett, field_id) {
	showWW();
	let out = {};
	out[sett] = v;
	out['fid'] = <?=!empty($rec['id'])?$rec['id']:0?>;
	out['table_name'] = $('#frm-edit-form select[name=table]').val();
	out['field_id'] = field_id;
	$.post('/forms/save_field_settings',out,() => hideWW());
}
function _save_script(fid) {
	showWW();
	$.post('/forms/save_script',{fid:fid,script:$('#elf-form-script').val()},() => hideWW());
}
function _change_field_sett_names(v, field_id) {
	_disabled_field_selector_opt(field_id, true);
	$('#form-field-name-'+field_id).attr('data-old',v);
	$('#form-field-sett-cont-'+field_id+' input[name=size]').attr('value',$('#form-field-name-'+field_id+' option:selected').attr('data-size'));
	showWW();
	$.post('/forms/save_field_settings',{field_name:v,
										fid:<?=!empty($rec['id'])?$rec['id']:0?>,
										table_name:$('#frm-edit-form select[name=table]').val(),
										field_id:field_id,
										size:$('#form-field-name-'+field_id+' option:selected').attr('data-size')},() => hideWW());
}
function _change_field_type(t, field_id) {
	showWW();
	$.post('/elf/loadtemplate',{template:'forms/template_type_'+t,
								field_id:field_id,
								size:$('#form-field-sett-cont-'+field_id+' input[name=size]').val()?$('#form-field-sett-cont-'+field_id+' input[name=size]').val():$('#form-field-name-'+field_id+' option:selected').attr('data-size'), //
								default_value:''}, data => $('#form-field-cntrl-cont-'+field_id).html(data));
	$.post('/forms/save_field_settings',{type:t,
										fid:<?=!empty($rec['id'])?$rec['id']:0?>,
										table_name:$('#frm-edit-form select[name=table]').val(),
										field_id:field_id},() => hideWW());
}
function _save_link_size(obj, param, field_id, size) {
	let out = {};
	out[param] = '';
	if (obj.get(0).tagName == 'SELECT') {
		obj.find('option:selected').each(function() {
			out[param] += (out[param]?',':'') + $(this).val();
		});
	}
	else if (obj.get(0).tagName == 'INPUT')
		out[param] = obj.val();
	if (out[param]) {
		showWW();
		if (param == 'table') {
			$('#link-type-size-'+field_id).show();
			$('#link-type-size-'+field_id+' .link-type-fields-selector').remove();
			$.post('/forms/get_link_type_fields',{table:out[param],name:'link_field',field_id:field_id,size:size},data => $('#link-field-'+field_id).append(data));
			$.post('/forms/get_link_type_fields',{table:out[param],name:'search_fields',field_id:field_id,size:size},data => $('#link-search-fields-'+field_id).append(data));
		}
		out['field_id'] = field_id;
		out['fid'] = <?=!empty($rec['id'])?$rec['id']:0?>;
		out['table_name'] = $('#frm-edit-form select[name=table]').val();
		$.post('/forms/save_link_size',out, data => {$('#form-field-sett-cont-'+field_id+' input[name=size]').attr('value',data);hideWW()});
	}
	else {
		if (size) {
			$('#link-type-size-'+field_id).hide();
		}
	}
}
////////////// RELATED Forms
function _add_related_form(fid) {
	showWW();
	$.post('/forms/get_related_form',{fid:fid,slave_id:0,create:true},function(data) {
		hideWW();
		$('#form-relforms-cont').append(data);
	})
}
function _get_related_forms(fid) {
	if (fid) {
		showWW();
		$.post('/forms/get_related_forms',{fid:fid}, function(data) {
			hideWW();
			$('#form-relforms-cont').html(data);
		});
	}
}
function _set_slave_form(slave_id, rfid) {
	if (slave_id) {
		showWW();
		$.post('/forms/save_related_data',{slave_id:slave_id,id:rfid});
		$.post('/forms/get_table_fields_by_formid',{form_id:slave_id,name:'slave_field',rfid:rfid},function(data) {
			hideWW();
			$('#slave-fields-cont-'+rfid).html(data);
		});
	}
	else
		$('#slave-fields-cont-'+rfid).html('-----------');
}
function _save_related_data(field, name, rfid) {
	showWW();
	let out = {};
	out[name] = field;
	out['id'] = rfid;
	if (name=='relation_type') {
		if (field == 'widemulti')
			$('#widemulti-blck-'+rfid).show();
		else
			$('#widemulti-blck-'+rfid).hide();
	}
	$.post('/forms/save_related_data',out,(data) => {hideWW()});
}
function _get_relation_table_fields(table, rfid) {
//	showWW();
//	alert(table+' '+rfid);
	$.post('/forms/get_relation_table_fields',{table:table, rfid:rfid},function(data) {
		$('#reltable-master-fields-cont-'+rfid).html(data.rel_master_fields);
		$('#reltable-slave-fields-cont-'+rfid).html(data.rel_slave_fields);
	}, 'json');
}
///////////// SERVICE functions
function _form_fields_editor_move(field_id, direct) {
	let repl = false;
	switch (direct) {
		case 'up':
			if ($('#form-fields-editor-blck-'+field_id).prev()) {
				$('#form-fields-editor-blck-'+field_id).insertBefore($('#form-fields-editor-blck-'+field_id).prev());
				repl = true;
			}
			break;
		case 'down':
			if ($('#form-fields-editor-blck-'+field_id).next()) {
				$('#form-fields-editor-blck-'+field_id).insertAfter($('#form-fields-editor-blck-'+field_id).next());
				repl = true;
			}
			break;
	}
	if (repl) {
		showWW();
		$.post('/forms/ch_fields_pos',{field_id:field_id,direct:direct},() => hideWW());
	}
}
function _block_wrapper(obj) {
	let pid = obj.parent().attr('id');
	if (obj.hasClass('fa-minus-square')) {
		if (obj.attr('data-parent-height') != $('#'+pid).height()+'px')
			obj.attr('data-parent-height',$('#'+pid).height()+'px');
		obj.attr('title','<% lang:forms:unwrap %>');
		$('#'+pid).stop().animate({height:'5px'},300);
	}
	else {
		obj.attr('title','<% lang:forms:wrap %>');
		$('#'+pid).stop().animate({height:obj.attr('data-parent-height')},300);
	}
	obj.toggleClass('fa-minus-square fa-plus-square');
}
function _get_table_fields_selector(fid, table) {
	_add_edit_field(fid, table);
//	$.post('/forms/get_table_fields',{table:table},function(data) {
//		hideWW();
//		$('#table-fields-selector-cont').html(data);
//	});
}
</script>