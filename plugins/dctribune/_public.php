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
		global $core;
		require_once dirname(__FILE__).'/class.dc.tribune.php';
		$tribune = new dcTribune($GLOBALS['core']->blog);
		
		$b_url = $_SERVER['REQUEST_URI'];
		$b_url .= strpos($b_url,'?') !== false ? '&' : '?';
		
		$str = '';
		
		if (isset($_GET['msg']))
		{
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
				
				$str .= sprintf("\n\t<p class=\"%s\"><strong title=\"%s\">%s</strong> (%s) %s %s</p>",
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
		if (!empty($_POST['tribnick']) AND !empty($_POST['tribmsg']) AND $_POST['tribnick'] != __('Your nick') AND $_POST['tribmsg'] != __('Your message'))
		{
			$add = $tribune->addMsg($core->HTMLfilter($_POST['tribnick']),$tribune->cleanMsg($core->HTMLfilter($_POST['tribmsg']),$wrap),$now, http::realIP());
		
			if ($add) http::redirect($b_url.'msg=1'); else http::redirect($b_url.'msg=0');
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
			$nick = $c_cookie['name']; 
			$message = '';
		} else if (!empty($_POST['tribnick']))
		{ 
			$nick = htmlentities($_POST['tribnick']); 
			$message = '';
		} else { 
			$nick = __('Your nick');
			$message = __('Your message');
		}
		
		# Retourne le formulaire
		$str = '<form action="http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].'" method="post" id="tribunelibreformulaire">'.
		'<p class="field"><input name="tribnick" type="text" size="20" maxlength="50" value="'.$nick.'" onmousedown="this.value=\'\'" /><br />'.
		'<input name="tribmsg" type="text" size="20" maxlength="200" value="'.$message.'" /></p>'.
		'<p><input type="submit" class ="submit" value="Ok"/></p>'.
		'</form>';
		
		return $str;
	}
}
?>
