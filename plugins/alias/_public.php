<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->url->register('alias','','^(.*)$',array('urlAlias','alias'));

class urlAlias extends dcUrlHandlers
{
	public static function alias($args)
	{
		$o = new dcAliases($GLOBALS['core']);
		$aliases = $o->getAliases();
		
		foreach ($aliases as $v)
		{
			if (@preg_match('#^/.*/$#',$v['alias_url']) && @preg_match($v['alias_url'],$args)) {
				self::callAliasHandler(preg_replace($v['alias_url'],$v['alias_destination'],$args));
				return;
			} elseif ($v['alias_url'] == $args) {
				self::callAliasHandler($v['alias_destination']);
				return;
			}
		}
		
		self::callAliasHandler($args);
	}
	
	public function callAliasHandler($part)
	{
		global $core;
		$core->url->unregister('alias');
		$core->url->getArgs($part,$type,$args);
		
		global $core;
		if (!$type) {
			$core->url->callDefaultHandler($args);
		} else {
			$core->url->callHandler($type,$args);
		}
	}
}
?>
