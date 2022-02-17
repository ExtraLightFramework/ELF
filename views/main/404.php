<h1>Запрашиваемая страница не найдена</h1>
<?php if (DEBUG_MODE):?>
	<?=Elf::routing()->controller()?>/<?=Elf::routing()->method()?><br />
<?php endif;?>
<a href="<?=DIR_ALIAS?>/">Вернуться на главную</a>
