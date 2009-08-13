<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of ExtendCategorie, a plugin for Dotclear.
# 
# Copyright (c) 2009 Rocky Horror
# rockyhorror@divingislife.net
# 
# Licensed under the GPL version 3.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/gpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

$core->url->register('category','category','^category/(.+)$',array('CustomCategoryURL','CustomCategory'));

?>