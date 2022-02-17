<div id="adm-mnu">
    <a href="{site_url}route" title="системный роутинг" <?=Elf::routing()->controller()=='route'?'class="selected"':''?>>Роутинг</a>
    <a href="{site_url}settings" title="установки системы" <?=Elf::routing()->controller()=='settings'?'class="selected"':''?>>Установки</a>
    <a href="{site_url}tags" title="хэш-теги системы" <?=Elf::routing()->controller()=='tags'?'class="selected"':''?>>Хэш-теги</a>
    <a href="{site_url}forms" title="формы системы" <?=Elf::routing()->controller()=='forms'?'class="selected"':''?>>Формы</a>
    <!-- insert below module items [DO NOT REMOVE THIS]-->

    <!-- module items end -->
    <a href="{site_url}" title="перейти на сайт" target="_blank">На сайт</a>
    <a href="{site_url}admin/logout" title="выйти из системы">Выход</a>
</div>
