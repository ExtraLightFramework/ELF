	<script> 
	$(function() {
		modals_cnt.push('<% wid %>'); //.attr('data-id','<% wid %>')
		$("#modal").css('display','block').css('opacity',0).animate({opacity:0.7},500);
		$("#<% wid %>").css('top',$("body").scrollTop()+30);
		$(window).scrollTop(0);
		_help_tooltip_creator();
	});
	</script> 
	<div class="dialog" id="<% wid %>" data-app="appearance /views/main/dialog">
		<div class="top">
			<?=Elf::get_data('caption')?Elf::get_data('caption'):'<% lang:systemdialog %>'?>
			<div class="close-dialog-button" title="<% lang:closeunsave %>" <?=Elf::get_data('close_func')?'onclick="'.Elf::get_data('close_func').'"':''?> data-id="<% wid %>"></div>
		</div>
		<div class="text">
		<?=Elf::load_template(Elf::get_data('dialog'))?>
		</div>
	</div>
	<script>
	// Slider Form Init
	$(".slider-frm form").each(function() {
		let _w = $(this).closest('.slider-frm').width();
		$(this).width(_w);
	});

	// INIT advanced inputs
	$('.elf-advs-input').each(function() {
		if ($(this).hasClass('elf-clever-selector'))
			new ELF_CleverSelector($(this));
		if ($(this).hasClass('elf-enumenator-selector'))
			new ELF_EnumenatorSelector($(this));
	});
	</script>