<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('widgetKutrl','adminShorten'));
$core->addBehavior('initWidgets',array('widgetKutrl','adminRank'));

class widgetKutrl
{
	public static function adminShorten($w)
	{
		$w->create('shortenkutrl',__('Links shortener'),
			array('widgetKutrl','publicShorten')
		);
		$w->shortenkutrl->setting('title',
			__('Title:'),__('Shorten link'),'text'
		);
		$w->shortenkutrl->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}

	public static function adminRank($w)
	{
		$w->create('rankkutrl',__('Top mini links'),
			array('widgetKutrl','publicRank')
		);
		$w->rankkutrl->setting('title',
			__('Title:'),__('Top mini links'),'text'
		);
		$w->rankkutrl->setting('text',
			__('Text:'),'%rank% - %url% - %counttext%','text'
		);
		$w->rankkutrl->setting('urllen',
			__('Link length (if truncate)'),20
		);
		$w->rankkutrl->setting('type',
			__('Type:'),'all','combo',array(
				__('All') => '-',
				__('Mini URL') => 'normal',
				__('Custom URL')=>'custom'
			)
		);
		$w->rankkutrl->setting('sort',
			__('Sort:'),'desc','combo',array(
				__('Ascending') => 'asc',
				__('Descending') => 'desc'
			)
		);
		$w->rankkutrl->setting('limit',
			__('Limit:'),'10','text'
		);
		$w->rankkutrl->setting('hideempty',
			__('Hide no followed links'),0,'check'
		);
		$w->rankkutrl->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}

	public static function publicShorten($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') return;

		if (!$core->blog->settings->kutrl_active 
		 || !$core->blog->settings->kutrl_srv_local_public) return;

		$hmf = hmfKutrl::create();
		$hmfp = hmfKutrl::protect($hmf);

		return 
		'<div class="shortenkutrlwidget">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<form name="shortenkutrlwidget" method="post" action="'.
		 $core->blog->url.$core->url->getBase('kutrl').'">'.
		'<p><label>'.
		 __('Long link:').'<br />'.
		 form::field('longurl',20,255,'').
		'</label></p>'.
		'<p><label>'.
		 sprintf(__('Write "%s" in next field to see if you are not a robot:'),$hmf).'<br />'.
		 form::field('hmf',20,255,'').
		'</label></p>'.
		'<p><input class="submit" type="submit" name="submiturl" value="'.__('Create').'" />'.
		form::hidden('hmfp',$hmfp).
		$core->formNonce().
		'</p>'.
		'</form>'.
		'</div>';
	}

	public static function publicRank($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') return;

		if (!$core->blog->settings->kutrl_active) return;

		if ($w->type == 'normal')
		{
			$type = "AND kut_type ='".$core->con->escape('localnormal')."' ";
		}
		elseif ($w->type == 'custom')
		{
			$type = "AND kut_type ='".$core->con->escape('localcustom')."' ";
		}
		else
		{
			$type = "AND kut_type ".$core->con->in(array('localnormal','localcustom'))." ";
		}

		$hide = (boolean) $w->hideempty ? 'AND kut_counter > 0 ' : '';

		$rs = $core->con->select(
		'SELECT kut_counter, kut_hash '.
		"FROM ".$core->prefix."kutrl ".
		"WHERE blog_id='".$core->con->escape($core->blog->id)."' ".
		"AND kut_service = 'local' ".
		$type.$hide.
		'ORDER BY kut_counter '.($w->sort == 'asc' ? 'ASC' : 'DESC').',kut_hash ASC '.
		$core->con->limit(abs((integer) $w->limit)));

		if ($rs->isEmpty()) return;

		$content = '';
		$i = 0;

		while($rs->fetch())
		{
			$i++;
			$rank = '<span class="rankkutrl-rank">'.$i.'</span>';

			$hash = $rs->kut_hash;
			$url = $core->blog->url.$core->url->getBase('kutrl').'/'.$hash;
			$cut_len = - abs((integer) $w->urllen);

			if (strlen($url) > $cut_len)
				$url = '...'.substr($url,$cut_len);
/*
			if (strlen($hash) > $cut_len)
				$url = '...'.substr($hash,$cut_len);
*/
			if ($rs->kut_counter == 0)
				$counttext = __('never followed');
			elseif ($rs->kut_counter == 1)
				$counttext = __('followed one time');
			else
				$counttext = sprintf(__('followed %s times'),$rs->kut_counter);

			$content .= 
				'<li><a href="'.
				$core->blog->url.$core->url->getBase('kutrl').'/'.$rs->kut_hash.
				'">'.
				str_replace(
					array('%rank%','%hash%','%url%','%count%','%counttext%'),
					array($rank,$hash,$url,$rs->kut_counter,$counttext),
					$w->text
				).
				'</a></li>';

		}

		if (!$content) return;

		return 
		'<div class="rankkutrlwidget">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$content.'</ul>'.
		'</div>';
	}
}
?>