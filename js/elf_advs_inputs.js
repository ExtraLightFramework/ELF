// Advanced input objects ELF

var elf_advs_inputs = {};

// Clever SELECTor with searching input and autoloader data
function ELF_CleverSelector(obj) {
	this.id = "sco-" + parseInt(Math.random()*1000000);
	this.getter = obj.attr('data-getter');
	this.selected = obj.attr('data-selected');
	this.link_field = obj.attr('data-link-field');
	this.display_field = obj.attr('data-display-field');
	this.search_fields = obj.attr('data-search-fields');
	if (!this.display_field || !this.link_field || !this.search_fields) {
		showBaloon('The clever selector with name '+obj.attr('name')+' not working! Required fields missing');
		return;
	}
	this.offset = 0;
	this.step = parseInt(obj.attr('data-step'));
	this.name = this.id+'-'+obj.attr('name');
	this.searcher_id = this.id+'-searcher';
	this.alert_id = this.id+'-alert';
	this.add_id = this.id+'-add-cntrl';
	this.clear_id = this.id+'-clear-cntrl';
	this.link_params = obj.attr('data-params');
	this.searching_full = false;
	if (this.onchange = obj.attr('onchange'))
		this.onchange = this.onchange.replace(/\(\)/g,"");
//////////////////////////////////////////////////////////////	
	obj.parent().append('<div id="'+this.id+'" class="select-clever-options-cont auto-hider">'+
							'<i data-scops-id="'+this.id+'-scops" class="select-clever-search-cntrl fas fa-plus-circle" id="'+this.add_id+'" title="добавить новую запись" '+(this.link_params?'data-params="'+this.link_params+'" onclick="showDialog(this)"':'onclick="showBaloon(\'Не заданы параметры добавления новой записи\')"')+'></i>'+
							'<i data-scops-id="'+this.id+'-scops" class="select-clever-search-cntrl fas fa-times" id="'+this.clear_id+'" title="очистить результаты и строку поиска"></i>'+
							'<input type="text" class="select-clever-searcher" data-scops-id="'+this.id+'-scops" id="'+this.searcher_id+'">'+
							'<div class="select-clever-options" id="'+this.id+'-scops"></div>'+
							'<div class="select-clever-search-alert" id="'+this.alert_id+'">Пока ничего не найдено...<br />Для начала поиска введите не менее 3-х символов</div>'+
							'</div>');
	obj.attr('data-trgt',this.id).attr('id',this.name);
	$("#"+this.id).css({left:obj.position().left+'px',width:obj.width()+'px'}).hide();
	elf_advs_inputs[this.id+'-scops'] = this;
	this._init(obj[0]);
}

