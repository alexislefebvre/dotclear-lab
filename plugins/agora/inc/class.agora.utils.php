<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012 Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class agoraTools
{
	public static function wikiTransform($content)
	{
		global $core;
		
		$core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		$core->initWikiPost();
		/// coreInitWikiPost
		$content = $core->wikiTransform($content);
		$content = $core->HTMLfilter($content);
		
		return $content;
	}
}
?>
