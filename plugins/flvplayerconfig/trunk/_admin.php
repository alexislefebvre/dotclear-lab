<?php if (!defined('DC_CONTEXT_ADMIN')) {return;}
/**
 * @author kévin lepeltier [lipki] (kevin@lepeltier.info)
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 */

$core->addBehavior('adminPostHeaders',array('dcflvplayerconfig','jsLoad'));
$core->addBehavior('adminPageHeaders',array('dcflvplayerconfig','jsLoad'));
$core->addBehavior('adminRelatedHeaders',array('dcflvplayerconfig','jsLoad'));
$core->addBehavior('adminDashboardHeaders',array('dcflvplayerconfig','jsLoad'));

$core->addBehavior('coreAfterPostContentFormat',array('dcflvplayerconfig','flvPlayerConfig'));

$_menu['Plugins']->addItem(
	__('flvPlayer config'),
	'plugin.php?p=flvplayerconfig',
	'index.php?pf=flvplayerconfig/icon.png',
	preg_match('/plugin.php\?p=flvplayerconfig(&.*)?$/', $_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));


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
	
	public static function flvPlayerConfig ($arr) {
	
		$arr['excerpt_xhtml'] = dcflvplayerconfig::dcAddFlvPlayer($arr['excerpt_xhtml']);
		$arr['content_xhtml'] = dcflvplayerconfig::dcAddFlvPlayer($arr['content_xhtml']);
		
	}
	
	public static function dcAddFlvPlayer($txt) {
		
		//remplacement suite au passage dans le traducteur Wiki
		$txt=str_ireplace('<a href="/flvplayer" title="/flvplayer">/flvplayer</a>','[/flvplayer]',$txt);
		$txt=preg_replace('`<a href="flvplayer[^\"]*" title="flvplayer([^\"]*)([^>]*)>(.*?)<\/a>`is','[flvplayer$1]',$txt);
		$txt=preg_replace('`(.*flvplayer.*)`e',"str_replace(array('<p>', '</p>', '<br/>'), '', '\\1')",$txt);
		
		//conversion HTML
		
		preg_match_all('`[\<p\>]?\[flvplayer\b([^\]]*)?\][\<\/p\>]?`is',$txt,$out);
		
		foreach ($out[0] as $key => $value) {
			
			$out[1][$key] = str_replace(array('<pre>', '</pre>', '<br'), '', $out[1][$key]);
			preg_match_all('`(\w+)\s*=\s*(\S+)`is',$out[1][$key],$tinout);
			
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
			
		return $txt;
		
	}
}