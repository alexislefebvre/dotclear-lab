<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pollsFactory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

require_once dirname(__FILE__).'/_widgets.php';

$_menu['Plugins']->addItem(
	__('Polls factory'),
	'plugin.php?p=pollsFactory','index.php?pf=pollsFactory/icon.png',
	preg_match('/plugin.php\?p=pollsFactory(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

$core->addBehavior('adminPostNavLinks',array('adminPollsFactory','adminPostNavLinks'));
$core->addBehavior('adminBeforePostDelete',array('adminPollsFactory','adminBeforePostDelete'));
$core->addBehavior('adminPostsActionsCombo',array('adminPollsFactory','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActionsContent',array('adminPollsFactory','adminPostsActionsContent'));
$core->addBehavior('adminPostsActions',array('adminPollsFactory','adminPostsActions'));

class adminPollsFactory
{
	public static function adminPostNavLinks($post)
	{
		global $core;

		if ($post === null || !$core->auth->check('admin',$core->blog->id)) return;
		
		$fact = new pollsFactory($core);
		$rs = $fact->getPolls(array('post_id'=>$post->post_id));
		if ($rs->isEmpty()) {
			echo 
			' - <a title="'.__('add a poll to this entry').'" '.
			'href="plugin.php?p=pollsFactory&amp;tab=addpoll&amp;post_id='.
			$post->post_id.'">'.__('add poll').'</a>';
		}
		else {
			echo 
			' - <a title="'.__('edit poll linked to this entry').'" '.
			'href="plugin.php?p=pollsFactory&amp;tab=addpoll&amp;post_id='.
			$post->post_id.'">'.__('edit poll').'</a>';
		}
	}

	public static function adminBeforePostDelete($post_id)
	{
		global $core;

		if ($post_id === null || !$core->auth->check('admin',$core->blog->id)) return;

		$fact = new pollsFactory($core);
		$poll = $fact->getPolls(array('post_id'=>$post_id));
		if ($poll->isEmpty()) return;

		libPollsFactory::deletePoll($fatc,$poll->poll_id);
	}
	
	public static function adminPostsActionsCombo(&$args)
	{
		global $core;
		if (!$core->auth->check('admin',$core->blog->id)) return;

		$args[0][__('polls')][__('delete poll')] = 'delete_poll';
		$args[0][__('polls')][__('remove poll')] = 'remove_poll';
		$args[0][__('polls')][__('close poll')] = 'close_poll';
	}
	
	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if (!in_array($action,array('delete_poll','remove_poll','close_poll'))) return;

		try
		{
			foreach ($_POST['entries'] as $k => $v) {
				$entries[$k] = (integer) $v;
			}

			$params = array();
			$params['sql'] = 'AND P.post_id '.$core->con->in($entries);
			$params['no_content'] = true;
			$params['poll_status'] = '';

			$fact = new pollsFactory($core);
			$rs = $fact->getPolls($params);

			if ($action == 'delete_poll') {
				echo '<h2>'.__('delete polls related to selected entries').'</h2>';
			}
			elseif ($action == 'close_poll') {
				echo '<h2>'.__('close polls related to selected entries').'</h2>';
			}
			elseif ($action == 'remove_poll') {
				echo '<h2>'.__('remove polls related to selected entries').'</h2>'.
				'<p>'.__('This does not erase poll.').'</p>';
			}
			
			if ($rs->isEmpty())
			{
				echo '<p>'.__('There is no poll for selected entries').'</p>';
			}
			else
			{
				echo 
				'<form action="posts_actions.php" method="post"><ul>';

				while($rs->fetch())
				{
					echo
					'<li><label class="classic">'.
					form::checkbox(array('pollentries[]'),$rs->post_id,0).' '.
					$rs->post_title.
					'</label></li>';
				}

				echo 
				'</ul><p>'.
				$hidden_fields.
				$core->formNonce().
				form::hidden(array('action'),$action).
				'<input type="submit" value="'.__('yes').'" /></p>'.
				'</form>';
			}
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}

	public static function adminPostsActions(&$core,$posts,$action,$redir)
	{
		if (!in_array($action,array('delete_poll','remove_poll','close_poll'))
		 || empty($_POST['pollentries'])) return;

		try {
			$fact = new pollsFactory($core);

			while($posts->fetch())
			{
				$rs = $fact->getPolls(array('post_id'=>$posts->post_id));
				if (!$rs->isEmpty())
				{
					if ($action == 'delete_poll') {
						libPollsFactory::deletePoll($fact,$rs->poll_id);
					}
					elseif ($action == 'close_poll') {
						libPollsFactory::closePoll($fact,$rs->poll_id);
					}
					elseif ($action == 'remove_poll') {
						libPollsFactory::uncompletePoll($fact,$rs->poll_id);
					}
				}
			}
			http::redirect($redir);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
}
?>