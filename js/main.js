var modals_cnt = [];
var alert_hide_delay = 3000;
var alert_rem_delay = 1000;
var _prev_scroll_window = 0;
var _scroll_on_top = true;
var _scroll_un_top = false;
var currentPopup = null;
var hidePopup = null;
var _hideBaloon = null;
var _help_delay = null;

// Create Base64 Object
var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}


function alert_hider(obj) {
	obj.stop().animate({opacity:0},alert_rem_delay,function(){obj.remove();});
}
function create_wyswyg(id) {
	if (typeof CKEDITOR.instances[id] == 'undefined')
		CKEDITOR.replace(id);
}
function getCookie(name) {
	var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}
function setCookie(name, val, exp) {
	document.cookie = name+'='+val+'; max-age='+(typeof exp != 'undefined'?parseInt(exp):3600);
}
function accept_cookie_agreement(id) {
	setCookie('cookie_agreement', true, 86400);
	$('#'+id).stop().animate({opacity: 0}, 500, () => location.reload());
}
function showWaitWnd(txt) {
	if (txt)
		$('#wait-window .txt').text(txt).show();
	$('#wait-window').show();
}
function hideWaitWnd() {
	setTimeout(function(){$('#wait-window').hide().find('.txt').hide();}, 150);
}
function showWW(txt) {
	showWaitWnd(txt);
}
function hideWW() {
	hideWaitWnd();
}
function showDialog(obj) {
	let parms = typeof obj['data-params'] != 'undefined'?obj['data-params']:(obj.getAttribute?obj.getAttribute('data-params'):null);
	if (parms) {
		showWW();
		let mas = parms.split(';');
		let out = {};
		for (let i = 0; i < mas.length; i ++) {
			let arr = mas[i].split('=');
			out[arr[0]] = arr[1];
		}
		$.post('/elf/showdialog',out,function(data){hideWW();_showDialog(data);});
	}
	else
		alert('Attribute data-params not found');
}

function _showDialog(data) {
//	console.log(data);
	let first = data.indexOf('{');
	let last = data.lastIndexOf('}');
	if (last>=0 && first>=0 && (first < last)) {
		data = data.slice(first,last+1);
		data = JSON.parse(data);
		if (data) {
			if (data.exception)
				showBaloon(data.exception);
			else if (data.error)
				alert(data.error);
			else if (data.dialog) {
				$("body").append(data.dialog);
				$('textarea.ckeditor').each(function() {
					if (typeof CKEDITOR.instances[$(this).attr('id')] != 'undefined')
						CKEDITOR.instances[$(this).attr('id')].destroy();
					create_wyswyg($(this).attr('id'));
				});
			}
			else if (data.redirect)
				location.href = data.redirect;
			else
				alert(data);
		}
	}
	else
		alert(data);
		
}
function hideDialogsByModal() {
	if (modals_cnt.length) {
		let id = modals_cnt.pop();
		hideDialog(id);
	}
}
function hideDialog(id) {
	if (modals_cnt.length) {
		let _new = [];
		modals_cnt.forEach(function(item, i, arr) {
			if (item != id) {
				_new.push(item);
			}
		});
		modals_cnt = _new;
	}
	if (!modals_cnt.length) {
		$('body').css('overflow-y','auto');
		$("#modal").animate({opacity:0},500,function(){$(this).hide();});
		modals_cnt = [];
	}
	$("#"+id).remove();
}
function showPopup(obj) {
	_hidePopup();
	try {
		let parms = obj.attr('data-params')?obj.attr('data-params'):obj.attr('params');
		if (parms != '' && parms != undefined)
		{
			let mas = parms.split(';');
			let out = {};
			for (let i = 0; i < mas.length; i ++) {
				let arr = mas[i].split('=');
				out[arr[0]] = arr[1];
			}
			let tp = obj.offset();
			out.top = (parseInt(tp.top))+'px';
			out.left = (parseInt(tp.left)-20)+'px';
			$.post('/elf/showpopup',out,function(data){currentPopup = _showPopup(data);},'json');
		}
	}
	catch(e) {
		showBaloon(e.message);
	}
}
function _showPopup(data) {
	_hideTooltip();
	$("body").append(data.popup);
	let _w = $('#'+data.pid).css('left', 0).width();
	$('#'+data.pid).hide();
	let _off = $(window).width() - parseInt(data.left) - _w;
	if (_off < 0)
		$('#'+data.pid).css('left',(parseInt(data.left) + _off - 10)+'px');
	else
		$('#'+data.pid).css('left',data.left);
	$('#'+data.pid).show();
//	alert($('#'+data.pid).width()+' '+data.left+' '+$(window).width());
	return data.pid;
}
function _hidePopup() {
	if (currentPopup) {
		$("#"+currentPopup).remove();
		currentPopup = null;
	}
}
function showTooltip(obj) {
	var info = obj.attr('data-info');
	if (info != '' && info != undefined) {
		var tp = obj.offset();
		var out = {info:info, top:(tp.top+parseInt(obj.css('height'))-20)+'px', left:tp.left+'px'};
		$.post('/elf/showtooltip',out,function(data){currentTooltip = _showPopup(data);},'json');
	}
}
function _hideTooltip() {
	$("div.tooltip").remove();
}
function showBaloon(t) {
	var _bid = 'baloon-'+parseInt(Math.random()*1000000);
	$("body").append('<div class="system-baloon" id="'+_bid+'">'+t.replace(/\n/g,'<br /><br />')+'<div class="close-baloon" onclick="hideBaloon(\''+_bid+'\')"><i class="far fa-times-circle"></i></div></div>');
	var _h = $("#"+_bid).height()+35;//+$(window).scrollTop();
	$("div.system-baloon").each(function() {
		if ($(this).attr('id')==_bid)
			$(this).stop().animate({top:'+=100px'},500);
		else
			$(this).stop().animate({top:'+='+_h},500);
	});
	_hideBaloon = setTimeout(function(){hideBaloon(_bid)},5000);
}
function hideBaloon(id) {
	$("#"+id).stop().animate({opacity:0},300,function(){$(this).remove()});
}
function _help_tooltip_creator() {
	$(".help").each(function() {
		if (!$(this).attr('data-title') && $(this).attr('title')) {
			$(this).attr('data-title',$(this).attr('title')).removeAttr('title');
			$(this).append('<div class="help-tooltip" id="'+('hwid-'+parseInt(Math.random()*1000000))+'"><div class="arr arr-l"></div><div class="tlt">'+$(this).attr('data-title')+'</div></div>');			
		}
	});
}
function translit(str, delim) {
	let ret = '';
	if (typeof str != 'undefined' && str) {
		let space = typeof delim != 'undefined'?delim:'-';
		let transl = {
			'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e', 'ж': 'zh',
			'з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
			'о': 'o', 'п': 'p', 'р': 'r','с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h',
			'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch','ъ': space,
			'ы': 'y', 'ь': space, 'э': 'e', 'ю': 'yu', 'я': 'ya'
		};
		str = str.toLowerCase();
	 
		for (var i = 0; i < str.length; i++){
			if (/[а-яё]/.test(str.charAt(i))){ // заменяем символы на русском
				ret += transl[str.charAt(i)];
			} else if (/[a-z0-9]/.test(str.charAt(i))){ // символы на анг. оставляем как есть
				ret += str.charAt(i);
			} else {
				if (ret.slice(-1) !== space) ret += space; // прочие символы заменяем на space
			}
		}
	}
    return ret;
}
function reloadCaptcha(name, len) {
	$.post('/elf/reload_captcha',{name:typeof name==='undefined'?'captcha':name,
									len:typeof len==='undefined'?4:parseInt(len)},
		function(data) {
			$('#'+(typeof name==='undefined'?'captcha':name)).attr('src', data);
		}
	);
}

