<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Include subcats" plugin.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->blog->settings->addNamespace('incsubcat');
if ($core->blog->settings->incsubcat->incsubcat_enabled) {
	$core->addBehavior("urlHandlerBeforeGetData",array('urlISC','urlHandlerBeforeGetData'));
}

class urlISC  extends dcUrlHandlers
{
	public static function urlHandlerBeforeGetData($_ctx) {
		if ($_ctx->exists("categories")) {
			$_ctx->categories->cat_id .= " ?sub";
		}
	}
}
?>