<?php
// +-----------------------------------------------------------------------+
// | tagFlash  - a plugin for Dotclear                                     |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2010,2015 Nicolas Roudaire        http://www.nikrou.net  |
// | Copyright(C) 2010 Guenaël                                             |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License version 2 as     |
// | published by the Free Software Foundation.                            |
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

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$version = $core->plugins->moduleInfo('tagflash','version');
if (version_compare($core->getVersion('tagflash'),$version,'>=')) {
  return;
}

$settings = $core->blog->settings;
$settings->addNamespace('tagflash');

$settings->tagflash->put('active', false, 'boolean', 'Tag Flash plugin activated', false);
$settings->tagflash->put('bgcolor', 'FFFFFF', 'string', 'Animation background color', false);
$settings->tagflash->put('color1', '333333', 'string', 'Color 1 for animation', false);
$settings->tagflash->put('color2', 'FF3363', 'string', 'Color 2 for animation', false);
$settings->tagflash->put('hicolor', '00CC00', 'string', 'Highlight color for animation', false);
$settings->tagflash->put('width', '600', 'string', 'Animation width', false);
$settings->tagflash->put('height', '400', 'string', 'Animation height', false);
$settings->tagflash->put('speed', '', 'string', 'Animation roation speed', false);

$core->setVersion('tagflash', $version);

return true;
