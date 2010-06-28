<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of templator a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

if ($core->blog->settings->templator->templator_flag)
{
	$core->addBehavior('publicBeforeDocument',array('publicTemplatorBehaviors','addTplPath'));
	$core->addBehavior('urlHandlerBeforeGetData',array('publicTemplatorBehaviors','BeforeGetData'));
}

class publicTemplatorBehaviors
{
	public static function addTplPath($core)
	{
		$core->tpl->setPath($core->tpl->getPath(), DC_TPL_CACHE.'/templator/default-templates');
	}
	
	public static function BeforeGetData ($_ctx)
	{
		global $core;
		
		if (array_key_exists($core->url->type,$core->getPostTypes()))
		{
			$params = array();
			$params['meta_type'] = 'template';
			$params['post_id'] = $_ctx->posts->post_id;
			$post_meta = $core->meta->getMetadata($params);
			
			if (!$post_meta->isEmpty())
			{
				$_ctx->current_tpl = $post_meta->meta_id;
			}
		}
	}
}
?>
