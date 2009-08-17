<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of noodles, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$GLOBALS['__autoload']['noodlesImg'] = dirname(__FILE__).'/inc/lib.noodles.img.php';

$n_m = $GLOBALS['core']->blog->settings->noodles_module_prefix;
$n_m = $n_m ? $n_m : 'noodles';
$GLOBALS['core']->url->register('noodlesmodule',$n_m,'^'.$n_m.'/(.+)$',
	array('urlNoodles','noodles'));

$n_r = $GLOBALS['core']->blog->settings->noodles_service_prefix;
$n_r = $n_r ? $n_r : 'noodle';
$GLOBALS['core']->url->register('noodlesservice',$n_r,'^'.$n_r.'/$',
	array('urlNoodles','service'));

unset($n_m,$n_r);

if (!is_callable(array('libImagePath','getArray')))
	require dirname(__FILE__).'/inc/lib.image.path.php';

?>