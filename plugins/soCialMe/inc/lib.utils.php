<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class soCialMeUtils
{
	# Admin URLs
	public static function link($amp,$page='',$part='',$lib='',$more='')
	{
		if (!defined('DC_CONTEXT_ADMIN')) return '';
		
		$url = DC_ADMIN_URL.'plugin.php?p=soCialMe&page=%s&part=%s&lib=%s%s';
		if ($amp) {
			$url = str_replace('&','&amp;',$url);
		}
		return sprintf($url,$page,$part,$lib,$more);
	}
	
	# Top admin menu
	public static function top($page='',$more_head='')
	{
		if (!defined('DC_CONTEXT_ADMIN')) return '';
		
		$title = !empty($page['title']) ? ' '.$page['title'] : '';
		$menu = !empty($page['parts']) ? ' &rsaquo; '.self::menu($page['parts']) : '';
		$section = !empty($_REQUEST['section']) ? $_REQUEST['section'] : '';
		
		return 
		'<html><head><title>'.__('Social').$title.'</title>'.
		'<style type="text/css" media="screen">'."\n".
		"@import url(index.php?pf=soCialMe/style.css);\n".
		"</style>\n".
		dcPage::jsLoad('index.php?pf=soCialMe/js/main.js').
		'<script type="text/javascript">'."\n//<![CDATA[\n".
		dcPage::jsVar('jcToolsBox.prototype.text_wait',__('Please wait')).
		dcPage::jsVar('jcToolsBox.prototype.section',$section).
		"\n//]]>\n</script>\n".
		$more_head.
		'</head>'.
		'<body>'.
		'<div class="two-cols"><div class="col left"><h2>'.
		$GLOBALS['core']->blog->name.' &rsaquo; <a href="'.self::link(1,'').'" title="'.__('Social home').'">'.__('Social').'</a>'.
		(!empty($title) ? ' &rsaquo;'.$title : '').
		$menu.'</h2></div></div><div class="clear">';
	}
	
	# Top admin sub menu
	private static function menu($parts)
	{
		$page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : '';
		$part = !empty($_REQUEST['part']) ? $_REQUEST['part'] : key($parts);
		$res = '';
		
		if (isset($parts[$part]))
		{
			$res = $parts[$part].' ';
		}
		$res .= '</h2></div><div class="col right"><h2>';
		
		foreach($parts as $k => $v)
		{
			//if ($k == $current) continue; //remove current page from menu
			
			$res .= '<a class="button" href="'.soCialMeUtils::link(1,$page,$k).'">'.$v.'</a> ';
		}
		return $res;
	}
	
	# Javacsript for dragsort on multiple tables
	public static function multiDragsortScript($things)
	{
		$i = 0;
		$script = $script_var = $script_drag = $script_sort = '';
		foreach($things as $thing => $plop)
		{
			$k = $i == 0 ? '' : (string) $i; //first dragsort hate another name
			$i++;
			
			$script_var .= "var dragsort".$k." = ToolMan.dragsort();\n";
			$script_drag .= 'dragsort'.$k.'.makeTableSortable($("#priority-list-'.$thing.'").get(0),dotclear.sortable.setHandle,dotclear.sortable.saveOrder'.$k.');'."\n";
			$script_sort .= ',saveOrder'.$k.': function(item) {	var group = item.toolManDragGroup; var order = document.getElementById('."'".'js_orders_'.$thing."'".'); group.register('."'".'dragend'."'".', function() { order.value = '."''".'; items = item.parentNode.getElementsByTagName('."'".'tr'."'".'); for (var i=0; i<items.length; i++) { order.value += items[i].id.substr(3)+'."'".','."'".'; } }); }'."\n";
		}

		return 
		'<script type="text/javascript">'.
		"//<![CDATA[\n".
		$script_var.'$(function() { '.$script_drag.' }); dotclear.sortable = { setHandle: function(item) { var handle = $(item).find('."'".'td.handle'."'".').get(0); while (handle.firstChild) { handle.removeChild(handle.firstChild); } item.toolManDragGroup.setHandle(handle); handle.className = handle.className+'."'".' handler'."'".'; }'.$script_sort.'};'.
		"\n//]]>\n</script>\n";
	}
	
	# Reduce link
	public static function reduceURL($url,$custom=null)
	{
		global $core;
		$shorturl = false;
		
		# Reduce URL using plugin kUtRL
		try
		{
			if ($core->plugins->moduleExists('kUtRL') && 
				$core->blog->settings->kUtRL->kutrl_active && 
				$core->blog->settings->kUtRL->kutrl_plugin_service &&
				!empty($core->kutrlServices[$core->blog->settings->kUtRL->kutrl_plugin_service]))
			{
				$kut = new $core->kutrlServices[$core->blog->settings->kUtRL->kutrl_plugin_service]($core,true);
				$prefix = !empty($custom) && $kut->allow_customized_hash ? $custom : null;
				$rs = $kut->hash($url,$prefix);
				$shorturl = $kut->url_base.$rs->hash;
			}
		}
		catch (Exception $e) {}
		
		# Reduce URL using quick service
		if (!$shorturl)
		{
			try
			{
				# Config
				$enc = SHORTEN_SERVICE_ENCODE ? urlencode($url) : $url;
				$api = SHORTEN_SERVICE_API;
				$path = '';
				$data = array(SHORTEN_SERVICE_PARAM => $enc);
				
				# Send request
				$client = netHttp::initClient($api,$path);
				$client->setUserAgent('soCialMeDotclear');
				$client->setPersistReferers(false);
				$client->get($path,$data);
				
				# Receive short url
				if ($client->getStatus() == 200)
				{
					$shorturl = (string) $client->getContent();
					$shorturl = SHORTEN_SERVICE_BASE.str_replace(SHORTEN_SERVICE_BASE,'',$shorturl);
				}
			}
			catch (Exception $e) {}
		}
		
		return $shorturl ? $shorturl : $url;
	}
	
	# Cut on word message $str into sub messages less than $len chars long
	public static function splitString($str,$len=140)
	{
		$split = array(0=>'');
		$j = 0;
		if (strlen($str) < $len)
		{
			$words = explode(' ',$str);
			for($i = 0; $i < count($words); $i++)
			{
				$s = empty($split[$j]) ? '' : ' ';

				$next_len = $split[$j].$s.$words[$i];
				if (strlen($next_len) < $len)
				{
					$split[$j] .= $s.$words[$i];
				}
				else
				{
					$j++;
					$split[$j] = $words[$i];
				}
			}
		}
		else
		{
			$split[0] = $str;
		}
		return $split;
	}
	
	# Check related plugin version
	public static function checkPlugin($n,$v)
	{
		global $core;
		return $core->plugins->moduleExists($n) && version_compare(str_replace("-r","-p",(string) $core->plugins->moduleInfo($n,'version')),$v,'>=');
	}
	
	# Shortcut for a standard link with an image
	public static function easyLink($href,$title,$src,$type='sharer')
	{
		if ($type == 'sharer') {
			$title = sprintf(__('Share on %s'),$title);
		}
		elseif ($type == 'profil') {
			$title = sprintf(__('View my profil on %s'),$title);
		}
		
		return 
		'<a href="'.$href.'" title="'.$title.'">'.
		'<img src="'.$src.'" alt="'.$title.'" />'.
		'</a>';
	}
	
	//not always so speed, bug on some case (if there's an onload event)
	public static function preloadBox($content)
	{
		if (!isset($GLOBALS['soCialMePreloadBoxNumber'])) $GLOBALS['soCialMePreloadBoxNumber'] = 0;
		
		$GLOBALS['soCialMePreloadBoxNumber'] += 1;
		
		return
		'<div id="social-preloadbox'.$GLOBALS['soCialMePreloadBoxNumber'].'"></div>'.
		'<script type="text/javascript">'.
		"\n//<![CDATA[ \n".
		'$(\'#social-preloadbox'.$GLOBALS['soCialMePreloadBoxNumber'].'\').hide(); '.
		'$(document).ready(function(){ '.
		'$(\'#social-preloadbox'.$GLOBALS['soCialMePreloadBoxNumber'].'\').show().replaceWith($(\''.$content.'\')); '.
		"}); ".
		"\n//]]> \n".
		'</script> ';
	}
	
	# Execute services actions like "playXxxScript"
	public static function publicScripts($core,$ns)
	{
		if (!$core->blog->settings->{$ns}->active){return;}
		
		$res = '';
		$class = new $ns($core);
		
		$things = $class->things();
		foreach($things as $thing => $plop)
		{
			$available[$thing] = $class->can($thing.'Script');
		}
		
		$s_order = $class->fillOrder($available);
		
		foreach($s_order as $thing => $services)
		{
			if (empty($available[$thing])) continue;
			
			foreach($services as $id)
			{
				if (!in_array($id,$available[$thing])) continue;
				
				try {
					$tmp = $class->play($id,$thing,'Script');
					$res .= $tmp;
				}
				catch (Exception $e) { }
			}
		}
		return $res;
	}
	
	# clean the $record array passed to the play() func.
	public static function fillPlayRecord($partial)
	{
		if (!is_array($partial)) $partial = array();
		
		$full = array(
			'url' => '',
			'shorturl' => '',
			'title' => '',
			'excerpt' => '',
			'content' => '',
			'author' => '',
			'category' => '',
			'tags' => '',
			'type' => '',
		);
		
		if (!empty($partial['url']) && empty($partial['shorturl'])) {
			$partial['shorturl'] = soCialMeUtils::reduceURL($partial['url']);
		}
		
		return new arrayObject(array_merge($full,$partial));
	}
}
?>