ELF_CleverSelector.prototype = {
	
	constructor: ELF_CleverSelector,
	
	_init: function(obj) {
		let _obj = this;
		document.getElementById(this.id+'-scops').addEventListener('scroll',this._scroll);
		document.getElementById(this.id+'-scops').addEventListener('wheel',() => {event.preventDefault();return false;});
		document.getElementById(this.searcher_id).addEventListener('input',this._search);
		document.getElementById(this.searcher_id).addEventListener('focus',this._search_focus);
		document.getElementById(this.searcher_id).addEventListener('blur',this._search_blur);
		document.getElementById(this.clear_id).addEventListener('click',this._clear_search);
		
		this._init_selected(this.selected);

		obj.onclick = function() {
			_obj._getter(true);
			obj.style.display = 'none';
			setTimeout(function(){obj.style.display = 'inline-block';},50);
			$('#'+this.getAttribute('data-trgt')).show();
		}
		
		$(document).on('click','#'+this.id+' .select-clever-option',function() {
			if ($("#"+_obj.name+' > option[value='+$(this).attr('value')+']').length) {
				$("#"+_obj.name+' > option[value='+$(this).attr('value')+']').prop('selected','selected');
			}
			else
				$("#"+_obj.name).append('<option value="'+$(this).attr('value')+'" selected="selected">'+$(this).text()+'</option>');
			$("#"+_obj.id).hide();
			if (window[_obj.onchange])
				window[_obj.onchange]();
		});
	},
	_init_selected: function(sel) {
		let _obj = this;
		if (sel) {
			$.post(this.getter, {selected:sel, link_field:this.link_field}, function(data) {
				if (data)
					if (data.exception)
						showBaloon(data.exception);
					else if (data.data && data.data[_obj.display_field]) {
						$("#"+_obj.name).append('<option value="'+data.data[_obj.link_field]+'" selected="selected">'+data.data[_obj.display_field]+'</option>');
					}
			}, 'json');
		}
	},
	_getter: function(clear) {
		let _obj = this;
		if (clear)
			$('#'+this.id).find('div.select-clever-options').html('');
		showWW();
		$.post(this.getter, {data:true,offset:this.offset}, function(data) {
			hideWW();
			if (data.exception)
				showBaloon(data.exception);
			else if (data.data) {
				let appnd = '';
				for (let i = 0; i < data.data.length; i ++) {
					appnd += '<div class="select-clever-option" value="'+data.data[i][_obj.link_field]+'" id="select-clever-option-'+data.data[i][_obj.link_field]+'">';
					appnd += data.data[i][_obj.display_field];
					appnd += '</div>';
				}
				$('#'+_obj.id).find('div.select-clever-options').append(appnd);
			}
		}, 'json');
	},
	_scroll: function() {
		let obj = $('#'+this.id);
		let _es = obj.find('div.select-clever-option:first-child').height()*(parseInt(obj.find('div.select-clever-option').length)-10);
		if (obj.scrollTop() > _es) {
			elf_advs_inputs[this.id].offset += elf_advs_inputs[this.id].step;
			elf_advs_inputs[this.id]._getter(false);
		}
	},
	_search: function() {
		let _obj = elf_advs_inputs[this.getAttribute('data-scops-id')];
		let srch = $('#'+this.id);
		let v = srch.val();
		$('#'+_obj.id).find('div.select-clever-options').html('');
		if (v.length >= 3) {
			showWW();
			$.post(_obj.getter, {search:_obj.search_fields, value:v, offset:_obj.offset}, function(data) {
				hideWW();
				if (data.exception)
					showBaloon(data.exception);
				else if (data.data) {
					let appnd = '';
					for (let i = 0; i < data.data.length; i ++) {
						appnd += '<div class="select-clever-option" value="'+data.data[i][_obj.link_field]+'" id="select-clever-option-'+data.data[i][_obj.link_field]+'">';
						appnd += data.data[i][_obj.display_field];
						appnd += '</div>';
					}
					$('#'+_obj.id).find('div.select-clever-options').append(appnd);
					$('#'+_obj.id).find('div.select-clever-search-alert').hide();
					_obj.searching_full = true;
				}
				else {
					_obj.searching_full = false;
					$('#'+_obj.id).find('div.select-clever-search-alert').show();
				}
			}, 'json');
		}
		else {
			_obj.searching_full = false;
			$('#'+_obj.id).find('div.select-clever-search-alert').show();
		}
	},
	_search_focus: function() {
		let _obj = elf_advs_inputs[this.getAttribute('data-scops-id')];
		$('#'+_obj.id).find('div.select-clever-options').html('');
		$('#'+_obj.id).find('div.select-clever-search-alert').show();
		$('#'+_obj.id).find('.select-clever-search-cntrl').show().stop().animate({opacity:1},300);
	},
	_search_blur: function() {
		let _obj = elf_advs_inputs[this.getAttribute('data-scops-id')];
		$('#'+_obj.id).find('.select-clever-search-cntrl').stop().animate({opacity:0},300,function(){$(this).hide()});
		if (!_obj.searching_full) {
			$('#'+_obj.alert_id).hide();
			document.getElementById(_obj.searcher_id).value = '';
			_obj._getter(true);
		}
	},
	_clear_search: function() {
		let _obj = elf_advs_inputs[this.getAttribute('data-scops-id')];
		$('#'+_obj.alert_id).hide();
		document.getElementById(_obj.searcher_id).value = '';
//		_obj._getter(true);
	}
}

