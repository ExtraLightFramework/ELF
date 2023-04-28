<form method="post" class="form-signin" action="<?=Elf::get_data('action')?Elf::get_data('action'):Elf::site_url().'auth/auth/'.Elf::get_data('group').'/'.Elf::get_data('redirect')?>">
	<input type="hidden" name="datain" value="1" />
	<input name="login" type="text" class="form-control" placeholder="Логин / номер телефона" value="<% login %>" required autofocus autocomplete="off" />
	<input name="passwd" type="password" class="form-control" placeholder="Пароль" required />
	<br /><label><input name="remme" type="checkbox" class="form-control nowide" /> - запомнить на этом компьютере</label>
	<div class="cntr">
	<input class="cntrl sbmt" type="submit" value="Войти" />
	<input class="cntrl" type="button" value="Отмена" onclick="hideDialog(<% wid %>)" />
	</div>
</form>