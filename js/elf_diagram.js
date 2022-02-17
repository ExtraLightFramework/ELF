class ELF_Diagram {
	constructor(cont, data) {
		try {
			this.OFFSET_H = 100;
			this.OFFSET_V = 120;
			this.OFFSET_LEGEND = 40;
			
			this.sum = 0;
			data.forEach(item => {this.sum += Math.abs(item[1])});
			if (!this.sum)
				throw new Error('Empty values array');
			
			if (!(this.obj = document.getElementById(cont)))
				throw new Error('Can not find diagram container');
			
			this.center = {x:parseInt(this.obj.clientWidth/2),y:parseInt(this.obj.clientHeight/2)};
			this.radius = this.obj.clientWidth - this.OFFSET_H>this.obj.clientHeight - this.OFFSET_V?parseInt((this.obj.clientHeight - this.OFFSET_V)/2):parseInt((this.obj.clientWidth - this.OFFSET_H)/2);

			let rot = 0;
			let radrot = 0;
			let i = 0;
			this.coords = {};
			data.forEach(item => {
				this.coords[i] = {legend:{name:item[0]},
								value:item[1],
								modul:Math.abs(item[1]),
								percent:(item[1]/this.sum*100).toFixed(2)};
				this.coords[i].fi = (parseFloat((2*Math.PI*(this.coords[i].percent/100)))).toFixed(2);
				this.coords[i].rotate = rot;

				this.coords[i].legend.textx = this.center.x + Math.round((this.radius+this.OFFSET_LEGEND)*Math.sin(radrot+this.coords[i].fi/2));
				if (this.coords[i].legend.textx < this.center.x)
					this.coords[i].legend.textx -= 20;
				else
					this.coords[i].legend.textx += 15;
				this.coords[i].legend.texty = this.center.y - Math.round((this.radius+this.OFFSET_LEGEND)*Math.cos(radrot+this.coords[i].fi/2));

				rot += parseFloat((360*(this.coords[i].percent/100)));
				radrot += parseFloat((2*Math.PI*(this.coords[i].percent/100)));
				this.coords[i].x = this.center.x + Math.round(this.radius*Math.sin(this.coords[i].fi));
				this.coords[i].y = this.center.y - Math.round(this.radius*Math.cos(this.coords[i].fi));
				this.coords[i].vw = this.coords[i].fi<=Math.PI?'0,1':'1,1';
				this.coords[i].legend.x1 = this.center.x + Math.round(this.radius*Math.sin(this.coords[i].fi/2));
				this.coords[i].legend.x2 = this.center.x + Math.round((this.radius+this.OFFSET_LEGEND)*Math.sin(this.coords[i].fi/2));
				this.coords[i].legend.y1 = this.center.y - Math.round(this.radius*Math.cos(this.coords[i].fi/2));
				this.coords[i].legend.y2 = this.center.y - Math.round((this.radius+this.OFFSET_LEGEND)*Math.cos(this.coords[i].fi/2));
				i ++;
			});
			this._draw();
		}
		catch (err) {
			alert(err.message);
		}
	}
	_draw() {
		for (let i in this.coords) {
			let clr = ((1<<24)*Math.random()|0).toString(16);
			this.obj.innerHTML += '<path d="M'+this.center.x+','+this.center.y
									+'V'+(this.OFFSET_V/2)+'A'+this.radius+','+this.radius+' 0 '+this.coords[i].vw+' '+this.coords[i].x+','+this.coords[i].y
									+'Z" transform="rotate('+this.coords[i].rotate+' '+this.center.x+' '+this.center.y
									+')" stroke="transparent" stroke-width="0" fill="#'+clr+'"><title>'+this.coords[i].legend.name+' ('+this.coords[i].value+')</title></path>';
			this.obj.innerHTML += '<line x1="'+this.coords[i].legend.x1+'" y1="'+this.coords[i].legend.y1+'" x2="'+this.coords[i].legend.x2+'" y2="'+this.coords[i].legend.y2+'" stroke="#'+clr+'" stroke-width="1" transform="rotate('+this.coords[i].rotate+' '+this.center.x+' '+this.center.y+')" />';
			this.obj.innerHTML += '<text x="'+(this.coords[i].legend.textx)+'" y="'+this.coords[i].legend.texty+'" font-size="14" font-family="Tahoma,Arial,Verdana">'+parseInt(this.coords[i].percent)+'%</text>';
		}
	}
}
