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

class urlFilesAlias extends dcUrlHandlers
{
	public static function alias($args)
	{
		global $core;
		$f = new FilesAliases($core);
		$dest = $f->getAlias($args);
		$owned = false;
				
		if ($dest->isEmpty()) {
			self::p404();
		}
		
		$target = $dest->filesalias_destination;
		
		if ($dest->filesalias_disposable) {
			$f->deleteAlias($args);
		}

		$a= new aliasMedia($core);
		
		if (!preg_match('/^'.preg_quote($a->root_url,'/').'/',$target)) {

			$media = $a->getMediaId($target);

			if (empty($media))
			{
				self::p404();			
			}
			
			$file = $core->media->getFile($media);
		
			if (empty($file->file))
		       {
			    self::p404();
			    return;
		       }
		       
			header('Content-type: '.$file->type);
			header('Content-Length: '.$file->size);
			header('Content-Disposition: attachment; filename="'.$file->basename.'"');
			readfile($file->file);
			return;
		}
		else
		{
			http::head(302, 'Found');
			header('Location: '.$target);
			exit;
		}
	}
}
?>