<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of specificsTemplates, a plugin for Dotclear.
# 
# Copyright (c) 2009 Thierry Poinot
# dev@thierrypoinot.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
$core->url->register('category','category','^category/(.+)$',array('specificsTemplatesURLHandlers','category')); // inc/prepend.php
$core->url->register('pages','pages','^pages/(.+)$',array('specificsTemplatesURLHandlers','pages')); // /plugins/pages/_prepend.php

?>