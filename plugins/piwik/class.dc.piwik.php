<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2008 Olivier Meunier and contributors. All rights
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

class dcPiwik extends netHttp
{
	protected $api_path;
	protected $api_token;
	
	public function __construct($uri)
	{
		self::parseServiceURI($uri,$base,$token);
		
		if (!self::readURL($base,$ssl,$host,$port,$path,$user,$pass)) {
			throw new Exception(__('Unable to read Piwik URI.'));
		}
		
		parent::__construct($host,$port,10);
		$this->useSSL($ssl);
		$this->setAuthorization($user,$pass);
		$this->api_path = $path;
		$this->api_token = $token;
	}
	
	public function siteExists($id)
	{
		try
		{
			$sites = $this->getSitesWithAdminAccess();
			foreach ($sites as $v) {
				if ($v['idsite'] == $id) {
					return true;
				}
			}
		}
		catch (Exception $e) {}
		return false;
	}
	
	public function getSitesWithAdminAccess()
	{
		$get = $this->methodCall('SitesManager.getSitesWithAdminAccess');
		$this->get($get['path'],$get['data']);
		$rsp = $this->readResponse();
		$res = array();
		foreach ($rsp as $v) {
			$res[$v['idsite']] = $v;
		}
		return $res;
	}
	
	public function addSite($name,$url)
	{
		$data = array(
			'siteName' => $name,
			'urls' => $url
		);
		$get = $this->methodCall('SitesManager.addSite',$data);
		$this->get($get['path'],$get['data']);
		return $this->readResponse();
	}
	
	protected function methodCall($method,$data=array())
	{
		$data['token_auth'] = $this->api_token;
		$data['module'] = 'API';
		$data['format'] = 'php';
		$data['method'] = $method;
		
		return array('path' => $this->api_path, 'data' => $data);
	}
	
	protected function readResponse()
	{
		$res = $this->getContent();
		$res = @unserialize($res);
		
		if ($res === false) {
			throw new Exception(__('Invalid Piwik Response.'));
		}
		
		if (is_array($res) && !empty($res['result']) && $res['result'] == 'error') {
			$this->piwikError($res['message']);
		}
		return $res;
	}
	
	protected function piwikError($msg)
	{
		throw new Exception(sprintf(__('Piwik returned an error: %s'),strip_tags($msg)));
	}
	
	public static function getServiceURI(&$base,$token)
	{
		if (!preg_match('/^[a-f0-9]{32}$/i',$token)) {
			throw new Exception('Invalid Piwik Token.');
		}
		
		$base = preg_replace('/\?(.*)$/','',$base);
		if (!preg_match('/index\.php$/',$base)) {
			if (!preg_match('/\/$/',$base)) {
				$base .= '/';
			}
			$base .= 'index.php';
		}
		
		return $base.'?token_auth='.$token;
	}
	
	public static function parseServiceURI(&$uri,&$base,&$token)
	{
		$err = new Exception(__('Invalid Service URI.'));
		
		$p = parse_url($uri);
		$p = array_merge(array('scheme'=>'','host'=>'','user'=>'','pass'=>'','path'=>'','query'=>'','fragment'=> ''),
			$p);
		
		if ($p['scheme'] != 'http' && $p['scheme'] != 'https') {
			throw $err;
		}
		
		if (empty($p['query'])) {
			throw $err;
		}
		
		parse_str($p['query'],$query);
		if (empty($query['token_auth'])) {
			throw $err;
		}
		
		$base = $uri;
		$token = $query['token_auth'];
		$uri = self::getServiceURI($base,$token);
	}
	
	public static function getScriptCode($uri,$idsite,$action='')
	{
		self::getServiceURI($uri,'00000000000000000000000000000000');
		$js = dirname($uri).'/piwik.js';
		$php = dirname($uri).'/piwik.php';
		
		return
		"<!-- Piwik -->\n".
		'<script type="text/javascript" src="'.html::escapeURL($js).'"></script>'."\n".
		'<script type="text/javascript">'.
		"//<![CDATA[\n".
		"piwik_tracker_pause = 250;\n".
		"piwik_log('".html::escapeJS($action)."', ".(integer) $idsite.", '".html::escapeJS($php)."');\n".
		"//]]>\n".
		"</script>\n".
		'<noscript><div><img src="'.html::escapeURL($php).'" style="border:0" alt="piwik" width="0" height="0" /></div>'."\n".
		"</noscript>\n".
		"<!-- /Piwik -->\n";
	}
}
?>