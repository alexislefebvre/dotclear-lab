/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of splitPost, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/

jsToolBar.prototype.elements.splitPost = {
	type: 'button',
	title: 'Post pager',
	icon: 'index.php?pf=splitPost/img/bt_splitpost.png',
	fn: {
		wiki: function() { this.encloseSelection("---\n",'') },
		xhtml: function() { this.encloseSelection("---\n",'')}
	}
};