<?php
if (!defined('DC_CONTEXT_ADMIN')) { return; }
require_once(dirname(__FILE__).'/inc/feed2img.class.php');
$core->addBehavior('adminAfterPostCreate', array('EntriesSelection','ImgOutput'));
$core->addBehavior('adminAfterPostUpdate', array('EntriesSelection','ImgOutput'));
$core->addBehavior('adminAfterPostDelete', array('EntriesSelection','ImgOutput'));
?>