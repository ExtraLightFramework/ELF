<?php
	$rec = Elf::get_data('rec');
?>
<table class="form-relform-editor-blck" id="form-relform-editor-blck-<?=$rec['id']?>">
	<tr class="form-relform-editor">
		<td>
			<h6 class="field-title"><% lang:forms:master.name %></h6>
			<input type="text" disabled="disabled" value="<?=!empty($rec['master_name'])?$rec['master_name']:Elf::lang('forms')->item('master.name.not.set')?>" />
		</td>
		<td>
			<h6 class="field-title"><% lang:forms:slave.name %></h6>
			<?=!empty($rec['form_selector'])?$rec['form_selector']:'----------------'?>
		</td>
	</tr>
	<tr class="form-relform-editor">
		<td>
			<h6 class="field-title"><% lang:forms:master.field %></h6>
			<?=!empty($rec['master_fields'])?$rec['master_fields']:'------------'?>
		</td>
		<td>
			<h6 class="field-title"><% lang:forms:slave.field %></h6>
			<div id="slave-fields-cont-<?=$rec['id']?>">
			<?=!empty($rec['slave_fields'])?$rec['slave_fields']:'------------'?>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<h6 class="field-title"><% lang:forms:relation.type %></h6>
			<?=!empty($rec['relation_type_selector'])?$rec['relation_type_selector']:'------------'?>
		</td>
	</tr>
	<tbody id="widemulti-blck-<?=$rec['id']?>" class="widemulti-blck" style="display:<?=!empty($rec['relation_type'])&&$rec['relation_type']=='widemulti'?'table-row-group':'none'?>;">
		<tr class="form-relform-editor widemulti-blck-<?=$rec['id']?>">
			<td colspan="2">
				<h6 class="field-title"><% lang:forms:relation.table.selector %></h6>
				<?=!empty($rec['relation_table_selector'])?$rec['relation_table_selector']:'------------'?>
			</td>
		</tr>
		<tr class="form-relform-editor widemulti-blck-<?=$rec['id']?>">
			<td>
				<h6 class="field-title"><% lang:forms:reltable.master.field %></h6>
				<div id="reltable-master-fields-cont-<?=$rec['id']?>">
				<?=!empty($rec['rel_master_fields'])?$rec['rel_master_fields']:'------------'?>
				</div>
			</td>
			<td>
				<h6 class="field-title"><% lang:forms:reltable.slave.field %></h6>
				<div id="reltable-slave-fields-cont-<?=$rec['id']?>">
				<?=!empty($rec['rel_slave_fields'])?$rec['rel_slave_fields']:'------------'?>
				</div>
			</td>
		</tr>
		<tr>
			<td id="rel-display-field-<?=$rec['id']?>">
				<h6 class="field-title"><% lang:forms:display.field %></h6>
				<?=!empty($rec['rel_display_fields'])?$rec['rel_display_fields']:''?>
			</td>
			<td id="rel-search-fields-<?=$rec['id']?>">
				<h6 class="field-title"><% lang:forms:search.fields %></h6>
				<?=!empty($rec['rel_search_fields'])?$rec['rel_search_fields']:''?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<h6 class="field-title"><% lang:forms:field.data.getter %></h6>
				<input type="text" name="rel_getter" value="<?=!empty($rec['rule']['rel_getter'])?$rec['rule']['rel_getter']:''?>" onblur="_save_related_data(this.value,'rule_rel_getter',<?=$rec['id']?>)" />
				<h6 class="field-title"><% lang:forms:field.data.params %></h6>
				<input type="text" name="rel_params" value="<?=!empty($rec['rule']['rel_params'])?$rec['rule']['rel_params']:''?>" onblur="_save_related_data(this.value,'rule_rel_params',<?=$rec['id']?>)" />
			</td>
		</tr>
	</tbody>
	<tr>
		<td colspan="2">
			<h6 class="field-title"><% lang:forms:related.rule %></h6>
			<select name="rule_type" onchange="_save_related_data(this.value,'rule_type',<?=$rec['id']?>)">
				<option value="always" <?=!empty($rec['rule']['type'])&&($rec['rule']['type']=='always')?'selected="selected"':''?>><% lang:forms:always %></option>
				<option value="condition" <?=!empty($rec['rule']['type'])&&($rec['rule']['type']=='condition')?'selected="selected"':''?>><% lang:forms:condition %></option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?=!empty($rec['rule_fields'])?$rec['rule_fields']:'------------'?> 
			<select class="nowide" onchange="_save_related_data(this.value,'rule_condition_oper',<?=$rec['id']?>)">
				<option value="">операция</option>
				<option value="==" <?=!empty($rec['rule']['condition_oper'])&&($rec['rule']['condition_oper']=='==')?'selected="selected"':''?>>равно</option>
				<option value="!=" <?=!empty($rec['rule']['condition_oper'])&&($rec['rule']['condition_oper']=='!=')?'selected="selected"':''?>>не равно</option>
			</select>
		</td>
		<td>
			<input type="text" name="rule_value" value="<?=!empty($rec['rule']['value'])?$rec['rule']['value']:''?>" onblur="_save_related_data(this.value,'rule_value',<?=$rec['id']?>)" />
		</td>
	</tr>
</table>
