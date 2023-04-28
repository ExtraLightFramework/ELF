class ELF_Video_player {
	constructor(cont, params) {
		try {
			if (!(this.root = document.getElementById(cont)))
				throw new Error('Container '+cont+' for video not found');
			this.root.classList.add('elf-video-player-cont');
			
			let wh = getComputedStyle(this.root);
			this.WIDTH = parseInt(wh.width);
			this.HEIGHT = parseInt(wh.height);
			this.ORIENT = (this.WIDTH>this.HEIGHT)?'horizontal':'vertical';
			
			if (params) {
				if (params.url)
					this.VIDEO_URL = params.url;
				else
					throw new Error('Video URL is not set');
//				if (params.codec)
//					this.VIDEO_CODEC = 'video/'+params.codec;
//				else
					this.VIDEO_CODEC = 'video/mp4; codecs="avc1.42E01E, mp4a.40.2"';
				if (params.width)
					this.VIDEO_WIDTH = params.width;
				else
					this.VIDEO_WIDTH = this.width;
				if (params.height)
					this.VIDEO_HEIGHT = params.height;
				else
					this.VIDEO_HEIGHT = this.height;
				if (params.orientation)
					this.VIDEO_ORIENT = params.orientation;
				else
					this.VIDEO_ORIENT = (this.width>this.height)?'horizontal':'vertical';
				this.VIDEO_COOF = Math.round((this.VIDEO_WIDTH/this.VIDEO_HEIGHT) * 100)/100;
			}
			if (this.VIDEO_ORIENT == 'horizontal') {
				this.VIDEO_WIDTH = this.WIDTH;
				this.VIDEO_HEIGHT = parseInt(this.VIDEO_WIDTH/this.VIDEO_COOF);
			}
			else { //vertical
				this.VIDEO_HEIGHT = this.HEIGHT;
				this.VIDEO_WIDTH = parseInt(this.VIDEO_HEIGHT/this.VIDEO_COOF);
			}
			this.video = document.createElement('video');
			this.video.setAttribute('width',this.VIDEO_WIDTH);
			this.video.setAttribute('height',this.VIDEO_HEIGHT);
			let source = document.createElement('source');
			source.setAttribute('src', this.VIDEO_URL);
			source.setAttribute('type', this.VIDEO_CODEC);
			this.video.append(source);
			this.root.append(this.video);
			
			this.video.play();
			
			console.log(this.VIDEO_WIDTH);
		}
		catch(err) {
			alert('ELF_Video_player constructor error: '+err.message+' Stack: '+err.stack);
		}
	}
}