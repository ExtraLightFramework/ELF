<div class="elf-page-not-found">
	<h1>Страница не найдена</h1>
	<div class="elf-page-not-found-404">404</div>
	<?php if (DEBUG_MODE):?>
		<div class="elf-page-not-found-page-url">/<?=Elf::routing()->controller()?>/<?=Elf::routing()->method()?></div>
	<?php endif;?>
	<div class="elf-page-not-found-main-url"><a href="<?=DIR_ALIAS?>/">Вернуться на главную</a></div>
</div>