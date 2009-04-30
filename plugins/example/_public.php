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