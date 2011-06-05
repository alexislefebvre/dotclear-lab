<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of flvplayerconfig, a plugin for Dotclear 2.2
# 
# Copyright (c) 2010 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcflvplayerconfig {
	
	public static function jsLoad() {

		return
		'<script type="text/javascript" src="index.php?pf=flvplayerconfig/js/post.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('jsToolBar.prototype.elements.flvplayerconfig.title',__('FLV Player')).
		"\n//]]>\n".
		"</script>\n";
	}
	
	public static function adminEnabledPlugin($core, $settings) {
		echo '<p><label class="classic">'.
		form::checkbox('flvplayerconfig_enabled','1',$settings->flvplayerconfig->enabled).
		__('Enable flvplayerconfig').'</label></p>'.
		'<p class="form-note">'.$core->plugins->moduleInfo('flvplayerconfig','desc').'</p>';
	}
	
	public static function adminBeforeBlogSettingsUpdate($settings) {
		$settings->addNameSpace('flvplayerconfig');
		$settings->flvplayerconfig->put('enabled',!empty($_POST['flvplayerconfig_enabled']),'boolean');
	}
	
	public static function coreAfterPostContentFormat ($arr) {
		
		$arr['excerpt_xhtml'] = dcflvplayerconfig::patchAfterTranslator($arr['excerpt_xhtml']);
		$arr['content_xhtml'] = dcflvplayerconfig::patchAfterTranslator($arr['content_xhtml']);
		
	}
	
	public static function patchAfterTranslator ($txt) {
		
		//remplacement suite au passage dans le traducteur Wiki
		$txt=str_ireplace("&#8221;",'"',$txt);
		$txt=str_ireplace('<a href="/flvplayer" title="/flvplayer">/flvplayer</a>','[/flvplayer]',$txt);
		$txt=preg_replace('`(.*flvplayer.*)`e',"str_replace(array('<br/>'), '', '\\1')",$txt);
		return $txt;
		
	}
	
	public static function publicBeforeContentFilter ($core, $tag, $arr) {
	
		if( $tag == 'EntryContent' || $tag == 'EntryExcerpt' ) {
			
			$txt = $arr[0];
			
			preg_match_all('`[\<p\>]?\[flvplayer\b([^\]]*)?\][\<\/p\>]?`is',$txt,$out);
		
			foreach ($out[0] as $key => $value) {
				
				$out[1][$key] = str_replace(array('<pre>', '</pre>', '<br'), '', $out[1][$key]);
				preg_match_all('`(\w+)\s*=\s*([^;]*)`is',$out[1][$key],$tinout);
				
				// config par défaut
				$values = unserialize($GLOBALS['core']->blog->settings->themes->flvplayer_style);
				
				foreach ($tinout[1] as $key2 => $value2)
					$values[$value2] = $tinout[2][$key2];
				
				$player = '<div>';
				if( $values['align'] == 'center' ) $player = '<div style="text-align: center;">';
				if( $values['align'] == 'left' ) $player = '<div style="float: left; margin: 0 1em 1em 0;">';
				if( $values['align'] == 'right' ) $player = '<div style="float: right; margin: 0 0 1em 1em;">';
				
				$player .= '<object type="application/x-shockwave-flash" data="?pf=player_flv.swf" width="'.$values['width'].'" height="'.$values['height'].'">
				<param name="movie" value="?pf=player_flv.swf">
				<param name="wmode" value="transparent">
				<param name="allowFullScreen" value="true">
				<param name="FlashVars" value="';
				
				foreach ($values as $key2 => $value2)
					$player .= $key2.'='.$value2.'&amp;';
				
				$player .= '">';
				
				$txt = str_replace($out[0][$key], $player, $txt);
				
			}
			
			$txt=preg_replace('`\[\/flvplayer\]`is','</object>
				</div>',$txt);
				
			$arr[0] = $txt;
			
		}
		
	}
}