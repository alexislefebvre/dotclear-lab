jsToolBar.prototype.elements.link.open_url = 'plugin.php?p=externalLinks&popup=1';

jsToolBar.prototype.elements.link.popup = function (args) {
  window.the_toolbar = this;
  args = args || '';
  args = args.replace('?','&');

  this.elements.link.data = {};
  var url = this.elements.link.open_url+args;
  
  var p_win = window.open(url,'dc_popup',
			  'alwaysRaised=yes,dependent=yes,toolbar=yes,height=420,width=380,'+
			  'menubar=no,resizable=yes,scrollbars=yes,status=no');
};

jsToolBar.prototype.elements.link.fncall.xhtml = function() {
  var data = this.elements.link.data;
	
  if (data.href == '') { return; }
	
  var stag = '<a href="'+data.href+'"';

  if (data.external==1) {
    stag += ' rel="external"';
  }
	
  if (data.hreflang) { 
    stag += ' hreflang="'+data.hreflang+'"'; 
  }

  stag += '>';
  var etag = '</a>';
	
  if (data.content) {
    this.encloseSelection('','',function() {
	return stag + data.content + etag;
      });
  } else {
    this.encloseSelection(stag,etag);
  }
};

jsToolBar.prototype.elements.link.fn.wysiwyg = function() {
  var href, hreflang, external;
  href = hreflang = '';external = 0;
  hreflang = this.elements.link.default_hreflang;
	
  var a = this.getAncestor();
  if (a.tagName == 'a') {
    href= a.tag.href || '';
    hreflang = a.tag.hreflang || '';
    if (a.tag.rel && a.tag.rel=='external') {
      external = 1;
    } else {
      external = 0;
    }
  }
	
  this.elements.link.popup.call(this,'?href='+href+'&hreflang='+hreflang+'&external='+external);
};
jsToolBar.prototype.elements.link.fncall.wysiwyg = function() {
  var data = this.elements.link.data;
	
  var a = this.getAncestor();

  if (a.tagName == 'a') {
    if (data.href == '') {
      // Remove link
      this.replaceNodeByContent(a.tag);
      this.iwin.focus();
      return;
    } else {
      // Update link
      a.tag.href = data.href;
      if (data.hreflang) {
	a.tag.setAttribute('hreflang', data.hreflang);
      } else {
	a.tag.removeAttribute('hreflang');
      }      
      if (data.external==1) {
	a.tag.setAttribute('rel', 'external');
      } else {
	a.tag.removeAttribute('rel');
      }
      
      return;
    }
  }
	
  // Create link
  if (data.content) {
    var n = document.createTextNode(data.content);
  } else {
    var n = this.getSelectedNode();
  }
  var a = this.iwin.document.createElement('a');
  a.href = data.href;
  if (data.hreflang) {
    a.setAttribute('hreflang', data.hreflang);
  }
  if (data.external==1) {
    a.setAttribute('rel', 'external');
  }
  a.appendChild(n);
  this.insertNode(a);
};
