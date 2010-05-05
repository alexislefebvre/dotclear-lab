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

class shareOn
{
	public $core;
	public $s;

	public $id = 'undefined';
	public $name = 'undefined';
	public $home = '';
	public $base = '';

	public $_active = false; // enable button
	public $_small = false; // button size
	public $size = array(
		0 => array('style'=>'normal','width'=>53,'height'=>69),
		1 => array('style'=>'compact','width'=>90,'height'=>20)
	);
	public $encode = true;

	public function __construct($core)
	{
		$this->core = $core;
		$this->s = shareOnSettings($core);
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

	public function generateHTMLButton($url,$title)
	{
		if (!$this->_active) return '';

		if ($this->encode)
		{
			$url = urlencode($url);
			$title = urlencode($title);//htmlspecialchars($title);
		}
		$style = $this->size[(integer) $this->_small]['style'];
		$width = $this->size[(integer) $this->_small]['width'];
		$height = $this->size[(integer) $this->_small]['height'];
	
		$base = str_replace(
			array('%URL%','%TITLE%','%STYLE%','%WIDTH%','%HEIGHT%'),
			array($url,$title,$style,$width,$height),
			$this->base
		);
		
		return $this->completeHTMLButton($base);
	}

	public function completeHTMLButton($base)
	{
		return $base;
	}
}

class tweetmemeButton extends shareOn
{
	public $id = 'tweetmeme';
	public $name = 'TweetMeme';
	public $home = 'http://tweetmeme.com';
	public $base = '<script type="text/javascript">tweetmeme_url = "%URL%";tweetmeme_source = "%RT%";tweetmeme_style = "%STYLE%";</script><script type="text/javascript" src="http://tweetmeme.com/i/scripts/button.js"></script>';
	public $size = array(
		0 => array('style'=>'normal','width'=>53,'height'=>69),
		1 => array('style'=>'compact','width'=>90,'height'=>20)
	);
	public $_rt = ''; // retweet special name
	public $encode = false;

	public function __construct($core)
	{
		parent::__construct($core);
		$this->_rt = (string) $this->s->shareOn_button_tweetmeme_rt;
	}

	public function moreSettingsForm()
	{
		return
	    '<p class="field"><label>'.
		__('Retweet name:').
	    form::field(array('tweetmeme_rt'),50,255,$this->_rt).
		'</label></p>'.
		'<p class="form-note">'.__("Change the RT source of the button from RT @tweetmeme to RT @yourname. Please use the format of 'yourname', not 'RT @yourname'.").'</p>';
	}

	public function moreSettingsSave()
	{
		if (isset($_POST['tweetmeme_rt'])) {
			$this->s->put('shareOn_button_tweetmeme_rt',$_POST['tweetmeme_rt'],'string');
		}
	}

	public function completeHTMLButton($base)
	{
		return str_replace('%RT%',$this->_rt,$base);
	}
}

class fbshareButton extends shareOn
{
	public $id = 'fbshare';
	public $name = 'Facebook Share';
	public $home = 'http://fbshare.me';
	public $base = '<script type="text/javascript">var fbShare = {url: \'%URL%\', title: \'%TITLE%\', size: \'%STYLE%\', google_analytics: \'false\'}</script><script src="http://widgets.fbshare.me/files/fbshare.js"></script>';
	public $size = array(
		0 => array('style'=>'large','width'=>53,'height'=>69),
		1 => array('style'=>'small','width'=>80,'height'=>22)
	);
	public $encode = false;

	public function __construct($core)
	{
		parent::__construct($core);
	}
}

class fbloveButton extends shareOn
{
	public $id = 'fblove';
	public $name = 'Facebook Love';
	public $home = 'http://developers.facebook.com/docs/reference/plugins/like';
	public $base = '<iframe width="%WIDTH%" height="%HEIGHT%" src="http://www.facebook.com/widgets/like.php?width=%WIDTH%&amp;show_faces=%SHOWFACES%&amp;layout=%STYLE%&amp;colorscheme=%COLORSCHEME%&amp;action=%ACTION%&amp;href=%URL%" title="%HOVER%" scrolling="no" frameborder="0"></iframe>';
	public $size = array(
		0 => array('style'=>'standard','width'=>450,'height'=>22),
		1 => array('style'=>'button_count','width'=>100,'height'=>22)
	);

	public $_hover = '';
	public $_showfaces = 'false';
	public $_colorscheme = 'light';
	public $_action = 'like';

