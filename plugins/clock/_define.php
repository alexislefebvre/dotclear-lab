<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Clock, a plugin for Dotclear 2
# Copyright (C) 2007-2008,2009,2010 Moe (http://gniark.net/)
#
# Clock is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Clock is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
    /* Name */             "Clock",
    /* Description*/       "Display the date of a time zone with the strftime() format in a widget",
    /* Author */           "Moe (http://gniark.net/)",
    /* Version */          '1.2.5',
    /* Permissions */      'admin'
);
?>
