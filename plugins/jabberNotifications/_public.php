<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is Jabber Notifications, a plugin for Dotclear 2      *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
 *  Oleksandr Syenchuk, Olivier Tétard and contributors.       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along Jabber Notifications (see COPYING.txt);      *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$core->addBehavior('publicAfterCommentCreate', array('publicJabberNotifications', 'sendNotifications'));
$core->addBehavior('publicAfterTrackbackCreate', array('publicJabberNotifications', 'sendNotifications'));

class publicJabberNotifications
{
	public static function sendNotifications(&$cur, $comment_id)
	{
		global $core,$_ctx;
		
		# Spam or Jabber notifications not enabled
		if ($cur->comment_status == -2 || !$core->blog->settings->jn_enab) {
			return;
		}
		
		# Get post info if trackback
		if ($cur->comment_trackback) {
			$params = new ArrayObject();
			$params['no_content'] = 1;
			$params['post_id'] = $cur->post_id;
			$_ctx->posts = $core->blog->getPosts($params);
			unset($params);
		}
		
		$author = $_ctx->posts->user_id;
		$blog = $core->blog->id;
		$users = self::getBlogUsers($blog);
		
		$notifications = array();
		
		# Notifications list
		foreach ($users as $user=>$u)
		{
			if (!$u['notify'] || $u['notify'] == 'never' || !$u['jabberid']) {
				continue;
			}

			if (($u['notify'] == 'entries' && $user == $author) ||
				($u['notify'] == 'blog' && $u['default_blog'] == $blog) ||
				($u['notify'] == 'blogs' && $u['own_blog']) ||
				($u['notify'] == 'all' && $u['super'])) {
				$notifications[$user] = $u['jabberid'];
			}
		}
		
		if (empty($notifications)) {
			return;
		}
		
		$settings = array(
			'enab'=>$core->blog->settings->jn_enab,
			'serv'=>$core->blog->settings->jn_serv,
			'user'=>$core->blog->settings->jn_user,
			'pass'=>@base64_decode($core->blog->settings->jn_pass),
			'port'=>$core->blog->settings->jn_port,
			'con'=>$core->blog->settings->jn_con,
			'gateway'=>$core->blog->settings->jn_gateway
		);
		$j = new jabberNotifier($settings['serv'],$settings['port'],$settings['user'],$settings['pass'],$settings['con']);
		
		$msg = html::decodeEntities(html::clean($cur->comment_content));
		$msg .= "\n\n--\n".
			sprintf(__('Entry: %s <%s>'),$_ctx->posts->post_title,$_ctx->posts->getURL())."\n".
			sprintf(__('Comment by: %s <%s>'),$cur->comment_author,$cur->comment_email)."\n".
			sprintf(__('Website: %s'),$cur->comment_site);
		$msg = sprintf(
				$cur->comment_trackback
					? __('You received a new trackback on the blog \'%s\':')
					: __('You received a new comment on the blog \'%s\':'),
				$core->blog->name)."\n\n".$msg;
		
		$j->setMessage($msg);
		foreach ($notifications as $user=>$jabberid)
		{
			$j->addDestination($jabberid);
		}
		
		if (empty($settings['gateway'])) {
			$j->commit(3);
		} else {
			$j->commitThroughGateway($settings['gateway']);
		}
	}
	
	public static function getBlogUsers($blog_id)
	{
		global $core;
		
		$res = array();
		
		# Super users
		$strReq =
		'SELECT U.user_id AS user_id, user_default_blog, user_options '.
		'FROM '.$core->prefix.'user U '.
		'WHERE user_super = 1';
		
		$rs = $core->con->select($strReq);
		$rs->extend('rsExtUser');
		
		while ($rs->fetch())
		{
			$res[$rs->user_id] = array(
				'default_blog'=>$rs->user_default_blog,
				'notify'=>$rs->option('jn_notify'),
				'jabberid'=>$rs->option('jn_jabberid'),
				'super'=>true,
				'own_blog'=>false
			);
		}
		
		# Blog users (may override super users)
		$strReq =
		'SELECT U.user_id as user_id, user_default_blog, user_options, user_super '.
		'FROM '.$core->prefix.'user U '.
		'JOIN '.$core->prefix.'permissions P ON U.user_id = P.user_id '.
		"WHERE blog_id = '".$core->con->escape($blog_id)."' ";
		
		$rs = $core->con->select($strReq);
		$rs->extend('rsExtUser');

		while ($rs->fetch())
		{
			$res[$rs->user_id] = array(
				'default_blog'=>$rs->user_default_blog,
				'notify'=>$rs->option('jn_notify'),
				'jabberid'=>$rs->option('jn_jabberid'),
				'super'=>(boolean) $rs->user_super,
				'own_blog'=>true
			);
		}
		
		return $res;
	}
}
?>
