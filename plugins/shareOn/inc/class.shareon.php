<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shareOn, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$GLOBALS['shareOnBoxCounter'] = 0;

class shareOn
{
	public $core;
	public $s;
	
	public $id = 'undefined';
	public $name = 'undefined';
	public $home = '';
	
	public $_active = false; // enable button
	public $_small = false; // button size
	public $size = array(
		0 => array('style'=>'normal','width'=>50,'height'=>80),
		1 => array('style'=>'compact','width'=>90,'height'=>20)
	);
	protected $encode = true;
	protected $preload = false;
	
	public $js_var = '';
	public $js_content = '$(\' \')';
	public $nl_content = '';
	protected $info = array();
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->s = $core->blog->settings->shareOn;
		$this->loadSettings();
	}
	
	public function loadSettings()
	{
		$a = 'shareOn_button'.$this->id.'_active';
		$s = 'shareOn_button'.$this->id.'_small';
		$this->_active = (boolean) $this->s->{$a};
		$this->_small = (boolean) $this->s->{$s};
	}
	
	public function saveSettings($active,$small)
	{
		$this->s->put('shareOn_button'.$this->id.'_active',$active,'boolean');
		$this->s->put('shareOn_button'.$this->id.'_small',$small,'boolean');
	}
	
	public function moreSettingsForm()
	{
		return ;
	}
	
	public function moreSettingsSave()
	{
	
	}
	
	public function preload()
	{
		return $this->preload ? true : false;
	}
	
	public function generateHTMLButton($url,$title)
	{
		if (!$this->_active) return '';
		
		$GLOBALS['shareOnBoxCounter'] += 1;
		
		$this->info['URL'] = $this->encode ? urlencode($url) : $url;
		$this->info['TITLE'] = $this->encode ? urlencode($title) : $title;
		$this->info['STYLE'] = $this->size[(integer) $this->_small]['style'];
		$this->info['WIDTH'] = $this->size[(integer) $this->_small]['width'];
		$this->info['HEIGHT'] = $this->size[(integer) $this->_small]['height'];
		
		$this->completeInfo();
		
		$keys = $values = array();
		foreach($this->info as $k => $v)
		{
			$keys[] = '%'.$k.'%';
			$values[] = $v;
		}
		
		$var = $this->jsVar();
		if (!empty($var))
		{
			$var =
			'<script type="text/javascript">'.
			"\n//<![CDATA[ \n".
			$var.' '.
			"\n//]]> \n".
			'</script> ';
		}
		
		if ($this->preload)
		{
			$content =
			'<div id="shareon-object-'.$GLOBALS['shareOnBoxCounter'].'"></div>'.
			'<script type="text/javascript">'.
			"\n//<![CDATA[ \n".
			'$(document).ready(function(){ '.
			'$(\'#shareon-object-'.$GLOBALS['shareOnBoxCounter'].'\').replaceWith('.$this->jsContent().'); '.
			"}); ".
			"\n//]]> \n".
			'</script> ';
		}
		else
		{
			$content = $this->nlContent();
		}
		
		return str_replace($keys,$values,
			'<div class="shareon-box shareon-box-'.$this->id.'">'.
			$var.$content.
			'</div>'
		);
	}
	
	protected function completeInfo()
	{
		
	}
	
	protected function jsVar()
	{
		return $this->js_var;
	}
	
	protected function jsContent()
	{
		return $this->js_content;
	}
	
	protected function nlContent()
	{
		return $this->nl_content;
	}
}

class tweetmemeButton extends shareOn
{
	public $id = 'tweetmeme';
	public $name = 'TweetMeme';
	public $home = 'http://tweetmeme.com';
	public $js_content = '$(\'<iframe src="http://api.tweetmeme.com/button.js?url=%URL%&amp;style=%STYLE%&amp;source=%RT%&amp;service=bit.ly&amp;width=%WIDTH%&amp;height=%HEIGHT%"  width="%WIDTH%" height="%HEIGHT%" frameborder="0" scrolling="no" allowtransparency="true"></iframe>\')';
	public $size = array(
		0 => array('style'=>'normal','width'=>50,'height'=>61),
		1 => array('style'=>'compact','width'=>70,'height'=>18)
	);
	protected $preload = true;
	
	public function __construct($core)
	{
		parent::__construct($core);
		
		$rt = (string) $this->s->shareOn_button_tweetmeme_rt;
		
		$this->info['RT'] = $rt ? $rt : '';
	}
	
