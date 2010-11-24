<?php
if (!defined('DC_RC_PATH')) { return; }

if (!class_exists('dcPublicWidget')) {
    require dirname(__FILE__).'/class.dc.publicWidget.php';
}

class publicTagFlashWidget extends dcPublicWidget {

    public static function tagFlashWidget(&$w) {

        $_ctx = $GLOBALS['_ctx'];
        $_ctx->widget = $w;

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