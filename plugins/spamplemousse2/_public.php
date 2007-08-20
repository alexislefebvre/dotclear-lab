<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Spamplemousse2, a plugin for DotClear.  
# Copyright (c) 2007 Alain Vagner and contributors. All rights
# reserved.
#
# Spamplemousse2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Spamplemousse2 is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Spamplemousse2; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$core->addBehavior('publicAfterCommentCreate',array('dcFilterSpample2','toggleLearnedFlag'));
$core->addBehavior('publicAfterTrackbackCreate',array('dcFilterSpample2','toggleLearnedFlag'));
?>