	public function moreSettingsForm()
	{
		return
	    '<p><label>'.
		__('Retweet name:').'<br />'.
	    form::field(array('tweetmeme_rt'),50,255,$this->info['RT']).
		'</label></p>'.
		'<p class="form-note">'.__("Change the RT source of the button from RT @tweetmeme to RT @yourname. Please use the format of 'yourname', not 'RT @yourname'.").'</p>';
	}
	
	public function moreSettingsSave()
	{
		if (isset($_POST['tweetmeme_rt']))
		{
			$this->s->put('shareOn_button_tweetmeme_rt',$_POST['tweetmeme_rt'],'string');
		}
	}
}

class fbshareButton extends shareOn
{
	public $id = 'fbshare';
	public $name = 'Facebook Share';
	public $home = 'http://fbshare.me';
	public $js_content = '$(\'<iframe src="http://widgets.fbshare.me/files/fbshare.php?size=%STYLE%&url=%URL%&title=%TITLE%&google_analytics=false&awesm_api_key=&badge_color=&badge_text=" width="%WIDTH%" height="%HEIGHT%" frameborder="0" scrolling="no" allowtransparency="true"> </iframe>\')';
	public $size = array(
		0 => array('style'=>'large','width'=>53,'height'=>69),
		1 => array('style'=>'small','width'=>70,'height'=>18)
	);
	protected $preload = true;
	
	public function __construct($core)
	{
		parent::__construct($core);
	}
//add options for google_analytics,badge_color,awesm_api_key,badge_text
}

class fbloveButton extends shareOn
{
	public $id = 'fblove';
	public $name = 'Facebook Love';
	public $home = 'http://developers.facebook.com/docs/reference/plugins/like';
	public $js_content = '$(\'<iframe src="http://www.facebook.com/plugins/like.php?href=%URL%&amp;layout=%STYLE%&amp;show_faces=%SHOWFACES%&amp;width=100%&amp;action=%ACTION%&amp;colorscheme=%COLORSCHEME%&amp;height=%HEIGHT%" width="%WIDTH%" height="%HEIGHT%" frameborder="0" scrolling="no" allowtransparency="true"> </iframe>\')';
	public $size = array(
		0 => array('style'=>'standard','width'=>450,'height'=>25),
		1 => array('style'=>'button_count','width'=>80,'height'=>25)
	);
	protected $preload = true;
	
	public function __construct($core)
	{
		parent::__construct($core);
		
		$hover = (string) $this->s->shareOn_button_fblove_hover;
		$showfaces = (string) $this->s->shareOn_button_fblove_showfaces;
		$colorscheme = (string) $this->s->shareOn_button_fblove_colorscheme;
		$action = (string) $this->s->shareOn_button_fblove_action;
		
		$this->info['HOVER'] = $hover ? $hover : '';
		$showfaces = $showfaces == 'true' ? 'true' : 'false';
		$this->info['SHOWFACES'] = $showfaces;
		$colorscheme = $colorscheme == 'light' ? 'light' : 'dark';
		$this->info['COLORSCHEME'] = $colorscheme;
		$action = $action == 'like' ? 'like' : 'recommend';
		$this->info['ACTION'] = $action;
	}
	
	public function moreSettingsForm()
	{
		return
	    '<p><label>'.
		__('Button title:').'<br />'.
	    form::field(array('fblove_hover'),50,7,$this->info['HOVER']).
		'</label></p>'.
	    '<p><label>'.
		__('Show faces:').'<br />'.
	    form::combo(array('fblove_showfaces'),array(__('yes')=>'true',__('no')=>'false'),$this->info['SHOWFACES']).
		'</label></p>'.
	    '<p><label>'.
		__('Colors scheme:').'<br />'.
	    form::combo(array('fblove_colorscheme'),array(__('light')=>'light',__('dark')=>'dark'),$this->info['COLORSCHEME']).
		'</label></p>'.
	    '<p><label>'.
		__('Type:').'<br />'.
	    form::combo(array('fblove_action'),array(__('I like')=>'like',__('I recommend')=>'recommend'),$this->info['ACTION']).
		'</label></p>';
	}
	
	public function moreSettingsSave()
	{
		if (isset($_POST['fblove_hover']))
		{
			$this->s->put('shareOn_button_fblove_hover',$_POST['fblove_hover'],'string');
		}
		if (isset($_POST['fblove_showfaces']))
		{
			$this->s->put('shareOn_button_fblove_showfaces',$_POST['fblove_showfaces'],'string');
		}
		if (isset($_POST['fblove_colorscheme']))
		{
			$this->s->put('shareOn_button_fblove_colorscheme',$_POST['fblove_colorscheme'],'string');
		}
		if (isset($_POST['fblove_action']))
		{
			$this->s->put('shareOn_button_fblove_action',$_POST['fblove_action'],'string');
		}
	}
}

