<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Spamplemousse2, a plugin for Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('publicAfterCommentCreate',array('dcFilterSpample2','toggleLearnedFlag'));
$core->addBehavior('publicAfterTrackbackCreate',array('dcFilterSpample2','toggleLearnedFlag'));
?>
