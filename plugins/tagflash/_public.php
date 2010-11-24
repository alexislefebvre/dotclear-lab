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

require(dirname(__FILE__).'/_widgets.php');

class publicTagFlashWidget extends dcPublicWidget 
{
  public static function tagFlashWidget($w) {
    global $core, $_ctx;
    
    $_ctx->widget = $w;
    $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
    
    return self::serveWidget('tagFlash-widget.html');
  }
}

$core->tpl->addValue('WidgetParam',
    array('tplWidget', 'widgetParam'));

class tplWidget {

    public static function widgetParam($attr) {

        $param = null;
        if (!empty($attr['param'])) {
            $param = addslashes($attr['param']);
        }

        $f = $GLOBALS['core']->tpl->getFilters($attr);
        if ($param != null) {
            return '<?php echo '.sprintf($f,'$GLOBALS[\'_ctx\']->widget->'.$param.'').'; ?>';
        }
        return;
    }
}

$core->tpl->addValue('tagFlashWidth', array('tplTagFlash', 'tagFlashWidth'));
$core->tpl->addValue('tagFlashHeight', array('tplTagFlash', 'tagFlashHeight'));
$core->tpl->addValue('tagFlashTagColor', array('tplTagFlash', 'tagFlashTagColor'));
$core->tpl->addValue('tagFlashBackgroundColor', array('tplTagFlash', 'tagFlashBackgroundColor'));
$core->tpl->addBlock('tagFlashIfTransparent', array('tplTagFlash', 'tagFlashIfTransparent'));
$core->tpl->addValue('tagFlashSpeed', array('tplTagFlash', 'tagFlashSpeed'));
$core->tpl->addValue('tagFlashTags', array('tplTagFlash', 'tagFlashTags'));

class tplTagFlash {

    private static $SIZE_TRANSLATOR = array(
        '0' => '0',
        '10' => '8',
        '20' => '10',
        '30' => '12',
        '40' => '14',
        '50' => '16',
        '60' => '18',
        '70' => '20',
        '80' => '22',
        '90' => '24',
        '100' => '26'
    );

    public static function tagFlashWidth($attr) {

        return '<?php echo tplTagFlash::getWidth(); ?>';
    }
    public static function getWidth() {

        return $GLOBALS['_ctx']->widget->width;
    }

    public static function tagFlashHeight($attr) {
        return '<?php echo tplTagFlash::getHeight(); ?>';
    }
    public static function getHeight() {
        return $GLOBALS['_ctx']->widget->height;
    }

    public static function tagFlashTagColor($attr) {
        return '<?php echo tplTagFlash::getTagColor(); ?>';
    }
    public static function getTagColor() {
        $w = $GLOBALS['_ctx']->widget;
        $color = '';
        if ($w->color != '') {
            $color = '0x'.$w->color;
        }
        return $color;
    }

    public static function tagFlashBackgroundColor($attr) {
        return '<?php echo tplTagFlash::getBackgroundColor(); ?>';
    }
    public static function getBackgroundColor() {

        $w = $GLOBALS['_ctx']->widget;

        $color = '';
        if ($w->bgcolor != '') {
            $color = '#'.$w->bgcolor;
        }
        return $color;
    }

    public static function tagFlashIfTransparent($attr, $content) {

        return '<?php if (tplTagFlash::isTransparent()) { echo \''.$content.'\'; } ?>';
    }
    public static function isTransparent() {

        return ($GLOBALS['_ctx']->widget->transparent == "1");
    }

    public static function tagFlashSpeed($attr) {
        return '<?php echo tplTagFlash::getSpeed(); ?>';
    }
    public static function getSpeed() {

        $w = $GLOBALS['_ctx']->widget;

        $speed = '150';
        if ((integer) $w->speed > 0) {
            $speed = $w->speed;
        }

        return $speed;
    }

    public static function tagFlashTags($attr) {
        return '<?php echo tplTagFlash::getTags(); ?>';
    }
    public static function getTags() {

        $core = $GLOBALS['core'];
        $w = $GLOBALS['_ctx']->widget;

        $limit = null;
        if ($w->limit != "" && is_numeric($w->limit)) {
            $limit = $w->limit;
        }

        $objMeta = new dcMeta($core);
        $rs = $objMeta->getMeta('tag', $limit);

        $buff = '<tags>';

        if (!$rs->isEmpty()) {
            while ($rs->fetch()) {

                $buff .=
                '<a href=\''.$core->blog->url.$core->url->getBase('tag').'/'.rawurlencode($rs->meta_id).'\' '.
                'style=\''.self::$SIZE_TRANSLATOR[$rs->roundpercent].'\'>'.
                $rs->meta_id.'<\\/a>';
            }
        }

        $buff .= '<\\/tags>';

        return $buff;
    }
}
?>