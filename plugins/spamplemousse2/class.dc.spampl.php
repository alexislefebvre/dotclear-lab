<?php
# ***** BEGIN LICENSE BLOCK *****
# This is spamplemousse, a plugin for DotClear. 
# Copyright (c) 2005 Benoit CLERC, Alain Vagner and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

class dcSpamFilter
{
	private $core;
	private $con;
	private $table;
	
	private $remote_dnsbl = array(
		//'dnsbl.sorbs.net',
		'sbl-xbl.spamhaus.org',
		'bl.blbl.org',
		'bsb.spamlookup.net'
	);
	
	private $remote_dnsbl_submit = array(
		'http://blbl.org/add?data=%s'
	);
	
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->con =& $core->con;
		$this->table = $core->prefix.'spamrule';
	}
	
	public function getRules($type)
	{
		$strReq = 'SELECT rule_id, blog_id, rule_content '.
				'FROM '.$this->table.' '.
				"WHERE rule_type = '".$this->con->escapeStr($type)."' ".
				"AND blog_id = '".$this->con->escapeStr($this->core->blog->id)."' ".
				"OR blog_id IS NULL ".
				'ORDER BY blog_id ASC, rule_content ASC ';
		
		return $this->con->select($strReq);
	}
	
	public function addRule($type,$content,$general=false)
	{
		$strReq = 'SELECT rule_id FROM '.$this->table.' '.
				"WHERE rule_type = '".$this->con->escapeStr($type)."' ".
				"AND rule_content = '".$this->con->escapeStr($content)."' ";
		$rs = $this->con->select($strReq);
		
		if (!$rs->isEmpty()) {
			throw new Exception(__('This word exists'));
		}
		
		$rs = $this->con->select('SELECT MAX(rule_id) FROM '.$this->table);
		$id = (integer) $rs->f(0) + 1;
		
		$cur = $this->con->openCursor($this->table);
		$cur->rule_id = $id;
		$cur->rule_type = (string) $type;
		$cur->rule_content = (string) $content;
		
		if ($general && $this->core->auth->isSuperAdmin()) {
			$cur->blog_id = null;
		} else {
			$cur->blog_id = $this->core->blog->id;
		}
		
		$cur->insert();
	}
	
	public function removeRule($ids)
	{
		$strReq = 'DELETE FROM '.$this->table.' ';
		
		if (is_array($ids)) {
			foreach ($ids as $i => $v) {
				$ids[$i] = (integer) $v;
			}
			$strReq .= 'WHERE rule_id IN ('.implode(',',$ids).') ';
		} else {
			$ids = (integer) $ids;
			$strReq .= 'WHERE rule_id = '.$ids.' ';
		}
		
		if (!$this->core->auth->isSuperAdmin()) {
			$strReq .= "AND blog_id = '".$this->con->escapeStr($this->core->blog->id)."' ";
		}
		
		$this->con->execute($strReq);
	}
	
	public function countJunkComments()
	{
		$params = array();
		$params['comment_status'] = -2;
		
		$rs = $this->core->blog->getComments($params,true);
		return $rs->f(0);
	}
	
	public function countPublishedComments()
	{
		$params = array();
		$params['comment_status'] = 1;
		
		$rs = $this->core->blog->getComments($params,true);
		return $rs->f(0);
	}
	
	public function checkSpamWords($str)
	{
		$rs = $this->getRules('word');
		
		while ($rs->fetch()) {
			$word = $rs->rule_content;
			
			if (substr($word,0,1) == '/' && substr($word,-1,1) == '/') {
				$reg = substr(substr($word,1),0,-1);
			} else {
				$reg = preg_quote($word);
				$reg = '(^|\s+|>|<)'.$reg.'(>|<|\s+|$)';
			}
			
			if (preg_match('/'.$reg.'/msiu',$str)) {
				return true;
			}
		}
		
		return false;
	}
	
	public function getUserCode()
	{
		$code =
		bin2hex(pack('a32',$this->core->auth->userID())).
		pack('H*',crypt::hmac(DC_MASTER_KEY,$this->core->auth->getInfo('user_pwd')));
		
		return bin2hex($code);
	}
	
	public function checkUserCode($code)
	{
		$code = pack('H*',$code);
		
		$user_id = trim(@pack('H64',substr($code,0,64)));
		$pwd = @unpack('H40hex',substr($code,64,40));
		
		if ($user_id === false || $pwd === false) {
			return false;
		}
		
		$pwd = $pwd['hex'];
		
		$strReq = 'SELECT user_id, user_pwd '.
				'FROM '.$this->core->prefix.'user '.
				"WHERE user_id = '".$this->con->escapeStr($user_id)."' ";
		
		$rs = $this->con->select($strReq);
		
		if ($rs->isEmpty()) {
			return false;
		}
		
		if (crypt::hmac(DC_MASTER_KEY,$rs->user_pwd) != $pwd) {
			return false;
		}
		
		return $rs->user_id;
	}
	
	public function dnsblLookup($ip)
	{
		$revIp = implode('.',array_reverse(explode('.',$ip)));
		
		if (!preg_match('/^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$/', $ip)) {
			return false;
		}
		
		foreach ($this->remote_dnsbl as $rbl) {
			$host = $revIp.'.'.$rbl;
			if (gethostbyname($host) != $host) {
				return $rbl;
			}
		}
		
		return false;
	}
	
	public function dnsblSubmit($ip)
	{
		if (!ini_get('allow_url_fopen')) {
			return false;
		}
		
		foreach ($this->remote_dnsbl_submit as $submit) {
			$url = sprintf($submit,$ip);
			@fopen($url,'rb');
		}
	}
	
	public function defaultWordsList()
	{
		$words = array(
			'/-credit(\s+|$)/',
			'/-digest(\s+|$)/',
			'/-loan(\s+|$)/',
			'/-online(\s+|$)/',
			'4u',
			'adipex',
			'advicer',
			'amazing',
			'ambien',
			'astonishing',
			'baccarat',
			'baccarrat',
			'blackjack',
			'bllogspot',
			'bolobomb',
			'booker',
			'byob',
			'car-rental-e-site',
			'car-rentals-e-site',
			'carisoprodol',
			'cash',
			'casino',
			'casinos',
			'chatroom',
			'cialis',
			'craps',
			'credit-card',
			'credit-report-4u',
			'cwas',
			'cyclen',
			'cyclobenzaprine',
			'dating-e-site',
			'day-trading',
			'debt',
			'digest-',
			'discount',
			'discreetordering',
			'duty-free',
			'dutyfree',
			'enjoyed',
			'estate',
			'favourits',
			'fioricet',
			'flowers-leading-site',
			'freenet',
			'freenet-shopping',
			'funny',
			'gambling',
			'gamias',
			'health-insurancedeals-4u',
			'helpful',
			'holdem',
			'holdempoker',
			'holdemsoftware',
			'holdemtexasturbowilson',
			'hotel-dealse-site',
			'hotele-site',
			'hotelse-site',
			'husband',
			'incest',
			'insurance-quotesdeals-4u',
			'insurancedeals-4u',
			'interesting',
			'jrcreations',
			'levitra',
			'macinstruct',
			'mortgage',
			'nice site',
			'online-gambling',
			'onlinegambling-4u',
			'ottawavalleyag',
			'ownsthis',
			'palm-texas-holdem-game',
			'paxil',
			'pharmacy',
			'phentermine',
			'pills',
			'poker',
			'poker-chip',
			'poze',
			'prescription',
			'rarehomes',
			'refund',
			'rental-car-e-site',
			'roulette',
			'shemale',
			'slot',
			'slot-machine',
			'soma',
			'taboo',
			'tamiflu',
			'teen',
			'texas-holdem',
			'thorcarlson',
			'top-e-site',
			'top-site',
			'tramadol',
			'trim-spa',
			'ultram',
			'v1h',
			'vacuum',
			'valeofglamorganconservatives',
			'viagra',
			'vicodin',
			'vioxx',
			'xanax',
			'zolus'
		);
		
		foreach ($words as $w) {
			try {
				$this->addRule('word',$w,true);
			} catch (Exception $e) {}
		}
	}
}
?>