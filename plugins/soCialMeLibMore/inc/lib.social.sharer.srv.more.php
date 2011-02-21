<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMeLibMore, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}
# More services
// some services that not have full soCialMe librairies yet
// temporaly include in "more" soCialMe plugin

# bebo
class beboMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'bebo',
		'name' => 'Bebo',
		'home' => 'http://www.bebo.com',
		'icon' => 'pf=soCialMeLibMore/inc/icons/bebo.png'
	);
	
	protected $actions = array(
		'playIconContent' => true
	);
	
	public function playIconContent($post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://www.bebo.com/c/share?Url='.urlencode($url).'&amp;Title='.urlencode($title)
		);
		return $record;
	}
}
# blinklist
class blinklistMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'blinklist',
		'name' => 'Blinklist',
		'home' => 'http://blinklist.com',
		'icon' => 'pf=soCialMeLibMore/inc/icons/blinklist.png'
	);
	
	protected $actions = array(
		'playIconContent' => true
	);
	
	public function playIconContent($post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://blinklist.com/blink?t='.urlencode($title).'&amp;u='.urlencode($url)
		);
		return $record;
	}
}
# blogmarks
class blogmarksMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'blogmarks',
		'name' => 'BlogMarks',
		'home' => 'http://blogmarks.net',
		'icon' => 'pf=soCialMeLibMore/inc/icons/blogmarks.gif'
	);
	
	protected $actions = array(
		'playIconContent' => true
	);
	
	public function playIconContent($post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://blogmarks.net/my/new.php?mini=1&amp;simple=1&amp;url='.urlencode($url).'&amp;title='.urlencode($title)
		);
		return $record;
	}
}
# bloglines
class bloglinesMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'bloglines',
		'name' => 'Bloglines',
		'home' => 'http://www.bloglines.com',
		'icon' => 'pf=soCialMeLibMore/inc/icons/bloglines.png'
	);
	
	protected $actions = array(
		'playIconContent' => true
	);
	
	public function playIconContent($post)
	{
		if (!$post) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://www.bloglines.com/sub/'.str_replace(array('http://','https://'),'',$url)
		);
		return $record;
	}
}
# delicious
class deliciousMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'delicious',
		'name' => 'Delicious',
		'home' => 'http://delicious.com',
		'icon' => 'pf=soCialMeLibMore/inc/icons/delicious.png'
	);
	protected $actions = array(
		'playIconContent' => true
	);
	
	public function playIconContent($post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://delicious.com/save?url='.urlencode($url).'&amp;title='.urlencode($title)
		);
		return $record;
	}
}
# digg
class diggMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'digg',
		'name' => 'Digg',
		'home' => 'http://digg.com',
		'icon' => 'pf=soCialMeLibMore/inc/icons/digg.png'
	);
	
	protected $actions = array(
		'playPublicScript' => true,
		'playIconContent' => true,
		'playSmallContent' => true,
		'playBigContent' => true
	);
	
	public function playPublicScript($available)
	{
		if (isset($available['Icon']) && in_array($this->id,$available['Icon']) 
		 || isset($available['Small']) && in_array($this->id,$available['Small'])
		 || isset($available['Big']) && in_array($this->id,$available['Big']))
		{
			return 
			"<script type=\"text/javascript\">\n".
			"(function() {\n".
			"var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];\n".
			"s.type = 'text/javascript';\n".
			"s.async = true;\n".
			"s.src = 'http://widgets.digg.com/buttons.js';\n".
			"s1.parentNode.insertBefore(s, s1);\n".
			"})();\n".
			"</script>";
		}
	}
	
	public function playIconContent($post)
	{
		return $this->parseContent('DiggIcon',$post);
	}
	
	public function playBigContent($post)
	{
		return $this->parseContent('DiggMedium',$post);
	}
	
	public function playSmallContent($post)
	{
		return $this->parseContent('DiggCompact',$post);
	}
	
	private function parseContent($type,$post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'content' => '<a class="DiggThisButton '.$type.'" href="http://digg.com/submit?url='.urlencode($url).'&amp;title='.urlencode($title).'"></a>'
		);
		return $record;
	}
}
# diigo
//todo: SocialLibDiigo with full api
class diigoMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'diigo',
		'name' => 'Diigo',
		'home' => 'http://diigo.com',
		'icon' => 'pf=soCialMeLibMore/inc/icons/diigo.png'
	);
	
	protected $actions = array(
		'playIconContent' => true
	);
	
	public function playIconContent($post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://www.diigo.com/post?url='.urlencode($url).'&amp;title='.urlencode($title)
		);
		return $record;
	}
}
# dzone
class dzoneMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'dzone',
		'name' => 'Dzone',
		'home' => 'http://www.dzone.com',
		'icon' => 'pf=soCialMeLibMore/inc/icons/dzone.png'
	);
	
	protected $actions = array(
		'playIconContent' => true,
		'playSmallContent' => true,
		'playBigContent' => true
	);
	
	private $script_loaded = false;
	
	public function playIconContent($post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://www.dzone.com/links/add.html?url='.urlencode($url).'&amp;title='.urlencode($title)
		);
		return $record;
	}
	
	public function playSmallContent($post)
	{
		return $this->parseContent(2,$post);
	}
	
	public function playBigContent($post)
	{
		return $this->parseContent(1,$post);
	}
	
	private function parseContent($type,$post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		if ($type == '2') {
			$w = 155; $h = 25;
		}
		else {
			$w = 50; $h = 70;
		}
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'content' => 
				'<iframe src="http://widgets.dzone.com/links/widgets/zoneit.html?'.
				't='.$type.'&amp;url='.urlencode($url).'&amp;title='.urlencode($title).
				'" height="'.$h.'" width="'.$w.'" scrolling="no" frameborder="0"></iframe>'
		);
		return $record;
	}
}
# email huhu!
class emailMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'email',
		'name' => 'Email',
		'home' => '',
		'icon' => 'pf=soCialMeLibMore/inc/icons/email.png'
	);
	
	protected $actions = array(
		'playIconContent' => true
	);
	
	public function playIconContent($post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		$href = 'mailto:?Subject='.urlencode($title).'&amp;Body=%0D%0A'.urlencode($url);
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => __('Send by email'),
			'avatar' => $this->icon,
			'url' => $href
		);
		return $record;
	}
}
# favorites
class favoritesMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'favorites',
		'name' => 'Favorites',
		'home' => '',
		'icon' => 'pf=soCialMeLibMore/inc/icons/favorites.png'
	);
	
	protected $actions = array(
		'playPublicScript'=>true,
		'playIconContent'=>true
	);
	
	public function playPublicScript($available)
	{
		if (isset($available['Icon']) && in_array($this->id,$available['Icon']))
		{
			return 
			"<script type=\"text/javascript\"> \n".
			"function bookmarksite(title, url){ \n".
			" if (document.all) { window.external.AddFavorite(url, title); } \n".
			" else if (window.sidebar) { window.sidebar.addPanel(title, url, ''); } \n".
			"} \n".
			"$('.buttonFavorites').each(function(){  \n".
			" var url = $(this).attr('href'); var title = $(this).attr('title'); \n".
			" $(this).attr('href','').attr('title','".html::escapeJS(__('Add to favorites'))."'); \n".
			" $(this).click(function(){ bookmarksite(title,url); return false; }); \n".
			"}); \n".
			"</script>\n";
		}
	}
	
	public function playIconContent($post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'content' => 
				'<a class="buttonFavorites" href="'.$url.'" title="'.html::escapeHTML($title).'">'.
				'<img src="'.$this->icon.'" alt="'.$this->name.'" /><\/a>'
		);
		return $record;
	}
}
# fark
class farkMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'fark',
		'name' => 'Fark',
		'home' => 'http://www.fark.com',
		'icon' => 'pf=soCialMeLibMore/inc/icons/fark.gif'
	);
	
	protected $actions = array(
		'playIconContent' => true
	);
	
	public function playIconContent($post)
	{
		if (!$post) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://cgi.fark.com/cgi/fark/submit.pl?new_url='.urlencode($url)
		);
		return $record;
	}
}
# flattr
class flattrMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $setting_ns = 'soCialMeLibMore';
	protected $setting_id = 'soCialMe_sharer_flattr';
	
	protected $define = array(
		'id' => 'flattr',
		'name' => 'Flattr',
		'home' => 'http://flattr.com',
		'icon' => 'pf=soCialMeLibMore/inc/icons/flattr.png'
	);
	protected $actions = array(
		'playPublicScript' => true,
		'playSmallContent' => true,
		'playBigContent' => true
	);
	
	protected $config = array(
		'uid' => '',
		'lang' => 'en_GB'
	);
	
	public function init() { $this->readSettings(); $this->available = true; return true; }
	public function playSmallContent($post) { return $this->parseContent('compact',$post); }
	public function playBigContent($post) { return $this->parseContent('',$post); }
	
	public function playPublicScript($available)
	{
		if (empty($this->config['uid'])) return '';
		
		if (isset($available['Small']) && in_array($this->id,$available['Small']) 
		 || isset($available['Big']) && in_array($this->id,$available['Big']))
		{
			return "<script type=\"text/javascript\">\n/* <![CDATA[ */\n".
			"(function() {\n".
			"var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];\n".
			"s.type = 'text/javascript';\n".
			"s.async = true;\n".
			"s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto&amp;uid=".$this->config['uid']."';\n".
			"t.parentNode.insertBefore(s, t);\n".
			"})();\n".
			"/* ]]> */\n".
			"</script>\n";
		}
	}
	
	public function parseContent($type,$post)
	{
		if (empty($this->config['uid']) || !$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		$text = '';
		if (!empty($post['excerpt'])) $text = text::cutString(html::clean($post['excerpt']),900);
		if (empty($text) && !empty($post['content'])) $text = text::cutString(html::clean($post['content']),900);
		if (empty($text)) $text = $title;
		
		$lang = !empty($post['lang']) ? self::flattrLangCode($post['lang']) : $this->config['lang'];
		
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'content' => 
				'<a class="FlattrButton" style="display:none;" '.
				'title="'.$post['title'].'" '.
				(!empty($post['tags']) ? 'data-flattr-tags="'.$post['tags'].'" ' : '').
				'data-flattr-category="text" '.
				'data-flattr-language="'.$lang.'" '.
				'href="'.$url.'"> '.
				$text.
				'</a>'
		);
		return $record;
	}
	
	public function adminSave($service_id,$admin_url)
	{
		if ($service_id != $this->id || empty($_REQUEST['save'])) return;
		
		$this->config = array(
			'uid' => !empty($_POST['soCialMe_sharer_flattr_uid']) ? $_POST['soCialMe_sharer_flattr_uid'] : ''
		);
		$this->writeSettings();
	}
	
	public function adminForm($service_id,$admin_url)
	{
		$admin_url = str_replace('&','&amp;',$admin_url);
		
		return  
		'<form id="soCialMe_sharer_flattr-form" method="post" action="'.$admin_url.'">'.
		'<p><label class="classic">'.__('Your profil name or uid:').'<br />'.
		form::field(array('soCialMe_sharer_flattr_uid'),50,255,$this->config['uid']).
		'</label></p>'.
		'<p><input type="submit" name="save" value="'.__('save').'" />'.
		$this->core->formNonce().'</p>'.
		'</form>';
	}
	
	# Switch Flattr lang to l10n lang
	protected static function flattrLangCode($code)
	{
		# See http://flattr.com/support/integrate/languages
		$langs = array(
			'sq' => 'sq_AL',
			'ar' => 'ar_DZ',
			'dz' => 'ar_DZ',
			'be' => 'be_BY',
			'bg' => 'bg_BG',
			'ca' => 'ca_ES',
			'zh' => 'zh_CN',
			'cz' => 'cz_CZ',
			'nl' => 'nl_NL',
			'en' => 'en_GB',
			'et' => 'et_EE',
			'ee' => 'et_EE',
			'fi' => 'fi_FI',
			'fr' => 'fr_FR',
			'de' => 'de_DE',
			'el' => 'el_GR',
			'he' => 'iw_IL',
			'hi' => 'hi_IN',
			'hu' => 'hu_HU',
			'is' => 'is_IS',
			'id' => 'in_ID',
			'ga' => 'ga_IE',
			'it' => 'it_IT',
			'ja' => 'ja_JP',
			'ko' => 'ko_KR',
			'lv' => 'lv_LV',
			'lt' => 'lt_LT',
			'mk' => 'mk_MK',
			'ms' => 'ms_MY',
			'my' => 'ms_MY',
			'mt' => 'mt_MT',
			'no' => 'no_NO',
			'pl' => 'pl_PL',
			'pt' => 'pt_PT',
			'ro' => 'ro_RO',
			'ru' => 'ru_RU',
			'sr' => 'sr_SR',
			'sk' => 'sk_SK',
			'sl' => 'sl_SL',
			'es' => 'es_ES',
			'sv' => 'sv_SE',
			'th' => 'th_TH',
			'tr' => 'tr_TR',
			'uk' => 'uk_UA',
			'vi' => 'vi_VN'
		);
		if (!isset($langs[$code]))
		{
			return 'en_GB';
		}
		else
		{
			return $langs[$code];
		}
	}
}
# friendfeed
class friendfeedMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'friendfeed',
		'name' => 'FriendFeed',
		'home' => 'http://friendfeed.com',
		'icon' => 'pf=soCialMeLibMore/inc/icons/friendfeed.png'
	);
	
	protected $actions = array(
		'playIconContent' => true
	);
	
	public function playIconContent($post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = str_replace(' ','%20',html::clean($post['title']));
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://friendfeed.com/share/bookmarklet/frame#title='.urlencode($title).'&amp;url='.urlencode($url)
		);
		return $record;
	}
}
# googlebookmarks
class googlebookmarksMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'googlebookmarks',
		'name' => 'Google Bookmarks',
		'home' => 'http://www.google.com/bookmarks',
		'icon' => 'pf=soCialMeLibMore/inc/icons/googlebookmarks.png'
	);
	
	protected $actions = array(
		'playIconContent' => true
	);
	
	public function playIconContent($post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = str_replace(' ','%20',html::clean($post['title']));
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://www.google.com/bookmarks/mark?op=edit&amp;output=popup&amp;bkmk='.urlencode($url).'&amp;title='.urlencode($title)
		);
		return $record;
	}
}
# googlebuzz
class googlebuzzMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'googlebuzz',
		'name' => 'Google Buzz',
		'home' => 'http://www.google.com/buzz',
		'icon' => 'pf=soCialMeLibMore/inc/icons/googlebuzz.png'
	);
	
	protected $actions = array(
		'playPublicScript' => true,
		'playIconContent' => true,
		'playSmallContent' => true,
		'playBigContent' => true
	);
	
	public function playPublicScript($available)
	{
		if (isset($available['Small']) && in_array($this->id,$available['Small']) 
		 || isset($available['Big']) && in_array($this->id,$available['Big']))
		{
			return '<script type="text/javascript" src="http://www.google.com/buzz/api/button.js"></script>';
		}
	}
	
	public function playIconContent($post)
	{
		if (!$post) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://www.google.com/buzz/post?url='.urlencode($url)
		);
		return $record;
	}
	
	public function playBigContent($post)
	{
		return $this->parseContent('normal-count',$post);
	}
	
	public function playSmallContent($post)
	{
		return $this->parseContent('small-count',$post);
	}
	
	private function parseContent($type,$post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'content' => 
				'<a title="'.sprintf(__('Share on %s'),$this->name).'" class="google-buzz-button" href="http://www.google.com/buzz/post" '.
				'data-button-style="'.$type.'" '.
				'data-locale="fr" '.
				'data-url="'.$url.'" '.
				'data-message="'.$title.'"></a>'
		);
		return $record;
	}
}
# reddit
class redditMoreSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	
	protected $define = array(
		'id' => 'reddit',
		'name' => 'Reddit',
		'home' => 'http://reddit.com',
		'icon' => 'pf=soCialMeLibMore/inc/icons/reddit.png'
	);
	
	protected $actions = array(
		'playIconContent'=>true,
		'playSmallContent'=>true,
		'playBigContent'=>true
	);
	
	public function playIconContent($post)
	{
		if (!$post) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://reddit.com/submit?url='.urlencode($url)
		);
		return $record;
	}
	
	public function playSmallContent($post)
	{
		return $this->parseContent(1,$post);
	}
	
	public function playBigContent($post)
	{
		return $this->parseContent(2,$post);
	}
	
	private function parseContent($type,$post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		if ($type == 1) {
			$w = 100; $h = 20;
		}
		else {
			$w = 60; $h = 67;
		}
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'content' => 
				'<iframe src="http://www.reddit.com/static/button/button'.$type.'.html?'.
				'width='.$w.
				'&amp;url='.urlencode($url).
				'&amp;title='.urlencode($title).
				'&amp;newwindow=1" '.
				'style="border:none; overflow:hidden;height:'.$h.'px; width:'.$w.'px;"'.
				'scrolling="no" frameborder="0" allowTransparency="true"></iframe>'
		);
		return $record;
	}
}
?>