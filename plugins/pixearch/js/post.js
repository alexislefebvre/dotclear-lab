// Originally written by Olivier Meunier and modified by Oleksandr Syenchuk
// Modified by Hadrien Lanneau for pixearch


$(function() {
	$('#edit-entry').onetabload(function() {
		
	});
});

jsToolBar.prototype.elements.pixearch = 
{
	type: 'button',
	title: 'Pixearch',
	context: 'post',
	icon: 'index.php?pf=pixearch/img/bt_pixearch.png',
	fn:{},
	fncall:{},
	open_url:'plugin.php?p=pixearch&popup=1',
	data:{},
	popup: function() {
		window.the_toolbar = this;
		this.elements.pixearch.data = {};
		
		var p_win = window.open(
			this.elements.pixearch.open_url,
			'dc_popup',
			'alwaysRaised=yes,dependent=yes,toolbar=yes,height=500,width=760,' +
			'menubar=no,resizable=yes,scrollbars=yes,status=no'
		);
	},
	
	gethtml: function()
	{
		var d = this.data;
		
		var res = '<div class="externalPicture"';
		
		if (d.pixearchAlign == 'left')
		{
			res += ' style="float: left; margin: 0 1em 1em 0;"';
		}
		else if (d.pixearchAlign == 'right')
		{
			res += ' style="float: right; margin: 0 0 1em 1em;"';
		}
		else if (d.pixearchAlign == 'center')
		{
			res += ' style="margin: 1em auto; text-align: center;"';
		}		
		res += '>\n';
		
		if (d.pixearch_insert != 0)
		{
			res += '<a href="' + d.pixearch_insert + '">';
		}
		res += '<img src="' + d.pixearchUri+'" class="pixearchImg" alt="' + d.pixearchTitle + '" />';
		if (d.pixearch_insert != 0)
		{
			res += '</a>';
		}
		
		res += '\n</div>\n';
	
		return res;
	},
	
	getwiki: function() {
		var d = this.data;
		
		var res = '';
		
		if (d.pixearchInsert != '0')
		{
			res += '[';
		}
		
		res += '(('+d.pixearchUri+'|'+d.pixearchTitle;
		
		if (d.pixearchAlign == 'left') {
			res += '|G';
		} else if (d.pixearchAlign == 'right') {
			res += '|D';
		} else if (d.pixearchAlign == 'center') {
			res += '|C';
		}
		
		res += '))';
		
		if (d.pixearchInsert != '0')
		{
			res += '|' + d.pixearchInsert + ']';
		}
		
		return res;
	}
};

jsToolBar.prototype.elements.pixearch.fn.wiki = function()
{
	this.elements.pixearch.popup.call(this);
};
jsToolBar.prototype.elements.pixearch.fn.xhtml = function()
{
	this.elements.pixearch.popup.call(this);
};
jsToolBar.prototype.elements.pixearch.fn.wysiwyg = function()
{
	this.elements.pixearch.popup.call(this);
};

jsToolBar.prototype.elements.pixearch.fncall.wiki = function()
{
	var wiki = this.elements.pixearch.getwiki();
	
	this.encloseSelection(
		'',
		'',
		function()
		{
			return wiki;
		}
	);
};
jsToolBar.prototype.elements.pixearch.fncall.xhtml = function()
{
	var html = this.elements.pixearch.gethtml();
	
	this.encloseSelection(
		'',
		'',
		function()
		{
			return html;
		}
	);
};

jsToolBar.prototype.elements.pixearch.fncall.wysiwyg = function()
{
	var blockLevel = this.getBlockLevel();
	if (blockLevel !== null)
	{
		this.replaceNodeByContent(blockLevel);
	}
	
	var html = this.elements.pixearch.gethtml();
	var div = this.iwin.document.createElement('div');
	div.innerHTML = html + '<p> </p>';
	media = div.firstChild;
	this.insertNode(media);
};
