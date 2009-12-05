<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shareOn, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
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
		$this->s =& $core->blog->settings;
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
		$this->s->setNameSpace('shareOn');
		$this->s->put('shareOn_button'.$this->id.'_active',$active,'boolean');
		$this->s->put('shareOn_button'.$this->id.'_small',$small,'boolean');
		$this->s->setNameSpace('system');
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
	    '<p><label class="classic">'.
		__('Retweet name:').'<br />'.
	    form::field(array('tweetmeme_rt'),50,255,$this->_rt).
		'</label></p>'.
		'<p class="form-note">'.__("Change the RT source of the button from RT @tweetmeme to RT @yourname. Please use the format of 'yourname', not 'RT @yourname'.").'</p>';
	}

	public function moreSettingsSave()
	{
		if (isset($_POST['tweetmeme_rt']))
		{
			$this->s->setNameSpace('shareOn');
			$this->s->put('shareOn_button_tweetmeme_rt',$_POST['tweetmeme_rt'],'string');
			$this->s->setNameSpace('system');
		}
	}

	public function completeHTMLButton($base)
	{
		return str_replace('%RT%',$this->_rt,$base);
	}
}

class fbshareButton extends shareOn
{
	public $id = 'fbsahre';
	public $name = 'FB Share';
	public $home = 'http://fbshare.me';
	public $base = '<script>var fbShare = { url: "%URL%", size: "%STYLE%",google_analytics: "false" }</script><script src="http://widgets.fbshare.me/files/fbshare.js"></script>';
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
	    '<p><label class="classic">'.
		__('Background color:').'<br />'.
	    form::field(array('digg_bgcolor'),50,7,$this->_bgcolor).
		'</label></p>'.
		'<p class="form-note">'.__("Use color code like '#CC00FF'.").'</p>';
	}

	public function moreSettingsSave()
	{
		if (isset($_POST['digg_bgcolor']))
		{
			$this->s->setNameSpace('shareOn');
			$this->s->put('shareOn_button_digg_bgcolor',$_POST['digg_bgcolor'],'string');
			$this->s->setNameSpace('system');
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