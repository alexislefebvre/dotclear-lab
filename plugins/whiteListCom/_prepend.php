<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of whiteListCom, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

global $__autoload, $core;

$__autoload['whiteListCom'] = 
	dirname(__FILE__).'/inc/lib.whitelistcom.php';

$__autoload['whiteListComBehaviors'] = 
	dirname(__FILE__).'/inc/lib.whitelistcom.php';

$__autoload['whiteListComReservedFilter'] = 
	dirname(__FILE__).'/inc/lib.whitelistcom.php';

$__autoload['whiteListComModeratedFilter'] = 
	dirname(__FILE__).'/inc/lib.whitelistcom.php';

# This filter is used only if comments are moderates
if (!$core->blog->settings->system->comments_pub)
{
	$core->spamfilters[] = 'whiteListComModeratedFilter';
	$core->addBehavior('publicAfterCommentCreate',
		array('whiteListComBehaviors','switchStatus')
	);
}

$core->spamfilters[] = 'whiteListComReservedFilter';
?>