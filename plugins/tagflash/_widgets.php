<?php
// +-----------------------------------------------------------------------+
// | Tag Flash  - a plugin for Dotclear                                    |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2010 Nicolas Roudaire             http://www.nikrou.net  |
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

$core->addBehavior('initWidgets', 
		   array('tagFlashWidgetBehaviors','initWidgets')
		   );
$core->addBehavior('initDefaultWidgets', 
		   array('tagFlashWidgetBehaviors','initDefaultWidgets')
);

class tagFlashWidgetBehaviors 
{
  public static function initDefaultWidgets($w, $d) {
    $d['extra']->append($w->tagFlash);
  }

  public static function initWidgets($w) {
    $w->create('tagFlash',__('Tags Flash'),
	       array('publicTagFlashWidget','tagFlashWidget')
	       );

    $w->tagFlash->setting('title',__('Title:'),
			  'Tags','text');
    $w->tagFlash->setting('width',__('Width of the Flash tag cloud:'),
			  '100%','text');
    $w->tagFlash->setting('height',__('Height of the Flash tag cloud:'),
			  '200px','text');
    $w->tagFlash->setting('color',__('Color of the tags (6 character hex color value without the # prefix):'),
			  '000000','text');
    $w->tagFlash->setting('bgcolor',__('Backgound color (6 character hex color value without the # prefix):'),
			  'ffffff','text');
    $w->tagFlash->setting('transparent',__('Use transparent mode'),
            'true','check');
    $w->tagFlash->setting('speed',__('Rotation speed (default=150):'),
			  '150','text');
    $w->tagFlash->setting('limit',__('Maximun displayed tags (empty=no limit):'),
			  '','text');
  }
}