	public function __construct($core)
	{
		parent::__construct($core);
		$this->_hover = (string) $this->s->shareOn_button_fblove_hover;
		$this->_showfaces = (string) $this->s->shareOn_button_fblove_showfaces;
		$this->_colorscheme = (string) $this->s->shareOn_button_fblove_colorscheme;
		$this->_action = (string) $this->s->shareOn_button_fblove_action;
	}

	public function moreSettingsForm()
	{
		return
	    '<p class="field"><label>'.
		__('Button title:').
	    form::field(array('fblove_hover'),50,7,$this->_hover).
		'</label></p>'.
	    '<p class="field"><label>'.
		__('Show faces:').
	    form::combo(array('fblove_showfaces'),array(__('yes')=>'true',__('no')=>'false'),$this->_showfaces).
		'</label></p>'.
	    '<p class="field"><label>'.
		__('Colors scheme:').
	    form::combo(array('fblove_colorscheme'),array(__('light')=>'light',__('dark')=>'dark'),$this->_colorscheme).
		'</label></p>'.
	    '<p class="field"><label>'.
		__('Type:').
	    form::combo(array('fblove_action'),array(__('I like')=>'like',__('I recommend')=>'recommend'),$this->_action).
		'</label></p>';
	}

	public function moreSettingsSave()
	{
		if (isset($_POST['fblove_hover'])) {
			$this->s->put('shareOn_button_fblove_hover',$_POST['fblove_hover'],'string');
		}
		if (isset($_POST['fblove_showfaces'])) {
			$this->s->put('shareOn_button_fblove_showfaces',$_POST['fblove_showfaces'],'string');
		}
		if (isset($_POST['fblove_colorscheme'])) {
			$this->s->put('shareOn_button_fblove_colorscheme',$_POST['fblove_colorscheme'],'string');
		}
		if (isset($_POST['fblove_action'])) {
			$this->s->put('shareOn_button_fblove_action',$_POST['fblove_action'],'string');
		}
	}

	public function completeHTMLButton($base)
	{
		return str_replace(
			array(
				'%HOVER%',
				'%SHOWFACES%',
				'%COLORSCHEME%',
				'%ACTION%'
			),
			array(
				$this->_hover,
				$this->_showfaces,
				$this->_colorscheme,
				$this->_action
			),
			$base
		);
	}
}

class diggButton extends shareOn
{
	public $id = 'digg';
	public $name = 'Digg';
	public $home = 'http://digg.com';
	public $base = '<script type="text/javascript">digg_url = \'%URL%\';digg_title = \'%TITLE%\';digg_skin = \'%STYLE%\';digg_bgcolor = \'%BGCOLOR%\';digg_window = \'new\';</script><script src="http://digg.com/tools/diggthis.js" type="text/javascript"></script>';
	public $size = array(
		0 => array('style'=>'normal','width'=>52,'height'=>80),
		1 => array('style'=>'compact','width'=>120,'height'=>18)
	);
	public $_bgcolor = '#FFFFFF'; // special background-color;

	public function __construct($core)
	{
		parent::__construct($core);
		$this->_bgcolor = (string) $this->s->shareOn_button_digg_bgcolor;
	}

	public function moreSettingsForm()
	{
		return
	    '<p class="field"><label>'.
		__('Background color:').
	    form::field(array('digg_bgcolor'),50,7,$this->_bgcolor).
		'</label></p>'.
		'<p class="form-note">'.__("Use color code like '#CC00FF'.").'</p>';
	}

	public function moreSettingsSave()
	{
		if (isset($_POST['digg_bgcolor'])) {
			$this->s->put('shareOn_button_digg_bgcolor',$_POST['digg_bgcolor'],'string');
		}
	}

	public function completeHTMLButton($base)
	{
		return str_replace('%BGCOLOR%',$this->_bgcolor,$base);
	}
}

class redditButton extends shareOn
{
	public $id = 'reddit';
	public $name = 'Reddit';
	public $home = 'http://www.reddit.com';
	public $base = '<script type="text/javascript">reddit_newwindow="1";reddit_url="%URL%";reddit_title="%TITLE%";</script><script type="text/javascript" src="http://www.reddit.com/button.js?t=%STYLE%"></script>';
	public $size = array(
		0 => array('style'=>'2','width'=>52,'height'=>80),
		1 => array('style'=>'1','width'=>120,'height'=>20)
	);

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
	public $base = '<script type="text/javascript">var dzone_url = "%URL%";var dzone_title = "%TITLE%";var dzone_style = "%STYLE%";</script><script language="javascript" src="http://widgets.dzone.com/links/widgets/zoneit.js"></script>';
	public $size = array(
		0 => array('style'=>'1','width'=>52,'height'=>80),
		1 => array('style'=>'2','width'=>120,'height'=>18)
	);

