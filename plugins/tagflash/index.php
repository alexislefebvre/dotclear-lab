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

$default_tab = 'tagflash_settings';

$page_title = __('Tag Flash');
$SpectrumLangs = array('de','dk','es','fi','fr','it','ja','nl','pt-br','ru','sv','tr');

$_key = array_search($core->auth->getInfo('user_lang'), $SpectrumLangs);
if ($_key) {
    $spectrum_lang = $SpectrumLangs[$_key];
} else {
    $spectrum_lang = 'en';
}

$tagflash_active = $core->blog->settings->tagflash->active;
$tagflash_bgcolor = $core->blog->settings->tagflash->bgcolor;
$tagflash_color1 = $core->blog->settings->tagflash->color1;
$tagflash_color2 = $core->blog->settings->tagflash->color2;
$tagflash_hicolor = $core->blog->settings->tagflash->hicolor;
$tagflash_speed = $core->blog->settings->tagflash->speed;
$tagflash_width = $core->blog->settings->tagflash->width;
$tagflash_height = $core->blog->settings->tagflash->height;

$core->blog->settings->addNameSpace('tagflash');

if (!empty($_POST['saveconfig'])) {
    try {
        $tagflash_active = (empty($_POST['tagflash_active']))?false:true;
        $core->blog->settings->tagflash->put('active', $tagflash_active, 'boolean');

        if (!empty($_POST['tagflash_width'])) {
            $tagflash_width = trim($_POST['tagflash_width'], " /\n\t\r\0\x0B");
            $core->blog->settings->tagflash->put('width', $tagflash_width, 'string');
        }

        if (!empty($_POST['tagflash_height'])) {
            $tagflash_height = trim($_POST['tagflash_height'], " /\n\t\r\0\x0B");
            $core->blog->settings->tagflash->put('height', $tagflash_height, 'string');
        }

        if (!empty($_POST['tagflash_bgcolor'])) {
            $tagflash_bgcolor = trim($_POST['tagflash_bgcolor'], " /\n\t\r\0\x0B");
            $core->blog->settings->tagflash->put('bgcolor', $tagflash_bgcolor, 'string');
        }

        if (!empty($_POST['tagflash_color1'])) {
            $tagflash_color1 = trim($_POST['tagflash_color1'], " /\n\t\r\0\x0B");
            $core->blog->settings->tagflash->put('color1', $tagflash_color1, 'string');
        }

        if (!empty($_POST['tagflash_color2'])) {
            $tagflash_color2 = trim($_POST['tagflash_color2'], " /\n\t\r\0\x0B");
            $core->blog->settings->tagflash->put('color2', $tagflash_color2, 'string');
        }

        if (!empty($_POST['tagflash_hicolor'])) {
            $tagflash_hicolor = trim($_POST['tagflash_hicolor'], " /\n\t\r\0\x0B");
            $core->blog->settings->tagflash->put('hicolor', $tagflash_hicolor, 'string');
        }

        if (!empty($_POST['tagflash_speed'])) {
            $tagflash_speed = (int)trim($_POST['tagflash_speed'], " /\n\t\r\0\x0B");
            if ($tagflash_speed==0) {
                $tagflash_speed = 100;
            }
            $core->blog->settings->tagflash->put('speed', $tagflash_speed, 'string');
        }

        $core->blog->triggerBlog();
        $message = __('Configuration successfully updated.');
    } catch (Exception $e) {
        $core->error->add($e->getMessage());
    }
}

function breadcrumb($elements=null,$options=array()) {
    if (method_exists('dcPage', 'breadcrumb')) {
        return dcPage::breadcrumb($elements, $options);
    } else {
        $with_home_link = isset($options['home_link'])?$options['home_link']:true;
        $hl = isset($options['hl'])?$options['hl']:true;
        $hl_pos = isset($options['hl_pos'])?$options['hl_pos']:-1;
        // First item of array elements should be blog's name, System or Plugins
        $res = '<h2>';
        $index = 0;
        if ($hl_pos < 0) {
            $hl_pos = count($elements)+$hl_pos;
        }
        foreach ($elements as $element => $url) {
            if ($hl && $index == $hl_pos) {
                $element = sprintf('<span class="page-title">%s</span>',$element);
            }
            $res .= ($index == 0 ? ' ' : ' &rsaquo; ').
                ($url ? '<a href="'.$url.'">' : '').$element.($url ? '</a>' : '');
            $index++;
        }
        $res .= '</h2>';

        return $res;
    }
}


include(dirname(__FILE__).'/tpl/index.tpl');
