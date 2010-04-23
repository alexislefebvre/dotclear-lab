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
	public $name = 'FB Share';
	public $home = 'http://fbshare.me';
	public $base = '<script type="text/javascript">var fbShare = {url: \'%URL%\', title: \'%TITLE%\', size: \'%STYLE%\', google_analytics: \'false\'}</script><script src="http://widgets.fbshare.me/files/fbshare.js"></script>';
	public $size = array(
		0 => array('style'=>'large','width'=>53,'height'=>69),
		1 => array('style'=>'small','width'=>80,'height'=>20)
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
	public $name = 'FB Love';
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
	public $name = 'YBuzz';
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
?>