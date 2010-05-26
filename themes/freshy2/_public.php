<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Freshy2, a theme for Dotclear.
# Original WP Theme from Julien de Luca
# (http://www.jide.fr/francais/)
#
# Copyright (c) 2008-2009
# Bruno Hondelatte dsls@morefnu.org
# Pierre Van Glabeke contact@brol.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
$core->tpl->addValue('Freshy2StyleSheet',array('tplFreshy2Theme','FreshyStyleSheet'));
$core->tpl->addValue('Freshy2LayoutClass',array('tplFreshy2Theme','FreshyLayoutClass'));
$core->tpl->addBlock('Freshy2IfHasSidebar',array('tplFreshy2Theme','FreshyIfHasSidebar'));
$core->tpl->addBlock('Freshy2IfHasSidebarContent',array('tplFreshy2Theme','FreshyIfHasSidebarContent'));
$core->addBehavior('publicHeadContent',array('tplFreshy2Theme','publicHeadContent'));
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

class tplFreshy2Theme
{
	public static $settings;
	
	public static function initSettings() {
		global $core;
		if (!version_compare(DC_VERSION,'2.2-alpha','<'))
			self::$settings =& $core->blog->settings->freshy2;
		else
			self::$settings =& $core->blog->settings;
	}

	public static function FreshyStyleSheet($attr) {
		return "style.css";
	}

	public static function FreshyLayoutClass($attr) {
		$p = '<?php '."\n";
		$p .= 'if (tplFreshy2Theme::$settings->freshy2_sidebar_right != "none")'."\n";
		$p .= '  echo "sidebar_right ";'."\n";
		$p .= 'if (tplFreshy2Theme::$settings->freshy2_sidebar_left != "none")'."\n";
		$p .= '  echo "sidebar_left";'."\n";
		$p .= '?>'."\n";
		return $p;
	}

	public static function FreshyIfHasSidebar($attr,$content) {
		if (isset($attr['pos'])) 
			$pos=trim(strtolower($attr['pos']));
		else
			$pos="right";
		if ($pos == 'both') {
			return '<?php if ((tplFreshy2Theme::$settings->freshy2_sidebar_left != "none") '.
				'&& (tplFreshy2Theme::$settings->freshy2_sidebar_right != "none")): ?>'."\n".
				$content."\n".
				'<?php endif; ?>'."\n";
		} else {
			$setting = "freshy2_sidebar_".$pos;
			return '<?php if (tplFreshy2Theme::$settings->'.$setting.' != "none"): ?>'."\n".
				$content."\n".
				'<?php endif; ?>'."\n";
		}
	}
	public static function FreshyIfHasSidebarContent($attr,$content) {
		if (isset($attr['pos'])) 
			$pos=trim(strtolower($attr['pos']));
		else
			$pos="right";
		$setting = "freshy2_sidebar_".$pos;
		if (isset($attr['value'])) 
			$value=trim(strtolower($attr['value']));
		else
			$value="nav";
		return '<?php if (tplFreshy2Theme::$settings->'.$setting.' == "'.$value.'"): ?>'."\n".
			$content."\n".
			'<?php endif; ?>'."\n";
	}

	public static function publicHeadContent($core)
	{
		$cust = self::$settings->freshy2_custom;
		$topimg = self::$settings->freshy2_top_image;
		$theme_url= self::$settings->themes_url."/".self::$settings->theme;

		$css_content='';
		if (empty($cust) === false && $cust !== 'default') {
			$css_content .= '@import url('.
			$theme_url.'/'.$cust.");\n";
		}
		if ($topimg !== null && $topimg !== 'default') {
			$css_content .= "#header_image {\n".
				"background-image:url(".$theme_url.'/images/headers/'.$topimg.");\n".
				"}\n";
		}
		if ($css_content != "") {
			echo '<style type="text/css" media="screen">'."\n".
				$css_content.
				"</style>\n";
		}
	}
}
$core->tpl->addValue('gravatar', array('gravatar', 'tplGravatar'));

class gravatar {

  const
    URLBASE = 'http://www.gravatar.com/avatar.php?gravatar_id=%s&amp;default=%s&amp;size=%d',
    HTMLTAG = '<img src="%s" class="%s" alt="%s" />',
    DEFAULT_SIZE = '40',
    DEFAULT_CLASS = 'gravatar_img',
    DEFAULT_ALT = 'Gravatar de %s';

  public static function tplGravatar($attr)
  {
    $md5mail = '\'.md5(strtolower($_ctx->comments->getEmail(false))).\'';
    $size    = array_key_exists('size',   $attr) ? $attr['size']   : self::DEFAULT_SIZE;
    $class   = array_key_exists('class',  $attr) ? $attr['class']  : self::DEFAULT_CLASS;
    $alttxt  = array_key_exists('alt',    $attr) ? $attr['alt']    : self::DEFAULT_ALT;
    $altimg  = array_key_exists('altimg', $attr) ? $attr['altimg'] : '';
    $gurl    = sprintf(self::URLBASE,
                       $md5mail, urlencode($altimg), $size);
    $gtag    = sprintf(self::HTMLTAG,
                       $gurl, $class, preg_match("/%s/i", $alttxt) ?
                                      sprintf($alttxt, '\'.$_ctx->comments->comment_author.\'') : $alttxt);
    return '<?php echo \'' . $gtag . '\'; ?>';
  }

}

tplFreshy2Theme::initSettings();
?>
