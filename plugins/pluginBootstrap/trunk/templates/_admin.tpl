<?php
##licence_block##
if (!defined('DC_RC_PATH')) { return; }

##autoload##

$_menu['Plugins']->addItem(__('##plugin_name##'),'plugin.php?p=##plugin_id##','index.php?pf=##plugin_id##/icon.png',
	preg_match('/plugin.php\?p=##plugin_id##(&.*)?$/',$_SERVER['REQUEST_URI']));
