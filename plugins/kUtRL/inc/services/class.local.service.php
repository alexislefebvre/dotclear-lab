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

class localKutrlService extends kutrlServices
{
	public $id = 'local';
	public $name = 'My blog';
	public $home = 'http://kutrl.fr';

	public function __construct($core,$limit_to_blog=true)
	{
		parent::__construct($core,$limit_to_blog);

		$protocols = (string) $core->blog->settings->kutrl_srv_local_protocols;
		$this->allowed_protocols = empty($protocols) ? array() : explode(',',$protocols);
		$this->allow_customized_hash = true;

		$this->url_base = $core->blog->url.$core->url->getBase('kutrl').'/';
		$this->url_min_length = strlen($this->url_base) + 2;
	}

	public function saveSettings()
	{
		$this->s->setNameSpace('kUtRL');
		$this->s->put('kutrl_srv_local_protocols',$_POST['kutrl_srv_local_protocols']);
		$this->s->put('kutrl_srv_local_public',isset($_POST['kutrl_srv_local_public']));
		$this->s->put('kutrl_srv_local_css',$_POST['kutrl_srv_local_css']);
		$this->s->setNameSpace('system');
	}

	public function settingsForm()
	{
		echo
		'<p>'.
		__('This service use your own Blog to shorten and serve URL.').'<br />'.
		sprintf(__('This means that with this service short links start with "%s".'),$this->url_base).
		'</p>'.
		'<p>'.
		__("You can use Dotclear's plugin called myUrlHandlers to change short links prefix on your blog.");

		if (preg_match('/index\.php/',$this->url_base))
		{
			echo 
			'<p>'.
			__("We recommand that you use a rewrite engine in order to remove 'index.php' from your blog's URL.").
			'<br /><a href="http://fr.dotclear.org/documentation/2.0/usage/blog-parameters">'.
			__("You can find more about this on the Dotclear's documentation.").
			'</a></p>';
		}
		echo 
		'</p>'.

	    '<p><label class="classic">'.
		__('Allowed protocols:').'<br />'.
	    form::field(array('kutrl_srv_local_protocols'),50,255,$this->s->kutrl_srv_local_protocols).
		'</label></p>'.

	    '<p class="form-note">'.
	    __('Use comma seperated list like: "http:,https:,ftp:"').
	    '</p>'.

		'<p><label class="classic">'.
		form::checkbox(array('kutrl_srv_local_public'),'1',$this->s->kutrl_srv_local_public).' '.
		__('Enable public page for visitors to shorten links').
		'</label></p>'.

		'<p class="area" id="style-area"><label for="_style">'.__('CSS:').'</label>'.
		form::textarea('kutrl_srv_local_css',50,3,html::escapeHTML($this->s->kutrl_srv_local_css),'',2).
		'</p>'.
		'<p class="form-note">'.__('You can add here special cascading style sheet. Body of page has class "dc-kutrl" and widgets have class "shortenkutrlwidget" and "rankkutrlwidget".').'</p>';
	}

	public function testService()
	{
		return !empty($this->allowed_protocols);
	}

	public function createHash($url,$hash=null)
	{
		$rs = new ArrayObject();
		$rs->type = 'local';
		$rs->url = $url;

		if ($hash === null)
		{
			$type = 'localnormal';
			$rs->hash = $this->next($this->last());
		}
		else
		{
			$type = 'localcustom';
			$rs->hash = $hash;
		}

		$this->log->insert($rs->url,$rs->hash,$type,$rs->type);
		return $rs;
	}

	protected function last()
	{
		return 
		false === ($rs = $this->log->select(null,null,'localnormal','local')) ?
		-1 : $rs->hash;
	}

	protected function next($last_id)
	{
		if ($last_id == -1)
		{
			$next_id = 0;
		}
		else
		{
			for($x = 1; $x <= strlen($last_id); $x++)
			{
				$pos = strlen($last_id) - $x;

				if ($last_id[$pos] != 'z')
				{
					$next_id = $this->increment($last_id,$pos);
					break;
				}
			}

			if (!isset($next_id))
			{
				$next_id = $this->append($last_id);
			}
		}

		return 
		false === ($rs = $this->log->select(null,$next_id,'localnormal','local')) ?
		$next_id : $this->next($next_id);
	}

	protected function append($id)
	{
		for ($x = 0; $x < strlen($id); $x++)
		{
			$id[$x] = 0;
		}
		$id .= 0;

		return $id;
	}

	protected function increment($id,$pos)
	{
		$char = $id[$pos];

		if (is_numeric($char))
		{
			$new_char = $char < 9 ? $char + 1 : 'a';
		}
		else
		{
			$new_char = chr(ord($char) + 1);
		}
		$id[$pos] = $new_char;
		
		if ($pos != (strlen($id) - 1))
		{
			for ($x = ($pos + 1); $x < strlen($id); $x++)
			{
				$id[$x] = 0;
			}
		}

		return $id;
	}

	public function getUrl($hash)
	{
		if (false === ($rs = $this->log->select(null,$hash,null,'local')))
		{
			return false;
		}
		$this->log->counter($rs->id,'up');
		return $rs->url;
	}

	public function deleteUrl($url)
	{
		if (false === ($rs = $this->log->select($url,null,null,'local')))
		{
			return false;
		}
		$this->log->delete($rs->id);
		return true;
	}
}
?>