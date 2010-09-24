
jsToolBar.prototype.elements.flvplayerconfig = {
	type:'button',
	title:'FLV Player',
	icon:'index.php?pf=flvplayerconfig/icon.png',
	fn:{},
	fncall:{},
	open_url:'plugin.php?p=flvplayerconfig&media=1&popup=1',
	//open_url:'media.php?popup=1',
	data:{},
	popup:function(){
		window.the_toolbar=this;
		this.elements.flvplayerconfig.data={};
		var p_win=window.open(this.elements.flvplayerconfig.open_url,'dc_popup','alwaysRaised=yes,dependent=yes,toolbar=yes,height=600,width=810,'+'menubar=no,resizable=no,scrollbars=no,status=no');
	},
	gethtml:function(){
		var d = this.data;
		if (d.m_encodemath=='') {
			return false;
		}
		return '((http://www.algebra.com/cgi-bin/plot-formula.mpl?expression='+d.m_encodemath+'))';
	}
};

jsToolBar.prototype.elements.flvplayerconfig.fn.wiki=function() {
	this.elements.flvplayerconfig.popup.call(this);
};
jsToolBar.prototype.elements.flvplayerconfig.fn.xhtml=function(){
	this.elements.flvplayerconfig.popup.call(this);
};
jsToolBar.prototype.elements.flvplayerconfig.fncall.wiki=function(){
	var html=this.elements.flvplayerconfig.gethtml();
	this.encloseSelection('','',function(){
		return '\n'+html+'\n';
	});
};
jsToolBar.prototype.elements.flvplayerconfig.fncall.xhtml=function(){
	var html=this.elements.flvplayerconfig.gethtml();
	this.encloseSelection('','',function(){
		return html;
	});
};