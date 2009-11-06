<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of filesAlias, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

class urlFilesAlias extends dcUrlHandlers
{
	public static function alias($args)
	{
		$o = new FilesAliases($GLOBALS['core']);
		$aliases = $o->getAliases();

		foreach ($aliases as $v)
		{
			if ($v['filesalias_url'] == $args)
			{
				http::head(302, 'Found');
				header('Location: '.$v['filesalias_destination']);
				return;
			}
		}
		
		self::p404();
	}
}
?>