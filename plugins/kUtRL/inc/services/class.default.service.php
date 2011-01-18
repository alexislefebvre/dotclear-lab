<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# nb: "default" ne veut pas dire service par d�faut
# mais service simple et rapide configur� par des constantes
# cela permet de configurer ses constante dans le fichier 
# config de Dotclear pour une plateforme compl�te.

class defaultKutrlService extends kutrlServices
{
	public $core;

	public $id = 'default';
	public $name = 'Default';
	public $home = '';

	private $url_api = '';
	private $url_param = '';
	private $url_encode = '';
	public $url_base = '';
	private $url_test = 'http://dotclear.jcdenis.com/go/kUtRL';

	public function __construct($core,$limit_to_blog=true)
	{
		parent::__construct($core,$limit_to_blog);

		$this->url_api = SHORTEN_SERVICE_API;
		$this->url_param = SHORTEN_SERVICE_PARAM;
		$this->url_encode = SHORTEN_SERVICE_ENCODE;
		$this->url_base = SHORTEN_SERVICE_BASE;
		$this->url_min_length = strlen(SHORTEN_SERVICE_BASE) + 2;
	}

	public function settingsForm()
	{
		echo 
		'<p class="form-note">'.
		__('There is nothing to configure for this service.').
		'</p>'.
		'<p>'.__('This service is set to:').'</p>'.
		'<dl>'.
		'<dt>'.__('Service name:').'</dt>'.
		'<dd>'.SHORTEN_SERVICE_NAME.'</dd>'.
		'<dt>'.__('Full API URL:').'</dt>'.
		'<dd>'.SHORTEN_SERVICE_API.'</dd>'.
		'<dt>'.__('Query param:').'</dt>'.
		'<dd>'.SHORTEN_SERVICE_PARAM.'</dd>'.
		'<dt>'.__('Short URL domain:').'</dt>'.
		'<dd>'.SHORTEN_SERVICE_BASE.'</dd>'.
		'<dt>'.__('Encode URL:').'</dt>'.
		'<dd>'.(SHORTEN_SERVICE_ENCODE ? __('yes') : __('no')).'</dd>'.
		'</dl>';
	}

	public function testService()
	{
		$url = $this->url_encode ? urlencode($this->url_test) : $this->url_test;
		$arg = array($this->url_param => urlencode($this->url_test));
		if (!self::post($this->url_api,$arg,true,true))
		{
			$this->error->add(__('Service is unavailable.'));
			return false;
		}
		return true;
	}

	public function createHash($url,$hash=null)
	{
		$enc = $this->url_encode ? urlencode($url) : $url;
		$arg = array($this->url_param => $url);

		if (!($response = self::post($this->url_api,$arg,true,true)))
		{
			$this->error->add(__('Service is unavailable.'));
			return false;
		}

		$rs = new ArrayObject();
		$rs->hash = str_replace($this->url_base,'',$response);
		$rs->url = $url;
		$rs->type = $this->id;

		return $rs;
	}
}
?>