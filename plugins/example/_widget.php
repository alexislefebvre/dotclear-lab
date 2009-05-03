<?php 
/***** BEGIN LICENSE BLOCK *****
This program is free software. It comes without any warranty, to
the extent permitted by applicable law. You can redistribute it
and/or modify it under the terms of the Do What The Fuck You Want
To Public License, Version 2, as published by Sam Hocevar. See
http://sam.zoy.org/wtfpl/COPYING for more details.
	
Icon (icon.png) is from Silk Icons :
	http://www.famfamfam.com/lab/icons/silk/

***** END LICENSE BLOCK *****/

if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('initWidgets',array('exampleWidget',
	'initWidgets'));

/**
@ingroup Example
@brief Widget
*/
class exampleWidget
{
	/**
	widget
	@param	w	<b>object</b>	Widget
	*/
	public static function initWidgets(&$w)
	{
		# create the widget
		$w->create(
			# ID of the widget, create $w->example, used below
			'example',
			# name of the widget in the widget list
			__('Example'),
			# callback : the function called by the widget
			array('exampleWidget','show'));
		
		# add a setting
		$w->example->setting(
			# name of the value
			'text',
			# label of the setting
			__('Text:').' ('.__('optional').')',
			# default value
			__('Hello World!'),
			# type of the value
			'text');
		
		# homepage only setting
		$w->example->setting('homeonly',__('Home page only'),
			false,'check');
	}
	
	/**
	show widget
	@param	w	<b>object</b>	Widget
	@return	<b>string</b> XHTML
	*/
	public static function show(&$w)
	{
		global $core;
		
		# homepage only setting
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		# output
		$header = '<h2>'.__('Example').'</h2>';
		
		# the text setting
		$text = '<p>'.html::escapeHTML($w->text).'</p>';
		
		# return the string
		return '<div class="example">'.$header.$text.'</div>';
	}
}
?>