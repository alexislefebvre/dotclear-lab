// Originally written by Olivier Meunier and modified by Oleksandr Syenchuk
// Modified by Charles Delorme (getwiki) for dcFlickr


$(function() {
	$('#edit-entry').onetabload(function() {
		
	});
});

jsToolBar.prototype.elements.dcflickr = 
{
	type: 'button',
	title: 'dcflickr',
	context: 'post',
	icon: 'index.php?pf=dcFlickr/bt_dcflickr.png',
	fn:{},
	fncall:{},
	open_url:'plugin.php?p=dcFlickr&popup=1',
	data:{},
	popup: function() {
		window.the_toolbar = this;
		this.elements.dcflickr.data = {};
		
		var p_win = window.open(this.elements.dcflickr.open_url,'dc_popup',
		'alwaysRaised=yes,dependent=yes,toolbar=yes,height=500,width=760,'+
		'menubar=no,resizable=yes,scrollbars=yes,status=no');
	},
	
	gethtml: function() {
		var d = this.data;
		
		if (d.dcflickrIsValidUrl == 0) { return; }
		
		var res = '<!-- dcFlickr version 0.1 - start -->\n';
		
		res += '<div class="dcflickrdiv"';
		
		if (d.dcflickrAlignment == 'left') {
			res += ' style="float: left; margin: 0 1em 1em 0;"';
		} else if (d.dcflickrAlignment == 'right') {
			res += ' style="float: right; margin: 0 0 1em 1em;"';
		} else if (d.dcflickrAlignment == 'center') {
			res += ' style="margin: 1em auto; text-align: center;"';
		}		
		res += '>\n';
		
 		res += '<a href="'+d.dcflickrHref+'">';
 		res += '<img src="'+d.dcflickrImg+'" class="dcflickrimg" />';
 		res += '</a>';
 		
		if (d.dcflickrTitle) 
		{
			if (d.dcflickrHref) 
			{
    		res += '<br />\n<a href="'+d.dcflickrPhotopage+'">';
    		res += d.dcflickrTitle;
    		res += '</a>';
			}
			res += '\n<br />';
		}
		
		res += '\n</div>\n';
		
		res += '<!-- dcFlickr version 0.1 - end -->';
	
		return res;
	},
	
	getwiki: function() {
		var d = this.data;
		
		if (d.dcflickrIsValidUrl == 0) { return; }
		
		var res = '\n';
    
    res += '[(('+d.dcflickrImg+'|'+d.dcflickrTitle;
		
		if (d.dcflickrAlignment == 'left') {
			res += '|G';
		} else if (d.dcflickrAlignment == 'right') {
			res += '|D';
		} else if (d.dcflickrAlignment == 'center') {
			res += '|C';
		}
		
    res += '))|'+d.dcflickrHref+']\n\n';
		
		if (d.dcflickrTitle) 
		{
		  res += '\'\'['+d.dcflickrTitle+'|';
			if (d.dcflickrPhotopage) 
			{
        res += d.dcflickrPhotopage;
			}
			else
			{
			  res += d.dcflickrHref;
			}

			res += ']\'\'';
		}
	
		return res;
	}	
	
};

jsToolBar.prototype.elements.dcflickr.fn.wiki = function() {
	this.elements.dcflickr.popup.call(this);
};
jsToolBar.prototype.elements.dcflickr.fn.xhtml = function() {
	this.elements.dcflickr.popup.call(this);
};
jsToolBar.prototype.elements.dcflickr.fn.wysiwyg = function() {
	this.elements.dcflickr.popup.call(this);
};

jsToolBar.prototype.elements.dcflickr.fncall.wiki = function() {
	var wiki = this.elements.dcflickr.getwiki();
	
	this.encloseSelection('','',function() {
		return wiki;
	});
};
jsToolBar.prototype.elements.dcflickr.fncall.xhtml = function() {
	var html = this.elements.dcflickr.gethtml();
	
	this.encloseSelection('','',function() {
		return html;
	});
};

jsToolBar.prototype.elements.dcflickr.fncall.wysiwyg = function() {
	var blockLevel = this.getBlockLevel();
	if (blockLevel !== null) {
		this.replaceNodeByContent(blockLevel);
	}
	
	var html = this.elements.dcflickr.gethtml();
	var div = this.iwin.document.createElement('div');
	div.innerHTML = html+'<p> </p>';
	media = div.firstChild;
	this.insertNode(media);
};
