<?php
	$form_fields = $form->get_fields(%%form_id%%, '%%full_table%%');
//	$lang = '%%lang%%';
	$model = new Elf\App\Models\%%model%%;
//	$recs = $model->_select()->_where("`%%slave_field%%`='".$master_rec['%%master_field%%']."'")->_limit(20)->_execute();
	$recs = $form->get_related_widemulti_data(%%master_id%%, %%slave_id%%, !empty($master_rec['%%master_field%%'])?$master_rec['%%master_field%%']:0);
//	$top = %%top%%;
	$add_link = '<i class="fas fa-plus-circle" title="добавить запись"
		data-params="dialog=forms/widemulti_popup;field_name=%%rel_slave_field%%;getter=%%rel_getter%%;selected=0;link_field=%%slave_field%%;search_fields=%%rel_search_field%%;display_field=%%rel_display_field%%;slave_id=%%slave_id%%;slave_field=%%slave_field%%;getter_variable_name=%%getter_variable_name%%;caption=%%name%%;params=%%rel_params%%" onclick="showPopup($(this))"></i>';
	$tmpl_link = '<a href="javascript:;" data-params="dialog=%%fullpath%%;getter_variable_name=%%getter_variable_name%%;%%getter_variable_name%%=%%id%%;slave_field=%%slave_field%%;%%slave_field%%='.$master_rec['%%master_field%%'].';caption=%%name%%;is_related_form=%%form_id%%" onclick="showDialog(this)"><i class="fas fa-plus-circle"></i></a>';
?>
<fieldset id="elf-form-related-%%slave_id%%">
	<legend>%%name%% <?=$add_link?></legend>
	<table class="elf-form-related-recs">
		<tr class="elf-form-related-recs-top">
		<?php $cells_cnt = 0;foreach ($form_fields as $k=>$v): if ($v['show_in_related_data']): $cells_cnt ++;?>
			<td><?=$v['name']?$v['name']:'&nbsp;'?></td>
		<?php endif; endforeach;?>
		</tr>
		<tbody id="elf-form-related-recs-%%form_id%%">
		</tbody>
	<?php if ($recs):?>
			<?=Elf::load_template('forms/related_rec_line',['fields'=>$form_fields,
															'recs'=>$recs,
															'lang'=>'%%lang%%',
															'form_id'=>%%form_id%%,
															'add_link'=>$tmpl_link])?>
	<?php else:?>
	<tr><td colspan="<?=$cells_cnt?>"><div class="alert"><% lang:datanotfound %></div></td></tr>
	<?php endif;?>
	</table>
</fieldset>
<script>
$(function() {
	%%condition_rule%%
});
function _get_rec_by_field_name() {
	let v = $('select[name=%%rel_slave_field%%]').val();
	showWW();
	$.post('/forms/get_related_rec',{v:v,link_field:'%%slave_field%%',
										relwid:%%form_id%%,
										table:'%%full_table%%',
										lang:'%%lang%%',
										add_link:'<?=base64_encode($tmpl_link)?>'}, function(data) {
		hideWW();
		elf_form_related_rec_callback(data);
	}, 'json');
}
</script>
