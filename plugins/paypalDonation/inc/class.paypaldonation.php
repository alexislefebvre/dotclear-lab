<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of paypalDonation, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# 
# -- END LICENSE BLOCK ------------------------------------


class paypalDonation
{
	private $core;
	private $def;
	# List of available butons
	private $buttons = array(
		'small' => 'https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif',
		'large' => 'https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif',
		'cards' => 'https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif'
	);
	# List of available currencies
	private $currencies = array(
		'AUD' => 'Australian Dollars',
		'CAD' => 'Canadian Dollars',
		'EUR' => 'Euros',
		'GBP' => 'Pounds Sterling',
		'JPY' => 'Yen',
		'USD' => 'U.S. Dollars',
		'NZD' => 'New Zealand Dollar',
		'CHF' => 'Swiss Franc',
		'HKD' => 'Hong Kong Dollar',
		'SGD' => 'Singapore Dollar',
		'SEK' => 'Swedish Krona',
		'DKK' => 'Danish Krone',
		'PLN' => 'Polish Zloty',
		'NOK' => 'Norwegian Krone',
		'HUF' => 'Hungarian Forint',
		'CZK' => 'Czech Koruna',
		'ILS' => 'Israeli Shekel',
		'MXN' => 'Mexican Peso'
	);
	# List of available countries languages
	private $countries = array(
		'en_AU' => 'Australia - Australian English',
		'de_DE/AT' => 'Austria - German',
		'nl_NL/BE' => 'Belgium - Dutch',
		'fr_XC' => 'Canada - French',
		'zh_XC' => 'China - Simplified Chinese',
		'fr_FR/FR' => 'France - French',
		'de_DE/DE' => 'Germany - German',
		'it_IT/IT' => 'Italy - Italian',
		'ja_JP/JP' => 'Japan - Japanese',
		'es_XC' => 'Mexico - Spanish',
		'nl_NL/NL' => 'Netherlands - Dutch',
		'pl_PL/PL' => 'Poland - Polish',
		'es_ES/ES' => 'Spain - Spanish',
		'de_DE/CH' => 'Switzerland - German',
		'fr_FR/CH' => 'Switzerland - French',
		'en_US' => 'United States - U.S. English'
	);
	# Editable settings
	private $settings = array(
		'item_name' => '',
		'item_number' => '',
		'amount' => '10'
	);
	
	public function __construct($core)
	{
		$this->core = $core;
		
		$this->def = $core->blog->settings->paypalDonation;
		$this->loadSettings();
	}
	
	private function loadSettings()
	{
		foreach($this->settings as $k => $v)
		{
			if ($this->def->get($k) !== null)
			{
				$this->settings[$k] = $this->def->get($k);
			}
		}
	}
	
	private function checkSetting($k)
	{
		if (null === $this->settings[$k])
		{
			throw new Exception(__('Not an editable setting'));
		}
	}
	
	public function getButtons()
	{
		return $this->buttons;
	}
	
	public function getCurrencies()
	{
		return $this->currencies;
	}
	
	public function getCountries()
	{
		return $this->countries;
	}
	
	public function __set($k,$v)
	{
		$this->checkSetting($k);
		
		$this->settings[$k] = $v;
	}
	
	public function __get($k)
	{
		$this->checkSetting($k);
		
		return $this->settings[$k];
	}
	
	public function build()
	{
		if (!$this->def->business 
		|| $this->def->button_type == 'custom' && !$this->def->button_url)
		{
			throw new Exception(__('Button is not well configured'));
		}
		
		$res =
		"\n<!-- Begin PayPal Donations by http://wpstorm.net/ -->\n".
		'<form action="https://www.paypal.com/cgi-bin/webscr" method="post">'.
		'<div class="paypal-donations">'.
		'<input type="hidden" name="cmd" value="_donations" />'.
		'<input type="hidden" name="business" value="'.$this->def->business.'" />';
		
		if ($this->def->page_style)
		{
			$res .= '<input type="hidden" name="page_style" value="'.
			html::escapeHTML($this->def->page_style).'" />';
		}
		if ($this->def->return_page)
		{
			$url = $this->core->blog->url.$this->core->url->getBase('paypaldonation');
			$res .= '<input type="hidden" name="return" value="'.$url.'" />';
		}
		if ($this->def->currency_code)
		{
			$res .= '<input type="hidden" name="currency_code" value="'.
			$this->def->currency_code.'" />';
		}
		
		if ($this->settings['item_name'])
		{
			$res .= '<input type="hidden" name="item_name" value="'.
			html::escapeHTML($this->settings['item_name']).'" />';
		}
		if ($this->settings['item_number'])
		{
			$res .= '<input type="hidden" name="item_number" value="'.
			html::escapeHTML($this->settings['item_number']).'" />';
		}
		if ($this->settings['amount'])
		{
			$res .= '<input type="hidden" name="amount" value="'.
			$this->settings['amount'].'" />';
		}
		
		$button_url = $this->def->button_url;
		if ($this->def->button_type != 'custom' && !$this->def->button_url)
		{
			$button_url = str_replace('en_US',$this->def->country_code,
			$this->buttons[$this->def->button_type]);
		}
		
		$res .=	
		'<input type="image" src="'.$button_url.'" name="submit" '.
		'alt="PayPal - The safer, easier way to pay online." />'.
		'<img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />'.
		'</div>'.
		'</form>'.
		"\n";
		
		return $res;
	}
	
	public function setPostInfo($post_id,$item_name='',$item_number='',$amount='')
	{
		$post_id = (integer) $post_id;
		$res = array(
			'item_name' => $item_name,
			'item_number' => $item_number,
			'amount' => $amount
		);
		$ppd = base64_encode(serialize($res));
		
		$this->core->meta->setPostMeta($post_id,'paypaldonation',$ppd);
	}
	
	public function getPostInfo($post_id)
	{
		$post_id = (integer) $post_id;
		$res = array();
		$meta_params = array(
			'post_id' => $post_id,
			'meta_type' => 'paypaldonation',
			'limit' => 1
		);
		$ppd = $this->core->meta->getMetadata($meta_params);
		
		if (!$ppd->isEmpty()) {
			$res = @unserialize(@base64_decode($ppd->meta_id));
		}
		if (empty($res)) {
			$res = $this->settings;
			$res['use'] = false;
		}
		else {
			$res['use'] = true;
		}
		return $res;
	}
	
	public function delPostInfo($post_id)
	{
		$post_id = (integer) $post_id;
		$this->core->meta->delPostMeta($post_id,'paypaldonation');
	}
	
	public function parsePostInfo($str,$post)
	{
		return text::deaccent(html::escapeHTML(str_replace(
			array('%T','%I','%B'),
			array($post->post_title,$post->post_id,$this->core->blog->id),
			$str
		)));
	}
}

?>