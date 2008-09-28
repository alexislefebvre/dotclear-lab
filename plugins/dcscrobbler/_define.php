<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of dcScrobbler for DotClear.
# Copyright (c) 2005-2006 Boris de Laage. All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

# $Id: _define.php 24 2006-08-23 11:53:04Z bdelaage $
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
    /* Name */            "dcScrobbler",
    /* Description*/        "Displays recently played tracks with Last.fm",
    /* Author */            "Boris de Laage",
    /* Version */            '1.0.2',
    /* Permissions */        'usage,contentadmin'
);
?>
