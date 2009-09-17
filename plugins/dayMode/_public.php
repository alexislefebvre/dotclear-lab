<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dayMode, a plugin for Dotclear 2.
#
# Copyright (c) 2006-2009 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

require_once dirname(__FILE__).'/_widgets.php';

if (!$core->blog->settings->daymode_active) {
	return;
}

#-----------------------------------------------------------
# Adds a new template behavior
#-----------------------------------------------------------
$core->addBehavior('templateBeforeBlock',	array('dayModeBehaviors','block'));
$core->addBehavior('publicBeforeDocument',	array('dayModeBehaviors','addTplPath'));

#-----------------------------------------------------------
# Overloads some Archives* dedicated template tags
#-----------------------------------------------------------
$core->tpl->addValue('ArchiveURL', 	array('dayModeTemplates','ArchiveURL'));
$core->tpl->addBlock('ArchivesHeader',	array('dayModeTemplates','ArchivesHeader'));
$core->tpl->addBlock('ArchivesFooter',	array('dayModeTemplates','ArchivesFooter'));
$core->tpl->addValue('ArchiveDate',	array('dayModeTemplates','ArchiveDate'));
$core->tpl->addBlock('ArchiveNext',	array('dayModeTemplates','ArchiveNext'));
$core->tpl->addBlock('ArchivePrevious',	array('dayModeTemplates','ArchivePrevious'));

class dayModeTemplates
{
	/* Archives ------------------------------------------- */
	public static function ArchivesHeader($attr,$content)
	{
		$trg = ($GLOBALS['_ctx']->exists("day"))?'day':'archives';
		return
		"<?php if (\$_ctx->".$trg."->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}

	public static function ArchivesFooter($attr,$content)
	{
		$trg = ($GLOBALS['_ctx']->exists("day"))?'day':'archives';
		return
		"<?php if (\$_ctx->".$trg."->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}

	public static function ArchiveDate($attr)
	{
		if ($GLOBALS['_ctx']->exists("day")) {
			$trg = 'day';
			$format = $GLOBALS['core']->blog->settings->date_format;
		} else {
			$trg = 'archives';
			$format = '%B %Y';
		}
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}

		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,"dt::dt2str('".$format."',\$_ctx->".$trg."->dt)").'; ?>';
	}

	public static function ArchiveEntriesCount($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$trg = ($GLOBALS['_ctx']->exists("day"))?'day':'archives';
		return '<?php echo '.sprintf($f,'$_ctx->'.$trg.'->nb_post').'; ?>';
	}

	public static function ArchiveNext($attr,$content)
	{
		$p = '$params = array();';
		$trg = ($GLOBALS['_ctx']->exists("day"))?'day':'archives';
		if ($trg == 'day') {
			$p .= '$params[\'type\'] = \'day\';'."\n";
		} else {
			$p .= '$params[\'type\'] = \'month\';'."\n";
		}
		if (isset($attr['type'])) {
			$p .= "\$params['type'] = '".addslashes($attr['type'])."';\n";
		}

		$p .= "\$params['post_type'] = 'post';\n";
		if (isset($attr['post_type'])) {
			$p .= "\$params['post_type'] = '".addslashes($attr['post_type'])."';\n";
		}

		$p .= "\$params['next'] = \$_ctx->".$trg."->dt;";

		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->'.$trg.' = $core->blog->getDates($params); unset($params);'."\n";
		$res .= "?>\n";
		$res .=
		'<?php while ($_ctx->'.$trg.'->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->'.$trg.' = null; ?>';
		return $res;
	}

	public static function ArchivePrevious($attr,$content)
	{
		$p = '$params = array();';
		$trg = ($GLOBALS['_ctx']->exists("day"))?'day':'archives';
		if ($trg == 'day') {
			$p .= '$params[\'type\'] = \'day\';'."\n";
		} else {
			$p .= '$params[\'type\'] = \'month\';'."\n";
		}
		if (isset($attr['type'])) {
			$p .= "\$params['type'] = '".addslashes($attr['type'])."';\n";
		}

		$p .= "\$params['post_type'] = 'post';\n";
		if (isset($attr['post_type'])) {
			$p .= "\$params['post_type'] = '".addslashes($attr['post_type'])."';\n";
		}

		$p .= "\$params['previous'] = \$_ctx->".$trg."->dt;";

		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->'.$trg.' = $core->blog->getDates($params); unset($params);'."\n";
		$res .= "?>\n";
		$res .=
		'<?php while ($_ctx->'.$trg.'->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->'.$trg.' = null; ?>';
		return $res;
	}

	public static function ArchiveURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return
		'<?php if ($_ctx->exists("day")) { '.
		'echo '.sprintf($f,'$_ctx->day->url($core)').'; echo "/".$_ctx->day->day(); } '.
		'else { echo '.sprintf($f,'$_ctx->archives->url($core)').'; } ?>';
	}
}


#-----------------------------------------------------------
# Redefines 'archive' urlHandler to plug the new day mode
#-----------------------------------------------------------
$core->url->register('archive','archive','^archive(/.+)?$',array('dayModeUrlHandlers','archive'));

class dayModeUrlHandlers extends dcUrlHandlers
{
	public static function archive($args)
	{
		global $_ctx,$core;
		
		if (preg_match('|^/([0-9]{4})/([0-9]{2})/([0-9]{2})$|',$args,$m)) {
			$params['year']	  = $m[1];
			$params['month']	  = $m[2];
			$params['day']		  = $m[3];
			$params['post_type']  = 'post';
			
			$_ctx->day = $core->blog->getDates($params);
			if ($_ctx->day->isEmpty()) {
				self::p404();
				return;
			}

			self::serveDocument('archive_day.html');
		}
		else {
			parent::archive($args);
		}
		return;
	}
}
?>