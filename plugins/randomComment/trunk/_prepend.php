<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of randomComment, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$__autoload['randomComment'] = dirname(__FILE__).'/inc/class.random.comment.php';

$core->url->register('randomComment','randomComment','^randomComment$',array('randomCommentUrl','randomComment'));

require dirname(__FILE__).'/_widgets.php';