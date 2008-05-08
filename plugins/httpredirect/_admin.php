<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'HTTP Redirect', a plugin for Dotclear 2           *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'HTTP Redirect' (see COPYING.txt);      *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$core->addBehavior('adminPostHeaders',array('adminHttpRedirect','js'));
$core->addBehavior('adminPostFormSidebar',array('adminHttpRedirect','form'));
$core->addBehavior('adminBeforePostCreate',array('adminHttpRedirect','save'));
$core->addBehavior('adminBeforePostUpdate',array('adminHttpRedirect','save'));

$core->addBehavior('adminPostsActionsCombo',array('adminHttpRedirect','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActionsContent',array('adminHttpRedirect','adminPostsActionsContent'));
$core->addBehavior('adminPostsActions',array('adminHttpRedirect','adminPostsActions'));

class adminHttpRedirect
{
	public static function adminPostsActionsCombo(&$args)
	{
		$args[0][__('Redirect by HTTP')] = 'httpredirect';
	}
	
	public static function adminPostsActionsContent(&$core,$action,$hidden_fields)
	{
		global $__httpRedirectPosts;

		if (!( $action == 'httpredirect' && self::isInstalled() )) {
			return;
		}
		
		$entries = $_POST['entries'];
		foreach ($entries as $k => $v) {
			$entries[$k] = (integer) $v;
		}
		$posts = self::getPostsInfo($core,$entries,$core->prefix.'post');

		echo
		'<h2>'.__('HTTP Redirection').'</h2>'.
		'<form action="posts_actions.php" method="post">'.
		'<table class="maximal">'.
		'<thead><tr>'.
		'<th>'.__('Title').'</th>'.
		'<th>'.__('Redirect URL').'</th>'.
		'<th>'.__('Hide').'</th>'.
		'<th>'.__('Status').'</th>'.
		'</tr></thead>'.
		'<tbody>';

		foreach ($posts as $k=>$v)
		{
			# 'Switch' taken from Dotclear, inc/admin/lib.pager.php (modified)
			$hided = 0; $accessible = 1;
			$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
			switch ($v['post_status']) {
				case 1:
					$img_status = sprintf($img,__('published'),'check-on.png');
					break;
				case 0:
					$img_status = sprintf($img,__('unpublished'),'check-off.png');
					$hided = 1;
					break;
				case -1:
					$img_status = sprintf($img,__('scheduled'),'scheduled.png');
					$accessible = 0;
					break;
				case -2:
					$img_status = sprintf($img,__('pending'),'check-wrn.png');
					$accessible = 0;
					break;
			}
			echo '<tr class="line'.($accessible ? '' : ' offline').'">'.
				'<td>'.html::escapeHTML($v['post_title']).'</td>'.
				'<td class="nowrap">'.
				form::field(array('redirect_url['.$k.']'),40,255,html::escapeHTML($v['redirect_url']),'maximal').'</td>'.
				'<td class="minimal">'.form::checkbox(array('posts_hide[]'),$k,$hided,'','',!$accessible).'</td>'.
				'<td class="minimal status">'.$img_status.'</td></tr>';
		}
		
		echo
		'</tbody></table>'.
		'<p>'.$hidden_fields.$core->formNonce().
		form::hidden(array('action'),'httpredirect').
		'<input type="submit" value="'.__('save').'" /></p>'.
		'</form>';
	}
	
	public static function adminPostsActions(&$core,$posts,$action,$redir)
	{
		if (	empty($_POST['redirect_url'])
		|| !is_array($_POST['redirect_url'])
		|| $action != 'httpredirect'
		|| !self::isInstalled() ) {
			return;
		}
		
		$table = $core->prefix.'post';
		$blog_id = $core->con->escape($core->blog->id);
		$postsRedirect = $_POST['redirect_url'];
		$postsHide = !empty($_POST['posts_hide']) && is_array($_POST['posts_hide'])
			? $_POST['posts_hide'] : array();
		
		try
		{
			while ($posts->fetch())
			{
				$post_id = (integer) $posts->post_id;
				$post_status = (integer) $posts->post_status;
				$redirect_url = '';

				if (!empty($postsRedirect[$post_id])) {
					$redirect_url = $postsRedirect[$post_id];
				}
				
				if ($post_status == 1 && in_array($post_id,$postsHide)) {
					$post_status = 0;
				}
				elseif ($post_status == 0 && !in_array($post_id,$postsHide)) {
					$post_status = 1;
				}
				
				$cur = $core->con->openCursor($table);
				$cur->post_status = $post_status;
				$cur->redirect_url = $redirect_url;
				$cur->update('WHERE post_id = '.$post_id.' '.
					"AND blog_id = '".$blog_id."' ");
			}
			$core->blog->triggerBlog();
			http::redirect($redir);
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	
	public static function getPostsInfo(&$core,$entries,$table)
	{
		$blog_id = $core->con->escape($core->blog->id);
		$entries = implode(',',$entries);
		
		$strReq =
			'SELECT post_id, post_title, post_status, redirect_url '.
			'FROM '.$table.' '.
			"WHERE blog_id = '".$blog_id."' AND post_id IN (".$entries.") ".
			'ORDER BY post_dt DESC ';
		$posts = $core->con->select($strReq);
		
		$res = array();
		
		try
		{
			while ($posts->fetch())
			{
				$res[(integer) $posts->post_id] = array(
					'post_status'=>$posts->post_status,
					'post_title'=>$posts->post_title,
					'redirect_url'=>$posts->redirect_url);
			}
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
		
		return $res;
	}
	
	public static function js()
	{
		return dcPage::jsLoad('index.php?pf=httpredirect/js/post.js');
	}

	public static function form(&$post)
	{
		global $core;
		
		if (!self::isInstalled()) {
			return;
		}
		
		if ($post === null) {
			$redirect_url = '';
		}
		else {
			$strReq =
			'SELECT post_id, redirect_url '.
			'FROM '.$core->prefix.'post '.
			"WHERE post_id = '".$core->con->escape($post->post_id)."' ";
			
			$rs = $core->con->select($strReq);
			
			$redirect_url = $rs->redirect_url;
		}
		
		if (!empty($_POST['redirect_url'])) {
			$redirect_url = $_POST['redirect_url'];
		}
		
		echo
		'<h3 class="httpredirect">'.__('HTTP Redirection:').'</h3>'.
		'<p class="httpredirect"><label>'.__('Redirect URL:').' '.
			form::field('redirect_url',10,255,html::escapeHTML($redirect_url),'maximal').
		'</label></p>'.
		'<p class="httpredirect form-note">'.__('Leave empty to cancel redirection.').'</p>';
	}

	public static function save(&$cur)
	{
		global $core;
		
		if (!self::isInstalled()) {
			return;
		}
		
		$cur->redirect_url = $_POST['redirect_url'];
	}

	public static function isInstalled()
	{
		global $core;
		
		$label = 'httpredirect';
		$m_version = $core->plugins->moduleInfo($label,'version');
		$i_version = $core->getVersion($label);

		if (version_compare($i_version,$m_version,'=')) {
			return true;
		}
		return false;
	}
}
?>