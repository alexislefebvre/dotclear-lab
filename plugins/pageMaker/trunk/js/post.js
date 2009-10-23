/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pageMaker, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/

jsToolBar.prototype.elements.pageMaker = {
	type: 'button',
	title: 'Post pager',
	icon: 'index.php?pf=pageMaker/img/bt_pagemaker.png',
	fn: {
		wiki: function() { this.encloseSelection("\n\n---\n\n",''); },
		xhtml: function() { this.encloseSelection("\n\n---\n\n",''); },
		wysiwyg: function() {
			var c = this.applyHtmlFilters(this.ibody.innerHTML);
			var s = '<p>---</p>';
			this.ibody.innerHTML = c + s;
		}
	}
};