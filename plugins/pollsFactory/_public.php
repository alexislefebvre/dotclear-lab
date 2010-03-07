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

if (!defined('DC_RC_PATH')){return;}

require_once dirname(__FILE__).'/_widgets.php';

$core->addBehavior('publicEntryAfterContent',array('publicBehaviorPollsFactory','publicEntryAfterContent'));

class publicUrlPollsFactory extends dcUrlHandlers
{
	public static function page($args)
	{
		global $core;
		# Plugin not active
		if (!$core->blog->settings->pollsFactory_active) {
			self::p404();
			return;
		}
		# Submit vote for a poll
		if ($args == '/vote' && !empty($_POST))
		{
			if (empty($_POST['pollquery']) || !is_array($_POST['pollquery']) || empty($_POST['poll'])) {
				self::p404();
				return;
			}
			$poll_id = (integer) $_POST['poll'];
			$redir = !empty($_POST['redir']) ? $_POST['redir'] : $core->blog->url;

			try {
				$fact = new pollsFactory($core);
			}
			catch (Exception $e) {
				self::p404();
				return;
			}
			# Get poll
			try {
				$poll = $fact->getPolls(array('poll_id'=>$poll_id));
			}
			catch (Exception $e) {
				self::p404();
				return;
			}
			if ($poll->isEmpty()) {
				self::p404();
				return;
			}
			# Check if user already voted
			if ($fact->hasUser($poll_id)) {
				http::redirect($redir);
			}
			# Get queries
			try {
				$queries = $fact->getQueries(array('poll_id'=>$poll_id));
			}
			catch (Exception $e) {
				self::p404();
				return;
			}
			if ($queries->isEmpty()) {
				self::p404();
				return;
			}
			# Default values
			$add = false;
			$rsp = array();
			# Loop through queries
			while ($queries->fetch())
			{
				if (isset($_POST['pollquery'][$queries->query_id]))
				{
					$id = (integer) $queries->query_id;
					switch($queries->query_type) {

						case 'checkbox':
						if (is_array($_POST['pollquery'][$id])) {
							foreach($_POST['pollquery'][$id] as $k => $v) {
								$rsp[] = array($id,(integer) $v);
								$add = true;
							}
						}
						break;

						case 'radio':
						case 'combo':
						$rsp[] = array($id,(integer) $_POST['pollquery'][$id]);
						$add = true;
						break;

						case 'field':
						case 'textarea':
						$rsp[] = array($id,(string) $_POST['pollquery'][$id]);
						$add = true;
						break;

						default:
						break;
					}
				}
			}
			# Add reponse in database
			if ($add && !empty($rsp)) {
				try {
					$user_id = $fact->setUser($poll_id);
					$cur = $fact->openCursor('response');
					foreach($rsp as $k => $v) {
						$cur->clean();
						$cur->query_id = $v[0];
						$cur->user_id = $user_id;
						$cur->response_text = $core->con->escape($v[1]);
						$fact->addResponse($cur);
					}

					# --BEHAVIOR-- publicAfterAddResponse
					$core->callBehavior('publicAfterAddResponse',$poll,$user_id);
				}
				catch (Exception $e) {}
			}
			# Redirect after vote
			http::redirect($redir);
		}
	}
	public static function chart($args)
	{
		$ids = explode('/',$args);
		if (empty($ids[0]) || empty($ids[1])) {
			self::p404();
			return;
		}
	
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];

		if (!$_ctx->exists('pollsFactoryChart')) {
			$_ctx->pollsFactoryChart = new pollsFactoryChart($core);
		}
		echo $_ctx->pollsFactoryChart->serveChart($ids[0],$ids[1]);
	}
}

class publicBehaviorPollsFactory
{
	public static function publicEntryAfterContent($core,$_ctx)
	{
		# Plugin not active or not on post
		if (!$core->blog->settings->pollsFactory_active 
		 || !$_ctx->exists('posts') 
		 || !$_ctx->posts->post_type == 'post')
		{
			return;
		}
		# Not show poll on some pages
		$types = @unserialize($core->blog->settings->pollsFactory_public_tpltypes);
		if (!is_array($types) || !in_array($core->url->type,$types))
		{
			return;
		}
		# Use common form for tpl and widget
		echo publicPollsFactoryForm($core,$_ctx->posts->post_id,__('Poll'),false,$core->blog->settings->pollsFactory_public_graph);
	}
}

