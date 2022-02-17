class ELF_ScrollBar {
	constructor(cont, params) {
		try {
			this.root = document.getElementById(cont);
			this.scrollcont = document.createElement('div');
			this.scrollcont.id = (params && params.id)?params.id:'elf-scrollbar-cont-'+parseInt(Math.random()*100000);
			this.scrollcont.innerHTML = this.root.innerHTML;
			this.root.innerHTML = '';
			this.root.append(this.scrollcont);

			if (params && params.type)
				this.TYPE = params.type;
			if (this.TYPE != 'horizontal' && this.TYPE != 'vertical')
				this.TYPE = 'vertical';
			
			this.scrollcont.classList.add('elf-scrollbar-cont','elf-scrollbar-cont-'+this.TYPE);
			this.scrollcont.style.visibility = 'visible';
			if (this.TYPE == 'horizontal')
				this.scrollcont.style.display = 'flex';
			
			// ==== Init Scrolling
			this.scrollbar = document.createElement('div');
			this.scrollbar.classList.add('elf-scrollbar','elf-scrollbar-'+this.TYPE);

			this.OFFSET_V = this.OFFSET_H = 20;
			this.CONT_HEIGHT = this.scrollcont.getBoundingClientRect().height;
			this.CONT_WIDTH = this.scrollcont.getBoundingClientRect().width;
			this.ROOT_HEIGHT = params&&params.height?parseInt(params.height):this.root.getBoundingClientRect().height;
			if (params&&params.height)
				this.root.style.height = params.height+'px';
			this.ROOT_WIDTH = params&&params.width?parseInt(params.width):this.root.getBoundingClientRect().width;
			if (params&&params.width)
				this.root.style.width = params.width+'px';

			if (params && params.position)
				this.position = params.position;
			else
				this.position = 'right';
			if (this.position != 'right' && this.position != 'left'
				&& this.position != 'top' && this.position != 'bottom')
				this.position = 'right';
			if (params && params.offset) {
				if (params.offset.vertical || typeof params.offset.vertical != 'undefined')
					this.OFFSET_V = parseInt(params.offset.vertical);
				if (params.offset.horizontal || typeof params.offset.horizontal != 'undefined')
					this.OFFSET_H = parseInt(params.offset.horizontal);
			}
			
			if (this.TYPE == 'vertical') {
				this.SCROLL_HEIGHT = this.ROOT_HEIGHT - this.OFFSET_V*2;
				this.scrollbar.style.height = this.SCROLL_HEIGHT + 'px';
				this.scrollbar.style.top = this.OFFSET_V+'px';
				this.scrollbar.style.left = '0px';
			}
			else {
				this.SCROLL_WIDTH = this.ROOT_WIDTH - this.OFFSET_H*2;
				this.scrollbar.style.width = this.SCROLL_WIDTH + 'px';
				this.scrollbar.style.left = this.OFFSET_H+'px';
				this.scrollbar.style.top = '0px';
			}
			
			switch (this.position) {
				case 'left':
					this.scrollbar.style.left = this.OFFSET_H+'px';
					break;
				case 'right':
					this.scrollbar.style.left = (this.ROOT_WIDTH - this.OFFSET_H)+'px';
					break;
				case 'top':
					this.scrollbar.style.top = this.OFFSET_V+'px';
					break;
				case 'bottom':
					this.scrollbar.style.top = (this.ROOT_HEIGHT - this.OFFSET_V)+'px';
					break;
			}
			
			// ===== Init scrollbar cursor
			this.cursor = document.createElement('div');
			this.cursor.classList.add('elf-scrollbar-cursor','elf-scrollbar-cursor-'+this.TYPE);
			this.recalcCursorSize();
			
			this.cursor.addEventListener('dragstart', () => this.cursorDragStart());
			this.scrollbar.addEventListener('click', () => this.scrollClick());
			this.scrollbar.addEventListener('mousemove', () => this.scrollMouseMove());
			this.root.addEventListener('mousemove', () => this.scrollMouseMove());
			this.scrollbar.addEventListener('mousedown', () => this.scrollMouseDown());
			this.scrollbar.addEventListener('mouseup', () => this.scrollMouseUp());
			this.root.addEventListener('mouseup', () => this.scrollMouseUp());
			this.root.addEventListener('mouseleave', () => this.scrollMouseOut());
			this.root.addEventListener('wheel', () => this.rootScroll());
			this.scrollbar.addEventListener("selectstart", () => this.scrollSelectStart());
			this.root.addEventListener("selectstart", () => this.scrollSelectStart());
			
			this.scrollbar.append(this.cursor);
			this.root.append(this.scrollbar);
			
			this.TOP = this.LEFT = 0;
			this.BOTTOM = this.SCROLL_HEIGHT - this.cursor.HEIGHT;
			this.RIGHT = this.SCROLL_WIDTH - this.cursor.WIDTH;
			if (params && params.onresize)
				this.resizeSniffer();
			else
				this.calcCoof(this.TYPE=='vertical'?this.CONT_HEIGHT:this.CONT_WIDTH);
		}
		catch (err) {
			alert('ELF_ScrollBar constructor error: '+err.message);
		}
	}
	resizeSniffer() {
		if (this.TYPE == 'vertical' && this.scrollcont.offsetHeight != this.CONT_HEIGHT) {
			this.CONT_HEIGHT = this.scrollcont.offsetHeight;
			this.calcCoof(this.CONT_HEIGHT);
			this.recalcCursorSize();
		}
		else if (this.TYPE == 'horizontal' && this.scrollcont.offsetWidth != this.CONT_WIDTH) {
			this.CONT_WIDTH = this.scrollcont.offsetWidth;
			this.calcCoof(this.CONT_WIDTH);
			this.recalcCursorSize();
		}
		setTimeout(() => this.resizeSniffer(), 1000);
	}
	recalcCursorSize() {
		if (this.TYPE == 'vertical') {
			this.cursor.HEIGHT = this.SCROLL_HEIGHT*(this.SCROLL_HEIGHT/this.CONT_HEIGHT);
			this.cursor.style.height = this.cursor.HEIGHT+'px';
			this.cursor.WIDTH = this.cursor.getBoundingClientRect().width;
		}
		else {
			this.cursor.WIDTH = this.SCROLL_WIDTH*(this.SCROLL_WIDTH/this.CONT_WIDTH);
			this.cursor.style.width = this.cursor.WIDTH+'px';
			this.cursor.HEIGHT = this.cursor.getBoundingClientRect().height;
		}
	}
	calcCoof(hw) {
		if (this.TYPE == 'vertical') {
			this.COOF = hw / this.ROOT_HEIGHT;//(hw+this.OFFSET_V*4-this.ROOT_HEIGHT) / this.ROOT_HEIGHT;
		}
		else {
			this.COOF = hw / this.ROOT_WIDTH;//(hw+this.OFFSET_H*4-this.ROOT_WIDTH) / this.ROOT_WIDTH;
		}
//		this.COOF = 2;
		// hide scrollbar if content HEIGHT/WIDTH smaller then container HEIGHT
		if (this.COOF <= 0)
			this.scrollbar.style.display = 'none';
		else
			this.scrollbar.style.display = 'block';
	}
	cursorDragStart() {
		return false;
	}
	cursorMouseDown() {
		this.cursor.lock = true;
	}
	scrollSelectStart () {
		if (this.cursor.lock) {
			event.preventDefault();
			return false;
		}
	}
	scrollClick() {
		this.cursor.lock = false;
		if (this.TYPE == 'vertical')
			this.moveAtY(event.pageY);
		else {
			//alert(event.pageX+' '+event.clientX+' '+event.offsetX);
			this.moveAtX(event.clientX - this.scrollbar.getBoundingClientRect().left);
		}
	}
	scrollMouseDown() {
		this.cursor.lock = true;
	}
	scrollMouseUp() {
		this.cursor.lock = false;
	}
	scrollMouseOut() {
		this.cursor.lock = false;
	}
	scrollMouseMove() {
		if (this.cursor.lock) {
			if (this.TYPE == 'vertical')
				this.moveAtY(event.pageY);
			else
				this.moveAtX(event.clientX - this.scrollbar.getBoundingClientRect().left);
		}
	}
	rootScroll() {
		event.preventDefault();
		if (this.TYPE == 'vertical')
			this.moveAtYOff(event.deltaY/4);
		else
			this.moveAtXOff(event.deltaY/4);
		return false;
	}
	moveAtY(y) {
		let _top = y - this.OFFSET_V - this.cursor.HEIGHT/2;
		if (_top < this.TOP)
			_top = this.TOP;
		else if (_top > this.BOTTOM)
			_top = this.BOTTOM;
		if (_top != parseInt(this.cursor.style.top)) {
			this.cursor.style.top = _top+'px';
			this.scrollcont.style.marginTop = '-'+(_top*this.COOF)+'px';
		}
	}
	moveAtX(x) {
		let _left = x - this.OFFSET_H - this.cursor.WIDTH/2;
		if (_left < this.LEFT)
			_left = this.LEFT;
		else if (_left > this.RIGHT)
			_left = this.RIGHT;
		if (_left != parseInt(this.cursor.style.left)) {
			this.cursor.style.left = _left+'px';
			this.scrollcont.style.marginLeft = '-'+(_left*this.COOF)+'px';
		}
	}
	moveAtYOff(y) {
		let _top = parseInt(this.cursor.style.top) + y;
		_top = _top?_top:y;
		if (_top < this.TOP)
			_top = this.TOP;
		else if (_top > this.BOTTOM)
			_top = this.BOTTOM;
		if (_top != parseInt(this.cursor.style.top)) {
			this.cursor.style.top = _top+'px';
			this.scrollcont.style.marginTop = '-'+(_top*this.COOF)+'px';
		}
	}
	moveAtXOff(x) {
		let _left = parseInt(this.cursor.style.left) + x;
		_left = _left?_left:x;
		if (_left < this.LEFT)
			_left = this.LEFT;
		else if (_left > this.RIGHT)
			_left = this.RIGHT;
		if (_left != parseInt(this.cursor.style.left)) {
			this.cursor.style.left = _left+'px';
			this.scrollcont.style.marginLeft = '-'+(_left*this.COOF)+'px';
		}
	}
	static _get_height(obj) {
		let wh = getComputedStyle(obj);
		return wh.height?parseInt(wh.height):0;
	}
}