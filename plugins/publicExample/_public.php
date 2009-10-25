<?php 
# ***** BEGIN LICENSE BLOCK *****
# 
# This program is free software. It comes without any warranty, to
# the extent permitted by applicable law. You can redistribute it
# and/or modify it under the terms of the Do What The Fuck You Want
# To Public License, Version 2, as published by Sam Hocevar. See
# http://sam.zoy.org/wtfpl/COPYING for more details.
# 
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

class publicExampleDocument extends dcUrlHandlers
{
	public static function page($args)
	{
		global $core;
 
		# $_ctx is the context, we can create any value
		# $_ctx est le contexte, on y insère ce que l'on veut
		$_ctx =& $GLOBALS['_ctx'];
		
		# $_ctx->publicExample will be a string
		# $_ctx->publicExample sera une chaîne de caractères
 
		# if the URL is "publicexample/lorem"
		# si l'URL est "publicexample/lorem"
		if ($args == 'lorem')
		{
			$_ctx->publicExample = __('Lorem ipsum');
		}
		# else, if the URL is "publicexample"
		# sinon, si l'URL est "publicexample"
		else
		{
			$_ctx->publicExample = __('Public');
		}
 
		# save the directory which contain the default template file,
		# the public_example.html file
		# enregistrer le répertoire qui contient le fichier template par défaut,
		# le fichier public_example.html
		$core->tpl->setPath($core->tpl->getPath(),
			dirname(__FILE__).'/default-templates/');
 
		self::serveDocument('public_example.html','text/html');
	}
}
 
# declare the {{tpl:Public}} tag
# déclarer la balise {{tpl:Public}}
$core->tpl->addValue('PublicExampleValue',
	array('publicExampleTpl','PublicExampleValue'));
 
class publicExampleTpl
{
	public static function PublicExampleValue()
	{
		return('<?php echo($_ctx->publicExample); ?>');
	}
}

?>