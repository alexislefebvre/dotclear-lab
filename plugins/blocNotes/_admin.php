<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Bloc-Notes.
# Copyright 2008,2009,2010,2011 Moe (http://gniark.net/)
#
# Bloc-Notes is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Bloc-Notes is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icons (*.png) are from Tango Icon theme :
#	http://tango.freedesktop.org/Tango_Icon_Gallery
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$__autoload['blocNotes'] =
	dirname(__FILE__).'/inc/lib.blocNotes.php';

# dashboard
if ($core->auth->check('usage,contentadmin',$core->blog->id))
{
	$core->addBehavior('adminDashboardIcons',
		array('blocNotes','adminDashboardIcons'));
}

# post
$core->addBehavior('adminPostForm',array('blocNotes','form'));
$core->addBehavior('adminAfterPostCreate',
	array('blocNotes','putSettings'));
$core->addBehavior('adminAfterPostUpdate',
	array('blocNotes','putSettings'));
$core->addBehavior('adminPostHeaders',
	array('blocNotes','adminPostHeaders'));

$_menu['Plugins']->addItem(__('Notebook'),
	'plugin.php?p=blocNotes',
	'index.php?pf=blocNotes/icon.png',
	preg_match('/plugin.php\?p=blocNotes(&.*)?$/',
	$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id));


# backups

/*$core->addBehavior('exportFull',
	array('blocNotesExportImport','exportFull'));
$core->addBehavior('exportSingle',
	array('blocNotesExportImport','exportSingle'));
$core->addBehavior('importInit',
	array('blocNotesExportImport','importInit'));
$core->addBehavior('importSingle',
	array('blocNotesExportImport','importSingle'));
$core->addBehavior('importFull',
	array('blocNotesExportImport','importFull'));

class blocNotesExportImport
{
	public static function exportFull($core,$exp)
	{
		$exp->export('user',
			'SELECT bloc_notes, user_id '.
			'FROM '.$core->prefix.'user U '.
			"WHERE U.bloc_notes != '' "
		);
	}
	
	public static function exportSingle($core,$exp,$blog_id)
	{
		# from /dotclear/admin/blog_pref.php
		$blog_users = $core->getBlogPermissions($blog_id,$core->auth->isSuperAdmin());
		$blog_users = array_keys($blog_users);
		
		$exp->export('user',
			'SELECT bloc_notes, user_id '.
			'FROM '.$core->prefix.'user U '.
			"WHERE U.bloc_notes != '' ".
			"AND U.user_id ".$core->con->in($blog_users)
		);
	}
	
	public static function importInit($bk,$core)
	{
		$bk->cur_user = $core->con->openCursor($core->prefix.'user');
	}
	
	public static function importFull($line,$bk,$core)
	{
		if ($line->__name == 'user')
		{
			$bk->cur_user->bloc_notes   = (string) $line->bloc_notes;
			$bk->cur_meta->update('WHERE user_id = \''.
				$core->con->escape($line->user_id).'\'');
		}
	}
	
	public static function importSingle($line,$bk,$core)
	{
		if ($line->__name == 'user')
		{
			$bk->cur_user->bloc_notes   = (string) $line->bloc_notes;
			$bk->cur_meta->update('WHERE user_id = \''.
				$core->con->escape($line->user_id).'\'');
		}
	}
}*/

?>