// Enumenator selector
function ELF_EnumenatorSelector(obj) {
	this.id = 'sao-'+parseInt(Math.random()*1000000);
	this.step = obj.attr('data-step');
	this.name = this.id+'-'+obj.attr('name');
	this.h_offset = obj.attr('data-horizontal-offset')?parseInt(obj.attr('data-horizontal-offset')):0;
	if (this.onchange = obj.attr('onchange'))
		this.onchange = this.onchange.replace(/\(\)/g,"");
////////////////////////////////////////////////////////////////
	obj.parent().append('<div id="'+this.id+'" class="select-alt-options auto-hider"></div>');
	obj.attr('id',this.name).attr('data-trgt',this.id);
	elf_advs_inputs[this.id] = this;
	this._init(obj);
}

ELF_EnumenatorSelector.prototype = {
	
	constructor: ELF_EnumenatorSelector,
	
	_init: function(obj) {
		let _obj = this;
		this.selected = parseInt(obj.attr('data-selected'));
		let _s = parseInt(obj.attr('data-start'));//parseInt(obj.find('option:first-child').val());
		let _e = parseInt(obj.attr('data-end'));//parseInt(obj.find('option:last-child').val());
		for (let _i = _s; _i <= _e; _i ++) {
			obj.append('<option value="'+_i+'" '+(_i==this.selected?'selected="selected"':'')+'>'+_i+'</option>');
			$('#'+this.id).append('<div class="option" data-value="'+_i+'">'+_i+'</div>');
		}
		$('#'+this.id).attr('data-parent',obj[0].id).css({left:(obj.position().left+this.h_offset)+'px'});
		document.getElementById(this.id).addEventListener('scroll',this._scroll);
		document.getElementById(this.id).addEventListener('wheel',() => {event.preventDefault();return false;});
		
		obj[0].onclick = function() {
			obj[0].style.display = 'none';
			setTimeout(function(){obj[0].style.display = 'inline-block';},50);
			$('#'+this.getAttribute('data-trgt')).show().scrollTop(0).find('div').removeClass('option-selected');
			let _tp = $('#'+this.getAttribute('data-trgt')).find('div[data-value='+(elf_advs_inputs[this.getAttribute('data-trgt')].selected)+']').addClass('option-selected').position().top;
			$('#'+this.getAttribute('data-trgt')).scrollTop(_tp-150);
		}
		
		$(document).on('click','#'+this.id+' .option',function() {
			_obj.selected = $(this).attr('data-value');
			$('#'+_obj.name).find('option[value='+$(this).attr('data-value')+']').prop('selected','selected');
			$(this).parent().hide();
			if (window[_obj.onchange])
				window[_obj.onchange]();
		});
	},
	_scroll: function() {
		let obj = $('#'+this.id);
		let _ss = obj.find('div.option:first-child').height()*10;
		let _es = obj.find('div.option:first-child').height()*obj.find('div.option').length;
		let _i = 0;
		if (obj.scrollTop() < _ss) {
			let _y = parseInt(obj.find('div.option:first-child').attr('data-value'));
			while (_i++ < parseInt(elf_advs_inputs[this.id].step)) {
				obj.prepend('<div class="option" data-value="'+(_y-_i)+'">'+(_y-_i)+'</div>');
				$('#'+obj.attr('data-parent')).prepend('<option value="'+(_y-_i)+'">'+(_y-_i)+'</option>');
			}
		}
		if (obj.scrollTop() > _es) {
			let _y = parseInt(obj.find('div.option:last-child').attr('data-value'));
			while (_i++ < parseInt(elf_advs_inputs[this.id].step)) {
				obj.append('<div class="option" data-value="'+(_y+_i)+'">'+(_y+_i)+'</div>');
				$('#'+obj.attr('data-parent')).append('<option value="'+(_y+_i)+'">'+(_y+_i)+'</option>');
			}
		}
		event.preventDefault();
		return false;
	}
}
