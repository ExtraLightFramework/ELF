class ELF_Uploader { // ELF Uploader v 2.0
	
/*	crop; // crop DIV object
	info; // info DIV object
	img; // img Container object
	file; // input for upload Container object
	imgLoaded = false;
	cropMove = false;
	buttDown = false;
	curCropMarker = null;
	cropHeight = 0;
	cropwidth = 0;
	static pasteCatcher = null;
	imgIsBlank = false;
	remBtn = null;
	static model = '';
	input = '';
	xhr = null;
	xhrCallback = null;
	x = 0;
	y = 0;
	mx = 0;
	my = 0;
*/
/*	multy = false;
	root = {};
	input_name;
	params;
	uploader = '';
	remover = '';
	croper = '';
	editable = false;
	
	static BLANK_IMAGE = '/img/elf_uploader/1x1-80pcn.png';
	static DEFAULT_BACKEND_PATH = '/';
	static OFFSET_CROP_BG = 0;
	static MIN_WIDTH = 120;
	static MIN_HEIGHT = 120;
*/	
	constructor(cont, model, input_name, file_name, multy, params) {
		try {
			ELF_Uploader.BLANK_IMAGE = '/img/elf_uploader/1x1-80pcn.png';
			ELF_Uploader.DEFAULT_BACKEND_PATH = '/';
			ELF_Uploader.OFFSET_CROP_BG = 0;
			ELF_Uploader.MIN_WIDTH = 120;
			ELF_Uploader.MIN_HEIGHT = 120;
			ELF_Uploader.CROP_SCALE = 1;
			ELF_Uploader.WINDOW_WIDTH = document.documentElement.clientWidth;
			ELF_Uploader.WINDOW_HEIGHT = document.documentElement.clientHeight;
			
			this.root = document.getElementById(cont);
			this.root.classList.add('elf-uploader-container');

			if (!model)
				throw new Error('Required field "model" is empty');
			this.model = model;
			if (!input_name)
				throw new Error('Required field "input_name" is empty');
			
			this.input_name = input_name;
			this.params = params;
			
			this.uploader = this.params&&("upload_func" in this.params)?this.params.upload_func:ELF_Uploader.DEFAULT_BACKEND_PATH+'upload.php';
			this.remover = this.params&&("rem_func" in this.params)?this.params.rem_func:ELF_Uploader.DEFAULT_BACKEND_PATH+'remfile.php';
			this.croper = this.params&&("crop_func" in this.params)?this.params.crop_func:ELF_Uploader.DEFAULT_BACKEND_PATH+'crop.php';
			this.multy = multy;
			if (this.params&&this.params.editable)
				this.editable = true;
			
			// calc CROP border width
			let obj = document.createElement('div');
			obj.classList.add('elf-uploader-crop');
			obj.style.display = 'none';
			this.root.append(obj);
			ELF_Uploader.OFFSET_CROP_BG = getComputedStyle(obj);
			ELF_Uploader.OFFSET_CROP_BG = parseInt(ELF_Uploader.OFFSET_CROP_BG.borderWidth) || 1;
			obj.remove();
			// -------------------------------------
			// calc MIN width&height for objs
			obj = document.createElement('div');
			obj.classList.add('elf-uploader');
			obj.style.display = 'none';
			this.root.append(obj);
			let wh = getComputedStyle(obj);
			ELF_Uploader.MIN_WIDTH = wh.minWidth?parseInt(wh.minWidth):ELF_Uploader.MIN_WIDTH;
			ELF_Uploader.MIN_HEIGHT = wh.minHeight?parseInt(wh.minHeight):ELF_Uploader.MIN_HEIGHT;
			obj.remove();
			// -------------------------------------
			
			// create empty UPLOAD place
			this.root.append(this._init(this.multy?'':file_name));
			// init files if set MULTY
			if (this.multy) {
				this.root.classList.add('elf-uploader-container-multy');
				if (file_name
					&& file_name.length) {
					let file;
					for (file of file_name) {
						if (file)
							this.root.append(this._init(file));
					}
				}
			}
		}
		catch(err) {
			alert('ELF_Uploader constructor error: '+err.message+' Stack: '+err.stack);
		}
	}
	
	_init(file_name) {
		try {
			let obj = document.createElement('div');
			let btn;
			obj.classList.add('elf-uploader','elf-uploader-item');
			if (this.multy)
				obj.classList.add('elf-uploader-multy');
			let _this = this;
			
			obj.input = document.createElement("input");
			obj.input.name = this.input_name+(this.multy?'[]':'');
			obj.input.type = 'hidden';
			obj.input.value = file_name?file_name:'';
			obj.append(obj.input);
			
			// ===== CropWnd INIT ======
			obj.cropwnd = document.createElement('div');
			obj.cropwnd.classList.add('elf-cropwnd');
			obj.cropwnd.id = "cropwnd-"+Math.random();
			obj.cropwnd.innerHTML = '<div class="elf-cropwnd-close" onclick="ELF_Uploader.hideCropDialog(\''+obj.cropwnd.id+'\')"><i class="far fa-times-circle"></i></div>';
			obj.objcrop = document.createElement('div');
			obj.objcrop.classList.add('elf-uploader-objcrop');
			obj.objcrop.img = document.createElement('img');
			obj.objcrop.img.classList.add('elf-uploader-cropimg');
			obj.objcrop.img.id = "img-"+obj.cropwnd.id;
			if (file_name) {
				obj.objcrop.img.src = file_name+'?'+Math.random();
			}
			obj.objcrop.append(obj.objcrop.img);
			obj.cropwnd.append(obj.objcrop);
			document.body.append(obj.cropwnd);

			
			// ===== Crop INIT =========
			obj.crop = document.createElement('div');
			obj.crop.style.display = 'none';
			obj.crop.classList.add('elf-uploader-crop');
			obj.crop.coof_w = obj.crop.coof_h = 0;
			//if (file_name)
			//	obj.crop.style.backgroundImage = "url('"+file_name+'?'+Math.random()+"')";
			
			btn = document.createElement('div');
			btn.title = 'Обрезать и сохранить';
			btn.addEventListener('click',() => this.cropImage(obj));
			btn.classList.add('elf-uploader-crop-ctrl','elf-uploader-crop-ctrl-left');
			btn.innerHTML = '<i class="fas fa-crop"></i>';
			obj.crop.append(btn);
			
			btn = document.createElement('div');
			btn.title = 'Отмена';
			btn.addEventListener('click',() => this.hideCrop(obj));
			btn.classList.add('elf-uploader-crop-ctrl','elf-uploader-crop-ctrl-right');
			btn.innerHTML = '<i class="fas fa-ban"></i>';
			obj.crop.append(btn);
			
			// --------- Crop Markers INIT
			let mrks = ['s','n','e','w','se','sw','ne','nw'];
			obj.crop.markers = {};
			mrks.forEach(function(mrk) {
				obj.crop.markers[mrk] = document.createElement('div');
				obj.crop.markers[mrk].classList.add('elf-uploader-crop-marker','elf-uploader-crop-marker-'+mrk);
				obj.crop.markers[mrk].addEventListener('mousedown', () => _this.mrkMouseDown(obj, mrk));
				obj.crop.markers[mrk].addEventListener('mouseup', () => _this.mrkMouseUp(obj));
				obj.crop.append(obj.crop.markers[mrk]);
			});
			
			obj.objcrop.append(obj.crop);
			
			// ===== Uploader INFO =====
			obj.info = document.createElement('div');
			obj.info.classList.add('elf-uploader-info');
			obj.info.innerHTML = "Перетащите изображение с диска, вставьте из буфера (Ctrl+V) или ";
			obj.info.addEventListener('click', () => this.infoSetFocus(obj));
			btn = document.createElement('div');
			btn.classList.add('elf-uploader-button-upload','elf-uploader-btns');
			btn.innerHTML = 'загрузите';
			btn.title = 'выбрать и загрузить изображение с диска';
			btn.addEventListener('click', () => ELF_Uploader.selectFiles(obj));
			obj.info.append(btn);
			obj.append(obj.info);
			
			// ===== UPLOAD Status
			obj.stat = document.createElement('div');
			obj.stat.classList.add('elf-uploader-status');
			obj.stat.innerHTML = '<i class="elf-uploader-progress-icon fas fa-circle-notch fa-spin fa-5x fa-fw"></i>Загрузка изображения';
			obj.progressbar = document.createElement('div');
			obj.progressbar.classList.add('elf-uploader-progress-bar');
			obj.progressline = document.createElement('div');
			obj.progressline.classList.add('elf-uploader-progress-line');
			obj.progressbar.append(obj.progressline);
			obj.progresstotal = document.createElement('div');
			obj.progresstotal.classList.add('elf-uploader-progress-total');
			obj.progresstotal.innerHTML = '0 / 0';
			obj.progressprompt = document.createElement('div');
			obj.progressprompt.classList.add('elf-uploader-progress-prompt');
			obj.stat.append(obj.progressbar);
			obj.stat.append(obj.progresstotal);
			obj.stat.append(obj.progressprompt);
			obj.append(obj.stat);
			
			// ===== UPLOAD input file INIT ==== 
			obj.file = document.createElement('input');
			obj.file.type = 'file';
			obj.file.name = 'elfuploadfile'+parseInt(Math.random()*100000);
			obj.file.style.display = 'none';
			obj.file.setAttribute('id',"elf-upload-file"+parseInt(Math.random()*100000));
			if (this.multy)
				obj.file.setAttribute('multiple','multiple');
			obj.file.addEventListener("change", () => this.onChange(obj));
			obj.append(obj.file);
			
			// ===== Paster Init
			obj.paster = document.createElement('div');
			obj.paster.classList.add('elf-uploader-paster');
			obj.paster.innerHTML = 'Вставить изображение из буфера?<br />';
			btn = document.createElement('div');
			btn.classList.add('elf-uploader-paster-ok','elf-uploader-paster-ctrl','elf-uploader-btns');
			btn.innerHTML = 'Да';
			btn.addEventListener('click', () => this.pasterConfirm(obj));
			obj.paster.append(btn);
			btn = document.createElement('div');
			btn.classList.add('elf-uploader-paster-cancel','elf-uploader-paster-ctrl','elf-uploader-btns');
			btn.innerHTML = 'Нет';
			btn.addEventListener('click', () => this.pasterCancel(obj));
			obj.paster.append(btn);
			obj.append(obj.paster);
			
			// ===== Image INIT =========
			obj.img = document.createElement('img');
			if (file_name) {
				obj.img.src = file_name+'?'+Math.random();
				obj.id = 'elf-uploader-item-'+Math.random();
			}
			else {
				obj.img.src = ELF_Uploader.BLANK_IMAGE+'?'+Math.random();
				obj.imgIsBlank = true;
			}
			obj.append(obj.img);
			obj.img.addEventListener("load", () => this.imgOnLoad(obj));

			// ===== EDIT/REMOVE IMG Button
			if (this.editable) { // init EDIT button
				obj.edit = document.createElement('div');
				obj.edit.classList.add('elf-uploader-crop-ctrl','elf-uploader-crop-ctrl-top','elf-uploader-crop-ctrl-edit');
				obj.edit.title = 'редактировать изображение';
				obj.edit.addEventListener('click',() => this.showCropDialog(obj));
				obj.edit.innerHTML = '<i class="fas fa-pen-alt"></i>';
				obj.append(obj.edit);
			}
			// -------------- REM Button -----------------
			obj.rem = document.createElement('div');
			obj.rem.title = 'удалить изображение';
			obj.rem.addEventListener('click',() => this.remFile(obj));
			obj.rem.classList.add('elf-uploader-crop-ctrl','elf-uploader-crop-ctrl-top','elf-uploader-crop-ctrl-remove');
			obj.rem.innerHTML = '<i class="far fa-times-circle"></i>';
			obj.append(obj.rem);
			if (this.multy) {
				// -------------- Move left Button -----------------
				obj.moveleft = document.createElement('div');
				obj.moveleft.title = 'переместить изображение влево';
				obj.moveleft.addEventListener('click',() => this.moveImg(obj, 'left'));
				obj.moveleft.classList.add('elf-uploader-crop-ctrl','elf-uploader-crop-ctrl-top','elf-uploader-crop-ctrl-moveleft');
				obj.moveleft.innerHTML = '<i class="fas fa-angle-left"></i>';
				obj.append(obj.moveleft);
				// -------------- Move right Button -----------------
				obj.moveright = document.createElement('div');
				obj.moveright.title = 'переместить изображение вправо';
				obj.moveright.addEventListener('click',() => this.moveImg(obj, 'right'));
				obj.moveright.classList.add('elf-uploader-crop-ctrl','elf-uploader-crop-ctrl-top','elf-uploader-crop-ctrl-moveright');
				obj.moveright.innerHTML = '<i class="fas fa-angle-right"></i>';
				obj.append(obj.moveright);
			}
			
			// ===== Class EVENTS ===== 
			obj.addEventListener("dragover", this.objDragOver);
			obj.objcrop.addEventListener("dragover", this.objDragOver);
			obj.addEventListener("drop", () => this.objOnDrop(obj));
			obj.objcrop.addEventListener("mousedown", () => this.objcropMouseDown(obj));
			obj.objcrop.addEventListener("mouseup", () => this.objcropMouseUp(obj));
			obj.objcrop.addEventListener("mousemove", () => this.objcropMouseMove(obj));
			obj.addEventListener("mouseover", () => this.objMouseOver(obj));
			obj.addEventListener("mouseleave", () => this.objMouseLeave(obj));
			obj.addEventListener("dragstart", () => this.objDragStart());
			obj.objcrop.addEventListener("dragstart", () => this.objDragStart());
			obj.addEventListener("selectstart", () => this.objSelectStart());
			obj.objcrop.addEventListener("selectstart", () => this.objSelectStart());
			// -------------------------------------------------------------------
			obj.crop.addEventListener("mousemove", () => this.cropMouseMove(obj));
			obj.crop.addEventListener("mousedown", () => this.cropMouseDown(obj));
			obj.crop.addEventListener("mouseup", () => this.cropMouseUp(obj));
			// ----- GLOBAL EVENTS -----------------------------------
			obj.xhr = ELF_Uploader.getXhrObject(); 
			obj.xhr.addEventListener('load', () => this.xhrResponse(obj));
			obj.xhr.upload.addEventListener('progress', () => this.xhrProgress(obj));
			obj.xhr.upload.addEventListener('load', () => this.xhrIsLoad(obj));
			
			window.addEventListener("paste", () => this.pasteHandler(obj));
			return obj;
		}
		catch(err) {
			alert('ELF_Uploader constructor error: '+err.message+' Stack: '+err.stack);
		}
	}
	// COMMON
	setParam(name, value) {
		if (!this.params)
			this.params = {};
		this.params[name] = value;
	}
	static getXhrObject() {
		if(typeof XMLHttpRequest === 'undefined'){
			XMLHttpRequest = function() {
				try {
					return new window.ActiveXObject("Microsoft.XMLHTTP");
				}
				catch(err) {
					alert('ELF_Uploader.getXhrObject error: Can not create XMLHttp object');
				}
			}
		}
		return new XMLHttpRequest();
	}
	xhrIsLoad(obj) {
		obj.progressprompt.innerHTML = 'Загрузка завершена. Ждите...';
	}
	xhrProgress(obj) {
		obj.progressprompt.innerHTML = 'Загрузка...';
		obj.progresstotal.innerHTML = event.loaded + ' / ' + event.total;
		obj.progressline.style.width = parseInt(parseFloat(event.loaded)/parseFloat(event.total)*100)+'%';
	}
	xhrResponse(obj) {
		try {
			if (obj.xhr.readyState == 4
				&& obj.xhr.status == 200) {
				let resp;
				let rstr = obj.xhr.response || obj.xhr.responseText;
				if (resp = ELF_Uploader.getJson(rstr, true)) {
					if (resp && resp.error) {
						alert(resp.error);
						this.setPictureBlank(obj, null, this.multy);
					}
					else if (obj.xhrCallback) {
						obj.xhrCallback(obj, resp, this.multy);
					}
				}
				else {
					this.setPictureBlank(obj, null, this.multy);
					obj.xhr.open('POST', '/uploader/__log', true);
					let dta = new FormData();
					dta.append('log', rstr);
					obj.xhr.send(dta);
				}
				obj.xhrCallback = null;
			}
		}
		catch (err) {
			alert('ELF_Uploader.xhrResponse error: '+err.message);
		}
	}
	static getJson(str, showerr) {
		let ret;
		if (str) {
			try {
				ret = JSON.parse(str);
			} catch (err) {
				if (showerr) {
					alert('ELF_Uploader.getJson error: '+err.message+' More info in logs/uploader.log');
					ret = false;
				}
				else
					ret = true;
			}
		}
		else
			ret = true;
		return ret;
	}
	
	// Obj EVENTS
	objSelectStart () {
		event.preventDefault();
		return false;
	}
	objDragStart() {
		event.preventDefault();
		return false;
	}
	objDragOver() {
		event.preventDefault();
		return false;
	}
	objOnDrop (obj) {
		event.preventDefault();
		let files = event.dataTransfer.files;
		
		if (event.dataTransfer.files[0] && event.dataTransfer.files[0].type.indexOf("image") !== -1) {
			this.uploadFile(obj, event.dataTransfer.files[0]);
		}
		return false;
	}
	objMouseLeave(obj) {
		obj.buttDown = false;
		obj.cropMove = false;
	}
	objMouseOver(obj) {
		if (this.editable
			&& !obj.imgIsBlank
			&& !obj.buttDown) {
		}
	}
	objcropMouseDown(obj) {
		if (!obj.cropMove
			&& !obj.buttDown
			&& !obj.curCropMarker
			&& obj.imgLoaded) {
			obj.crop.style.width = obj.crop.style.height = 0;
			obj.crop.style.left = event.offsetX+'px';
			obj.crop.style.top = event.offsetY+'px';

			obj.x = parseInt(obj.crop.style.left); // start crop X
			obj.y = parseInt(obj.crop.style.top);  // start crop Y

			obj.crop.style.backgroundPosition = ((-1)*obj.x-ELF_Uploader.OFFSET_CROP_BG)+"px "+((-1)*obj.y-ELF_Uploader.OFFSET_CROP_BG)+"px";

			obj.crop.style.top += 'px';
			obj.objcrop.img.style.webkitFilter = "grayscale(1) blur(3px)";
			obj.objcrop.img.style.filter = "grayscale(1) blur(3px)";
			obj.buttDown = true;
			this.cropCtrlsHideShow(obj.objcrop, 'hide');
		}
	}
	objcropMouseUp(obj) {
		if (parseInt(obj.crop.style.width) == 0 || parseInt(obj.crop.style.height) == 0) {
			obj.objcrop.img.style.webkitFilter = "";
			obj.objcrop.img.style.filter = "";
			obj.crop.style.display = 'none';
			obj.x = obj.crop.style.left = obj.y = obj.crop.style.top = 0;
			this.cropCtrlsHideShow(obj.objcrop, 'hide');
		}
		else {
			// reset crop start X Y
			obj.x = parseInt(obj.crop.style.left);
			obj.y = parseInt(obj.crop.style.top);
			this.cropCtrlsHideShow(obj.objcrop, 'show');
		}
		obj.buttDown = false;
		obj.curCropMarker = null;
	}
	objcropMouseMove(obj) {
		if (obj.buttDown || obj.curCropMarker) {
			
			let _off = obj.objcrop.getBoundingClientRect();
			let _x = event.clientX - _off.left - obj.x;
			let _y = event.clientY - _off.top - obj.y;
			
			if (!obj.crop.style.display || (obj.crop.style.display == 'none'))
				if (Math.abs(_x) > 15 || Math.abs(_y) > 15)
					obj.crop.style.display = 'block';
			
			switch (obj.curCropMarker) {
				case 'ne':
					obj.crop.style.top = (event.clientY - _off.top)+'px';
					obj.crop.style.backgroundPositionY = ((-1)*parseInt(obj.crop.style.top)-ELF_Uploader.OFFSET_CROP_BG)+"px";
					obj.crop.style.height = obj.cropHeight +(-1)*_y+'px';
					if (this.params.crop_ratio) {
						obj.crop.style.width = obj.cropHeight +(-1)*_y+'px';
						_y = _x;
					}
					else
						_y = 0;
					break;
				default:
				case 'se':
					if (this.params.crop_ratio)
						_y = _x;
					break;
				case 'nw':
					obj.crop.style.left = (event.clientX - _off.left)+'px';
					obj.crop.style.backgroundPositionX = ((-1)*parseInt(obj.crop.style.left)-ELF_Uploader.OFFSET_CROP_BG)+"px";
					obj.crop.style.width = obj.cropWidth +(-1)*_x+'px';
					_x = 0;
					obj.crop.style.top = (event.clientY - _off.top)+'px';
					obj.crop.style.backgroundPositionY = ((-1)*parseInt(obj.crop.style.top)-ELF_Uploader.OFFSET_CROP_BG)+"px";
					obj.crop.style.height = obj.cropHeight +(-1)*_y+'px';
					_y = 0;
					break;
				case 'sw':
					obj.crop.style.left = (event.clientX - _off.left)+'px';
					obj.crop.style.backgroundPositionX = ((-1)*parseInt(obj.crop.style.left)-ELF_Uploader.OFFSET_CROP_BG)+"px";
					obj.crop.style.width = obj.cropWidth +(-1)*_x+'px';
					_x = 0;
					break;
				case 's':
					obj.crop.style.height = _y+'px';
					if (this.params.crop_ratio)
						obj.crop.style.width = _y+'px';
					_x = _y = 0;
					break;
				case 'n':
					obj.crop.style.top = (event.clientY - _off.top)+'px';
					obj.crop.style.backgroundPositionY = ((-1)*parseInt(obj.crop.style.top)-ELF_Uploader.OFFSET_CROP_BG)+"px";
					obj.crop.style.height = obj.cropHeight +(-1)*_y+'px';
					if (this.params.crop_ratio)
						obj.crop.style.width = obj.cropHeight +(-1)*_y+'px';
					_x = _y = 0;
					break;
				case 'e':
					obj.crop.style.width = _x+'px';
					if (this.params.crop_ratio)
						obj.crop.style.height = _x+'px';
					_x = _y = 0;
					break;
				case 'w':
					obj.crop.style.left = (event.clientX - _off.left)+'px';
					obj.crop.style.backgroundPositionX = ((-1)*parseInt(obj.crop.style.left)-ELF_Uploader.OFFSET_CROP_BG)+"px";
					obj.crop.style.width = obj.cropWidth +(-1)*_x+'px';
					if (this.params.crop_ratio)
						obj.crop.style.height = obj.cropWidth +(-1)*_x+'px';
					_x = _y = 0;
					break;
			}
			this.cropResizeDefault(obj, _x, _y, _off);
		}
	}
	cropResizeDefault(obj, x, y, _off) {
		if (x > 0)
			obj.crop.style.width = x+'px';
		else if (x < 0){
			obj.crop.style.left = (event.clientX - _off.left)+'px';
			obj.crop.style.backgroundPositionX = ((-1)*parseInt(obj.crop.style.left)-ELF_Uploader.OFFSET_CROP_BG)+"px";
			obj.crop.style.width = Math.abs(x)+'px';
		}
		if (y > 0)
			obj.crop.style.height = y+'px';
		else if (y < 0) {
			obj.crop.style.top = (event.clientY - _off.top)+'px';
			obj.crop.style.backgroundPositionY = ((-1)*parseInt(obj.crop.style.top)-ELF_Uploader.OFFSET_CROP_BG)+"px";
			obj.crop.style.height = Math.abs(y)+'px';
		}
	}
// ===== Info
	infoSetFocus(obj) {
		obj.info.focus();
		obj.info.classList.add('elf-uploader-info-selected');
	}
// ========== Crop EVENTS
	cropMouseMove(obj) {
		if (obj.cropMove) {
			let _x = event.clientX - obj.mx;
			let _y = event.clientY - obj.my;
			obj.crop.style.left = parseInt(obj.crop.style.left)+_x+'px';
			obj.crop.style.top = parseInt(obj.crop.style.top)+_y+'px';
			obj.crop.style.backgroundPosition = ((-1)*parseInt(obj.crop.style.left)-ELF_Uploader.OFFSET_CROP_BG)+"px "+((-1)*parseInt(obj.crop.style.top)-ELF_Uploader.OFFSET_CROP_BG)+"px";
			obj.mx = event.clientX;
			obj.my = event.clientY;
		}
	}
	cropMouseDown(obj) {
		if (!obj.buttDown && !obj.curCropMarker) {
			obj.mx = event.clientX;
			obj.my = event.clientY;
			obj.cropMove = true;
		}
		return false;
	}
	cropMouseUp(obj) {
		obj.cropMove = false;
		//reset crop start X Y
		obj.x = parseInt(obj.crop.style.left);
		obj.y = parseInt(obj.crop.style.top);
	}
	cropCtrlsHideShow(obj, sw) {
		try {
			let elems = obj.querySelectorAll(".elf-uploader-crop-ctrl");
			if (elems.length) {
				for (let elem of elems) {
					switch (sw) {
						case 'show':
							elem.style.display = 'block';
							break;
						case 'hide':
							elem.style.display = 'none';
							break;
					}
				}
			}
		}
		catch(err) {}
	}
	hideCrop(obj) {
		obj.crop.style.display = 'none';
		obj.crop.style.width = obj.crop.style.height = 
			obj.crop.style.left = obj.crop.style.top = 0;
		obj.objcrop.img.style.webkitFilter = "";
		obj.objcrop.img.style.filter = "";
	}
	showCropDialog(obj) {
		obj.cropwnd.style.display = 'block';
		//obj.crop.style.backgroundSize = "50%";

		if (!obj.calc_scale) {
			obj.calc_scale = true;
			this.calcCropScale(obj);

			if (obj.crop.coof_w) {
				obj.objcrop.img.style.width = (obj.objcrop.img.clientWidth*obj.crop.coof_w)+'px'
				obj.objcrop.img.style.height = (obj.objcrop.img.style.width/obj.crop.coof_img)+'px'
			}
			else if (obj.crop.coof_h) {
				obj.objcrop.img.style.height = (obj.objcrop.img.clientHeight*obj.crop.coof_h)+'px'
				obj.objcrop.img.style.width = (obj.objcrop.img.style.height/obj.crop.coof_img)+'px'
			}
		}
		else {
			obj.objcrop.img.style.width = obj.objcrop.img.clientWidth+'px'
			obj.objcrop.img.style.height = obj.objcrop.img.clientHeight+'px'
		}
		obj.objcrop.style.width = obj.objcrop.img.clientWidth+'px';
		obj.objcrop.style.height = obj.objcrop.img.clientHeight+'px';

		obj.crop.style.backgroundImage = "url('"+obj.objcrop.img.src+"')";
		obj.crop.style.backgroundSize = obj.objcrop.style.width+" "+obj.objcrop.style.height;

		obj.objcrop.style.left = "calc(50% - "+(obj.objcrop.img.clientWidth/2)+"px)";
		obj.objcrop.style.top = "calc(50% - "+(obj.objcrop.img.clientHeight/2)+"px)";
		document.body.style.overflow = 'hidden';
	}
	static hideCropDialog(cid) {
		document.getElementById(cid).style.display = 'none';
		document.body.style.overflow = 'auto';
	}
	calcCropScale(obj) {
		
			let img = obj.objcrop.img;
			if (img.width > ELF_Uploader.WINDOW_WIDTH
				|| img.height > ELF_Uploader.WINDOW_HEIGHT) {
				let coof_w = img.width > ELF_Uploader.WINDOW_WIDTH?ELF_Uploader.WINDOW_WIDTH/img.width:0;
				let coof_h = img.height > ELF_Uploader.WINDOW_HEIGHT?ELF_Uploader.WINDOW_HEIGHT/img.height:0;
				if (coof_w && ((img.width*coof_w)/obj.crop.coof_img <= ELF_Uploader.WINDOW_HEIGHT)) {
					obj.crop.coof_w = coof_w;
				}
				else if (coof_h && ((img.height*coof_h)/obj.crop.coof_img <= ELF_Uploader.WINDOW_WIDTH)) {
					obj.crop.coof_h = coof_h;
				}
			}
	}
	// ------------- crop Markers
	mrkMouseDown(obj, marker) {
		if (!obj.buttDown && !obj.cropMove) {
			this.cropCtrlsHideShow(obj.objcrop, 'hide');
			obj.curCropMarker = marker;
			obj.cropHeight = parseInt(obj.crop.style.height);
			obj.cropWidth = parseInt(obj.crop.style.width);
		}
	}
	mrkMouseUp(obj) {
		obj.curCropMarker = null;
		// reset crop start X Y
		obj.x = parseInt(obj.crop.style.left);
		obj.y = parseInt(obj.crop.style.top);
	}

// ============ Img EVENTS
	imgOnLoad(obj) {
		obj.stat.style.display = 'none';
		if (!obj.imgIsBlank) {
			obj.imgLoaded = true;
			obj.rem.style.display = 'block';
			obj.info.style.display = 'none';
			this.cropCtrlsHideShow(obj, 'show');
		}
		else {
			obj.crop.style.backgroundImage = "none";
			obj.rem.style.display = 'none';
			obj.imgLoaded = false;
			this.cropCtrlsHideShow(obj, 'hide');
		}
		obj.style.width = (event.target.clientWidth > ELF_Uploader.MIN_WIDTH?event.target.clientWidth:ELF_Uploader.MIN_WIDTH)+'px';
		obj.style.height = (event.target.clientHeight > ELF_Uploader.MIN_HEIGHT?event.target.clientHeight:ELF_Uploader.MIN_HEIGHT)+'px';
		obj.crop.coof_img = parseInt(event.target.clientWidth)/parseInt(event.target.clientHeight);
	}

// ============ PASTER	
	pasteHandler(obj) {
		if (event.clipboardData) {
			// for web-kit
			let items;
			if (items = event.clipboardData.items) {
				let isPaste = false;
				for (let i = 0; i < items.length; i++) {
					if (items[i].type.indexOf("image") !== -1) {
						ELF_Uploader.uplReadyFile = items[i].getAsFile();
						isPaste = true;
						break;
					}
				}
				if (isPaste)
//					alert('Image not found in Clipboard');
//				else
					obj.paster.style.display = 'block';
			}
		}
		else {
			// for Firefox ???
			setTimeout(function(){ELF_Uploader.checkInput(obj)}, 1);
		}
	}
	pasterConfirm(obj) {
		obj.paster.style.display = 'none';
		this._pasteHandler(obj);
	}
	pasterCancel(obj) {
		obj.paster.style.display = 'none';
	}
	_pasteHandler(obj) {
		this.uploadFile(obj, ELF_Uploader.uplReadyFile);
	}
	static pasteImageFromClpbrd(obj) {
		alert('Функция временно недоступна. Используйте Ctrl+V для вставки из буфера');
	}
	static checkInput(ptr) {
		try {
			let child = ELF_Uploader.pasteCatcher.childNodes[0];   
			ELF_Uploader.pasteCatcher.innerHTML = "";    
			if (child && child.tagName === "IMG") {
			}
		}
		catch(err) {
			alert('ELF_Uploader class: '+err.message);
		}
	}
// **********************************************************************
	
	// ===== UPLOAD Logic
	static selectFiles(obj) {
		obj.file.click();
	}
	onChange(obj) {
		try {
			let k = event.target.files.length-1;
			while (k >= 0) {
				if (typeof event.target.files[k] != 'undefined'
					&& event.target.files[k].type.indexOf("image") !== -1) {
					if (this.multy) {
						let _obj = this._init();
						this.root.append(_obj);
						this.uploadFile(_obj, event.target.files[k]);
					}
					else
						this.uploadFile(obj, event.target.files[k]);
				}
				k --;
			}
			obj.file.value = '';
		}
		catch (err) {
			alert('ELF_Uploader.onChange error: '+err.message);
		}
	}
	uploadFile(obj, file) {
		obj.stat.style.display = 'block';
		obj.info.style.display = 'none';
		obj.xhrCallback = this.setPicture;
		let dta = new FormData();
		dta.append('field',obj.file.name);
		dta.append('model',this.model);
		dta.append('params',JSON.stringify(this.params));
		dta.append(obj.file.name,file);
		obj.xhr.open('POST', this.uploader, true);
		obj.xhr.send(dta);
	}
	setPicture(obj, resp) {
		console.log(resp);
		if (resp && resp.icon && resp.src) {
			obj.img.src = resp.icon+'?'+Math.random();
			obj.objcrop.img.src = resp.src+'?'+Math.random();
			obj.img.title = resp.name;
			obj.input.setAttribute('value',resp.name);
			obj.imgIsBlank = false;
			obj.id = 'elf-uploader-item-'+Math.random();
		}
		else
			alert('Invalid Image parameters return. Return: '+resp);
	}
	setPictureBlank(obj, resp, multy) {
		if (!multy) {
			obj.img.src = ELF_Uploader.BLANK_IMAGE+'?'+Math.random();
			obj.input.value = '';
			obj.objcrop.img.src = "";
			obj.objcrop.img.style.webkitFilter = "";
			obj.objcrop.img.style.filter = "";
			obj.info.style.display = 'block';
			obj.imgLoaded = false;
			obj.imgIsBlank = true;
			obj.crop.style.width = obj.crop.style.height = obj.x = obj.y = 0;
			obj.crop.style.display = 'none';
		}
		else {
			obj.style.display = 'none';
			obj.remove();
		}
	}
	remFile(obj) {
		obj.xhrCallback = this.setPictureBlank;
		obj.xhr.open('POST', this.remover, true);
		let dta = new FormData();
		dta.append('model', this.model);
		dta.append('params', this.params);
		dta.append('remfile', obj.input.value);
		obj.xhr.send(dta);
	}
	moveImg(obj, direct) {
		//alert(direct);
		let rpl = null;
		switch (direct) {
			case 'left':
				rpl = obj.previousSibling;
				break;
			case 'right':
				rpl = obj.nextSibling;
				break;
		}
		if (rpl && (rpl.id.indexOf('elf-uploader-item-') != -1)) {
			let elem = document.getElementById(rpl.id);
			switch(direct) {
				case 'left':
					obj.parentNode.insertBefore(obj,elem);
					break;
				case 'right':
					elem.parentNode.insertBefore(elem,obj);
					break;
			}
		}
	}
	cropImage(obj) {
		obj.stat.style.display = 'block';
		obj.info.style.display = 'none';
		obj.xhrCallback = this.setPicture;
		let w = parseInt(obj.crop.style.width);
		let h = parseInt(obj.crop.style.height);
		let x = parseInt(obj.crop.style.left);
		let y = parseInt(obj.crop.style.top);
		x = x < 0?0:x;
		y = y < 0?0:y;
		w = (w + x) > obj.objcrop.img.clientWidth?obj.objcrop.img.clientWidth-x:w;
		h = (h + y) > obj.objcrop.img.clientHeight?obj.objcrop.img.clientHeight-y:h;
		if (obj.crop.coof_w) {
			x = x/obj.crop.coof_w;
			y = y/(obj.crop.coof_w*obj.crop.coof_img);
			w = w/obj.crop.coof_w;
			h = h/obj.crop.coof_w;
		}
		else if (obj.crop.coof_h) {
			y = y/obj.crop.coof_h;
			x = x/(obj.crop.coof_h*obj.crop.coof_img);
			h = h/obj.crop.coof_h;
			w = w/obj.crop.coof_h;
		}
		let dta = new FormData();
		dta.append('model',this.model);
		dta.append('params',this.params);
		dta.append('picture',obj.input.value);
		dta.append('x',x);
		dta.append('y',y);
		dta.append('w',w);
		dta.append('h',h);
		obj.xhr.open('POST', this.croper, true);
		obj.xhr.send(dta);

		this.hideCrop(obj);
		ELF_Uploader.hideCropDialog(obj.cropwnd.id);
	}
}
