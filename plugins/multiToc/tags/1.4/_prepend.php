<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom and contributors
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$__autoload['multiTocUi'] = dirname(__FILE__).'/inc/class.multi.toc.php';
$__autoload['multiTocBehaviors'] = dirname(__FILE__).'/inc/class.multi.toc.php';
$__autoload['rsMultiTocPost'] = dirname(__FILE__).'/inc/class.multi.toc.php';

$core->url->register('multitoc','multitoc','^multitoc/(.*)$',array('multiTocUrl','multiToc'));

require dirname(__FILE__).'/_widgets.php';

?>