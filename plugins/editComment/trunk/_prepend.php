<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of editComment, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$__autoload['editComment'] = dirname(__FILE__).'/inc/class.edit.comment.php';

$core->url->register('edit','edit','^edit/(.*)$',array('editCommentUrl','editComment'));

?>