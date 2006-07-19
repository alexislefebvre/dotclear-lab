<?php
# ***** BEGIN LICENSE BLOCK *****
# This is spamplemousse, a plugin for DotClear. 
# Copyright (c) 2005 Benoit CLERC, Alain Vagner and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

require dirname(__FILE__).'/class.bayesian_filter.php';

$core->addBehavior('publicBeforeCommentCreate',array('spamFilterBehaviors','markSpam'));
$core->addBehavior('publicBeforeTrackbackCreate',array('spamFilterBehaviors','markSpam'));
$core->addBehavior('coreBeforeCommentUpdate',array('spamFilterBehaviors','retrain'));

class spamFilterBehaviors
{
	public static function markSpam(&$cur)
	{
		$spamFilter = new bayesian_filter($GLOBALS['core']);

		$spam = $spamFilter->handle_new_message($cur);
		if ($spam == 1) {
				$cur->comment_status = -2;
		}
	}
	
	public static function retrain(&$blog, &$cur, $id)
	{
		$spamFilter = new bayesian_filter($GLOBALS['core']);
		$strReq = 'SELECT * FROM '.$blog->prefix.'comment '.
					'WHERE comment_id = '.$id;
		$rs = $this->con->select($strReq);
		if ($rs->comment_status	!= $cur->comment_status) { # the status has been modified
			if ($cur->comment_status == -2) { # the current action marks the comment as spam
				$spamFilter->retrain($cur, 1);
			} else {
				$spamFilter->retrain($cur, 0);
			}
		}
	}	
}

?>