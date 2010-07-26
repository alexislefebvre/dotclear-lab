<?php if (!defined('DC_CONTEXT_ADMIN')) {return;}
/**
 * @author kévin lepeltier [lipki] (kevin@lepeltier.info)
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 */

$_menu['Plugins']->addItem(
	__('flvPlayer config'),
	'plugin.php?p=flvplayerconfig',
	'index.php?pf=flvplayerconfig/icon.png',
	preg_match('/plugin.php\?p=flvplayerconfig(&.*)?$/', $_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));