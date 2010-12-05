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

$core->addBehavior('initWidgets',array('tagFlashWidgetBehaviors','initWidgets'));
$core->addBehavior('initDefaultWidgets',array('tagFlashWidgetBehaviors','initDefaultWidgets'));

class tagFlashWidgetBehaviors 
{
  public static function initDefaultWidgets($w, $d) {
    $d['extra']->append($w->tagFlash);
  }

  public static function initWidgets($w) {
    $w->create('tagFlash',__('Tags Flash'),
	       array('tplTagFlash','widget')
	       );

    $w->tagFlash->setting('title',__('Title:'),'Tags','text');
    $w->tagFlash->setting('seo_content',__('Add tags for SEO'),1,'check');
    $w->tagFlash->setting('transparent_mode',__('Transparent mode'),1,'check');
    $w->tagFlash->setting('limit',__('Maximun displayed tags (empty=no limit):'),0,'text');
  }
}