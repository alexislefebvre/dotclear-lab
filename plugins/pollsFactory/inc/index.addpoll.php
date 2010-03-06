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

# Get post
$post_id = isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : -1;
$post = $core->blog->getPosts(array('post_id'=>$post_id));

$action_redir = 'plugin.php?p=pollsFactory&tab=addpoll&post_id='.$post_id.'&msg='.$action;

# Post
$_STEP_ = 0;
if (!$post->isEmpty())
{
	if ($core->auth->check('categories',$core->blog->id)) {
		$cat_link = '<a href="category.php?id=%s">%s</a>';
	} else {
		$cat_link = '%2$s';
	}

	if ($post->cat_title) {
		$cat_title = sprintf($cat_link,$post->cat_id,
		html::escapeHTML($post->cat_title));
	} else {
		$cat_title = __('None');
	}

	$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
	switch ($post->post_status) {
		case 1:
			$img_status = sprintf($img,__('published'),'check-on.png');
			break;
		case 0:
			$img_status = sprintf($img,__('unpublished'),'check-off.png');
			break;
		case -1:
			$img_status = sprintf($img,__('scheduled'),'scheduled.png');
			break;
		case -2:
			$img_status = sprintf($img,__('pending'),'check-wrn.png');
			break;
	}

	$protected = '';
	if ($post->post_password) {
		$protected = sprintf($img,__('protected'),'locker.png');
	}

	$selected = '';
	if ($post->post_selected) {
		$selected = sprintf($img,__('selected'),'selected.png');
	}

	#
	$_STEP_ = 1;
}
# Poll
if ($_STEP_ == 1)
{
	# Create poll
	if ($action == 'createpoll')
	{
		$_poll_strdt = isset($_POST['_poll_strdt']) ? $_POST['_poll_strdt'] : '';
		$_poll_enddt = isset($_POST['_poll_enddt']) ? $_POST['_poll_enddt'] : '';
		
		try {
			if (empty($_poll_strdt) || empty($_poll_enddt)) {
				throw new Exception(__('You must specify dates of poll'));
			}
			if (strtotime($_poll_strdt) > strtotime($_poll_enddt)) {
				throw new Exception(__('End date must be superior to start date'));
			}
			
			$cur = $fact->openCursor('poll');
			$cur->post_id = $post->post_id;
			$cur->poll_strdt = $_poll_strdt;
			$cur->poll_enddt = $_poll_enddt;

			$fact->addPoll($cur);
			
			http::redirect($action_redir);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	# Edit poll dates
	if ($action == 'editperiod')
	{
		$poll_id = isset($_POST['poll_id']) ? $_POST['poll_id'] : '';
		$_poll_strdt = isset($_POST['_poll_strdt']) ? $_POST['_poll_strdt'] : '';
		$_poll_enddt = isset($_POST['_poll_enddt']) ? $_POST['_poll_enddt'] : '';
		
		try {
			if (empty($_poll_strdt) || empty($_poll_enddt)) {
				throw new Exception(__('You must specify dates of poll'));
			}
			if (strtotime($_poll_strdt) > strtotime($_poll_enddt)) {
				throw new Exception(__('End date must be superior to start date'));
			}
			
			$cur = $fact->openCursor('poll');
			$cur->poll_strdt = $_poll_strdt;
			$cur->poll_enddt = $_poll_enddt;

			$fact->updPoll($poll_id,$cur);
			
			http::redirect($action_redir);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	# Get poll
	$poll = $fact->getPolls(array('post_id'=>$post_id,'poll_status'=>''));
	if (!$poll->isEmpty())
	{
		$old_queries = $fact->getQueries(array('poll_id'=>$poll->poll_id,'query_status'=>'!-2'));

		#
		$_STEP_ = 2;
	}
}
# Delete poll
if ($_STEP_ > 1 && $action == 'deletepoll')
{
	try {
		libPollsFactory::deletePoll($fact,$poll->poll_id);
		http::redirect($action_redir);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
# Delete old queries
if ($_STEP_ > 1 && $action == 'deletequery' 
 && !empty($_POST['_query_del']) && is_array($_POST['_query_del']))
{
	try {
		foreach($_POST['_query_del'] as $id)
		{
			libPollsFactory::deleteQuery($fact,$id);
		}

		http::redirect($action_redir);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
# Reorder old queries
if ($_STEP_ > 1 && $action == 'reorderquery')
{
	# Whitout js
	if (empty($_REQUEST['queries_order_js']) && !empty($_REQUEST['queries_order_html'])) {
		$order = $_REQUEST['queries_order_html'];
		asort($order);
		$order = array_keys($order);
	}
	# With js
	elseif (!empty($_REQUEST['queries_order_js'])) {
		$order = explode(',',$_REQUEST['queries_order_js']);
	}
	# There is something to sort
	if (!empty($order)) {
		# Arrange order
		$new_order = array();
		foreach ($order as $pos => $id) {
			$pos = ((integer) $pos)+1;
			if (!empty($pos) && !empty($id)) {
				$new_order[$pos] = $id;
			}
		}
		try {
			# Update queries order
			foreach($new_order as $pos => $id) {
				libPollsFactory::positionQuery($fact,$id,$pos);
			}

			http::redirect($action_redir);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
}

if ($_STEP_ == 2)
{
	# Create query
	if ($action == 'createquery')
	{
		$_query_title = isset($_POST['_query_title']) ? $_POST['_query_title'] : '';
		$_query_desc = isset($_POST['_query_desc']) ? $_POST['_query_desc'] : '';
		$_query_type = isset($_POST['_query_type']) ? $_POST['_query_type'] : '';
		
		try {
			if (empty($_query_title)) {
				throw new Exception(__('You must specify query title'));
			}
			if (empty($_query_type)) {
				throw new Exception(__('You must specify query type'));
			}

			$cur = $fact->openCursor('query');
			$cur->poll_id = $poll->poll_id;
			$cur->query_title = $_query_title;
			$cur->query_desc = $_query_desc;
			$cur->query_type = $_query_type;

			if (in_array($_query_type,array('field','textarea'))) {
				$cur->query_status = 1;
			}

			$_query_id = $fact->addQuery($cur);

			if (in_array($_query_type,array('field','textarea'))) {
				$cur = $fact->openCursor('option');
				$cur->query_id = $_query_id;
				$cur->option_text = '-';
				
				$fact->addOption($cur);
			}
			elseif ($poll->poll_status != -2) {
					libPollsFactory::uncompletePoll($fact,$poll->poll_id);
			}

			http::redirect($action_redir);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	# Get query
	$query = $fact->getQueries(array('poll_id'=>$poll->poll_id,'query_status'=>'-2'));
	if (!$query->isEmpty())
	{
		$old_options = $fact->getOptions(array('query_id'=>$query->query_id));

		#
		$_STEP_ = 3;
	}
}
# Delete new query
if ($_STEP_ > 2 && $action == 'deletenewquery' && !empty($_POST['query_id']))
{
	try {
		# Get options
		$options_del = $fact->getOptions(array('query_id'=>$_POST['query_id']));
		if (!$options_del->isEmpty()) {
			while ($options_del->fetch()) {
				# Delete option
				$fact->delOption($options_del->option_id);
			}
		}
		# Delete query
		$fact->delQuery($_POST['query_id']);

		http::redirect($action_redir);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
# Delete option
if ($_STEP_ > 2 && $action == 'deleteoption' 
 && !empty($_POST['_option_del']) && is_array($_POST['_option_del']))
{
	try {
		foreach($_POST['_option_del'] as $id) {
			$fact->delOption($id);
		}

		http::redirect($action_redir);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
# Option
if ($_STEP_ == 3)
{
	# Create option
	if ($action == 'createoption')
	{
		$_option_text = isset($_POST['_option_text']) ? $_POST['_option_text'] : '';
		
		try {
			if (empty($_option_text)) {
				throw new Exception(__('You must specify option text'));
			}
			
			$cur = $fact->openCursor('option');
			$cur->query_id = $query->query_id;
			$cur->option_text = $_option_text;

			$fact->addOption($cur);

			if ($poll->poll_status != -2) {
				libPollsFactory::uncompletePoll($fact,$poll->poll_id);
			}

			http::redirect($action_redir);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	# Get option
	$option = $fact->getOptions(array('query_id'=>$query->query_id));
	if (!$option->isEmpty())
	{
		#
		$_STEP_ = 4;
	}
}
if ($_STEP_ == 4)
{
	# Set query as finished
	if ($action == 'finishquery') {
		try {
			$cur = $fact->openCursor('query');
			$cur->query_status = 1;
			$fact->updQuery($query->query_id,$cur);

			http::redirect($action_redir);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
}
if ($_STEP_ == 4 || $_STEP_ == 2 && !$old_queries->isEmpty())
{
	# Set poll as finished
	if ($action == 'finishpoll') {
		try {
			if (!$query->isEmpty()) {
				$cur = $fact->openCursor('query');
				$cur->query_status = 1;
				$fact->updQuery($query->query_id,$cur);
			}
			libPollsFactory::uncompletePoll($fact,$poll->poll_id,false);

			# --BEHAVIOR-- adminAfterCompletePoll
			$core->callBehavior('adminAfterCompletePoll',$poll);

			http::redirect($action_redir);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
}

# Display

if ($_STEP_ == 0)
{
	$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
	$nb_per_page =  20;

	$params = array();
	$params['post_type'] = 'post';
	$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
	$params['no_content'] = true;
	$params['order'] = 'post_dt DESC';

	try {
		$posts = $core->blog->getPosts($params);
		$counter = $core->blog->getPosts($params,true);
		$post_list = new adminPollList($core,$posts,$counter->f(0));
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

	$echo .= '
	<h2>'.__('Select an entry to add a poll to it').'</h2>
	<div id="form-entries">'.$post_list->postDisplay($page,$nb_per_page).'</div>';
}
elseif ($_STEP_ > 0)
{
	$lis = '';
	if ($_STEP_ > 1) {
		$votes = $fact->countUsers($poll->poll_id);
		if ($votes) {
			$lis .= 
			'<li>'.__('Votes:').' '.
			'<a href="plugin.php?p=pollsFactory&amp;tab=viewpoll&amp;post_id='.
			$post_id.'" title="'.__('View results of this poll.').'">'.
			$votes.'</a></li>';
		}
	}
	$echo .= addpollform(
		__('Entry linked to this poll'),$post_id,
		__('delete this poll'),'deletepoll',
		'<ul>'.
		'<li>'.__('Title:').' <a href="'.
		$core->getPostAdminURL($post->post_type,$post->post_id).'">'.
		html::escapeHTML($post->post_title).'</a></li>'.
		'<li>'.__('Date:').' '.dt::dt2str(__('%Y-%m-%d %H:%M'),$post->post_dt).'</li>'.
		'<li>'.__('Category:').' '.$cat_title.'</li>'.
		'<li>'.__('Author:').' '.$post->user_id.'</li>'.
		'<li>'.__('Status:').' '.$img_status.' '.$selected.' '.$protected.'</li>'.
		$lis.
		'</ul>'
	);
}
if ($_STEP_ == 1)
{
	$echo .= addpollform(
		__('Select a period for this poll'),$post_id,
		__('save'),'createpoll','
		<form method="post" action="plugin.php">
		<p><label for="_poll_strdt">'.__('Start date:').'</label>'.
		form::field('_poll_strdt',20,1,$_poll_strdt,'',9).'</p>
		<p><label for="_poll_enddt">'.__('End date:').'</label>'.
		form::field('_poll_enddt',20,1,$_poll_enddt,'',10).'</p>'
	);
}
elseif ($_STEP_ > 1)
{
	$echo .= addpollform(
		__('Period of this poll'),$post_id,
		__('edit'),'editperiod','
		<p><label for="_poll_strdt">'.__('Start date:').'</label>'.
		form::field('_poll_strdt',20,1,substr($poll->poll_strdt,0,-3),'',9).'</p>
		<p><label for="_poll_enddt">'.__('End date:').'</label>'.
		form::field('_poll_enddt',20,1,substr($poll->poll_enddt,0,-3),'',10).'</p>'.
		form::hidden(array('poll_id'),$poll->poll_id)
	);

	if (!$old_queries->isEmpty())
	{
		$lis = '';
		$i = 1;
		while ($old_queries->fetch())
		{
			$lis .=
			'<tr class="line" id="l_'.$old_queries->query_id.'">'.
			'<td class="handle minimal">'.form::field(array('queries_order_html['.$old_queries->query_id.']'),2,5,$old_queries->query_pos).'</td>'.
			'<td class="minimal">
			'.form::checkbox(array('_query_del[]'),$old_queries->query_id).'
			</td>
			<td>'.$i.' '.html::escapeHTML($old_queries->query_title).' </td>
			<td>'.array_search($old_queries->query_type,libPollsFactory::getQueryTypes()).' </td>
			</tr>';
			$i++;
		}
		$echo .= addpollform(
			__('Queries already linked to this poll'),$post_id,
			__('action on existing queries:'),array(
				__('delete selected queries') => 'deletequery',
				__('reorder queries') => 'reorderquery'
			),
			'<table class="maximal dragable">'.
			'<thead><tr>'.
			'<th colspan="3">'.__('Query').form::hidden('queries_order_js','').'</th>'.
			'<th>'.__('Type').'</th>'.
			'</tr></thead>'.
			'<tbody id="queries-list">'.
			$lis.
			'</tbody>'.
			'</table>'
		);
	}
}
if ($_STEP_ == 2)
{
	$echo .= addpollform(
		__('New query'),$post_id,
		__('save'),'createquery','
		<p><label for="_query_title">'.__('Title:').'</label>'.
		form::field('_query_title',60,255,html::escapeHTML($_query_title),'',9).'</p>
		<p class="area"><label for="_query_desc">'.__('Description:').'</label>'.
		form::textarea('_query_desc',60,5,html::escapeHTML($_query_desc)).'</p>
		<p class="area"><label for="_query_desc">'.__('Type:').'</label>'.
		form::combo('_query_type',libPollsFactory::getQueryTypes(),$_query_type).'</p>'
	);
}
elseif ($_STEP_ > 2)
{
	$echo .= addpollform(
		__('New query'),$post_id,
		__('delete new query'),'deletenewquery',
		'<ul>'.
		'<li>'.__('Title:').' '.html::escapeHTML($query->query_title).form::hidden(array('query_id'),$query->query_id).'</li>'.
		'<li>'.__('Description:').' '.substr(html::escapeHTML($query->query_desc),0,150).'[...]</li>'.
		'<li>'.__('Type:').' '.array_search($query->query_type,libPollsFactory::getQueryTypes()).'</li>'.
		'</ul>'
	);

	if (!$old_options->isEmpty())
	{
		$lis = '';
		while ($old_options->fetch()) {
			$lis .= 
			'<li>'.form::checkbox(array('_option_del[]'),$old_options->option_id,0).' '.
			html::escapeHTML($old_options->option_text).'</li>';
		}
		$echo .= addpollform(
			__('Options already linked to this new query'),$post_id,
			__('delete selected options'),'deleteoption',
			'<ul>'.$lis.'</ul>'
		);
	}
	$echo .= addpollform(
		__('New option for this query'),$post_id,
		__('save'),'createoption','
		<p><label for="_option_text">'.__('Text:').'</label>'.
		form::field('_option_text',60,255,html::escapeHTML($_option_text),'',9).'</p>'
	);
}
if ($_STEP_ == 4)
{
	$echo .= addpollform(null,$post_id,__('query is complete'),'finishquery','');
}
if ($_STEP_ == 4 || $_STEP_ == 2 && !$old_queries->isEmpty() && $poll->poll_status == -2)
{
	$echo .= addpollform(null,$post_id,__('poll is complete'),'finishpoll','');
}

function addpollForm($title,$post_id,$submit,$action,$content)
{
	$r =  
	($title === null ? '' : '<h2>'.$title.'</h2>').'
	<form method="post" action="plugin.php">'.
	$content;
	
	if (is_array($action)) {
		$r .= 
		'<p>'.$submit.' '.
		form::combo('action',$action).
		'<input type="submit" value="'.__('ok').'" />';
	}
	else {
		$r .= 
		'<p><input type="submit" name="save" value="'.$submit.'" />'.
		form::hidden(array('action'),$action);
	}
	$r .=
	$GLOBALS['core']->formNonce().
	form::hidden(array('post_id'),$post_id).
	form::hidden(array('p'),'pollsFactory').
	form::hidden(array('tab'),'addpoll').'
	</p>
	</form>';

	return $r;
}
?>