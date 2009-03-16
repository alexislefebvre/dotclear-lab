<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Souvenir.
# Copyright 2008 Moe (http://gniark.net/)
#
# Souvenir is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Souvenir is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('initWidgets',array('souvenirBehaviors','initWidgets'));
 
class souvenirBehaviors
{
	public static function initWidgets(&$w)
	{
		global $core;

		$w->create('souvenir',__('Souvenir'),array('publicSouvenir','show'));

		$w->souvenir->setting('title',__('Title:').' ('.__('optional').')',
			__('Previously on this blog'),'text');

		$array_intervals = array();
		$array_intervals[__('1 month ago')] = 1;
		for ($i = 2;$i <= 11;$i++)
		{
			$array_intervals[$i.' '.__('months ago')] = $i;
		}
		$array_intervals[__('1 year ago')] = 12;
		
		$w->souvenir->setting('interval',__('Show a link to a post published:'),
			12,'combo',$array_intervals);

		$array_range = array();
		for ($i = 0;$i <= 31;$i++)
		{
			$array_range[$i] = $i;
		}
		$w->souvenir->setting('range',
			__('Maximum number of days before of after the date in the past:'),7,
			'combo',$array_range);

		$w->souvenir->setting('truncate',
			__('Number of characters of the post title to display (empty means no limit):'),
			null,'text');

		$w->souvenir->setting('date',
			__('Display date after post title (see PHP strftime function):').
			' ('.__('optional').')','('.$core->blog->settings->date_format.')','text');

		$w->souvenir->setting('home',__('Display on Home page'),true,'check');
	}
}

class publicSouvenir
{
	public static function show(&$w)
	{		
		global $core, $_ctx;

		if ($w->home && $core->url->type == 'default')
		{
			$dt = time();
			$post_id = 0;
		}
		elseif ($core->url->type == 'post')
		{
			$dt = strtotime($_ctx->posts->post_dt);
			# ignore the current post in post context
			$post_id = $_ctx->posts->post_id;
		}
		else
		{
			return;
		}
		
		# get the date of the "old time" : T
		$dt = strtotime('-'.$w->interval.' month',$dt);
		
		# timestamp before the timestamp : T-D (D = number of days)
		$before = dt::str('%Y-%m-%d %H:%m:%S',
			strtotime('-'.$w->range.' day',$dt),$w->timezone);
		# timestamp after the timestamp : T+D
		$after = dt::str('%Y-%m-%d %H:%m:%S',
			strtotime('+'.$w->range.' day',$dt),$w->timezone);

		if ($core->con->driver() == 'pgsql')
		{
			# pgsql
			$query =	
			'SELECT post_title, post_url, post_dt
			FROM '.$core->prefix.'post
			WHERE (
				(post_dt >= \''.$before.'\') 
				AND (post_dt <= \''.$after.'\')
				AND (post_status = \'1\')
				AND (blog_id = \''.$core->con->escape($core->blog->id).'\')
				AND (post_id != \''.$core->con->escape($post_id).'\')
				AND (post_type = \'post\')
			)
			LIMIT 1;';
		} else {
			# mysql
			# format the datetime
			$dt = dt::str('%Y-%m-%d %H:%m:%S',$dt,$w->timezone);
			$query =
			'SELECT post_title, post_url, post_dt, '.
			# calculate the difference between the posts and T
			'ABS(TIMESTAMPDIFF(SECOND,post_dt,\''.$dt.'\')) AS DIFF '.
			'FROM '.$core->prefix.'post '.
			'WHERE (
				(post_dt >= \''.$before.'\') 
				AND (post_dt <= \''.$after.'\')
				AND (post_status = \'1\')
				AND (blog_id = \''.$core->con->escape($core->blog->id).'\')
				AND (post_id != \''.$core->con->escape($post_id).'\')
				AND (post_type = \'post\')
			) '.
			# select the closest difference : the closest post to T
			'ORDER BY DIFF ASC LIMIT 1;';
		}
		
		unset($dt);
		
		$rs = $core->con->select($query);
		
		if (!$rs->isEmpty())
		{
			$post_title = ((strlen($w->truncate) > 0) && is_int($w->truncate))
				? text::cutString($rs->f('post_title'),$w->truncate)
				: $rs->f('post_title');
			
			$header = (strlen($w->title) > 0)
				? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;
			
			$date = (strlen($w->date) > 0)
				? ' '.dt::dt2str($w->date,$rs->f('post_dt'),
					$core->blog->settings->blog_timezone) : null;
			
			return '<div id="postBefore">'.
				$header.
				'<p><a href="'.
				$core->blog->url.$core->url->getBase('post').'/'.
				html::sanitizeURL($rs->f('post_url')).'" title="'.
				html::escapeHTML($rs->f('post_title')).'">'.$post_title.$date.'</a>'.
				'</p>'.
				'</div>';
		}
	}
}

?>