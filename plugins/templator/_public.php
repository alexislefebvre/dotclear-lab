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

if ($core->blog->settings->templator_flag)
{
	$core->addBehavior('urlHandlerServeDocument',array('publicTemplatorBehaviors','urlHandlerServeDocument'));
}

class publicTemplatorBehaviors
{
	public static function urlHandlerServeDocument ($result)
	{
		global $core, $_ctx;
		
		$meta = new dcMeta($core);
		
		if ($core->url->type == 'post' || $core->url->type == 'pages')
		{
			$post_meta = $meta->getMeta('template',null,null,$_ctx->posts->post_id);
			
			if (!$post_meta->isEmpty())
			{
				$tpl = $post_meta->meta_id;
				try
				{
					$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
					$tpl_file = $core->tpl->getFilePath($tpl);
					$result['content'] = $core->tpl->getData($tpl);
					$result['tpl'] = $tpl;
				}
				catch (Exception $e) 
				{
					return;
				}
			}
		}
		else
		{
			return;
		}
	}
}
?>
