<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of walouwalou, a theme for Dotclear 2.
#
# Copyright (c) 2009 Osku
#
# Thanks to Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

//l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

# We need some extra template tags for this theme
$core->tpl->addValue('WalouwalouMenu',array('tplWalou','WalouwalouMenu'));
$core->tpl->addValue('WalouSysHost',array('tplWalou','WalouSysHost'));
$core->tpl->addBlock('WalouGravatarOn',array('tplWalou','WalouGravatarOn'));
$core->addBehavior('publicHeadContent','walou_publicHeadContent');

function walou_publicHeadContent($core)
{
	$style = $core->blog->settings->walou_style;
	if (!preg_match('/^default|pastel|grey|gold$/',$style)) {
		$style = 'default';
	}
     
	$url = $core->blog->settings->themes_url.'/'.$core->blog->settings->theme;
	echo '<style type="text/css">'."\n".
		"@import url(".$url."/"."walou-".$style.".css);\n".
		"</style>\n";
}

class tplWalou
{
	public static function WalouSysHost($attr)
	{
		return '<?php echo rawurlencode(http::getHost()); ?>';
	}
	
	public static function WalouGravatarOn($attr,$content)
	{
		global $core;

		return
		'<?php if ( $core->blog->settings->walou_gravatar_on)  : ?>'.
	          $content.
 	        '<?php endif; ?>';
	}	
	
	public static function WalouwalouMenu($attr,$content)
	{
		$list = !empty($attr['list']) ? $attr['list'] : '';
		$item = !empty($attr['item']) ? $attr['item'] : '';
		$active_item = !empty($attr['active_item']) ? $attr['active_item'] : '';
		
		return "<?php echo tplWalou::WalouwalouMenuHelper('".addslashes($list)."','".addslashes($item)."','".addslashes($active_item)."'); ?>";
	}
	
	public static function WalouwalouMenuHelper($list,$item,$active_item)
	{
		global $core;
		
		$menu = @unserialize($core->blog->settings->walouwalou_nav);
		if (!is_array($menu) || empty($menu)) {
			$menu = array(array(
				'Blog',
				''
			));
		}
		
		$list = $list ? html::decodeEntities($list) : '<ul>%s</ul>';
		$item = $item ? html::decodeEntities($item) : '<li><a href="%s">%s</a></li>';
		$active_item = $active_item ? html::decodeEntities($active_item) : '<li class="nav-active"><a href="%s">%s</a></li>';
		
		$current = -1;
		$current_size = 0;
		
		# Clean urls and find current menu zone
		$self_uri = http::getSelfURI();
		foreach ($menu as $k => &$v)
		{
			$v[1] = preg_match('$^(/|[a-z][a-z0-9.+-]+://)$',$v[1]) ? $v[1] : $core->blog->url.$v[1];
			
			if (strlen($v[1]) > $current_size && preg_match('/^'.preg_quote($v[1],'/').'/',$self_uri)) {
				$current = $k;
				$current_size = strlen($v[1]);
			}
		}
		unset($v);
		
		$res = '';
		foreach ($menu as $i => $v)
		{
			if ($i == $current) {
				$res .= sprintf($active_item,html::escapeHTML($v[1]),html::escapeHTML($v[0]));
			} else {
				$res .= sprintf($item,html::escapeHTML($v[1]),html::escapeHTML($v[0]));
			}
		}
		
		return sprintf($list,$res);
	}
}
?>