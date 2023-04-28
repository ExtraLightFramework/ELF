class ELF_SimpleUploader {
	constructor(cont, uploader, params) {
		try {
			this.root = document.getElementById(cont);
			this.root.classList.add('elf-simpleuploader-container');
			
			if (uploader)
				this.uploader = uploader;
			else
				this.uploader = 'uploader';

			if (params) {
				this.params = params;
				if (params.ext)
					this.ext = params.ext;
				if (params.obj)
					this.obj = params.obj;
				if (params.model)
					this.model = params.model;
				if (params.title)
					this.title = params.title;
				else
					this.title = "Выбрать файл";
				if (params.file_name)
					this.file_name = params.file_name;
				else
					this.file_name = "";
				if (params.input_name)
					this.input_name = params.input_name;
				if (typeof params.showinfo != 'undefined')
					this.showinfo = params.showinfo;
				if (params.callback)
					this.userCallback = params.callback;
			}
			else {
				params = {};
				this.model = '';
				this.showinfo = true;
				this.title = "Выбрать файл";
				this.input_name = "picture";
				this.file_name = "";
			}
			
			this.file = document.createElement('input');
			this.file.type = "file";
			this.file.name = "uplfile"+parseInt(Math.random()*1000000);
			this.file.style.display = "none";
			if (params.mimes)
				this.file.accept = params.mimes;
			this.file.addEventListener("change", () => this.onChange());
			this.root.append(this.file);

			this.input = document.createElement('input');
			this.input.type = "hidden";
			this.input.name = this.input_name;
			this.input.value = this.file_name;
			this.root.append(this.input);

			this.info = document.createElement('div');
			this.info.classList.add('elf-simpleuploader-info');
			this.root.append(this.info);

			this.btn = document.createElement('button');
//			this.btn.type = "button";
			this.btn.innerHTML = this.file_name?this.file_name:this.title;
			this.btn.addEventListener('click', () => this.selectFiles());
			this.root.append(this.btn);

			// ===== UPLOAD Status
			this.stat = document.createElement('div');
			this.stat.classList.add('elf-uploader-status');
			this.stat.innerHTML = 'Загрузка файла';
			this.stat.style.display = 'none';
			this.progressbar = document.createElement('div');
			this.progressbar.classList.add('elf-uploader-progress-bar');
			this.progressline = document.createElement('div');
			this.progressline.classList.add('elf-uploader-progress-line');
			this.progressbar.append(this.progressline);
			this.progresstotal = document.createElement('div');
			this.progresstotal.classList.add('elf-uploader-progress-total');
			this.progresstotal.innerHTML = '0 / 0';
			this.progressprompt = document.createElement('div');
			this.progressprompt.classList.add('elf-uploader-progress-prompt');
			this.stat.append(this.progressbar);
			this.stat.append(this.progresstotal);
			this.stat.append(this.progressprompt);
			document.body.append(this.stat);

			this.xhr = ELF_SimpleUploader.getXhrObject();
			this.xhr.responseType = 'json';
			this.xhr.addEventListener('load', () => this.xhrResponse());
			this.xhr.upload.addEventListener('progress', () => this.xhrProgress());
			this.xhr.upload.addEventListener('load', () => this.xhrIsLoad());
			
			if (this.params && this.params.file) {
				this.setFile(this.params.file);
			}
		}
		catch (err) {
			alert('ELF_SimpleUploader constructor error: '+err.message);
		}
	}
// ===== GLOBALS
	static getXhrObject() {
		if(typeof XMLHttpRequest === 'undefined'){
			XMLHttpRequest = function() {
				try {
					return new window.ActiveXObject("Microsoft.XMLHTTP");
				}
				catch(err) {
					alert('ELF_SimpleUploader.getXhrObject error: Can not create XMLHttp object');
				}
			}
		}
		return new XMLHttpRequest();
	}
	xhrIsLoad() {
	}
	xhrProgress() {
		this.progressprompt.innerHTML = 'Загрузка...';
		this.progresstotal.innerHTML = event.loaded + ' / ' + event.total;
		this.progressline.style.width = parseInt(parseFloat(event.loaded)/parseFloat(event.total)*100)+'%';
	}
	xhrResponse() {
//		hideWW();
		this.stat.style.display = 'none';
		try {
			if (this.xhr.readyState == 4) {
				if (this.xhr.status == 200) {
					let resp;
					if (resp = this.xhr.response) {
						if (resp && resp.error) {
							alert(resp.error);
						}
						else {
							if (this.xhrCallback) {
								this.xhrCallback(resp);
							}
							if (this.userCallback && window[this.userCallback])
								window[this.userCallback](resp);
						}
						
					}
					this.xhrCallback = null;
				}
				else
					alert('Upload fail! Response code: '+this.xhr.status);
			}
		}
		catch (err) {
			alert('ELF_SimpleUploader.xhrResponse error: '+err.message);
		}
	}


// ===== Methods
	selectFiles() {
		if (this.input.value) {
//			console.log(this.input);
			this.removeFile(this.input.value);
		}
		this.file.click();
		event.preventDefault();
	}
	checkExt(fname) {
		if (this.ext) {
			let ext = fname.lastIndexOf('.') != -1?fname.substr(fname.lastIndexOf('.')+1):'';
			if (ext)
				return this.ext.search(ext) != -1?true:false;
			else
				return false;
		}
		return true;
	}
	onChange() {
		try {
			let k = event.target.files.length-1;
			while (k >= 0) {
				if (typeof event.target.files[k] != 'undefined') {
					this.uploadFile(event.target.files[k]);
				}
				k --;
			}
			event.target.value = '';
		}
		catch (err) {
			alert('ELF_SimpleUploader.onChange error: '+err.message);
		}
	}
	uploadFile(file) {
		if (this.checkExt(file.name)) {
//			showWW();
			this.stat.style.display = 'block';
			this.xhrCallback = this.setFile;
			let dta = new FormData();
			dta.append('field', this.file.name);
			dta.append('model', this.model);
			if (typeof this.file.accept != 'undefined')
				dta.append('accept', this.file.accept);
			dta.append('params', JSON.stringify(this.params));
			dta.append(this.file.name, file);
//			console.log(dta.getAll());
			this.xhr.open('POST', '/'+this.uploader+'/upload', true);
			this.xhr.send(dta);
		}
		else
			alert('File can`t load, wrong extension. Please select file with extension from extensions list '+(this.ext?this.ext:''));
	}
	removeFile(name) {
		let dta = new FormData();
		this.xhrCallback = this.unsetFile;
		dta.append('remfile', name);
		dta.append('model', this.model);
		dta.append('params', JSON.stringify(this.params));
		this.xhr.open('POST', '/'+this.uploader+'/remove', true);
		this.xhr.send(dta);
	}
	setFile(resp) {
//		console.log(resp);
		if (this.showinfo) {
			this.info.innerHTML = '<a href="'+resp.path+'" title="загруженный файл">'+resp.name+'</a> <i class="fas fa-times-circle" title="удалить файл с сервера" onclick="'+(this.obj?this.obj:'esu')+'.removeFile(\''+resp.name_encoded+'\')"></i>'
									+ '<input type="hidden" name="elf_simpleuploaded_fname" value="'+resp.name_encoded+'" />'
									+ '<input type="hidden" name="elf_simpleuploaded_name" value="'+resp.name+'" />';
			this.info.style.display = 'block';
			this.btn.style.display = 'none';
		}
		else
			this.btn.innerHTML = resp.name;
		this.input.value = resp.name;
	}
	unsetFile(resp) {
		if (this.showinfo) {
			this.info.innerHTML = '';
			this.info.style.display = 'none';
			this.btn.style.display = 'block';
		}
		else {
			this.btn.innerHTML = this.title;
		}
		this.input.value = '';
	}
}