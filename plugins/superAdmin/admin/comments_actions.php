<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Super Admin, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
#
# Super Admin is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Super Admin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

dcPage::checkSuper();

$params = array();
$redir = $p_url.'&file=comments';

if (!empty($_POST['action']) && !empty($_POST['comments']))
{
	$comments = $_POST['comments'];
	$action = $_POST['action'];
	
	if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false)
	{
		$redir = $_POST['redir'];
	}
	else
	{
		$redir =
		$p_url.
		'&file=comments'.
		'&blog_id='.$_POST['blog_id'].
		'&q='.$_POST['q'].
		'&type='.$_POST['type'].
		'&author='.$_POST['author'].
		'&status='.$_POST['status'].
		'&sortby='.$_POST['sortby'].
		'&ip='.$_POST['ip'].
		'&order='.$_POST['order'].
		'&page='.$_POST['page'].
		'&nb='.(integer) $_POST['nb'];
	}
	
	foreach ($comments as $k => $v) {
		$comments[$k] = (integer) $v;
	}
	
	$params['sql'] = 'AND C.comment_id IN('.implode(',',$comments).') ';
	$params['no_content'] = true;
	
	$co = superAdmin::getComments($params);
	
	if (preg_match('/^(publish|unpublish|pending|junk)$/',$action))
	{
		switch ($action) {
			case 'unpublish' : $status = 0; break;
			case 'pending' : $status = -1; break;
			case 'junk' : $status = -2; break;
			default : $status = 1; break;
		}
		
		while ($co->fetch())
		{
			try {
				$core->setBlog($co->blog_id);
				
				$core->blog->updCommentStatus($co->comment_id,$status);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
		
		if (!$core->error->flag()) {
			http::redirect($redir);
		}
	}
	elseif ($action == 'delete')
	{
		while ($co->fetch())
		{
			try {
				$core->setBlog($co->blog_id);
				
				$core->blog->delComment($co->comment_id);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
		
		if (!$core->error->flag()) {
			http::redirect($redir);
		}
	}
}

/* DISPLAY
-------------------------------------------------------- */
dcPage::open(__('Comments'));

echo '<p><a class="back" href="'.str_replace('&','&amp;',$redir).'">'.__('back').'</a></p>';

dcPage::close();
?>