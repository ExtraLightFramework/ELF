function ELF_pagination_showmore(cont,url,insert_type) {
	showWW();
	$.post(url,{},function(data) {
		hideWW();
		if (data.status && data.status == 'empty') {
			let i = 1;
		}
		else {
			switch (insert_type) {
				case 'insertin':
					$('#'+cont).append(data.data);
					break;
				case 'insertafter':
					$('#'+cont).after(data.data);
					break;
			}
			$("#elf-pagination-showmore").html(data.butt);
		}
	},'json');
}
