<?php if (Elf::get_data('size')): foreach (explode("\n",Elf::get_data('size')) as $v)
	echo '<input type="radio" class="nowide" value="'.$v.'" name="default_value"> - '.$v.'</br>';
	else:
	echo Elf::lang('forms')->item('open.field.settings');
	endif;
?>