function publicPollsFactoryForm($core,$post_id,$title='',$show_post_title=false,$use_graph=false)
{
	# Start of all: a post id
	$post_id = (integer) $post_id;
	# pollsFactor object
	$fact = new pollsFactory($core);
	# Poll
	$p_params = array(
		'post_id' => $post_id,
		'sql' => "AND poll_strdt < TIMESTAMP '".date('Y-m-d H:i:s')."' "
	);
	$poll = $fact->getPolls($p_params);
	# No poll on this post
	if ($poll->isEmpty())
	{
		return;
	}
	# Check if poll is finished
	$finished = strtotime($poll->poll_enddt) < time();
	# If user has not already voted and poll is not finished
	if (!$fact->hasUser($poll->poll_id) && !$finished)
	{
		$res = 
		'<div class="pollsfactory poll-form">'.
		(empty($title) ? '' : '<h2>'.$title.'</h2>').
		($show_post_title ? '<p>'.html::escapeHTML($poll->post_title).'</>' : '').
		'<form id="poll'.$poll->poll_id.'" method="post" action="'.$core->blog->url.$core->url->getBase('pollsFactoryPage').'/vote">';

		# Poll queries
		$queries = $fact->getQueries(array('poll_id'=>$poll->poll_id));
		# No query for this poll
		if ($queries->isEmpty())
		{
			return;
		}
		# Loop throught queries of this poll
		while ($queries->fetch())
		{
			# Query options
			$options = $fact->getOptions(array('query_id'=>$queries->query_id));
			# No option for this query
			if ($options->isEmpty())
			{
				return;
			}
			# Default values
			$options_res = $options_combo = '';
			$option_name = 'pollquery['.$queries->query_id.']';
			$option_selected = 1;
			# Loop through options of this query
			while ($options->fetch())
			{
				$option_id = 'pollquery['.$queries->query_id.']['.$options->option_id.']';
				# Parse option by type of query
				switch($queries->query_type) {
				
					case 'checkbox':
					$options_res .= 
					'<p><label for="'.$option_id.'">'.
					form::checkbox($option_id,$options->option_id,0,'poll-checkbox').' '.
					html::escapeHTML($options->option_text).'</label></p>';
					break;

					case 'radio':
					$options_res .= 
					'<p><label for="'.$option_id.'">'.
					form::radio(array($option_name,$option_id),$options->option_id,$option_selected,'poll-combo').' '.
					html::escapeHTML($options->option_text).'</label></p>';
					$option_selected = 0;
					break;

					case 'combo':
					if ($option_selected) {
						$option_combo_selected = $options->option_id;
						$option_selected = 0;
					}
					$options_combo_nid = array($option_name,$option_id);
					$options_combo[$options->option_text] = $options->option_id;
					break;

					case 'field':
					$options_res .= 
					'<p>'.form::field(array($option_name,$option_id),65,255,'','poll-field').'</p>';
					break;

					case 'textarea':
					$options_res .= 
					'<p>'.form::textArea(array($option_name,$option_id),65,5,'','poll-textarea').'</p>';
					break;
				
					default:
					break;
				}
			}
			# Parse query options
			if (!empty($options_res) && $queries->query_type != 'combo' || !empty($options_combo) && $queries->query_type == 'combo')
			{
				$res .= '<div class="poll-query">';

				# Query title
				$res .= '<h3>'.html::escapeHTML($queries->query_title).'</h3>';
				# Query description
				if (!empty($queries->query_desc))
				{
					$res .= '<div class="poll-query-desc">'.$queries->query_desc.'</div>';
				}
				# Options (and special content for combo)
				$res .= $queries->query_type == 'combo' ?
					'<p>'.form::combo($options_combo_nid,$options_combo,$option_combo_selected,'poll-combo').'</p>' :
					$options_res;

				$res .= '</div>';
			}
		}
		# Form
		return 
		$res. 
		'<div class="poll-submit"><p>'.
		'<input type="hidden" name="poll" value="'.$poll->poll_id.'" />'.
		'<input type="hidden" name="redir" value="'.http::getSelfURI().'" />'.
		'<input type="submit" name="submit" value="'.__('Validate').'" />'.
		'</p></div>'.
		'</form></div>';
	}
	# If user has voted and settings say to show reponses or poll is finished
	elseif ($core->blog->settings->pollsFactory_public_show || $finished)
	{
		# Count responses
		$count = $fact->countUsers($poll->poll_id);
		# Poll queries
		$queries = $fact->getQueries(array('poll_id'=>$poll->poll_id));
		# No query for this poll
		if ($queries->isEmpty())
		{
			return;
		}
		$res = '';
		# Loop through queries
		while ($queries->fetch())
		{
			# If display graphics is on, switch to image for integer responses
			if ($use_graph && in_array($queries->query_type,array('checkbox','radio','combo'))) {
				$res .= '<p class="poll-chart"><img src="'.
				$core->blog->url.$core->url->getBase('pollsFactoryChart').'/'.
				$poll->poll_id.'/'.$queries->query_id.'" alt="'.html::escapeHTML($queries->query_title).'" /></p>';
			}
			# Else use html
			else {
				$rs = array();
				$id = $queries->query_id;
				$options = $fact->getOptions(array('query_id'=>$id));
				$responses = $fact->getResponses(array('query_id'=>$id));

				# Loop through responses for this query
				while($responses->fetch())
				{
					switch($queries->query_type) {
					
						case 'checkbox':
						case 'radio':
						case 'combo':
						$rs[$responses->response_text] += 1;
						break;

						case 'field':
						case 'textarea':
						if ($responses->response_selected) {
							$rs[] = $responses->response_text;
						}
						default:
						break;
					}
				}
				# There's something to show
				if (!empty($rs))
				{
					# For integer responses
					if (in_array($queries->query_type,array('checkbox','radio','combo'))) {
						# Sort responses by number of votes
						$rs_sort = array();
						while($options->fetch())
						{
							$nb = isset($rs[$options->option_id]) ? $rs[$options->option_id] : 0;
							$percent = ceil($nb / $count * 100).'%';

							$rs_sort[] = array(
								'nb'=>$nb,
								'text'=>'<li><strong>'.$options->option_text.'</strong> '.$percent.' <em>('.sprintf(__('%s votes'),$nb).')</em></li>'
							);
						}
						$sorted = staticRecord::newFromArray($rs_sort);
						$sorted->sort('nb','desc');

						# Parse responses
						$res .= '<h3>'.html::escapeHTML($queries->query_title).'</h3>';
						if ('' != $queries->query_desc) {
//							$res .= '<p class="poll-query-desc">'.html::escapeHTML($queries->query_desc).'<p>';
						}
						$res .= '<ul>';
						while($sorted->fetch())
						{
							$res .= $sorted->text;
						}
						$res .= '</ul>';
					}
					# For text responses
					else {
						$res .= '<h3>'.html::escapeHTML($queries->query_title).'</h3>';
						if ('' != $queries->query_desc) {
//							$res .= '<p class="poll-query-desc">'.html::escapeHTML($queries->query_desc).'<p>';
						}
						$res .= '<p><em>'.__('Some selected responses:').'</em><br />';
						foreach($rs as $k => $v) {
							$res .= '<blockquote>'.html::escapeHTML($v).'</blockquote>';
						}
						$res .= '</p>';
					}
				}
			}
		}
		if (!empty($res))
		{
			$res =    
			'<div class="pollsfactory poll-result"><h2>'.$title.'</h2>'.
			($show_post_title ? '<p>'.html::escapeHTML($poll->post_title).'</p>' : '').
			'<p>'.
			(!$finished ?
				sprintf(__('This poll ends on %s.'),dt::dt2str($core->blog->settings->date_format.', '.$core->blog->settings->time_format,$poll->poll_enddt,$core->blog->timezone)).'<br />' :
				__('This poll is closed').'<br />'
			).
			sprintf(__('%s people participated.'),$count).
			'</p>'.
			$res.
			'</div>';
		}
		return $res;
	}
	# If user has voted and settings say not to show responses
	else
	{
		return   
		'<div class="pollsfactory poll-wait"><h2>'.$title.'</h2>'.
		($show_post_title ? '<p>'.html::escapeHTML($poll->post_title).'</>' : '').
		'<p>'.
		__('You have already participated to this poll.').'<br />'.
		__('Please wait the end of this poll to see results.').'<br />'.
		sprintf(__('This poll ends on %s.'),dt::dt2str($core->blog->settings->date_format.', '.$core->blog->settings->time_format,$poll->poll_enddt,$core->blog->timezone)).
		'</p>'.
		'</div>';
	}
}
?>