<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcMiniUrl, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('widgetAdminMiniUrl','shorten'));
$core->addBehavior('initWidgets',array('widgetAdminMiniUrl','rank'));

class widgetAdminMiniUrl
{
	public static function shorten($w)
	{
		$w->create('dominiurl',__('Links shortener'),
			array('widgetPublicMiniUrl','dominiurl'));

		$w->dominiurl->setting('title',__('Title:'),__('Shorten link'),'text');
		$w->dominiurl->setting('homeonly',__('Home page only'),1,'check');
	}

	public static function rank($w)
	{
		$w->create('rankminiurl',__('Top mini links'),
			array('widgetPublicMiniUrl','rankminiurl'));

		$w->rankminiurl->setting('title',__('Title:'),__('Top mini links'),'text');
		$w->rankminiurl->setting('text',__('Text:'),'%rank% - %url% - %counttext%','text');
		$w->rankminiurl->setting('urllen',__('Link length (if truncate)'),20);
		$w->rankminiurl->setting('sort',__('Sort:'),'desc','combo',array(
			__('Ascending') => 'asc',__('Descending') => 'desc'));
		$w->rankminiurl->setting('limit',__('Limit:'),'10','text');
		$w->rankminiurl->setting('homeonly',__('Home page only'),1,'check');
	}
}
?>