<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Subscribe to comments.
# Copyright 2008 Moe (http://gniark.net/)
#
# Subscribe to comments is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Subscribe to comments is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://txfx.net/code/wordpress/subscribe-to-comments/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Subscribe to comments'),
	'plugin.php?p=subscribeToComments',
	'index.php?pf=subscribeToComments/icon.png',
	preg_match('/plugin.php\?p=subscribeToComments(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

if ($core->blog->settings->subscribetocomments_active)
{
	$core->addBehavior('coreAfterCommentCreate',array('subscribeToComments',
		'coreAfterCommentCreate'));
	$core->addBehavior('adminAfterCommentDesc',array('subscribeToComments',
		'adminAfterCommentDesc'));
	# when a comment is published
	$core->addBehavior('coreAfterCommentUpdate',array('subscribeToComments',
		'coreAfterCommentUpdate'));
}

# import/Export

$core->addBehavior('exportFull',
	array('subscribeToCommentsAdmin','exportFull'));
$core->addBehavior('exportSingle',
	array('subscribeToCommentsAdmin','exportSingle'));
$core->addBehavior('importInit',
	array('subscribeToCommentsAdmin','importInit'));
$core->addBehavior('importSingle',
	array('subscribeToCommentsAdmin','importSingle'));
$core->addBehavior('importFull',
	array('subscribeToCommentsAdmin','importFull'));

/**
@ingroup Subscribe to comments
@brief Admin
*/
class subscribeToCommentsAdmin
{
	public static function exportFull(&$core,&$exp)
	{
		$exp->exportTable('comment_subscriber');
	}
	
	public static function exportSingle(&$core,&$exp,$blog_id)
	{
		$exp->export('comment_subscriber',
			'SELECT id, email, user_key, temp_key, temp_expire, status '.
			'FROM '.$core->prefix.'comment_subscriber');
	}
	
	public static function importInit(&$bk,&$core)
	{
		$bk->cur_comment_subscriber = $core->con->openCursor($core->prefix.'comment_subscriber');
	}
	
	public static function importFull(&$line,&$bk,&$core)
	{
		if ($line->__name == 'comment_subscriber')
		{
			$bk->cur_comment_subscriber->clean();
			
			$bk->cur_comment_subscriber->id = (integer) $line->id;
			
			$bk->cur_comment_subscriber->email = (string) $line->email;
			$bk->cur_comment_subscriber->user_key = (string) $line->user_key;
			$bk->cur_comment_subscriber->temp_key = (string) $line->temp_key;
			$bk->cur_comment_subscriber->temp_expire = (string) $line->temp_expire;
			
			$bk->cur_comment_subscriber->status = (integer) $line->status;
			
			$rs = $core->con->select('SELECT id FROM '.
				$core->prefix.'comment_subscriber WHERE (id = \''.$line->id.'\');');
			if ($rs->isEmpty())
			{
				$bk->cur_comment_subscriber->insert();
			}
			else
			{
				$bk->cur_comment_subscriber->update('WHERE (id = '.$core->con->escape($line->id).')');
			}
		}
	}
	
	public static function importSingle(&$line,&$bk,&$core)
	{
		if ($line->__name == 'comment_subscriber')
		{
			$cur = $core->con->openCursor($core->prefix.'comment_subscriber');
			$cur->id = $line->id;
			$cur->email = $line->email;
			$cur->user_key = $line->user_key;
			$cur->temp_key = $line->temp_key;
			$cur->temp_expire = $line->temp_expire;
			$cur->status = $line->status;
			
			$rs = $core->con->select('SELECT id FROM '.
				$core->prefix.'comment_subscriber WHERE (id = \''.$line->id.'\');');
			if ($rs->isEmpty())
			{
				$cur->insert();
			}
			else
			{
				$cur->update('WHERE (id = '.$core->con->escape($line->id).')');
			}
		}
	}
}
?>