<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of splitPost, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$__autoload['splitPostPager'] = dirname(__FILE__).'/inc/class.split.post.php';
$__autoload['splitPostBehaviors'] = dirname(__FILE__).'/inc/class.split.post.php';
$__autoload['rsExtPostSplitPost'] = dirname(__FILE__).'/inc/class.split.post.php';

if (!isset($core->post_page_pattern)) {
	$core->post_page_pattern = '#---#';
}

?>