<?php
if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$core->addBehavior('initWidgets',
	array('myWidgetBehaviors','initWidgets'));
	
/* Add menu item in extension list */
$_menu['Plugins']->addItem('OpenID','plugin.php?p=openid','index.php?pf=openid/icon_16.png',
		preg_match('/plugin.php\?p=openid(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));

 
class myWidgetBehaviors
{
	public static function initWidgets(&$w)
	{
		$w->create('OpenID',__('OpenID'),
			array('OpenidWidget','OpenidConnect'));
			
		$w->OpenID->setting('title',__('Title:'),
			'OpenID','text');
	}
}
?>