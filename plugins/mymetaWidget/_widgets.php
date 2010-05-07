<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of MyMetaWidget, a plugin for Dotclear.
# 
# Copyright (c) 2009 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$core->addBehavior('initWidgets', array('MyMetaWidget','initWidgets'));
 
class MyMetaWidget
{
	public static function initWidgets(&$w)
	{
		$w->create('MyMeta','MyMeta', array('publicMyMetaWidget','getContent'));
		$w->MyMeta->setting('title',__('Titre:'), '','text');
		$w->MyMeta->setting('homeonly',__('Home page only'),0,'check');
	}
}
?>
