<?php
// +-----------------------------------------------------------------------+
// | Tag Flash  - a plugin for Dotclear                                    |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2010-2011 Nicolas Roudaire        http://www.nikrou.net  |
// | Copyright(C) 2010 Guenaël Després                                     |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation                                          |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,            |
// | MA 02110-1301 USA.                                                    |
// +-----------------------------------------------------------------------+

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
    /* Name */          "Tag Flash",
    /* Description*/    "Flash based Tag Cloud for Dotclear",
    /* Author */        "Gwénaël Després (based on WordPress plugin by Roy Tanck)",
    /* Version */       '1.1.1',
    /* Permissions */   'usage,contentadmin'
);
?>