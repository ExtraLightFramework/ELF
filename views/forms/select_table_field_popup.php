<?php
	$form = new Elf\Libs\Forms;
	$tables = $form->_tables_selector(Elf::get_data('table'),'table','onchange="_add_edit_field(<% form_id %>, this.value)"');
?>
<?=$tables?>
<!-- <div id="table-fields-selector-cont">
----- выберите таблицу -----
</div> -->