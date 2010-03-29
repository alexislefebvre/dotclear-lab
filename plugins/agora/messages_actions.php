<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku ,Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }
dcPage::check('usage,contentadmin');

$redir_url = $p_url.'&act=messages';
$params = array();

if (!empty($_POST['action']) && !empty($_POST['messages']))
{
	$messages = $_POST['messages'];
	$action = $_POST['action'];
	
	if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false)
	{
		$redir = $_POST['redir'];
	}
	else
	{
		$redir =
		$redir_url.
		'&author='.$_POST['author'].
		'&status='.$_POST['status'].
		'&sortby='.$_POST['sortby'].
		'&order='.$_POST['order'].
		'&page='.$_POST['page'].
		'&nb='.(integer) $_POST['nb'];
	}
	
	foreach ($messages as $k => $v) {
		$messages[$k] = (integer) $v;
	}
	
	$params['sql'] = 'AND M.message_id IN('.implode(',',$messages).') ';
	$params['no_content'] = true;
	
	$mes = $core->blog->agora->getMessages($params);
	
	if (preg_match('/^(publish|unpublish|pending|junk)$/',$action))
	{
		switch ($action) {
			case 'unpublish' : $status = 0; break;
			case 'pending' : $status = -1; break;
			case 'junk' : $status = -2; break;
			default : $status = 1; break;
		}
		
		while ($mes->fetch())
		{
			try {
				$core->blog->agora->updMessageStatus($mes->message_id,$status);
			} catch (Exception $e) {
				$core->error->add($e->getMessage().'....'.$mes->message_content);
				break;
			}
		}
		
		if (!$core->error->flag()) {
			http::redirect($redir);
		}
	}
	elseif ($action == 'delete')
	{
		while ($mes->fetch())
		{
			try {
				$core->blog->agora->delMessage($mes->message_id);
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
//dcPage::open(__('Comments'));

echo '<html><body><p><a class="back" href="'.str_replace('&','&amp;',$redir).'">'.__('back').'</a></p></body></html>';

//dcPage::close();
?>
