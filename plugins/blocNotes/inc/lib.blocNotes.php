<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Bloc-Notes.
# Copyright 2008 Moe (http://gniark.net/)
#
# Bloc-Notes is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Bloc-Notes is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icons (*.png) are from Tango Icon theme :
#	http://tango.freedesktop.org/Tango_Icon_Gallery
#
# ***** END LICENSE BLOCK *****

class blocNotes
	{
		public static function adminDashboardIcons(&$core, &$icons)
		{
			$icons['blocNotes'] = array(__('Notebook'),
				'plugin.php?p=blocNotes',
				'index.php?pf=blocNotes/icon-big.png');
		}

		public static function form()
		{
			global $core;
			
			$set = $core->blog->settings;
			
			$notes = $core->con->select('SELECT blocNotes '.
				'FROM '.$core->prefix.'user '.
				'WHERE user_id = \''.
				$core->con->escape($core->auth->userID()).'\'')->f(0);

			echo '<p class="area" id="blocNotes_personal">'.
				'<label for="blocNotes_personal_text">'.
					__('Personal notebook (other users can\'t edit it):').
				'</label>'.
				form::textarea('blocNotes_personal_text',80,5,
				html::escapeHTML($notes),'maximal').
				'</p>'.
				'<p class="area" id="blocNotes">'.
				'<label for="blocNotes_text">'.
					__('Blog-specific notebook (users of the blog can edit it):').
				'</label>'.
				form::textarea('blocNotes_text',80,5,
				html::escapeHTML(base64_decode($set->blocNotes_text)),
				'maximal').
				'</p>'.
				'<p class="form-note">'.
				__('These notes may be read by anyone, don\'t write some sensitive information (password, personal information, etc.)').
				'</p>';
		}

		public static function putSettings()
		{
			global $core;

			if (isset($_POST['blocNotes_text']))
			{
				# Personal notebook
				$cur = $core->con->openCursor($core->prefix.'user');
				$cur->blocNotes = $_POST['blocNotes_personal_text'];
				$cur->update('WHERE user_id = \''.$core->con->escape($core->auth->userID()).'\'');
				
				$core->blog->settings->setNameSpace('blocnotes');
				# Blog-specific notebook
				$core->blog->settings->put('blocNotes_text',
					base64_encode($_POST['blocNotes_text']),'text',
					'Bloc-Notes\' text');
			}
		}

		public static function adminPostHeaders()
		{
			return '<script type="text/javascript" '.
				'src="index.php?pf=blocNotes/post.js">'.
				'</script>'."\n";
		}
	}

?>