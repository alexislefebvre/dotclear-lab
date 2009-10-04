<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dctribune, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku  and contributors
# Many thanks to Pep, Tomtom and JcDenis
# Originally from Antoine Libert
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

$__autoload['dcTribune'] = dirname(__FILE__).'/inc/class.dc.tribune.php';
$__autoload['tribuneTemplate'] = dirname(__FILE__).'/inc/class.template.tribune.php';
$__autoload['tribuneBehaviors'] = dirname(__FILE__).'/inc/class.behaviors.tribune.php';

$core->tribune = new dcTribune($core);
if ($core->blog->settings->tribune_flag)
{
	$core->url->register('tribune','tribune','^tribune$',array('urlTribune','tribuneHandler'));
}
?>