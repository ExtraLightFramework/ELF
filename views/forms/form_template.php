<?php
	$form = new Elf\Libs\Forms;
	$form_fields = $form->get_fields(%%form_id%%, '%%full_table%%');
	$model = new Elf\App\Models\%%model%%;
?>
%%getter%%
<?php
	$master_rec = $rec;
	if (!empty(Elf::$_data)) foreach (Elf::$_data as $k=>$v) $rec[$k] = $v;
?>
<script>
function _remove_related_rec(id, slave_id) {
	showWW();
	$.post('/forms/remove_related_rec',{id:id,slave_id:slave_id},function(data) {
		hideWW();
		$('#elf-form-related-rec-'+slave_id+'-'+id).remove();
	},'json');
}
function elf_form_check_new_rec() {
	if (<?=!empty($master_rec['id'])?(int)$master_rec['id']:0?> == 0) {
		if (confirm('Внимание! Это новая запись. При таком закрытии окна никакие данные не будут сохранены. Подтвердите свое действие.'))
			hideDialog(<% wid %>);
	}
	else
		hideDialog(<% wid %>);
}
$(function() {
	<?php foreach ($form_fields as $k=>$v): if (Elf::isset_data($k) && !Elf::isset_data($k.'_visibility')): ?>
	$('#elf-form-field-%%form_id%%-<?=$k?>').hide();
	<?php endif; endforeach;?>
%%script%%
});
</script>
<?php if (Elf::get_data('is_related_form')):?>
<form action="/forms/save_related_rec" method="%%method%%" class="elf-forms ajax-request" id="elf-form-%%form_id%%" data-close-form-id="<% wid %>" data-callback="elf_form_related_rec_callback;%%js_callback%%">
	<input type="hidden" name="table" value="%%full_table%%" />
	<input type="hidden" name="model" value="%%model%%" />
	<input type="hidden" name="lang" value="%%lang%%" />
	<input type="hidden" name="relwid" value="<% is_related_form %>" />
	<input type="hidden" name="master_field" value="<% master_field %>" />
	<input type="hidden" name="master_id" value="<% master_id %>" />
	<input type="hidden" name="add_link" value="<?=base64_encode(Elf::get_data('add_link'))?>" />
<?php elseif (Elf::get_data('ajax_request_forced')):?>
<form action="%%action%%" method="%%method%%" class="elf-forms ajax-request" id="elf-form-%%form_id%%" data-close-form-id="<% wid %>" data-callback="%%js_callback%%">
<?php else:?>
<form action="%%action%%" method="%%method%%" class="elf-forms %%ajax_request%%" id="elf-form-%%form_id%%" data-close-form-id="<% wid %>" data-callback="%%js_callback%%">
<?php endif;?>
	<input type="hidden" name="form_id" value="%%form_id%%" />
	<input type="hidden" name="redirect" value="<?=Elf::get_data('redirect')?Elf::get_data('redirect'):'%%redirect%%'?>" />
%%fields%%
</form>
%%related_forms%%
	<div class="cntr">
		<input type="submit" class="cntrl sbmt" value="<% lang:save %>" onclick="return _init_related_data(<% wid %>, %%form_id%%)" form="elf-form-%%form_id%%" />
		<input type="button" class="cntrl cncl" value="<% lang:cancel %>" onclick="<?=Elf::get_data('close_func')?Elf::get_data('close_func'):'hideDialog(<% wid %>)'?>" form="elf-form-%%form_id%%" />
	</div>
	<div class="elf-forms-system-info">
	<?php
		$user = new Elf\Libs\Users;
	?>
	Создано <?=!empty($master_rec['tm_created'])?date('d.m.Y H:i', $master_rec['tm_created']):'-'?>, пользователем <?=!empty($master_rec['initiator_id'])&&($u=$user->get_by_id($master_rec['initiator_id']))?$u['name']:'-'?><br />
	Изменено <?=!empty($master_rec['tm_edited'])?date('d.m.Y H:i', $master_rec['tm_edited']):'-'?>, пользователем <?=!empty($master_rec['editor_id'])&&($u=$user->get_by_id($master_rec['editor_id']))?$u['name']:'-'?>
	</div>
