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

class tribuneBehaviors
{
	public static function coreInitWikiMessage($wiki2xhtml)
	{
		global $core;
		
		$wiki2xhtml->setOpts(array(
			'active_lists' => 0,
			'active_br' => 0,
			'active_auto_br' => 0,
			'active_pre' => 0,
		));
		return;
	}
}
?>