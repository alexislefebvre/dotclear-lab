<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of openidDelegation, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------


$core->addBehavior('publicHeadContent',
	array('openidDelegationBehaviors','publicHeadContent'));

class openidDelegationBehaviors
{
	public static function publicHeadContent(&$core,$_ctx)
	{
		// Only on the home page
		if ($_ctx->current_tpl != "home.html") { return; }
		if (!$core->blog->settings->openid_active) { return; }
		// print the headers
		print "<!-- OpenID -->";
		print $core->blog->settings->openid_header;
		print "\n";
	}
}

?>
