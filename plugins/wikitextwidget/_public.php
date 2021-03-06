<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Wiki Text Widget.
# Copyright 2007 Moe (http://gniark.net/)
#
# Wiki Text Widget is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Wiki Text Widget is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

class publicWikiTextWidget
{
	public static function Show($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		$header = (strlen($w->title) > 0) ? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;

		if (strlen($w->text) > 0)
		{
			return '<div class="wikitext">'.$header.$GLOBALS['core']->wikiTransform($w->text).'</div>';
		}
	}
}
?>