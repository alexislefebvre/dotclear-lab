/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/
jsToolBar.prototype.elements.multiToc = {
	type: 'button',
	title: 'Table of content',
	icon: 'index.php?pf=multiToc/img/bt_multitoc.png',
	fn: {
		wiki: function() { this.encloseSelection("\n\n::TOC::\n\n",''); },
		xhtml: function() { this.encloseSelection("\n<p>::TOC::</p>\n",''); },
		wysiwyg: function() {
			var c = this.applyHtmlFilters(this.ibody.innerHTML);
			var s = '<p>::TOC::</p>';
			this.ibody.innerHTML = c + s;
		}
	}
};