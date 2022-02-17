function _init_related_data(wid, form_id) {
	let related_data = {};
	let len = false;
	$('#'+wid+' .elf-form-related-data').not(':disabled').each(function() {
		let fid = $(this).attr('data-related-form-id');
		if (!related_data[fid])
			related_data[fid] = {};
		related_data[fid][$(this).attr('name')] = $(this).val();
		len = true;
	});
	if (len) {
		$("#elf-form-"+form_id).append('<input type="hidden" name="related_data" value="'+Base64.encode(JSON.stringify(related_data))+'" />');
	}
}
function _sw_related_form(fid, status) {
	setTimeout(() => {
		switch (status) {
			case 'show':
				$("#elf-form-related-"+fid).show();
				$("#elf-form-related-"+fid+" .elf-form-related-data").removeProp('disabled');
				break;
			case 'hide':
				$("#elf-form-related-"+fid).hide();
				$("#elf-form-related-"+fid+" .elf-form-related-data").prop('disabled','disabled');
				break;
		}
	}, 300);
}
function elf_form_related_rec_callback(data) {
	if (data.error)
		showBaloon(data.error);
	else if (data.exception)
		showBaloon('Exception: '+data.exception);
	else if (data.relwid) {
		$('#elf-form-related-datanotfound-'+data.relwid).hide();
		if (data.newrec)
			$('#elf-form-related-recs-'+data.relwid).prepend(data.data);
		else
			$('#elf-form-related-rec-'+data.relwid+'-'+data.rec_id).replaceWith(data.data);
	}
}
