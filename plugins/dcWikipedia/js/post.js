/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcWikipedia, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/
var dcWikipedia = { msg :{}, option :{}};

jsToolBar.prototype.elements.dcWikipedia = 
{
	type: 'button',
	title: 'Wikipedia',
	context: 'post',
	icon: 'index.php?pf=dcWikipedia/img/bt_dcwikipedia.png',
	fn:{},
	fncall:{},
	open_url:'plugin.php?p=dcWikipedia&popup=1',
	data:{},
	popup: function() {
		window.the_toolbar = this;
		this.elements.dcWikipedia.data = {};

		if (this.mode == 'wysiwyg') {
			var res, commonAncestorContainer;
			if (this.iwin.getSelection) { //gecko
				var selection = this.iwin.getSelection();
				res = selection.getRangeAt(0);
				commonAncestorContainer = res.commonAncestorContainer;
				while (commonAncestorContainer.nodeType != 1) {
					commonAncestorContainer = commonAncestorContainer.parentNode;
				}
			} else { //ie
				res = this.iwin.document.selection.createRange();
				commonAncestorContainer = res.parentElement();
			}
		}
		else {
			this.textarea.focus();

			var start, end, sel, scrollPos, subst, res;

			if (typeof(document["selection"]) != "undefined") {
				sel = document.selection.createRange().text;
			} else if (typeof(this.textarea["setSelectionRange"]) != "undefined") {
				start = this.textarea.selectionStart;
				end = this.textarea.selectionEnd;
				scrollPos = this.textarea.scrollTop;
				sel = this.textarea.value.substring(start, end);
			}

			if (sel.match(/ $/)) { // exclude ending space char, if any
				sel = sel.substring(0, sel.length - 1);
				suffix = suffix + " ";
			}

			res = (sel) ? sel : '';
		}

		if (res == '') {
				alert(dcWikipedia.msg.noselection);
		}
		else {
			var p_win = window.open(
				this.elements.dcWikipedia.open_url+'&value='+res+'&lang='+$('#post_lang').val(),
				'dc_popup',
				'alwaysRaised=yes,dependent=yes,toolbar=yes,height=500,width=760,' +
				'menubar=no,resizable=yes,scrollbars=yes,status=no'
			);
		}
	},

	gethtml: function()
	{
		var d = this.data;

		var res = '';

		if (d.dcWikipediaValue != '0')
		{
			res += '<a href="'+d.dcWikipediaUri+'" class="dcwikipedia" title="'+d.dcWikipediaValue+'" ';
			if (dcWikipedia.option.langFlag == 'yes')
			{
				res += 'hreflang="'+$('#post_lang').val()+'"';
			}
			res += '>';
		}

		res += d.dcWikipediaValue;

		if (d.dcWikipediaValue != '0')
		{
			res += '</a>';
		}

		return res;
	},

	getwiki: function()
	{
		var d = this.data;

		var res = '';

		if (d.dcWikipediaValue != '0')
		{
			res += '[';
		}

		res += d.dcWikipediaValue;

		if (d.dcWikipediaValue != '0')
		{
			res += '|'+d.dcWikipediaUri;
			if (dcWikipedia.option.langFlag == 'yes')
			{
				res += '|'+$('#post_lang').val();
			}
			res += ']';
		}

		return res;
	}
};

jsToolBar.prototype.elements.dcWikipedia.fn.wiki = function()
{
	this.elements.dcWikipedia.popup.call(this);
};
jsToolBar.prototype.elements.dcWikipedia.fn.xhtml = function()
{
	this.elements.dcWikipedia.popup.call(this);
};
jsToolBar.prototype.elements.dcWikipedia.fn.wysiwyg = function()
{
	this.elements.dcWikipedia.popup.call(this);
};

jsToolBar.prototype.elements.dcWikipedia.fncall.wiki = function()
{
	var wiki = this.elements.dcWikipedia.getwiki();

	this.encloseSelection(
		'',
		'',
		function()
		{
			return wiki;
		}
	);
};
jsToolBar.prototype.elements.dcWikipedia.fncall.xhtml = function()
{
	var html = this.elements.dcWikipedia.gethtml();

	this.encloseSelection(
		'',
		'',
		function()
		{
			return html;
		}
	);
};

jsToolBar.prototype.elements.dcWikipedia.fncall.wysiwyg = function()
{
	var n = this.getSelectedNode();
	var a = this.iwin.document.createElement('a');

	a.href = this.elements.dcWikipedia.data.dcWikipediaUri;
	
	a.setAttribute('class','dcwikipedia');
	a.setAttribute('titel',this.elements.dcWikipedia.data.dcWikipediaValue);

	if (dcWikipedia.option.langFlag == 'yes') a.setAttribute('hreflang',$('#post_lang').val());

	a.appendChild(n);

	this.insertNode(a);
};