class diggButton extends shareOn
{
	public $id = 'digg';
	public $name = 'Digg';
	public $home = 'http://digg.com';
	public $js_var = "var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0]; s.type = 'text/javascript'; s.async = true; s.src = 'http://widgets.digg.com/buttons.js'; s1.parentNode.insertBefore(s, s1); \n";
	public $js_content = '$(\'<a class="DiggThisButton Digg%STYLE%" href="http://digg.com/submit?url=%URL%&amp;title=%TITLE%"></a>\')';
	public $size = array(
		0 => array('style'=>'Medium','width'=>52,'height'=>80),
		1 => array('style'=>'Compact','width'=>90,'height'=>18)
	);
	protected $preload = true;
	
	public function __construct($core)
	{
		parent::__construct($core);
	}
//add option to desc (350chars)
}

class redditButton extends shareOn
{
	public $id = 'reddit';
	public $name = 'Reddit';
	public $home = 'http://www.reddit.com';
	public $js_content = '$(\'<iframe src="http://www.reddit.com/static/button/button%STYLE%.html?width=%WIDTH%&url=%URL%&title=%TITLE%&newwindow=1" height="%HEIGHT%" width="%WIDTH%" scrolling="no" frameborder="0"></iframe>\')';
	public $size = array(
		0 => array('style'=>'2','width'=>52,'height'=>80),
		1 => array('style'=>'1','width'=>120,'height'=>22)
	);
	protected $preload = true;
	
	public function __construct($core)
	{
		parent::__construct($core);
	}
}

class dzoneButton extends shareOn
{
	public $id = 'dzone';
	public $name = 'Dzone';
	public $home = 'http://www.dzone.com';
	public $js_content = '$(\'<iframe src="http://widgets.dzone.com/links/widgets/zoneit.html?t=%STYLE%&url=%URL%&title=%TITLE%" height="%HEIGHT%" width="%WIDTH%" scrolling="no" frameborder="0"></iframe>\')';
	public $size = array(
		0 => array('style'=>'1','width'=>50,'height'=>70),
		1 => array('style'=>'2','width'=>155,'height'=>25)
	);
	protected $preload = true;
	
	public function __construct($core)
	{
		parent::__construct($core);
	}
//add option to description
}

class ybuzzButton extends shareOn
{
	public $id = 'ybuzz';
	public $name = 'Yahoo Buzz';
	public $home = 'http://buzz.yahoo.com';
	public $js_var = "yahooBuzzArticleHeadline= '%TITLE%'; yahooBuzzArticleId= '%URL%'; ";
	public $nl_content = '<script type="text/javascript" src="http://d.yimg.com/ds/badge2.js" badgetype="%STYLE%"></script>';
	public $size = array(
		0 => array('style'=>'square','width'=>51,'height'=>82),
		1 => array('style'=>'small-votes','width'=>159,'height'=>22)
	);
	protected $preload = false;
	protected $encode = false;
	
	public function __construct($core)
	{
		parent::__construct($core);
	}
}

class flattrButton extends shareOn
{
	public $id = 'flattr';
	public $name = 'Flattr';
	public $home = 'http://flattr.com';
	public $js_var = 'var flattr_uid = "%UID%"; var flattr_cat = "text"; var flattr_tle = "%TITLE%"; var flattr_dsc = "%DESC%"; var flattr_btn = "%STYLE%"; var flattr_tag = "%TAG%"; var flattr_url = "%URL%"; var flattr_lng = "%LANG%"; ';
	public $nl_content = '<script src="http://api.flattr.com/button/load.js" type="text/javascript"></script>';
	public $size = array(
		0 => array('style'=>'','width'=>53,'height'=>69),
		1 => array('style'=>'compact','width'=>110,'height'=>25)
	);
	protected $preload = false;
	protected $encode = false;
	
	public function __construct($core)
	{
		parent::__construct($core);
		
		$uid = (string) $this->s->shareOn_button_flattr_uid;
		
		$this->info['UID'] = $uid ? $uid : '';
		$this->info['LANG'] = 
		$this->info['DESC'] = 
		$this->info['TAG'] = '';
	}
	
