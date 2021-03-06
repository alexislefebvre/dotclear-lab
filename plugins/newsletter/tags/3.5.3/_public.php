<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Newsletter, a plugin for Dotclear 2.
# Copyright (C) 2009 Benoit de Marne, and contributors. All rights
# reserved.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 3
# of the License, or (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ***** END LICENSE BLOCK *****

//if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

// chargement des librairies
require_once dirname(__FILE__).'/inc/class.newsletter.plugin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.core.php';

require_once dirname(__FILE__).'/_public_tpl.php';
require_once dirname(__FILE__).'/_public_widgets.php';
require_once dirname(__FILE__).'/_public_urlhandlers.php';

?>
