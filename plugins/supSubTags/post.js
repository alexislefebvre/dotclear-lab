// ***** BEGIN LICENSE BLOCK *****
//
// This file is part of Sup Sub Tags.
// Copyright 2007 Moe (http://gniark.net/)
//
// Sup Sub Tags is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 3 of the License, or
// (at your option) any later version.
//
// Sup Sub Tags is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// Icon (icon.png) and images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
//
// ***** END LICENSE BLOCK *****

// sup
jsToolBar.prototype.elements.sup = {
	type: 'button',
	title: 'Superscript',
	context: 'post',
	icon: 'index.php?pf=supSubTags/text_superscript.png',
	fn: {
		xhtml: function() { this.singleTag('<sup>','</sup>') },
		wysiwyg: function() {
			// http://www.mozilla.org/editor/midas-spec.html
			this.iwin.document.execCommand('superscript', false, null);
			this.iwin.focus();
		}
	}
};
// sub
jsToolBar.prototype.elements.sub = {
	type: 'button',
	title: 'Subscript',
	context: 'post',
	icon: 'index.php?pf=supSubTags/text_subscript.png',
	fn: {
		xhtml: function() { this.singleTag('<sub>','</sub>') },
		wysiwyg: function() {
			// http://www.mozilla.org/editor/midas-spec.html
			this.iwin.document.execCommand('subscript', false, null);
			this.iwin.focus();
		}
	}
};