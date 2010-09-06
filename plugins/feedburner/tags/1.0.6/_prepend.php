<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of feedburner, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$__autoload['feedburner'] = dirname(__FILE__).'/inc/class.feedburner.php';
$__autoload['feedburnerUi'] = dirname(__FILE__).'/inc/lib.feedburner.ui.php';
$__autoload['feedburnerReader'] = dirname(__FILE__).'/inc/class.feedburner.reader.php';
$__autoload['feedburnerParser'] = dirname(__FILE__).'/inc/class.feedburner.parser.php';

$core->url->register('feedburnerStatsExport','feedburnerStatsExport','^feedburner/stats/export$',array('feedburnerUrl','export'));

require dirname(__FILE__).'/_widgets.php';

?>