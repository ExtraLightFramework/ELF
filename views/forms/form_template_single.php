<?php
	$form_fields = $form->get_fields(%%form_id%%, '%%full_table%%');
	$model = new Elf\App\Models\%%model%%;
	$rec = $model->get("`%%slave_field%%`='".$master_rec['%%master_field%%']."'");
?>
<?php
	if (!empty($rec)) foreach ($rec as $k=>$v) if (Elf::isset_data($k)) $rec[$k] = Elf::get_data($k);
?>
<fieldset id="elf-form-related-%%slave_id%%">
	<legend>%%name%%</legend>
	<form action="%%action%%" method="%%method%%" class="elf-forms elf-form-related">
		<input type="hidden" name="slave_id" value="%%form_id%%" />
		<input type="hidden" name="%%slave_field%%" value="<?=$master_rec['%%master_field%%']?>" class="elf-form-related-data" data-related-form-id="%%form_id%%" />
	%%fields%%
	</form>
</fieldset>
<script>
$(function() {
	<?php foreach ($form_fields as $k=>$v): if (Elf::isset_data($k) && !Elf::isset_data($k.'_visibility')): ?>
	$('#elf-form-field-%%form_id%%-<?=$k?>').hide();
	<?php endif; endforeach;?>
	%%condition_rule%%
});
</script>
