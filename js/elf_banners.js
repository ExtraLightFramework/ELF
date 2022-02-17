class ELF_Banners { // ELF Banners v 1.0
	constructor(cont, request, delay, direct, duration) {
		try {
			this.root = document.getElementById(cont);
			this.root.classList.add('elf-banners-container');
			this.cnt = 0;
			this.ptr = 0;
			this.obj = [];
			this.direct = direct?direct:'left';
			this.ROTATE_DURATION = duration?duration:1000;
			this.resizerTm = 444;
			
			// ===== MiniDOTS container Init
			this.mdcont = document.createElement('div');
			this.mdcont.classList.add('elf-banners-minidots-cont');
			this.mdcont.addEventListener('mouseenter', () => this.rotateInProc = false);
			this.root.append(this.mdcont);
			
			// ===== Next Prev Buttons Init
			let btn = document.createElement('div');
			btn.classList.add('elf-banners-navigate','elf-banners-navigate-next');
			btn.innerHTML = '<i class="fas fa-chevron-right"></i>';
			btn.addEventListener('click', () => this.rotate('left'));
			btn.addEventListener('mouseenter', () => this.rotateInProc = false);
			this.root.append(btn);
			btn = document.createElement('div');
			btn.classList.add('elf-banners-navigate','elf-banners-navigate-prev');
			btn.innerHTML = '<i class="fas fa-chevron-left"></i>';
			btn.addEventListener('click', () => this.rotate('right'));
			btn.addEventListener('mouseenter', () => this.rotateInProc = false);
			this.root.append(btn);
			
			this.root.addEventListener('mouseenter', () => this.onMouseEnter());
			this.root.addEventListener('mouseleave', () => this.onMouseLeave());
			window.addEventListener('resize', () => this.onWindowResize());
			
			let wh = getComputedStyle(this.root);
			this.WIDTH = wh.width?parseInt(wh.width):0;
			this.HEIGHT = wh.height?parseInt(wh.height):0;
			
			this.xhr = ELF_Banners.getXhrObject();
			this.xhr.addEventListener('readystatechange', () => this.xhrStateChange());
			this.xhr.addEventListener('load', () => this.xhrResponse());
			
			this.xhrCallback = this.init;
			this.xhr.open('POST', request, true);
			this.xhr.send();
			
			// === START Rotate
			this.delay = delay?parseInt(delay):0;
			this.rottm = null;
			this.rotateInProc = false;
		}
		catch(err) {
			alert('ELF_Banners load error: '+err.message);
		}
	}
	// ===== Mouse EVENTS
	onMouseEnter() {
		clearTimeout(this.rottm);
		this.rotateInProc = true;
	}
	onMouseLeave() {
		this.autoRotator(this);
//		if (this.delay && (this.cnt > 1))
//			this.rottm = setTimeout(() => this.rotate(this.direct), this.delay);
	}
	onWindowResize() {
		clearTimeout(this.resizerTm);
		this.resizerTm = setTimeout(() => this.resizeAction(), 300);
	}
	resizeAction() {
//		alert('resize');
	}
	init(obj, resp) {
		if (resp.length) {
			resp.forEach(function(item) {
				obj.obj[obj.cnt] = document.createElement('div');
				obj.obj[obj.cnt].classList.add('elf-banners-item');
				obj.obj[obj.cnt].innerHTML = item;
				if (obj.cnt) {
					obj.obj[obj.cnt].style.left = obj.WIDTH+'px';
					obj.obj[obj.cnt].style.zIndex = 0;
				}
				else
					obj.obj[obj.cnt].style.zIndex = 1;
				obj.obj[obj.cnt].style.width = obj.WIDTH+'px';
				obj.obj[obj.cnt].style.height = obj.HEIGHT+'px';
//				obj.obj[obj.cnt].style.zIndex = obj.cnt+1;
				
				let minidot = document.createElement('div');
				minidot.classList.add('elf-banners-minidot');
				minidot.setAttribute('data-num', obj.cnt);
				minidot.setAttribute('id', 'elf-banners-minidot-'+obj.cnt);
				if (!obj.cnt)
					minidot.classList.add('elf-banners-minidot-selected');
				minidot.addEventListener('click', () => ELF_Banners._rotate(obj, minidot));
				obj.mdcont.append(minidot);

				obj.root.append(obj.obj[obj.cnt]);
				obj.cnt ++;
			});
			obj.autoRotator(obj);
//			if (obj.delay && (obj.cnt > 1)) {
//				obj.rottm = setTimeout(() => obj.rotate(obj.direct), obj.delay);
//			}
			if (obj.cnt <= 1) {
				obj.mdcont.style.display = 'none';
				document.querySelectorAll('.elf-banners-navigate-next')[0].style.display = 'none';
				document.querySelectorAll('.elf-banners-navigate-prev')[0].style.display = 'none';
			}
		}
		
	}
	static _rotate(obj, minidot) {
		obj.rotate(obj.direct, parseInt(minidot.getAttribute('data-num')));
	}
	rotate(direct, next) {
		if (!this.rotateInProc) {
			clearTimeout(this.rottm);
			this.rotateInProc = true;
			let ptr = this.ptr;
			if (typeof next == 'undefined')
				next = ptr + 1 >= this.cnt?0:ptr + 1;

			let n_md = document.getElementById('elf-banners-minidot-'+next);
			if (n_md.classList.contains('elf-banners-minidot-selected'))
				return;

			let elems = document.querySelectorAll(".elf-banners-minidot-selected")[0].classList.remove('elf-banners-minidot-selected');

			switch (direct) {
				case 'right':
					this.obj[ptr].style.zIndex = 0;
					this.animate({timing: ELF_Banners.animate_liner,
									draw(progress, ptr, width) {ptr.style.left = (width*progress)+'px';},
									duration: this.ROTATE_DURATION,
									ptr: this.obj[ptr],
									width: this.WIDTH});
					n_md.classList.add('elf-banners-minidot-selected');
					this.obj[next].style.zIndex = 1;
					this.obj[next].style.left = '-'+this.WIDTH+'px';
					this.animate({timing: ELF_Banners.animate_liner,
									draw(progress, ptr, width) {ptr.style.left = '-'+(width - width*progress)+'px';},
									duration: this.ROTATE_DURATION,
									callback: this.autoRotator,
									_self: this,
									ptr: this.obj[next],
									width: this.WIDTH});
					break;
				default:
				case 'left':
					this.obj[ptr].style.zIndex = 0;
					this.animate({timing: ELF_Banners.animate_liner,
									draw(progress, ptr, width) {ptr.style.left = '-'+(width*progress)+'px';},
									duration: this.ROTATE_DURATION,
									ptr: this.obj[ptr],
									width: this.WIDTH});
					n_md.classList.add('elf-banners-minidot-selected');
					this.obj[next].style.zIndex = 1;
					this.obj[next].style.left = this.WIDTH+'px';
					this.animate({timing: ELF_Banners.animate_liner,
									draw(progress, ptr, width) {ptr.style.left = (width - width*progress)+'px';},
									duration: this.ROTATE_DURATION,
									callback: this.autoRotator,
									_self: this,
									ptr: this.obj[next],
									width: this.WIDTH});
					break;
			}
			this.ptr = next;
		}
	}
	autoRotator(obj) {
		if (obj.delay && (obj.cnt > 1))
			obj.rottm = setTimeout(() => obj.rotate(obj.direct), obj.delay);
		obj.rotateInProc = false;
//		console.log(obj.rotateInProc);
	}
	animate({timing, draw, callback, duration, ptr, width, _self}) {
		let start = performance.now();

		requestAnimationFrame(function animate(time) {
			// timeFraction изменяется от 0 до 1
			let timeFraction = (time - start) / duration;
			if (timeFraction > 1) timeFraction = 1;

			// вычисление текущего состояния анимации
			let progress = timing(timeFraction);

			draw(progress, ptr, width); // отрисовать её

			if (timeFraction < 1) {
				requestAnimationFrame(animate);
			}
			else if (callback)
				callback(_self);//(ptr, width);
		});
	}
	static animate_liner(tF) {
		return tF;
	}
	// COMMON
	static getXhrObject() {
		if(typeof XMLHttpRequest === 'undefined'){
			XMLHttpRequest = function() {
				try {
					return new window.ActiveXObject("Microsoft.XMLHTTP");
				}
				catch(err) {
					alert('ELF_Banners.getXhrObject error: Can not create XMLHttp object');
				}
			}
		}
		return new XMLHttpRequest();
	}
	xhrStateChange() {
		if (this.xhr.readyState == 1) { // .open Method called
			this.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		}
	}
	xhrResponse() {
		try {
			if (this.xhr.readyState == 4
				&& this.xhr.status == 200) {
				let resp;
				if (resp = ELF_Banners.getJson(this.xhr.response || this.xhr.responseText, true)) {
					if (resp && resp.error) {
						alert(resp.error);
					}
					else if (this.xhrCallback) {
						this.xhrCallback(this, resp);
					}
				}
				this.xhrCallback = null;
			}
		}
		catch (err) {
			alert('ELF_Banners.xhrResponse error: '+err.message);
		}
	}
	static getJson(str, showerr) {
		let ret;
		if (str) {
			try {
				ret = JSON.parse(str);
			} catch (err) {
				if (showerr) {
					alert('ELF_Banners.getJson error: '+err.message+' '+str.substr(0, 100)+'...');
					ret = false;
				}
				ret = true;
			}
		}
		else
			ret = true;
		return ret;
	}
}
