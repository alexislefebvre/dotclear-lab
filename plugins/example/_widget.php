<?php 
/***** BEGIN LICENSE BLOCK *****
Copyright (c) 2009, <Dotclear Lab Contributors>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

1. Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above
copyright notice, this list of conditions and the following
disclaimer in the documentation and/or other materials provided
with the distribution.
3. The name of the author may not be used to endorse or promote
products derived from this software without specific prior written
permission.

THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS
OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	
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