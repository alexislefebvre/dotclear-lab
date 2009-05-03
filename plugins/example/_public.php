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

/**
@ingroup Example
@brief Document
*/
class exampleDocument extends dcUrlHandlers
{
	/**
	serve the document
	@param	args	<b>string</b>	Argument
	*/
	public static function page($args)
	{
		global $core;
		
		# $_ctx contains all the context, we put what we want in it
		$_ctx =& $GLOBALS['_ctx'];
		
		# if the URL is "example/hello"
		if ($args == 'hello')
		{
			$_ctx->example = __('Hello World!');
		}
		# else, if the URL is "example"
		else
		{
			$_ctx->example = __('Hi!');
		}
		
		# register the directory which contain the default template
		# file example.html
		$core->tpl->setPath($core->tpl->getPath(),
			dirname(__FILE__).'/default-templates/');
		
		self::serveDocument('example.html','text/html');
	}
}

# register the {{tpl:Example}} tag
$core->tpl->addValue('Example',array('exampleTpl','Example'));

/**
@ingroup Example
@brief Template
*/
class exampleTpl
{
	/**
	display text
	@return	<b>string</b> PHP block
	*/
	public static function Example()
	{
		return('<?php echo($_ctx->example); ?>');
	}
}

?>