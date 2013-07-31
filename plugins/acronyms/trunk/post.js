jsToolBar.prototype.elements.acronyms = {
	type: 'button',
	title: 'Acronym',
	msg_title: 'Title?',
	msg_lang: 'Lang?',
	icon: 'index.php?pf=acronyms/icon.png',
	fn:{}
};
jsToolBar.prototype.elements.acronyms.fn.wiki = function() {
	this.encloseSelection('','',function(str) {
		if (str == '') { window.alert(dotclear.msg.no_selection); return ''; }
		var title = window.prompt(jsToolBar.prototype.elements.acronyms.msg_title);
		return '??' + str + (title ? '|'+title : '') + '??';
	});
};
jsToolBar.prototype.elements.acronyms.fn.xhtml = function() {
	this.encloseSelection('','',function(str) {
		if (str == '') { window.alert(dotclear.msg.no_selection); return ''; }
		var title = window.prompt(jsToolBar.prototype.elements.acronyms.msg_title);
		var lang = window.prompt(jsToolBar.prototype.elements.acronyms.msg_lang);
		return '<acronym' + (title ? ' title="'+title+'"' : '') + (lang ? ' lang="'+lang+'"' : '') + '>' + str + '</acronym>';
	});
};
jsToolBar.prototype.elements.acronyms.fn.wysiwyg = function() {
	var t = this.getSelectedText();

	if (t == '') { window.alert(dotclear.msg.no_selection); return; }
	var title = window.prompt(jsToolBar.prototype.elements.acronyms.msg_title);
	var lang = window.prompt(jsToolBar.prototype.elements.acronyms.msg_lang);

	var n = this.getSelectedNode();
	var acronym = document.createElement('acronym');
	if (title) { acronym.title = title;	}
	if (lang) { acronym.lang = lang;	}
	acronym.appendChild(n);
	this.insertNode(acronym);
};
