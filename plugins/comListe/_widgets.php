<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of comListe, a plugin for Dotclear.
# 
# Copyright (c) 2008-2009 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('comListeWidgets','initWidgets'));

class comListeWidgets
{
	public static function initWidgets(&$w)
	{
		$w->create('comListe',__('List of comments'),array('tplComListe','comListeWidget'));
		$w->comListe->setting('title',__('Title:'),__('ComListe'));
		$w->comListe->setting('link_title',__('Link title:'),__('List of comments'));
		$w->comListe->setting('homeonly',__('Home page only'),0,'check');
	}
}
?>