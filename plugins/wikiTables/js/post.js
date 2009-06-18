// ***** BEGIN LICENSE BLOCK *****
//
// This file is part of Wiki Tables, a plugin for Dotclear 2
// Copyright (C) 2009 Moe (http://gniark.net/)
//
// Wiki Tables is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License v2.0
// as published by the Free Software Foundation.
//
// Wiki Tables is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software Foundation,
// Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
//
// ***** END LICENSE BLOCK *****

jsToolBar.prototype.elements.table = {
	type: 'button',
	title: 'Table',
	context: 'post',
	icon: 'index.php?pf=wikiTable/icon.png',
	fn: {
		wiki: function() {this.encloseSelection('///table\n','\n///')}
	}
};