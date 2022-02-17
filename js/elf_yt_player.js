// YouTube player 
var elf_yt_players = {};

function ELF_YTPlayer(cont, youtube_id, seek, autostart, w, h) {
	this.id = cont;
	this.yt_id = youtube_id;
	this.w = (typeof w != 'undefined')&&w?parseInt(w):400;
	this.h = (typeof h != 'undefined')&&h?parseInt(h):300;
	this.autostart = (typeof autostart != 'undefined')&&autostart?true:false;
	this.seek = (typeof seek != 'undefined')&&seek?parseInt(seek):parseInt(0);
	this._init();
	elf_yt_players[this.id] = this;
}

ELF_YTPlayer.prototype = {
	
	constructor: ELF_YTPlayer,
	
	_init: function() {
//		let obj = this;
		if (typeof YT == 'undefined') {
			// Init YouTube API
			let tag = document.createElement('script');
			tag.src = "http://www.youtube.com/player_api";
			let firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
		}
//		setTimeout(() => this._create(this.yt_id, this.w, this.h), 1000);
	},
	_create: function(yid, w, h) {
		this.player = new YT.Player(this.id, {
			enablejsapi: 1,
			modestbranding: 1,
			origin: 'http://forecasts.srv',
			height: h,
			width: w,
			videoId: yid,
			showinfo: 0,
			controls: 0
		});
	},
	_play: function() {
//		let obj = this;
		if (this.seek)
			setTimeout(() => this.player.seekTo(obj.seek), 2000);
		if (this.autostart)
			setTimeout(() => obj.player.playVideo(), 3000);
	},
	_goto: function(seek) {
		this.seek = parseInt(seek);
		this.player.seekTo(this.seek);
//		this.player.playVideo();
//		setTimeout(() => this.player.stopVideo(), 100);
	}
}

function onYouTubeIframeAPIReady() {
	alert(typeof YT);
//	player = new YT.Player('player', {
//		height: '360',
//		width: '640',
//		videoId: 'M7lc1UVf-VE',
//		events: {
//			'onReady': onPlayerReady,
//			'onStateChange': onPlayerStateChange
//		}
//	});
}