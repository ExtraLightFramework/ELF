<?php
	$forms = new Elf\Libs\Forms;
	$size = (array)json_decode(base64_decode(Elf::get_data('size')));
	$selector = $forms->link_tables_selector(!empty($size['table'])?$size['table']:'', Elf::get_data('field_id'));
	echo $selector;
?>