	public function __construct($core)
	{
		parent::__construct($core);
	}
}

class ybuzzButton extends shareOn
{
	public $id = 'ybuzz';
	public $name = 'Yahoo Buzz';
	public $home = 'http://buzz.yahoo.com';
	public $base = '<script type="text/javascript" src="http://d.yimg.com/ds/badge2.js" badgetype="%STYLE%">%URL%</script>';
	public $size = array(
		0 => array('style'=>'square','width'=>51,'height'=>82),
		1 => array('style'=>'small-votes','width'=>159,'height'=>22)
	);

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
	public $base = "<script type=\"text/javascript\">var flattr_uid = '%UID%'; var flattr_cat = 'text'; var flattr_tle = '%TITLE%'; var flattr_dsc = '%DESC%'; var flattr_btn = '%STYLE%'; var flattr_tag = '%TAG%'; var flattr_url = '%URL%'; var flattr_lng = '%LANG%'; </script><script src=\"http://api.flattr.com/button/load.js\" type=\"text/javascript\"></script>";
	public $size = array(
		0 => array('style'=>'','width'=>53,'height'=>69),
		1 => array('style'=>'compact','width'=>90,'height'=>20)
	);
	public $_uid = ''; // flattr accound uid
	public $encode = false;
	
	public function __construct($core)
	{
		parent::__construct($core);
		$this->_uid = (string) $this->s->shareOn_button_flattr_uid;
	}

	public function moreSettingsForm()
	{
		return
	    '<p class="field"><label>'.
		__('Your Flattr UID:').
	    form::field(array('flattr_uid'),50,7,$this->_uid).
		'</label></p>';
	}

	public function moreSettingsSave()
	{
		if (isset($_POST['flattr_uid'])) {
			$this->s->put('shareOn_button_flattr_uid',$_POST['flattr_uid'],'string');
		}
	}

	public function completeHTMLButton($base)
	{
		global $core, $_ctx;
		
		$lang = 'en_GB';
		if ($_ctx->posts->post_lang != '') {
			$lang = $_ctx->posts->post_lang;
			$lang = self::flattrLangCode($lang);
		}
		
		$desc = '';
		if ($_ctx->posts->post_content != '') {
			if ($_ctx->posts->post_excerpt != '') {
				$desc = self::flattrClean($_ctx->posts->post_excerpt);
			}
			$desc .= self::flattrClean($_ctx->posts->post_content);
			
			$desc = text::cutString($desc,180);
		}
		
		$tag = '';
		if ($_ctx->exists('posts')) {
			$obj = new dcMeta($core);
			$metas = $obj->getMeta('tag',null,null,$_ctx->posts->post_id);
			$tags = array();
			while ($metas->fetch()) { 
				$tags[] = $metas->meta_id;
			}
			$tag = implode(', ',$tags);
			$tag = self::flattrClean($tag);
		}
		
		return str_replace(
			array('%UID%','%LANG%','%DESC%','%TAG%'),
			array($this->_uid,$lang,$desc,$tag),
			$base
		);
	}

	protected static function flattrClean($str)
	{
		return 
		trim(
		preg_replace(array('~\r\n|\r|\n~',"'"),array(' ',"\'"),
		text::cutString(
		html::escapeHTML(
		html::decodeEntities(
		html::clean(
			$str
		))),180)));
	}
	
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
		if (!isset($langs[$code])) {
			return 'en_GB';
		}
		else {
			return $langs[$code];
		}
	}
}

class gbuzzButton extends shareOn
{
	public $id = 'gbuzz';
	public $name = 'Google Buzz';
	public $home = 'http://www.google.com/buzz/stuff';
	public $base = '<a href="http://www.google.com/buzz/post" class="google-buzz-button" title="Google Buzz" data-message="%TITLE" data-url="%URL%" data-locale="%LANG%" data-button-style="%STYLE%"></a><script type="text/javascript" src="http://www.google.com/buzz/api/button.js"></script>';
	public $size = array(
		0 => array('style'=>'normal-count','width'=>51,'height'=>82),
		1 => array('style'=>'small-count','width'=>159,'height'=>22)
	);

	public function __construct($core)
	{
		parent::__construct($core);
	}
}
?>