<?php
	$forms = new Elf\Libs\Forms;
	$rec = $forms->get_field((int)Elf::$_data['field_id'], (int)Elf::$_data['fid'], Elf::get_data('table'), (bool)Elf::get_data('create'));
	//print_r($rec);
	
?>
<table class="form-fields-editor-blck" id="form-fields-editor-blck-<?=$rec['id']?>">
	<tr class="form-fields-editor form-fields-general-sett-<?=$rec['id']?>">
		<td class="form-fields-editor-mover">
			<div>
				<i class="fas fa-arrow-circle-up" title="<% lang:forms:move.field.up %>" onclick="_form_fields_editor_move(<?=$rec['id']?>, 'up')"></i>
				<i class="fas fa-arrow-circle-down" title="<% lang:forms:move.field.down %>" onclick="_form_fields_editor_move(<?=$rec['id']?>, 'down')"></i>
				<i class="fas fa-times-circle" title="<% lang:forms:remove.field %>" onclick="_remove_form_field(<?=$rec['form_id']?>, <?=$rec['id']?>)"></i>
			</div>
			<?=$rec['field_name_selector']?>
		</td>
		<td colspan="2"><?=$rec['type_selector']?></td>
	</tr>
	<tr class="form-fields-general-sett form-fields-general-sett-<?=$rec['id']?>">
		<td id="form-field-sett-cont-<?=$rec['id']?>">
			<h6 class="field-title"><% lang:forms:field.visible.name %></h6>
			<input type="text" data-name="name" name="name" placeholder="<% lang:forms:field.visible.name %>"
				value="<?=!empty($rec['name'])?$rec['name']:''?>" onblur="_save_field_data(this.value, 'name', <?=$rec['id']?>)" />
			<input type="hidden" name="title" value="<?=$rec['title']?>" />
			<input type="hidden" name="required" value="<?=$rec['required']?>" />
			<input type="hidden" name="placeholder" value="<?=$rec['placeholder']?>" />
			<input type="hidden" name="pattern" value="<?=$rec['pattern']?>" />
			<input type="hidden" name="autocomplete" value="<?=$rec['autocomplete']?>" />
			<input type="hidden" name="show_in_related_data" value="<?=$rec['show_in_related_data']?>" />
			<input type="hidden" name="size" value="<?=$rec['size']?>" />
			<?php if (!empty($rec['params'])): foreach ($rec['params'] as $k=>$v):?>
			<input type="hidden" name="params[<?=$k?>]" value="<?=$v?>" id="<?=$rec['id']?>.params.<?=$v?>" />
			<?php endforeach; endif;?>
		</td>
		<td class="form-field-cntrl-cont">
			<h6 class="field-title"><% lang:forms:field.default.value %></h6>
			<div id="form-field-cntrl-cont-<?=$rec['id']?>">
			<?php if (!empty($rec['type'])):?>
				<?=Elf::load_template('forms/template_type_'.$rec['type'],['field_id'=>$rec['id'],
																			'size'=>$rec['size'],
																			'default_value'=>$rec['default_value']])?>
			<?php endif;?>
			</div>
		</td>
		<td class="form-field-sett-icon">
			<i class="fas fa-sliders-h" data-params="dialog=forms/edit_field_settings;appearance=appearances/wide;field_id=<?=$rec['id']?>;fid=<?=$rec['form_id']?>;table_name=<?=$rec['table_name']?>" onclick="showDialog(this)"></i>
		</td>
	</tr>
</table>