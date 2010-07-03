<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('exportFull',array('aliasBehaviors','exportFull'));
$core->addBehavior('exportSingle',array('aliasBehaviors','exportSingle'));
$core->addBehavior('importInit',array('aliasBehaviors','importInit'));
$core->addBehavior('importFull',array('aliasBehaviors','importFull'));
$core->addBehavior('importSingle',array('aliasBehaviors','importSingle'));

$_menu['Plugins']->addItem(__('Aliases'),'plugin.php?p=alias','index.php?pf=alias/icon.png',
	preg_match('/plugin.php\?p=alias(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

if (!isset($__resources['help']['alias'])) {
	$__resources['help']['alias'] = dirname(__FILE__).'/locales/en/help.html';
	
	if (file_exists(dirname(__FILE__).'/locales/'.$_lang.'/help.html')) {
		$__resources['help']['alias'] = dirname(__FILE__).'/locales/'.$_lang.'/help.html';
	}
}

# Behaviors
class aliasBehaviors
{
	public static function exportFull($core,$exp)
	{
		$exp->exportTable('alias');
	}

	public static function exportSingle($core,$exp,$blog_id)
	{
		$exp->export('alias',
			'SELECT alias_url, alias_destination, alias_position '.
			'FROM '.$core->prefix.'alias A '.
			"WHERE A.blog_id = '".$blog_id."'"
		);
	}

	public static function importInit($bk,$core)
	{
		$bk->cur_alias = $core->con->openCursor($core->prefix.'alias');
		$bk->alias = new dcAliases($core);
		$bk->aliases = $bk->alias->getAliases();
	}

	public static function importFull($line,$bk,$core)
	{
		if ($line->__name == 'alias')
		{
			$bk->cur_alias->clean();

			$bk->cur_alias->blog_id = (string) $line->blog_id;
			$bk->cur_alias->alias_url = (string) $line->alias_url;
			$bk->cur_alias->alias_destination = (string) $line->alias_destination;
			$bk->cur_alias->alias_position = (integer) $line->alias_position;

			$bk->cur_alias->insert();
		}
	}

	public static function importSingle($line,$bk,$core)
	{
		if ($line->__name == 'alias')
		{
			$found = false;
			foreach ($bk->aliases as $v)
			{
				if ($v['alias_url'] == $line->alias_url) {
					$found = true;
				}
			}
			if ($found) {
				$bk->alias->deleteAlias($line->alias_url);
			}
			$bk->alias->createAlias($line->alias_url,$line->alias_destination,$line->alias_position);
		}
	}
}

?>
