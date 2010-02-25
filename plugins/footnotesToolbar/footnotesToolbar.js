// vim: set noexpandtab tabstop=5 shiftwidth=5:
// -- BEGIN LICENSE BLOCK ----------------------------------
// This file is part of footnotesToolbar, a plugin for Dotclear.
// 
// Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
// 
// Licensed under the AGPL version 3.0.
// A copy of this license is available in LICENSE file or at
// http://www.gnu.org/licenses/agpl-3.0.html
// -- END LICENSE BLOCK ------------------------------------

jsToolBar.prototype.elements.footnotes = 
{
	type: 'button',
	title: 'Footnote',
	section_name: 'Notes',
	icon: 'index.php?pf=footnotesToolbar/footnote.png',
	fn:{},
	fncall:{}
};
jsToolBar.prototype.elements.footnotes.getnoteszone = function(iwin) {
	var divs = iwin.document.getElementsByTagName("div");
	if (!divs) {
		return null;
	}
	for (var i=0; i < divs.length; i++) {
		if (divs[i].className == "footnotes") {
			return divs[i];
		}
	}
	return null;
};
jsToolBar.prototype.elements.footnotes.getnum = function(iwin){
	var noteszone = this.getnoteszone(iwin);
	if (! noteszone) {
		return 1;
	}
	var cur_num = 1;
	var new_num;
	var notes = noteszone.getElementsByTagName("a");
	for (var i=0; i < notes.length; i++) {
		if (notes[i].id.match(/^pnote-/)) {
			var noteid = notes[i].id.replace(/^pnote-/,"");
			new_num = parseInt(noteid, 10);
			if (new_num > cur_num) {
				cur_num = new_num;
			}
		}
	}
	return cur_num + 1;
};
jsToolBar.prototype.elements.footnotes.fn.wiki = function() {
	this.singleTag("$$");
};
jsToolBar.prototype.elements.footnotes.fn.xhtml = function() {
	var cur_num = jsToolBar.prototype.elements.footnotes.getnum(this.iwin);
	var section_name = jsToolBar.prototype.elements.footnotes.section_name;
	this.encloseSelection("",
	    '<sup>[<a href="#pnote-'+cur_num+'" id="rev-pnote-'+cur_num+'">'
	   +cur_num+'</a>]</sup><p>'
	   +'<div class="footnotes"><h4>'+section_name+'</h4>\n'
	   +'<p>[<a href="#rev-pnote-'+cur_num+'" id="pnote-'+cur_num+'">'
	   +cur_num+'</a>] </p></div>');
};
jsToolBar.prototype.elements.footnotes.fn.wysiwyg = function() {
	var cur_num = jsToolBar.prototype.elements.footnotes.getnum(this.iwin);
	var fnote = this.iwin.document.createElement('sup');
	fnote.innerHTML = '[<a href="#pnote-'+cur_num+'" '
	                 +'id="rev-pnote-'+cur_num+'">'+cur_num+'</a>]';
	this.insertNode(fnote);
	this.insertNode(this.iwin.document.createTextNode(" "));
	// add the footnotes section
	var noteszone = jsToolBar.prototype.elements.footnotes.getnoteszone(this.iwin);
	if (! noteszone) {
		noteszone = this.iwin.document.createElement('div');
		noteszone.className = "footnotes";
		var section_name = jsToolBar.prototype.elements.footnotes.section_name;
		noteszone.innerHTML = '<h4>'+section_name+'</h4>\n';
		//this.insertNode(noteszone);
		this.iwin.document.body.appendChild(noteszone);
	}
	// add the new footnote in the footnotes section
	noteszone.innerHTML = noteszone.innerHTML
	                     +'<p>[<a href="#rev-pnote-'+cur_num+'" '
	                     +'id="pnote-'+cur_num+'">'+cur_num+'</a>]&nbsp; </p>';
};

//jsToolBar.prototype.elements.footnotes.fncall.wiki = function() {
//};
//jsToolBar.prototype.elements.footnotes.fncall.xhtml = function() {
//};
//jsToolBar.prototype.elements.footnotes.fncall.wysiwyg = function() {
//};

