class ELF_SimpleUploader {
	constructor(cont, uploader, params) {
		try {
			this.root = document.getElementById(cont);
			this.root.classList.add('elf-simpleuploader-container');
			
			if (!uploader)
				throw new Error('Required field "uploader" is empty');
			this.uploader = uploader;
			
			if (params) {
				this.params = params;
				if (params.ext)
					this.ext = params.ext;
				if (params.obj)
					this.obj = params.obj;
				if (params.callback)
					this.userCallback = params.callback;
			}
			
			this.file = document.createElement('input');
			this.file.type = "file";
			this.file.name = "uplfile"+parseInt(Math.random()*1000000);
			this.file.style.display = "none";
			if (params.mimes)
				this.file.accept = params.mimes;
			this.file.addEventListener("change", () => this.onChange());
			this.root.append(this.file);

			this.info = document.createElement('div');
			this.info.classList.add('elf-simpleuploader-info');
			this.root.append(this.info);

			this.btn = document.createElement('input');
			this.btn.type = "button";
			this.btn.value = "Выбрать файл";
			this.btn.addEventListener('click', () => this.selectFiles());
			this.root.append(this.btn);

			this.xhr = ELF_SimpleUploader.getXhrObject(); 
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
	}
	xhrResponse() {
		hideWW();
		try {
			if (this.xhr.readyState == 4) {
				if (this.xhr.status == 200) {
					let resp;
					if (resp = ELF_SimpleUploader.getJson(this.xhr.response || this.xhr.responseText, true)) {
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
	static getJson(str, showerr) {
		let ret;
		if (str) {
			try {
				ret = JSON.parse(str);
			} catch (err) {
				if (showerr) {
					alert('ELF_SimpleUploader.getJson error: '+err.message+' '+str.substr(0, 100)+'...');
					ret = false;
				}
				ret = true;
			}
		}
		else
			ret = true;
		return ret;
	}


// ===== Methods
	selectFiles() {
		this.file.click();
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
			showWW();
			this.xhrCallback = this.setFile;
			let dta = new FormData();
			dta.append('field', this.file.name);
			dta.append('params', JSON.stringify(this.params));
			dta.append(this.file.name, file);
			this.xhr.open('POST', '/'+this.uploader+'/upload', true);
			this.xhr.send(dta);
		}
		else
			alert('File can`t load, wrong extension. Please select file with extension from extensions list '+(this.ext?this.ext:''));
	}
	removeFile(name) {
		let dta = new FormData();
		this.xhrCallback = this.unsetFile;
		dta.append('fname', name);
		dta.append('params', JSON.stringify(this.params));
		this.xhr.open('POST', '/'+this.uploader+'/remove', true);
		this.xhr.send(dta);
	}
	setFile(resp) {
		this.info.innerHTML = '<a href="'+resp.path+'" title="загруженный файл">'+resp.name+'</a> <i class="fas fa-times-circle" title="удалить файл с сервера" onclick="'+(this.obj?this.obj:'esu')+'.removeFile(\''+resp.name_encoded+'\')"></i>'
								+ '<input type="hidden" name="elf_simpleuploaded_fname" value="'+resp.name_encoded+'" />'
								+ '<input type="hidden" name="elf_simpleuploaded_name" value="'+resp.name+'" />';
		this.info.style.display = 'block';
		this.btn.style.display = 'none';
	}
	unsetFile(resp) {
		this.info.innerHTML = '';
		this.info.style.display = 'none';
		this.btn.style.display = 'block';
	}
}