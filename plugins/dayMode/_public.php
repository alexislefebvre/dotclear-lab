<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of dayMode, a plugin for DotClear2.
# Copyright (c) 2006-2008 Pep and contributors. All rights
# reserved.
#
# This plugin is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This plugin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this plugin; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

require_once dirname(__FILE__).'/_widgets.php';

if (!$core->blog->settings->daymode_active) {
	return;
}

#-----------------------------------------------------------
# Adds a new template behavior
#-----------------------------------------------------------
$core->addBehavior('templateBeforeBlock',array('behaviorDayMode','block'));
$core->addBehavior('publicBeforeDocument',array('behaviorDayMode','addTplPath'));

class behaviorDayMode
{
	public static function block()
	{
		$args = func_get_args();
		array_shift($args);

		if ($args[0] == 'Entries') {
			$attrs = $args[1];

			if (!empty($attrs['today'])) {
				$p =
				'<?php $today = dcDayTools::getEarlierDate(array("ts_type" => "day")); '.
					"\$params['post_year'] = \$today->year(); ".
					"\$params['post_month'] = \$today->month(); ".
					"\$params['post_day'] = \$today->day(); ".
					"unset(\$params['limit']); ".
					"unset(\$today); ".
				" ?>\n";
			}
			else {
				$p =
				'<?php if ($_ctx->exists("day")) { '.
					"\$params['post_year'] = \$_ctx->day->year(); ".
					"\$params['post_month'] = \$_ctx->day->month(); ".
					"\$params['post_day'] = \$_ctx->day->day(); ".
					"unset(\$params['limit']); ".
				"} ?>\n";
			}
			return $p;
		}
	}

	public static function addTplPath(&$core)
	{
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
	}
}


#-----------------------------------------------------------
# Overloads some Archives* dedicated template tags
#-----------------------------------------------------------
$core->tpl->addValue('ArchiveURL', array('tplDayMode','ArchiveURL'));
$core->tpl->addBlock('ArchivesHeader',array('tplDayMode','ArchivesHeader'));
$core->tpl->addBlock('ArchivesFooter',array('tplDayMode','ArchivesFooter'));
$core->tpl->addValue('ArchiveDate',array('tplDayMode','ArchiveDate'));
$core->tpl->addBlock('ArchiveNext',array('tplDayMode','ArchiveNext'));
$core->tpl->addBlock('ArchivePrevious',array('tplDayMode','ArchivePrevious'));

class tplDayMode
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
$core->url->register('archive','archive','^archive(/.+)?$',array('urlDayMode','archive'));

class urlDayMode extends dcUrlHandlers
{
	public static function archive($args)
	{
		if (preg_match('|^/([0-9]{4})/([0-9]{2})/([0-9]{2})$|',$args,$m))
		{
			$params['year'] = $m[1];
			$params['month'] = $m[2];
			$params['day'] = $m[3];
			$GLOBALS['_ctx']->day = $GLOBALS['core']->blog->getDates($params);

			if ($GLOBALS['_ctx']->day->isEmpty()) {
				self::p404();
			}

			self::serveDocument('archive_day.html');
			exit;
		}
		parent::archive($args);
	}
}
?>