<?php
	$form_fields = $form->get_fields(%%form_id%%, '%%full_table%%');
	$model = new Elf\App\Models\%%model%%;
	$recs = $model->_select()->_where("`%%slave_field%%`='".$master_rec['%%master_field%%']."'")->_limit(20)->_execute();
	$add_link = '<a href="javascript:;" data-params="dialog=%%fullpath%%;getter_variable_name=%%getter_variable_name%%;%%getter_variable_name%%=%%id%%;master_id=%%master_id%%;master_field=%%master_field%%;slave_field=%%slave_field%%;%%slave_field%%='.$master_rec['%%master_field%%'].';caption=%%name%%;is_related_form=%%form_id%%" onclick="showDialog(this)"><i class="fas fa-plus-circle"></i></a>';
?>
<fieldset id="elf-form-related-%%slave_id%%">
	<legend>%%name%% <?=str_replace('%%id%%','0',$add_link)?></legend>
	<table class="elf-form-related-recs">
		<tr class="elf-form-related-recs-top">
		<?php $cells_cnt = 0;foreach ($form_fields as $k=>$v): if ($v['show_in_related_data']): $cells_cnt ++;?>
			<td><?=$v['name']?$v['name']:'&nbsp;'?></td>
		<?php endif; endforeach;?>
			<td>-</td>
		</tr>
		<tbody id="elf-form-related-recs-%%form_id%%">
		</tbody>
	<?php if ($recs):?>
			<?=Elf::load_template('forms/related_rec_line',['fields'=>$form_fields,
															'recs'=>$recs,
															'lang'=>'%%lang%%',
															'form_id'=>%%form_id%%,
															'frm'=>$form->get_form_with_related_data(%%master_id%%,%%slave_id%%)])?>
	<?php else:?>
	<tr id="elf-form-related-datanotfound-%%slave_id%%"><td colspan="<?=$cells_cnt+1?>"><div class="alert"><% lang:datanotfound %></div></td></tr>
	<?php endif;?>
	</table>
</fieldset>
<script>
$(function() {
	%%condition_rule%%
});
</script>
