<?php
##licence_block##
if (!defined('DC_RC_PATH')) { return; }

##autoload##

/**
 * Plugin URL handler
 *
 * @package    ##plugin_name##
 * @author     ##plugin_author##
 * @version    SVN: $Id: $
 */
class ##class_name##URL extends dcUrlHandlers
{
	public static function ##plugin_id##($args)
	{
		global $core;

		#if ($condition)) {
		#	self::p404();
		#	exit;
		#}

		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('##plugin_id##.html');
		exit;
	}
}
