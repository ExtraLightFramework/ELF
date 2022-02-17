function rem_content_tag(tid,cid) {
	showWW();
	$.post("/tags/del_tag_content",{tid:tid,cid:cid},function(data) {
		hideWW();
		if (typeof data.ok != 'undefined') {
			$("#ctag-"+tid+"-"+cid).remove();
			$("#freq-"+tid).text(data.freq);
		}
		else
			alert(data.error);
	},'json');
}
