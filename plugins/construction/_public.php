<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of construction, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

if ($core->blog->settings->construction->construction_flag)
{
	$core->addBehavior('publicBeforeDocument',array('publicBehaviorsConstruction','checkVisitor'));
}

$core->tpl->addValue('ConstructionMessage',array('tplConstruction','ConstructionMessage'));
$core->tpl->addValue('ConstructionTitle',array('tplConstruction','ConstructionTitle'));

class publicBehaviorsConstruction 
{
	public static function checkVisitor($core)
	{
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		$all_allowed_ip = unserialize($core->blog->settings->construction->construction_allowed_ip);
		if (!in_array(http::realIP(),$all_allowed_ip))
		{
			$core->url->registerDefault(array('urlConstruction','constructionHandler'));
			$core->url->registerError(array('urlConstruction','default503'));
			
			foreach ($core->url->getTypes() as $k=>$v)
			{
				if ($k != 'contactme')
				{
					$core->url->register($k,$v['url'],$v['representation'],array('urlConstruction','p503'));
				}
			}

		}
	}
}

class urlConstruction extends dcUrlHandlers
{
	public static function p503()
	{
		throw new Exception ("Blog under construction",503);
	}
	
	public static function default503($args,$type,$e)
	{
		if ($e->getCode() == 503) {
			$_ctx =& $GLOBALS['_ctx'];
			$core =& $GLOBALS['core'];
		
			header('Content-Type: text/html; charset=UTF-8');
			http::head(503,'Service Unavailable');
			$core->url->type = '503';
			$_ctx->current_tpl = '503.html';
			$_ctx->content_type = 'text/html';
		
			echo $core->tpl->getData($_ctx->current_tpl);
		
			# --BEHAVIOR-- publicAfterDocument
			$core->callBehavior('publicAfterDocument',$core);
			exit;
		}
	}
	
	public static function constructionHandler($args)
	{
		$core =& $GLOBALS['core'];
		$core->url->type = 'default';
		self::p503();
		return;
	}
}

class tplConstruction
{
	public static function ConstructionMessage($attr)
	{
		$core =& $GLOBALS['core'];
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$core->blog->settings->construction->construction_message').'; ?>';
	}
	
	public static function ConstructionTitle($attr)
	{
		$core =& $GLOBALS['core'];
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, '$core->blog->settings->construction->construction_title').'; ?>';
	}	
}
?>