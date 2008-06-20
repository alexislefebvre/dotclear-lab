<?php
# -- BEGIN LICENSE BLOCK ---------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ---------------------------------- */

if (!defined('DC_TSEARCH_ACTIVE') || !DC_TSEARCH_ACTIVE) {
	return;
}
	
$__autoload['dcTsearch'] = dirname(__FILE__).'/class.tsearch.php';
$core->addBehavior('corePostSearch',array('dcTsearch','corePostSearch'));
$core->addBehavior('coreCommentSearch',array('dcTsearch','coreCommentSearch'));
?>