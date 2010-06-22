<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of noodles, a plugin for Dotclear 2.
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

$__autoload['noodlesImg'] = dirname(__FILE__).'/inc/lib.noodles.img.php';
$__autoload['noodlesLibImagePath'] = dirname(__FILE__).'/inc/lib.image.path.php';

$core->blog->settings->addNamespace('noodles');

$core->url->register('noodlesmodule','noodles','^noodles/(.+)$',
	array('urlNoodles','noodles'));
$core->url->register('noodlesservice','noodle','^noodle/$',
	array('urlNoodles','service'));

?>