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

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicHeadContent',array('apercitePublic','publicHeadContent'));
$core->addBehavior('publicFooterContent',array('apercitePublic','publicFooterContent'));

class apercitePublic
{
	public static function publicHeadContent(&$core)
	{
		if (!$core->blog->settings->apercite_enabled) {
			return;
		}
		
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
				
		$size = ($core->blog->settings->apercite_size || $core->blog->settings->apercite_size === null ? explode('x',$core->blog->settings->apercite_size) : array(120,90));
		$javascript = ($core->blog->settings->apercite_javascript || $core->blog->settings->apercite_javascript === null ? 'oui' : 'non');
		$java = ($core->blog->settings->apercite_java || $core->blog->settings->apercite_java === null ? 'oui' : 'non');
		$workers = @unserialize($core->blog->settings->apercite_workers);
		if (!$workers) {
			$workers = array();
		}
		
		echo
		'<style type="text/css">'."\n".
		'@import url('.$url.'/style.css);'."\n".
		'</style>'."\n".
		'<script type="text/javascript" src="'.$url.'/js/apercite.js"></script>'."\n".
		'<script type="text/javascript">'."\n".
		'//<![CDATA['."\n".
		'$(function() {'."\n".
		'$("body").apercite({'."\n".
		'"workers":Array('."\n";
			foreach ($workers as $k=>$v) {
				if ($k != 0) {
					echo ','."\n";
				}
				echo
				'"'.html::escapeHTML($v).'"';
			}
		echo
		"\n".
		'),'."\n".
		'"baseURL":"'.$core->blog->host.'",'."\n".
		'"localLink":"'.($core->blog->settings->apercite_local_link || $core->blog->settings->apercite_local_link === null ? 'oui' : 'non').'",'."\n".
		'"sizeX":'.$size[0].','."\n".
		'"sizeY":'.$size[1].','."\n".
		'"javascript":"'.$javascript.'",'."\n".
		'"java":"'.$java.'"'."\n".
		'});'."\n".
		'});'."\n".
		'//]]>'."\n".
		'</script>'."\n";
	}
	
	public static function publicFooterContent(&$core)
	{
		echo
		'<div id="apercite-thumbnail"><a href="http://www.apercite.fr/">Générateur de miniatures</a></div>'."\n";
	}
}
?>
