<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dctribune, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku  and contributors
# Many thanks to Pep, Tomtom and JcDenis
# Originally from Antoine Libert
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
$core->addBehavior('initWidgets',array('tribuneWidgets','initFormWidgets'));

class tribuneWidgets
{
	public static function initFormWidgets(&$w)
	{
		$w->create('tribunelibreform',__('Free chatbox'),array('tplTribune','tribuneWidget'));
		$w->tribunelibreform->setting('title',__('Title:'),__('Chatbox'));
		$w->tribunelibreform->setting('nick', __('Label for nick'),__('Your nick :'));
		$w->tribunelibreform->setting('message',__('Label for message'),__('Your message :'));
		$w->tribunelibreform->setting('button',__('Button'),__('ok'));
		$w->tribunelibreform->setting('formbefore',__('Show form after'),1,'check');
		$w->tribunelibreform->setting('homeonly',__('Home page only'),1,'check');
	}
}
?>