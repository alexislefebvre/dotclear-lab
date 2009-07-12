<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Apercite, a plugin for Dotclear.
# 
# Copyright (c) 2009 Francis Besset
# francis.besset@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class aperciteAPI
{
	const apercite_uri = 'www.apercite.fr';
	const apercite_port = 80;
	const apercite_path_update = '/api/maj-apercite/%1$s/%2$s/adresse/%3$s/%4$s/%5$s';
	
	
	public static function doUpdate($login, $apiKey, $javascript, $java, $uri)
	{
		$javascript = ($javascript || $javascript === null ? 'oui' : 'non');
		$java = ($java || $java === null ? 'oui' : 'non');
		
		$path = sprintf(self::apercite_path_update, $login, $apiKey, $javascript, $java, $uri);
		
		$o = new netHttp(self::apercite_uri,self::apercite_port);
		$o->setUserAgent('Clearbricks HTTP Client - DotClear 2 - Apercite 1.0.1');
		return $o->get($path);
	}
}

class aperciteBehaviors
{
	public static function aperciteForm()
	{
		echo '<h3 class="apercite-services">'.__('Apercite').'</h3>';
		
		$core =& $GLOBALS['core'];
		if ($core->blog->settings->apercite_enabled) {
			echo
			'<p class="apercite-services"><label class="classic">'.
			form::checkbox('apercite_update',1).' '.__('Apercite update thumbnails').
			'</label></p>';
		}
		else {
			echo
			'<p class="apercite-services">'.__('Apercite disabled').'</p>';
		}
	}
	
	public static function doUpdate(&$cur)
	{
		if (empty($_POST['apercite_update'])) { return; }
		
		$core =& $GLOBALS['core'];
		if (!$core->blog->settings->apercite_enabled) { return; }
		
		$apercite_login = $core->blog->settings->apercite_login;
		$apercite_api_key = $core->blog->settings->apercite_api_key;
		if (empty($apercite_login) OR empty($apercite_api_key)) { return; }
		
		$post = $cur->getField('post_excerpt_xhtml').$cur->getField('post_content_xhtml');
		if(preg_match_all('#<a(?:.*)href="((?:(?:https?://)?(?:(?:[-_a-zA-Z0-9]+\.)*(?:[-a-zA-Z0-9]{1,63})\.(?:[a-zA-Z]{2,4})|(?:(?:[0-1]|[0-9]{2}|1[0-9]{2}|2(?:[0-4][0-9]|5[0-5]))\.){3}(?:[0-1]|[0-9]{2}|1[0-9]{2}|2(?:[0-4][0-9]|5[0-5]))))?(?:\:[0-9]{0,5})?(?:/(?:[^"])*)?)#', $post, $match)) {
			if (isset($match[1]) && is_array($match[1])) {
				$tab_uri = array();
				foreach ($match[1] as $k=>$v) {
					if (!empty($v)) {
						if ($v{0} == '/') {
							$v = substr($core->blog->url, 0, -1).$v;
						}
						
						if (!in_array($v, $tab_uri)) {
							$tab_uri[] = $v;
							
							aperciteAPI::doUpdate(
								$core->blog->settings->apercite_login,
								$core->blog->settings->apercite_api_key,
								$core->blog->settings->apercite_javascript,
								$core->blog->settings->apercite_java,
								$v
							);
						}
					}
				}
			}
		}
	}
}
?>