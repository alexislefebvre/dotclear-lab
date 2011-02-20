/* ***** BEGIN LICENSE BLOCK *****

 This file is part of Bloc-Notes.
 Copyright 2008,2009,2010 Moe,2011 (http://gniark.net/)

 Bloc-Notes is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 Bloc-Notes is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.

 Icon (icon.png) is from Silk Icons :
 http://www.famfamfam.com/lab/icons/silk/

 ***** END LICENSE BLOCK *****/
$(function() {
	/* modified from /dotclear/admin/js/_post.js */
	$('#blocNotes_personal label').toggleWithLegend($('#blocNotes_personal').children().not('label'),{
		cookie: 'dcx_post_blocNotes_p'

	});
	$('#blocNotes label').toggleWithLegend($('#blocNotes').children().not('label'),{
		cookie: 'dcx_post_blocNotes'
	});
});