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

class libPollsFactory
{
	# Quick check if a poll is closed
	public function isClosedPoll($fact,$poll_id)
	{
		$params = array();
		$params['poll_id'] = (integer) $poll_id;
		$params['sql'] = "AND poll_enddt < TIMESTAMP '".date('Y-m-d H:i:s')."' ";

		return (boolean) $fact->getPolls($params,true)->f(0);
	}

	# Quick close poll
	public static function closePoll($fact,$poll_id)
	{
		$cur = $fact->openCursor('poll');
		$cur->poll_enddt = date('Y-m-d H:i:s');
		$fact->updPoll($poll_id,$cur);
	}

	# Quick mark poll as (un)complete
	public function uncompletePoll($fact,$poll_id,$uncomplete=true)
	{
		$uncomplete = (boolean) $uncomplete;

		$cur = $fact->openCursor('poll');
		$cur->poll_status = $uncomplete ? -2 : 1;
		$fact->updPoll($poll_id,$cur);
	}

	# Delete poll and related queries, options, responses, users
	public static function deletePoll($fact,$poll_id)
	{
		# Get queries
		$queries_del = $fact->getQueries(array('poll_id'=>$poll_id));
		while($queries_del->fetch())
		{
			# Get options
			$options_del = $fact->getOptions(array('query_id'=>$queries_del->query_id));
			while ($options_del->fetch()) {
				# Delete option
				$fact->delOption($options_del->option_id);
			}
			# Delete reponses
			$fact->delResponses($queries_del->query_id);
			# Delete query
			$fact->delQuery($queries_del->query_id);
		}
		# Delete users
		$fact->delUsers($poll_id);
		# Delete poll
		$fact->delPoll($poll_id);
	}

	# Delete query and related options, responses, users
	public static function deleteQuery($fact,$query_id)
	{
		# Delete options
		$options_del = $fact->getOptions(array('query_id'=>$query_id));
		while ($options_del->fetch()) {
			$fact->delOption($options_del->option_id);
		}
		# Delete reponses
		$fact->delResponses($query_id);
		# delete query
		$fact->delQuery($query_id);
	}

	# Quick update query order
	public function positionQuery($fact,$query_id,$position=0)
	{
		$position = (integer) $position;

		$cur = $fact->openCursor('query');
		$cur->query_pos = $position;
		$fact->updQuery($query_id,$cur);
	}

	# Quick update option order
	public function positionOption($fact,$option_id,$position=0)
	{
		$position = (integer) $position;

		$cur = $fact->openCursor('option');
		$cur->option_pos = $position;
		$fact->updQuery($option_id,$cur);
	}

	# Quick mark response as (un)selected
	public function selectResponse($fact,$response_id,$selected=true)
	{
		$selected = (boolean) $selected;

		$cur = $fact->openCursor('response');
		$cur->response_selected = $selected ? 1 : 0;
		$fact->updResponse($response_id,$cur);
	}

	# Get list of urls where to show full poll
	public static function getPublicUrlTypes($core)
	{
		$types = array();
		$core->callBehavior('pollsFactoryPublicUrlTypes',$types);

		$types[__('home page')] = 'default';
		$types[__('post pages')] = 'post';
		$types[__('tags pages')] = 'tag';
		$types[__('archives pages')] = 'archive';
		$types[__('category pages')] = 'category';
		$types[__('entries feed')] = 'feed';

		return $types;
	}

	public static function getQueryTypes()
	{
		return array(
			__('multiple choice list') => 'checkbox',
			__('single choice list') => 'radio',
			__('options box') => 'combo',
			__('text field') => 'field',
			__('text area') => 'textarea'
		);
	}
}
?>