	public function moreSettingsForm()
	{
		return
	    '<p><label>'.
		__('Your Flattr UID:').'<br />'.
	    form::field(array('flattr_uid'),50,7,$this->info['UID']).
		'</label></p>';
	}
	
	public function moreSettingsSave()
	{
		if (isset($_POST['flattr_uid']))
		{
			$this->s->put('shareOn_button_flattr_uid',$_POST['flattr_uid'],'string');
		}
	}
	
	protected function completeInfo()
	{
		if (!$this->info['UID']) return;
		
		global $core, $_ctx;
		
		# Lang
		$lang = 'en_GB';
		if ($_ctx->posts->post_lang != '')
		{
			$lang = $_ctx->posts->post_lang;
			$this->info['LANG'] = self::flattrLangCode($lang);
		}
		
		# Desc
		if ($_ctx->posts->post_content != '')
		{
			if ($_ctx->posts->post_excerpt != '')
			{
				$desc = self::flattrClean($_ctx->posts->post_excerpt);
			}
			$desc .= self::flattrClean($_ctx->posts->post_content);
			
			$desc = text::cutString($desc,180);
			$this->info['DESC'] = html::escapeJS($desc);
		}
		
		# Tag
		if ($_ctx->exists('posts'))
		{
			$params = array();
			$params['meta_type'] = 'tag';
			$params['post_id'] = $_ctx->posts->post_id;
			$params['limit'] = 5;
			
			$metas = $core->meta->getMetadata($params);
			$tags = array();
			while ($metas->fetch())
			{ 
				$tags[] = $metas->meta_id;
			}
			$tag = implode(', ',$tags);
			$tag = self::flattrClean($tag);
			$this->info['TAG'] = html::escapeJS($tag);
		}
	}
	
	protected static function flattrClean($str)
	{
		return 
		trim(
		str_replace("'","\'",
		preg_replace('~\r\n|\r|\n~',' ',
		text::cutString(
		html::escapeHTML(
		html::decodeEntities(
		html::clean(
			$str
		))),180))));
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

class gbuzzButton extends shareOn
{
	public $id = 'gbuzz';
	public $name = 'Google Buzz';
	public $home = 'http://www.google.com/buzz/stuff';
	public $nl_content = '<a href="http://www.google.com/buzz/post" class="google-buzz-button" title="Google Buzz" data-message="%TITLE%%DESC%" data-url="%URL%" data-locale="%LANG%" data-button-style="%STYLE%"></a><script type="text/javascript" src="http://www.google.com/buzz/api/button.js"></script>';
	public $size = array(
		0 => array('style'=>'normal-count','width'=>51,'height'=>82),
		1 => array('style'=>'small-count','width'=>100,'height'=>0)
	);
	protected $preload = false;
	protected $encode = false;
	
	public function __construct($core)
	{
		parent::__construct($core);
		
		$showdesc = (boolean) $this->s->shareOn_button_gbuzz_showdesc;
		
		$this->info['SHOWDESC'] = $showdesc ? true : false;
	}
	
	public function moreSettingsForm()
	{
		return
	    '<p><label class="classic">'.
	    form::checkbox(array('gbuzz_showdesc'),1,$this->info['SHOWDESC']).
		__('Add post description to message').
		'</label></p>';
	}
	
	public function moreSettingsSave()
	{
		if (isset($_POST['gbuzz_showdesc']))
		{
			$this->s->put('shareOn_button_gbuzz_showdesc',$_POST['gbuzz_showdesc'],'boolean');
		}
	}
	
	public function completeHTMLButton($base)
	{
		if (!$this->_showdesc)
		{ 
			return str_replace('%DESC%','',$base);
		}
		
		global $core, $_ctx;
		
		$desc = '';
		if ($_ctx->posts->post_excerpt != '')
		{
			$desc = self::gbuzzClean($_ctx->posts->post_excerpt);
		}
		elseif ($_ctx->posts->post_content != '')
		{
			$desc .= self::gbuzzClean($_ctx->posts->post_content,500);
		}
		
		return str_replace('%DESC%',' - '.$desc,$base);
	}
	
	protected static function gbuzzClean($str,$len=null)
	{
		$str =
		trim(
		preg_replace('~\r\n|\r|\n~',' ',
		html::escapeHTML(
		html::decodeEntities(
		html::clean(
			$str
		)))));
		
		if ($len)
		{
			return text::cutString($str,$len);
		}
		else
		{
			return $str;
		}
	}
}
?>