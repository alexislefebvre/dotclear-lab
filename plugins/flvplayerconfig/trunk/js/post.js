/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of flvplayerconfig, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2010 Rasibus Master and contributors
 * postmaster@rasib.us
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

jsToolBar.prototype.elements.flvplayerconfig = {
	type:'button',
	title:'FLV Player',
	icon:'index.php?pf=flvplayerconfig/icon.png',
	fn:{},
	fncall:{},
	open_url:'plugin.php?p=flvplayerconfig&media=1&popup=1',
	data:{},
	popup:function(){
		window.the_toolbar=this;
		this.elements.flvplayerconfig.data={};
		var p_win=window.open(this.elements.flvplayerconfig.open_url,'dc_popup','alwaysRaised=yes,dependent=yes,toolbar=yes,height=600,width=810,'+'menubar=no,resizable=no,scrollbars=no,status=no');
	}
};

jsToolBar.prototype.elements.flvplayerconfig.fn.wiki=function() {
	this.elements.flvplayerconfig.popup.call(this);
};
jsToolBar.prototype.elements.flvplayerconfig.fn.xhtml=function(){
	this.elements.flvplayerconfig.popup.call(this);
};
jsToolBar.prototype.elements.flvplayerconfig.fn.wysiwyg=function(){
	this.switchEdit();
	this.elements.flvplayerconfig.popup.call(this);
};
jsToolBar.prototype.elements.flvplayerconfig.fncall.wiki=function(){
	var d = this.elements.flvplayerconfig.data;
	res = '';
	for (i in d) res += '\n\t'+i+'='+d[i]+'; ';
	this.encloseSelection('[flvplayer'+res+'\n]\n','\n[/flvplayer]');
};
jsToolBar.prototype.elements.flvplayerconfig.fncall.xhtml=function(){
	var d = this.elements.flvplayerconfig.data;
	res = '';
	for (i in d) res += '\n\t'+i+'='+d[i]+'; ';
	this.encloseSelection('[flvplayer'+res+'\n]\n','\n[/flvplayer]');
	this.switchEdit();
};