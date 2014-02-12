<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Email Optionnel, a plugin for Dotclear.
#
# Copyright (c) 2007-2014 Oleksandr Syenchuk, Pierre Van Glabeke
#
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicPrepend',
	array('emailOptionnelBehaviors','publicPrepend'));
$core->addBehavior('publicBeforeCommentCreate',
	array('emailOptionnelBehaviors','publicBeforeCommentCreate'));
