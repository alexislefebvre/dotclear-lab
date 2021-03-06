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

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

$core->addBehavior('initWidgets',array('WikiTextWidgetBehaviors','initWidgets'));
 
class WikiTextWidgetBehaviors
{
	public static function initWidgets($w)
	{
		$w->create('WikiTextWidget',__('Wiki Text'),array('publicWikiTextWidget','Show'));

		$w->WikiTextWidget->setting('title',__('Title:').' ('.__('optional').')',null,'text');

		# example
		$example = '__foo__'."\n\n".'='."\n\n".'///html'."\n".'<p><strong>bar</strong></p>'."\n".'///'; 
		$w->WikiTextWidget->setting('text',__('Text:').' ('.__('wiki syntax').')',$example,'textarea');

		$w->WikiTextWidget->setting('homeonly',__('Home page only'),false,'check');
	}
}
?>