<?php
if (!defined('DC_RC_PATH')) { return; }

if (isset($core->tpl)) {
    $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
}
?>