<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of blogrollpage, a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->url->register('links','links','^links$',array('linksURL','links'));

class linksURL extends dcUrlHandlers
{
        public static function links($args)
        {
        	global $core;

			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
			self::serveDocument('links.html');
			exit;
        }
}
