<?php
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('SnapMeWidgets','initWidgets'));

class SnapMeWidgets
{
	public static function initWidgets($w)
	{
		$w->create('snapme',__('SnapMe'),array('snapMeTpl','widget'));
		$w->snapme->setting('title',__('Title:'),__('SnapMe'),'text');
		$w->snapme->setting('display',__('Display :'),1,'combo',array(__('Last Snap') => 1, __('Random Snap') => 2));
		$w->snapme->setting('homeonly',__('Home page only'),1,'check');
	}
}
?>