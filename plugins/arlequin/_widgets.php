<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Arlequin, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('widgetsArlequin','widget'));

class widgetsArlequin
{
	public static function widget(&$w)
	{
		$w->create('arlequin',__('Theme switcher'),
			array('publicArlequinInterface','widget'));
		$w->arlequin->setting('title',__('Title:'),
			__('Choose a theme'));
	}
}
?>