<?php
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('initWidgets',
    array('tagFlashWidgetBehaviors','initWidgets'));

class tagFlashWidgetBehaviors {

    public static function initWidgets(&$w) {

        $w->create('tagFlash',__('Tags Flash'),
            array('publicTagFlashWidget','tagFlashWidget'));

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
?>