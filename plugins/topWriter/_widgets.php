<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of topWriter, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('topWriterWidget','init'));

class topWriterWidget
{
	public static function init($w)
	{
		$w->create('topcom',__('Top comments'),
			array('topWriterWidget','topCom'));
		$w->topcom->setting('title',__('Title:'),
			__('Top comments'),'text');
		$w->topcom->setting('text',__('Text:'),
			'%author% (%count%)','text');
		$w->topcom->setting('period',__('Period:'),'year','combo',
			array(__('day')=>'day',__('week')=>'week',__('month')=>'month',
				__('year')=>'year',__('from begining')=>''));
		$w->topcom->setting('sort',__('Sort:'),'desc','combo',array(
			__('Ascending') => 'asc',__('Descending') => 'desc'));
		$w->topcom->setting('limit',__('Limit:'),'10','text');
		$w->topcom->setting('exclude',__('Exclude post writer from list'),0,'check');
		$w->topcom->setting('homeonly',__('Home page only'),1,'check');

		$w->create('toppost',__('Top entries'),
			array('topWriterWidget','topPost'));
		$w->toppost->setting('title',__('Title:'),
			__('Top entries'),'text');
		$w->toppost->setting('text',__('Text:'),
			'%author% (%count%)','text');
		$w->toppost->setting('period',__('Period:'),'year','combo',
			array(__('day')=>'day',__('week')=>'week',__('month')=>'month',
				__('year')=>'year',__('from begining')=>''));
		$w->toppost->setting('sort',__('Sort:'),'desc','combo',array(
			__('Ascending') => 'asc',__('Descending') => 'desc'));
		$w->toppost->setting('limit',__('Limit:'),'10','text');
		$w->toppost->setting('homeonly',__('Home page only'),1,'check');
	}

	public static function topCom($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') return;

		$req =
		'SELECT COUNT(*) AS count, comment_email '.
		"FROM ".$core->prefix."post P,  ".$core->prefix."comment C ".
		'WHERE P.post_id=C.post_id '.
		"AND blog_id='".$core->con->escape($core->blog->id)."' ".
		'AND post_status=1 AND comment_status=1 '.
		self::period('comment_dt',$w->period);

		if ($w->exclude) {
			$req .= 
			'AND comment_email NOT IN ('.
			' SELECT U.user_email '.
			' FROM '.$core->prefix.'user U'.
			' INNER JOIN '.$core->prefix.'post P ON P.user_id = U.user_id '.
			" WHERE blog_id='".$core->con->escape($core->blog->id)."' ".
			' GROUP BY U.user_email) ';
		}

		$req .=
		'GROUP BY comment_email '.
		'ORDER BY count '.($w->sort == 'asc' ? 'ASC' : 'DESC').' '.
		$core->con->limit(abs((integer) $w->limit));
		
		$rs = $core->con->select($req);

		if ($rs->isEmpty()) return;

		$content = '';
		$i = 0;
		while($rs->fetch()){
			$user = $core->con->select(
				"SELECT * FROM ".$core->prefix."comment WHERE comment_email='".$rs->comment_email."' "
			);

			if (!$user->comment_author) continue;

			$i++;
			$rank = '<span class="topcomments-rank">'.$i.'</span>';

			if ($user->comment_site) {
				$author = '<a href="'.$user->comment_site.'" title="'.
					__('Author link').'">'.$user->comment_author.'</a>';
			}
			else
				$author = $user->comment_author;

			if ($rs->count == 0)
				$count = __('no comment');

			elseif ($rs->count == 1)
				$count = __('one comment');

			else
				$count = sprintf(__('%s comments'),$rs->count);

			$content .= '<li>'.str_replace(
				array('%rank%','%author%','%count%'),
				array($rank,$author,$count),
				$w->text
			).'</li>';
		}

		if ($i < 1) return;

		return 
		'<div class="topcomments">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$content.'</ul>'.
		'</div>';
	}

	public static function topPost($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') return;

		$rs = $core->con->select(
		'SELECT COUNT(*) AS count, U.user_id '.
		"FROM ".$core->prefix."post P ".
		'INNER JOIN '.$core->prefix.'user U ON U.user_id = P.user_id '.
		"WHERE blog_id='".$core->con->escape($core->blog->id)."' ".
		'AND post_status=1 AND user_status=1 '.
		self::period('post_dt',$w->period).
		'GROUP BY U.user_id '.
		'ORDER BY count '.($w->sort == 'asc' ? 'ASC' : 'DESC').', U.user_id ASC '.
		$core->con->limit(abs((integer) $w->limit)));

		if ($rs->isEmpty()) return;

		$content = '';
		$i = 0;
		while($rs->fetch()){
			$user = $core->con->select(
				"SELECT * FROM ".$core->prefix."user WHERE user_id='".$rs->user_id."' "
			);

			$author = dcUtils::getUserCN($user->user_id,$user->user_name,
				$user->user_firstname,$user->user_displayname);

			if (empty($author)) continue;

			$i++;
			$rank = '<span class="topentries-rank">'.$i.'</span>';

			if ($core->blog->settings->authormode_active) {
				$author = '<a href="'.
					$core->blog->url.$core->url->getBase("author").'/'.$user->user_id.'" '.
					'title="'.__('Author posts').'">'.$author.'</a>';
			}
			elseif ($user->user_url) {
				$author = '<a href="'.$user->user_url.'" title="'.
					__('Author link').'">'.$author.'</a>';
			}

			if ($rs->count == 0)
				$count = __('no post');

			elseif ($rs->count == 1)
				$count = __('one post');

			else
				$count = sprintf(__('%s posts'),$rs->count);

			$content .= '<li>'.str_replace(
				array('%rank%','%author%','%count%'),
				array($rank,$author,$count),
				$w->text
			).'</li>';
		}

		if ($i < 1) return;

		return 
		'<div class="topentries">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$content.'</ul>'.
		'</div>';
	}

	private static function period($t,$p)
	{
		$pat = '%Y-%m-%d %H:%M:%S';
		switch($p) {
			case 'day': return
			"AND $t > TIMESTAMP '".dt::str($pat,time() - 3600*24)."' "; break;
			case 'week': return 
			"AND $t > TIMESTAMP '".dt::str($pat,time() - 3600*24*7)."' "; break;
			case 'month': return
			"AND $t > TIMESTAMP '".dt::str($pat,time() - 3600*24*30)."' "; break;
			case 'year': return
			"AND $t > TIMESTAMP '".dt::str($pat,time() - 3600*24*30*12)."' "; break;
		}
		return '';
	}
}
?>