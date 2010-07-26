<?php if (!defined('DC_CONTEXT_ADMIN')) {return;}
/**
 * @author kévin lepeltier [lipki] (kevin@lepeltier.info)
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 */

$_menu['Plugins']->addItem(
	__('flvPlayer config'),
	'plugin.php?p=flvplayer_config',
	'index.php?pf=flvplayer_config/icon.png',
	preg_match('/plugin.php\?p=flvplayer_config(&.*)?$/', $_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));