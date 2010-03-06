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

$url_base = 'plugin.php?p=pollsFactory';
$post_id = isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : -1;
$query_id = isset($_REQUEST['query_id']) ? $_REQUEST['query_id'] : -1;
$r_page = isset($_REQUEST['r_page']) ? abs((integer) $_REQUEST['r_page']) : 1;
$r_limit = isset($_REQUEST['r_limit']) ? abs((integer) $_REQUEST['r_limit']) : 20;

# Poll
$poll = $fact->getPolls(array('post_id'=>$post_id,'poll_status'=>''));
if ($poll->isEmpty()) {
	$echo .=
	'<p><a href="'.$url_base.'&amp;tab=polls" title="'.__('select poll').'">'.
	__('Select poll to view from polls list.').'</a></p>';
}
else {

	if ($action == 'selectresponses')
	{
		try {
			foreach($_POST['responses_list'] as $k => $id)
			{
				libPollsFactory::selectResponse($fact,$id,!empty($_POST['responses_sel'][$id]));
			}
			http::redirect($url_base.'&tab=viewpoll&post_id='.$post_id.'&query_id='.$query_id.'&r_page='.$r_page.'&r_limit='.$r_limit.'&msg='.$action);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}

	$url_base .= '&amp;tab=viewpoll&amp;post_id='.$post_id;
	$count = $fact->countUsers($poll->poll_id);

	$echo .=
	'<h2>'.sprintf(__('Result of the poll linked to entry called "%s"'),html::escapeHTML($poll->post_title)).'</h2>'.
	'<h3>'.__('Poll').'</h3><ul>'.
	'<li>'.__('Start date:').' '.dt::dt2str(__('%Y-%m-%d %H:%M'),$poll->poll_strdt,$core->auth->getInfo('user_tz')).'</li>'.
	'<li>'.__('End date:').' '.dt::dt2str(__('%Y-%m-%d %H:%M'),$poll->poll_enddt,$core->auth->getInfo('user_tz')).'</li>'.
	'<li>'.__('Votes:').' '.$count.'</li>'.
	'</ul>'.
	'<h3>'.__('Queries').'</h3><ul>';
	# Queries
	$queries = $fact->getQueries(array('poll_id'=>$poll->poll_id));
	while ($queries->fetch())
	{
		$echo .= '<li><a href="'.$url_base.'&amp;query_id='.$queries->query_id.'">'.html::escapeHTML($queries->query_title).'</a> <em>('.array_search($queries->query_type,libPollsFactory::getQueryTypes()).')</em></li>';
	}
	$echo .= '</ul>';

	# Query
	$query = $fact->getQueries(array('poll_id'=>$poll->poll_id,'query_id'=>$query_id));
	if ($query->isEmpty()) {
		$echo .= 
		'<h3>'.__('Options').'</h3><p>'.__('Select a query to view their responses.').'</p>';
	}
	else {
		$echo .= '<h3>'.sprintf(__('Responses to the query called "%s"'),html::escapeHTML($query->query_title)).'</h3>';

		$options = $fact->getOptions(array('query_id'=>$query_id));
		if ($query->isEmpty() || $options->isEmpty()) {
			$echo .= '<p>'.__('There is no response for this query.').'</p>';
		}
		else {
			$rs = array();
			$r_p = array('query_id' => $query->query_id);
			# limit results for numeric (id) responses
			if (in_array($query->query_type,array('checkbox','radio','combo'))) {
				$responses = $fact->getResponses($r_p);
			}
			# limit results for text responses
			else {
				$r_count = $fact->getResponses($r_p,true)->f(0);
				$r_p['limit'] = array(($r_page * $r_limit - $r_limit),$r_limit);

				$responses = $fact->getResponses($r_p);

				if ($r_count > $responses->count()) {
					$pager = new pager($r_page,$r_count,$r_limit);
					$pager->var_page = 'r_page';
					$pager->base_url = $url_base.'&amp;query_id='.$queries->query_id.'&amp;r_limit='.$r_limit.'&amp;r_page=%s';
				
					$echo .= $pager->getLinks();
				}
			}

			# Loop through responses for this query
			while($responses->fetch())
			{
				switch($query->query_type) {
				
					case 'checkbox':
					case 'radio':
					case 'combo':
					$rs[$responses->response_text] += 1;
					break;

					case 'field':
					case 'textarea':
					if ('' != $responses->response_text) {
						$rs[] =  
						'<tr class="line">'.
						'<td class="minimal nowrap">'.
						form::hidden(array('responses_list[]'),$responses->response_id).
						form::checkbox(array('responses_sel['.$responses->response_id.']'),1,$responses->response_selected).
						'</td>'.
						'<td class="maximal">'.html::escapeHTML(substr($responses->response_text,0,350)).'</td>'.
						'</tr>';
					}
					default:
					break;
				}
			}
			# There's something to show
			if (!empty($rs))
			{
				# for numeric (id) responses
				if (in_array($query->query_type,array('checkbox','radio','combo'))) {
					# Sort responses by number of votes
					$rs_sort = array();
					while($options->fetch())
					{
						$nb = isset($rs[$options->option_id]) ? $rs[$options->option_id] : 0;
						$percent = ceil($nb / $count * 100).'%';

						$rs_sort[] = array(
							'nb'=>$nb,
							'text'=>'<tr class="line"><td class="maximal">'.html::escapeHTML($options->option_text).'</td><td class="nowrap"> '.$percent.'</td><td class="nowrap">'.sprintf(__('%s votes'),$nb).'</td></tr>'
						);
					}
					$sorted = staticRecord::newFromArray($rs_sort);
					$sorted->sort('nb','desc');

					# Parse responses
					$echo .= '<table>';
					while($sorted->fetch())
					{
						$echo .= $sorted->text;
					}
					$echo .= '</table>';
				}
				# for text responses
				else {
					$echo .= 
					'<table>'.
					'<form method="post" action="plugin.php">'.
					implode('',$rs).
					'</table>'.
					'<p>'.
					'<input type="submit" name="save" value="'.__('Show selected responses on public side').'" />'.
					$GLOBALS['core']->formNonce().
					form::hidden(array('action'),'selectresponses').
					form::hidden(array('post_id'),$post_id).
					form::hidden(array('poll_id'),$poll_id).
					form::hidden(array('query_id'),$query_id).
					form::hidden(array('r_limit'),$r_limit).
					form::hidden(array('r_page'),$r_page).
					form::hidden(array('p'),'pollsFactory').
					form::hidden(array('tab'),'viewpoll').
					'</form>';
				}
			}
			else {
				$echo .= '<p>'.__('There is no response for this query.').'</p>';
			}
		}
	}
}
?>