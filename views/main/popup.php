	<div class="popup" id="<% wid %>" style="top:<% top %>;left:<% left %>">
		<?=Elf::load_template(Elf::$_data['dialog'])?>
	</div>
	<script>
		// INIT advanced inputs
		$('.elf-advs-input').each(function() {
			if ($(this).hasClass('elf-clever-selector'))
				new ELF_CleverSelector($(this));
			if ($(this).hasClass('elf-enumenator-selector'))
				new ELF_EnumenatorSelector($(this));
		});
	</script>