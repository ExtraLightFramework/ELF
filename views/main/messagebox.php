	<script> 
	$(function() {
		modals_cnt.push('<% wid %>');
		$("#modal").attr('data-id','<% wid %>').css('display','block').css('opacity',0).animate({opacity:0.7},500);
		$("#<% wid %>").css('top',$("body").scrollTop()+30);
	});
	</script> 
	<div class="dialog messagebox" id="<% wid %>">
		<div class="top">
			<?=Elf::get_data('caption')?Elf::get_data('caption'):'<% lang:systemdialog %>'?>
			<div class="close-dialog-button" title="<% lang:closeunsave %>" <?=Elf::get_data('close_func')?'onclick="'.Elf::get_data('close_func').'"':''?> data-id="<% wid %>"></div>
		</div>
		<div class="text">
		<% message %>
		</div>
	</div>
	