$(function() {
	_help_tooltip_creator();
	$(document).on('click',"#modal",hideDialogsByModal)
				.on('click',"div.close-dialog-button",function() {
					hideDialog($(this).attr('data-id'));
				}).on('mouseup',function (e) {
					if (jQuery(e.target).closest(".auto-hider").length > 0) {
						return false;
					}
					else $(".auto-hider").hide();
				});
	$(window).scroll(function() {
		if ($(this).scrollTop() && !_scroll_un_top && _prev_scroll_window>=100) { //not up
			$("div.top-panel-hd").stop().animate({top:'0px'},250);
			_scroll_un_top = true;
			_scroll_on_top = false;
		}
		else if ($(this).scrollTop()<70 && !_scroll_on_top) { //in up
			$("div.top-panel-hd").stop().animate({top:'-74px'},250);
			_scroll_on_top = true;
			_scroll_un_top = false;
		}
		_prev_scroll_window = $(this).scrollTop();
		// == elf-scroll-top-btn
		if (!_scroll_on_top)
			$('#elf-scroll-top-btn').css({display:'block'}).stop().animate({opacity:'.5'}, 300);
		else
			$('#elf-scroll-top-btn').stop().animate({opacity:'0'},300,function(){$(this).css({display:'none'})});
	});
	$(document).on('mouseout','div.popup',function() {
		hidePopup = setTimeout('_hidePopup()',800);
	}).on('mouseover','div.popup',function() {
		clearTimeout(hidePopup);
	}).on('click','div.popup a',function() {
		_hidePopup();
	});
	$(document).on("mouseenter","div.system-baloon",function() {
		clearTimeout(_hideBaloon);
	}).on("mouseleave","div.system-baloon",function() {
		let _bid = $(this).attr('id');
		_hideBaloon = setTimeout(function(){hideBaloon(_bid)},2000);
	}).on("click","div.system-baloon", function() {
		hideBaloon($(this).attr('id'));
	});
	$('label.elf-radio').each(function() {
		$(this).append('span');
		$(this).find('span').append('div.slider');
	});
	$(document).on("mouseover click",".help",function(e) {
		clearTimeout(_help_delay);
		if (_h_wid = $(this).find('div.help-tooltip').attr('id')) {
			$("#"+_h_wid).find('div.arr').height($("#"+_h_wid).height());
			$("#"+_h_wid).css({top:-($("#"+_h_wid).height()/2-$(this).height()/2)+'px'}).show();
		}
	}).on("mouseleave",".help",function(e) {
		_help_delay = setTimeout(function(){$("div.help-tooltip").hide();},100);
	});
	$(document).on('mouseenter',"div.help-tooltip",function() {
		clearTimeout(_help_delay);
	}).on('mouseleave',"div.help-tooltip",function() {
		_help_delay = setTimeout(function(){$("div.help-tooltip").hide();},100);
	}).on('click',"div.help-tooltip",function() {
		$("div.help-tooltip").hide();
	});
	$(document).on("focus","input[type=text],input[type=password],textarea",function() {
		if (!$(this).val()) {
			$(this).attr('_placeholder',$(this).attr('placeholder'));
			$(this).attr('placeholder','');
		}
	}).on("blur","input[type=text],input[type=password],textarea",function() {
		if (!$(this).val()) {
			$(this).attr('placeholder',$(this).attr('_placeholder'));
		}
	});
	$(document).on('click','table.list tr.list-data',function() {
		$(this).parent().find('tr.list-data').removeClass('list-data-clicked');
		$(this).addClass('list-data-clicked');
	}).on('dblclick','table.list .list-data',function() {
		$(this).find('a.list-item-edt').click();
	});;
// ===== SLIDER FORM INIT
	$(".slider-frm form").each(function() {
		let _w = $(this).closest('.slider-frm').width();
		$(this).width(_w);
	});
	$(document).on("click",".slider-frm-back",function() {
		let _off = $(this).closest('form').width();
		$(this).closest('.frm-cont').stop().animate({marginLeft:'+='+_off+'px'});
	});
// ===== AJAX Links: <a href="<url>" class="ajax-lnk" data-cont="<container ID>" data-upd-type="<append|prepend|replace>" />
	$(document).on('click','a.ajax-lnk',function() {
		showWW();
		let _self = $(this);
		event.preventDefault();
		$.post($(this).attr('href'), function(data) {
//			alert(data.data);
			hideWW();
			if (data.data) {
				if (_self.attr('data-cont')) {
					switch (_self.attr('data-upd-type')) {
						case 'replace':
							$("#"+_self.attr('data-cont')).html(data.data);
							break;
						case 'prepend':
							$("#"+_self.attr('data-cont')).prepend(data.data);
							break;
						case 'after':
							if (_self.attr('data-node')) {
								$("#"+_self.attr('data-node')).after(data.data);
							}
							else
								$("#"+_self.attr('data-cont')).append(data.data);
							break;
						default:
						case 'append':
							$("#"+_self.attr('data-cont')).append(data.data);
							break;
					}
				}
				else
					alert("1: "+data.data)
			}
			else if (data.error)
				alert(data.error);
		}, 'json');
	});
// ===== AJAX Show Dialog Lnk 
	$(document).on('click','a.show-dialog-request', function() {
		event.preventDefault();
		history.pushState('', '', $(this).attr('href'));
		showDialog($(this).get()[0]);
	});
// ===== AJAX form requestor	
	$(document).on('submit','form.ajax-request',function(e) {
		var frm = $(this);
		e.preventDefault();
		let arr = frm.serializeArray(), params = {};
		$.each(arr, function (idx, el) {
			let n = el.name.replace(/\[\]/g, '');
			if (typeof el.value == 'undefined')
				el.value = null;
			if (params[n]) {
				if (!Array.isArray(params[n])) {
					let v = params[n];
					params[n] = [];
					params[n].push(v);
				}
				params[n].push(el.value);
			}
			else
				params[n] = el.value;
		});
		showWW();
		if (frm.attr('action')) {
			let _aerr = setTimeout(() => {hideWW();alert("Can't execute action [function: main.js/form.ajax-request.submit, action: "+frm.attr('action')+"]")}, 5000);
			console.log(params);
			$.post(frm.attr('action'), params, function(data) {
				hideWW();
				clearTimeout(_aerr);
				if (typeof data != 'object')
					alert(data);
				else if (data && data.error) {
					showBaloon(data.error);
					console.log(data.error);
				}
				else if (data && data.exception) {
					showBaloon(data.exception);
					console.log(data.exception);
				}
				else {
					if (data && data.message) {
						showBaloon(data.message);
					}
					if (frm.attr('data-callback')) {
						let funcs = frm.attr('data-callback').split(';');
						for (let i = 0; i < funcs.length; i ++) {
							if (window[funcs[i]])
								window[funcs[i]](data);
							else {
								showBaloon('Function '+funcs[i]+' not found!');
								console.log('Function '+funcs[i]+' not found!');
							}
						}
					}
//					else if (frm.attr('data-callback'))
//						alert('Function '+frm.attr('data-callback')+' not found!');
					if (frm.attr('data-close-form-id'))
						hideDialog(frm.attr('data-close-form-id'));
					else if (frm.closest('.slider-frm').length) {
						let _off = frm.width();
						frm.closest('.frm-cont').stop().animate({marginLeft:'-='+_off+'px'});
					}
				}
			}, 'json');
		}
	});
});
