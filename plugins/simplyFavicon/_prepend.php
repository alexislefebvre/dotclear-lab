<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of simplyFavicon, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2016 JC Denis and contributors
# contact@jcdenis.fr http://jcdenis.net
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$__autoload['publicSimplyFavicon'] = dirname(__FILE__).'/_public.php';

$core->url->register('simplyFavicon','favicon','^favicon.(.*?)$',array('publicSimplyFavicon','simplyFaviconUrl'));