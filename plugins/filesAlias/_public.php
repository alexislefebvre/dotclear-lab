<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of filesAlias, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
$core->tpl->addValue('fileAliasURL',array('templateAlias','fileAliasURL'));

class templateAlias
{
	public static function fileAliasURL($attr)
	{
		global $core, $_ctx;
         
          $f = $GLOBALS['core']->tpl->getFilters($attr);
          return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("filesalias")."/".$_ctx->filealias->filesalias_url').'; ?>';
     }
}

class urlFilesAlias extends dcUrlHandlers
{
	public static function alias($args)
	{
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		$delete = false;
		
		$_ctx->filealias = $core->filealias->getAlias($args);

		if ($_ctx->filealias->isEmpty()) {
			self::p404();
		}
		
		if ($_ctx->filealias->filesalias_disposable) {
			$delete = true;
		}
		
		if ($_ctx->filealias->filesalias_password) {
		
			# Check for match
			if (!empty($_POST['filepassword']) && $_POST['filepassword'] == $_ctx->filealias->filesalias_password)
			{
				self::servefile($_ctx->filealias->filesalias_destination,$delete);
			}
			else
			{
				self::serveDocument('file-password-form.html','text/html',false);
				return;
			}
		}
		else
		{
			self::servefile($_ctx->filealias->filesalias_destination,$delete);
		}
	}
	
	public static function servefile($target,$delete=false)
	{
		$core =& $GLOBALS['core'];	
		
		$a= new aliasMedia($core);
		$media = $a->getMediaId($target);

		if (empty($media))
		{
			self::p404();			
		}
		
		$file = $core->media->getFile($media);
	
		if (empty($file->file))
		  {
		    self::p404();
		  }
		  
		header('Content-type: '.$file->type);
		header('Content-Length: '.$file->size);
		header('Content-Disposition: attachment; filename="'.$file->basename.'"');
		readfile($file->file);
		if ($delete) {
			$core->filealias->deleteAlias($target);
		}
		return;	
	}
}
?>