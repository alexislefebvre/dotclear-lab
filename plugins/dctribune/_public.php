<?php
# ***** BEGIN LICENSE BLOCK *****
#
# Tribune Libre is a small chat system for Dotclear 2
# Copyright (C) 2007  Antoine Libert
# 
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

class tplTribune
{
	public static function getTribune($nbshow, $sort, $deltime, $wrap)
	{
		global $core, $_ctx;
		require_once dirname(__FILE__).'/class.dc.tribune.php';
		$tribune = new dcTribune($GLOBALS['core']->blog);

		$_ctx->tribune = new ArrayObject(array(
			'name' => '',
			'message' => '',
		));
		
		$b_url = http::getSelfURI();
		$b_url .= strpos($b_url,'?') !== false ? '&' : '?';
		
		$str = '';

		# Spam trap
		if (!empty($_POST['f_message'])) {
			http::head(412,'Precondition Failed');
			header('Content-Type: text/plain');
			echo "So Long, and Thanks For All the Fish";
			exit;
		}
		
		if (isset($_GET['msg']))
		{
			if ($_GET['msg'] == 2) {
				$str .= '<p class="message">'.__('Message seems to be a spam.').'</p>';
			}
			if ($_GET['msg'] == 1) {
				$str .= '<p class="message">'.__('Message added.').'</p>';
			}
			if ($_GET['msg'] == 0) {
				$str .= '<p class="error">'.__('Your message cannot be added.').'</p>';
			}
		}
		
		if (isset($_GET['off']))
		{
			if ($_GET['off'] == 1) {
				$str .= '<p class="message">'.__('Your message has been deleted.').'</p>';
			}
			if ($_GET['off'] == 0) {
				$str .= '<p class="error">'.__('Your message cannot be deleted.').'</p>';
			}
		}
		
		$rs = $tribune->getMsg($nbshow,$sort);
		if ($core->blog->settings->use_smilies)
		{
			$GLOBALS['__smilies'] = context::getSmilies($core->blog);
		}
	
		//$now = time();
		$offset = dt::getTimeOffset($core->blog->settings->blog_timezone);
		$now = time() + $offset;
		$avant = $f = 0;
		
		if (!$rs->isEmpty())
		{
			while($rs->fetch())
			{
				# Smile ?
				$content = ($core->blog->settings->use_smilies) ? context::addSmilies($rs->f('tribune_msg')) : $rs->f('tribune_msg') ;
				
				$ts = strtotime($rs->f('tribune_dt'));
				
				if ($avant != date("d",$ts))
				{
					if (date("d",$ts) == date("d",$now)) { 
						$str .= '<span class="tribunedate">'.__('Today').'</span>'; 
					} else { 
						$str .= sprintf("<span class=\"tribunedate\">%s</span>",strftime("%d/%m/%y",$ts));
					}
				}
				
				$avant = date("d",$ts);
		
				# Ajout du lien de suppresion
				if (http::realIP() == $rs->f('tribune_ip') AND ($now - $ts) < $deltime) {
					$del_title = __('Delete this post');
					$del_link = sprintf("<a href=\"?tribdel=%d\"",$rs->f('tribune_id'),$del_title);
					$del_link = $del_link. 'class="msgoff " title="'.$del_title.'" rel="nofollow">[x]</a>';
				} else { $del_link = '';}
				
				# Alternance class paire et impaire
				$couleur = ($f % 2) ? 'tribune_odd' : 'tribune_even' ;
				$f++ ;
				
				$str .= sprintf("\n\t<p class=\"%s\"><strong title=\"%s\">%s</strong> <span>(%s)</span> %s %s</p>",
					$couleur,
					dt::rfc822($ts,$core->blog->settings->blog_timezone),
					date("H:i",$ts),
					$rs->f('tribune_nick'),
					$content,
					$del_link
					);
			}
		} else {
			$str .= '<p>'.__('No entry.').'</p>';
		}
		
		# Ajout du message
		if (!empty($_POST['tribnick']) AND !empty($_POST['tribmsg']))
		{
			$not_spam = true;
			# Check message form spam
			if (class_exists('dcAntispam') && isset($core->spamfilters))
			{
				# Fake cursor to check spam
				$cur = $core->con->openCursor('foobar');
				$cur->comment_trackback = 0;
				$cur->comment_author = $_ctx->tribune['name'];
				$cur->comment_email = '';
				$cur->comment_site = '';
				$cur->comment_ip = http::realIP();
				$cur->comment_content = $_ctx->tribune['message'];
				$cur->post_id = 0; // That could break things...
				$cur->comment_status = 1;
				
				@dcAntispam::isSpam($cur);
				
				if ($cur->comment_status == -2) {
					unset($cur);
					http::redirect($b_url.'msg=2');
					$not_spam = false;
				}
				unset($cur);
			}

			if ($not_spam)
			{
				$add = $tribune->addMsg($core->HTMLfilter($_POST['tribnick']),$tribune->cleanMsg($core->HTMLfilter($_POST['tribmsg']),$wrap),$now, http::realIP());
			
				if ($add) http::redirect($b_url.'msg=1'); else http::redirect($b_url.'msg=0');
			}
		}
		
		# Suppresion de message
		if (!isset($_GET['off']) && isset($_GET['tribdel']) AND is_numeric($_GET['tribdel']) AND empty($_POST['tribnick']) AND empty($_POST['tribmsg']))
		{
			$del = $tribune->changeState((integer) $_GET['tribdel'], 0, true, $now, $deltime, http::realIP());
		
			if ($del) http::redirect($b_url.'off=1'); else http::redirect($b_url.'off=0');
		}
		
		return $str;
	}
	
	# Widget function
	public static function tribunelibreWidget(&$w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		return
		'<div class="tribune">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		self::getTribune($w->nbshow, $w->sortasc, $w->deltime, $w->nbtronq).
		'</div>';
	}
	
	public static function tribunelibreFormWidget(&$w)
	{
		global $core;
  
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		if (!empty($_COOKIE['comment_info'])) {
			$c_cookie = unserialize($_COOKIE['comment_info']);
		}
		
		# Récupération du pseudo à partir du cookie, sinon du POST ou sinon affichage d'un message
		if (!empty($c_cookie['name']))
		{ 
			$nick_v = $c_cookie['name']; 
		} else if (!empty($_POST['tribnick']))
		{ 
			$nick_v = html::escapeHTML($_POST['tribnick']); 
		} else { 
			$nick_v = '';
		}

		$nick = __('Your nick');
		$message = __('Your message');
		
		# Retourne le formulaire
		$str = '<div class="tribuneform">'.
        ($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<form action="" method="post" id="tribunelibreform">'.
        '<fieldset>'.
		'<p class="field"><label for="tribnick">'.$nick.'&nbsp;:</label><input name="tribnick" id="tribnick" type="text" size="20" maxlength="50" value="'.$nick_v.'" /></p>'.
		'<p style="display:none"><input name="f_message" type="text" size="20" maxlength="255" value="" /></p>'.
		'<p class="field"><label for="tribmsg">'.$message.'&nbsp;:</label><textarea name="tribmsg" id="tribmsg" cols="20" rows="3"  </textarea></p>'.
		'<p class="buttons"><input type="submit" class ="submit" value="ok"/></p>'.
        '</fieldset>'.
		'</form>'.
		'</div>';
		
		return $str;
	}
}
?>