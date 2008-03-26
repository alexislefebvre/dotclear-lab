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
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

class blocNotes
	{
		public static function adminDashboardIcons(&$core, &$icons)
		{
			$icons['blocNotes'] = array(__('Bloc-Notes'),'plugin.php?p=blocNotes',
				'index.php?pf=blocNotes/icon.png');
		}

		public static function textarea(&$post='')
		{
			global $core;

			echo('<p class="area" id="blocNotes">'.
				'<label for="blocNotes_text">'.__('Bloc-notes :').'</label>'.
				form::textarea('blocNotes_text',80,20,
				html::escapeHTML($core->blog->settings->blocNotes_text)).
				'</p>');
		}

		public static function adminPost()
		{
			global $core;

			if (isset($_POST['blocNotes_text']))
			{
				$core->blog->settings->setNameSpace('blocnotes');
				# Bloc-Notes' text
				$core->blog->settings->put('blocNotes_text',
					$_POST['blocNotes_text'],'text','Bloc-Notes\' text');
			}
		}

		public static function adminPostHeaders()
		{
			return '<script type="text/javascript" src="index.php?pf=blocNotes/post.js">'.
			'</script>'."\n";
		}
	}

?>