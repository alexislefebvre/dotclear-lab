<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shortArchives, a plugin for Dotclear.
# 
# Copyright (c) 2009 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('initWidgets',array('shortArchivesWidgets','initWidgets'));

class shortArchivesWidgets
{
	public static function initWidgets(&$w)
	{
		$w->create('shortArchives',__('Short Archives'), array('tplShortArchives','shortArchivesWidgets'));
		$w->shortArchives->setting('title',__('Title'),('Archives'));
		$w->shortArchives->setting('postcount',__('With entries counts'),1,'check');
		$w->shortArchives->setting('homeonly',__('Home page only'),0,'check');
	}
}
?>
