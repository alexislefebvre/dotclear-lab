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
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://txfx.net/code/wordpress/subscribe-to-comments/
#
# ***** END LICENSE BLOCK *****

class publicSouvenir
{
	public static function show(&$w)
	{
		global $core, $_ctx;

		if ($w->home && $core->url->type == 'default')
		{
			$origin = time();
		}
		elseif ($core->url->type == 'post')
		{
			$origin = strtotime($_ctx->posts->post_dt);
		}
		else
		{
			return;
		}

		$dt = strtotime('-'.$w->interval.' month',$origin);
		$before = dt::str('%Y-%m-%d %H:%m:%S',
			strtotime('-'.$w->range.' day',$dt),$w->timezone);
		$after = dt::str('%Y-%m-%d %H:%m:%S',
			strtotime('+'.$w->range.' day',$dt),$w->timezone);

		$query = ($core->con->driver() == 'pgsql')
		?
			# pgsql
			/*'SELECT post_title, post_url, post_dt,
			ABS(TIMESTAMPDIFF(SECOND,\''.$dt.'\', SUBDATE(\''.$dt.'\', INTERVAL '.$w->interval.' MONTH))) AS DIFF */
			'SELECT post_title, post_url, post_dt
			FROM '.$core->prefix.'post
			WHERE (
				(post_dt >= \''.$before.'\') 
				AND (post_dt <= \''.$after.'\')
				AND (post_status = \'1\')
				AND (blog_id = \''.$core->con->escape($core->blog->id).'\')
			)
			LIMIT 1;'
		:
			# mysql
			'SELECT post_title, post_url, post_dt,
			ABS(TIMESTAMPDIFF(SECOND,\''.$dt.'\', SUBDATE(\''.$dt.'\', INTERVAL '.$w->interval.' MONTH))) AS DIFF 
			FROM '.$core->prefix.'post
			WHERE (
				(post_dt >= \''.$before.'\') 
				AND (post_dt <= \''.$after.'\')
				AND (post_status = \'1\')
				AND (blog_id = \''.$core->con->escape($core->blog->id).'\')
			)
			ORDER BY DIFF ASC LIMIT 1;'
		;

		$rs = $core->con->select($query);

		$post_title = (strlen($w->truncate) > 0) ? text::cutString($rs->f('post_title'),$w->truncate) : $rs->f('post_title');

		$header = (strlen($w->title) > 0) ? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;

		$date = (strlen($w->date) > 0) ? ' '.dt::dt2str($w->date,$rs->f('post_dt')) : null;

		if ($rs->count() > 0)
		{
			return '<div id="postBefore">'.$header.'<p class="text"><a href="'.$core->blog->url.$core->url->getBase('post').'/'.html::sanitizeURL($rs->f('post_url')).'" title="'.$rs->f('post_title').'">'.$post_title.$date.'</a></p></div>';
		}
	}
}
?>