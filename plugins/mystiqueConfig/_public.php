<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Mystique Config plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }
$core->addBehavior('publicBeforeDocument',array('pubWidgetMystique','addTplPath'));

if ($core->blog->settings->system->theme != 'mystique') {
	return;
}

require dirname(__FILE__).'/lib/class.mystique.config.php';

$core->addBehavior('publicHeadContent',array('tplThemeMystique','publicHeadContent'));


$core->url->register('mystiquePreview','mystiquePreview','^mystiquePreview$',array('urlMystique','preview'));

$core->tpl->addValue('mystiqueWidgets',array('tplMystique','advWidgets'));
$core->tpl->addValue('MystiqueMenu',array('tplMystique','menu'));
$core->tpl->addBlock('MystiqueIfHasSidebar',array('tplMystique','sidebarIf'));
$core->tpl->addBlock('MystiqueIf',array('tplMystique','configIf'));

$core->tpl->addValue('MystiqueSidebarLayout',array('tplMystique','sidebarLayout'));
$core->tpl->addValue('MystiquePageLayout',array('tplMystique','pageLayout'));
$core->tpl->addValue('MystiqueColorCSS',array('tplMystique','colorCSS'));
$core->tpl->addValue('MystiqueCategories',array('tplMystique','categories'));
$core->tpl->addValue('MystiqueLang',array('tplMystique','lang'));
$core->tpl->addValue('MystiqueThemeInfo',array('tplMystique','info'));
$core->tpl->addValue('MystiqueTwitterAccount',array('tplMystique','twitterAccount'));

class tplThemeMystique
{
	public static function publicHeadContent($core) {
		echo '<style type="text/css">'."\n".self::mystiqueStyleHelper()."\n</style>\n";
	}
	
	public static function mystiqueStyleHelper() {
		$s = $GLOBALS['core']->blog->settings->mystique->mystique_style;
		$page_width = ($GLOBALS['core']->blog->settings->mystique->mystique_width_type == 'fluid')?100:940;
		$unit = ($GLOBALS['core']->blog->settings->mystique->mystique_width_type == 'fluid') ? '%':'px';
		
		if ($s === null) {
			return;
		}
		
		$s = @unserialize($s);
		if (!is_array($s)) {
			return;
		}
		
		$css = array();
		$sizes = explode(';',$s['column_widths']);
		switch ($GLOBALS['core']->blog->settings->mystique->mystique_layout) {
			case 'col-2-left':
				self::prop($css,'body.col-2-left #primary-content','width',($page_width-$sizes[0]).$unit);
				self::prop($css,'body.col-2-left #primary-content','left',($sizes[0]).$unit);
				self::prop($css,'body.col-2-left #sidebar','width',$sizes[0].$unit);
				self::prop($css,'body.col-2-left #sidebar','left',(-($page_width-$sizes[0])).$unit);
				break;
			case 'col-2-right':
				self::prop($css,'body.col-2-right #primary-content','width',$sizes[0].$unit);
				self::prop($css,'body.col-2-right #primary-content','left',0);
				self::prop($css,'body.col-2-right #sidebar','width',($page_width-$sizes[0]).$unit);
				break;
			case 'col-3':
				self::prop ($css,'body.col-3 #primary-content','width',($sizes[1]-$sizes[0]).$unit);
				self::prop ($css,'body.col-3 #primary-content','left',$sizes[0].$unit);
				self::prop ($css,'body.col-3 #sidebar','width',($page_width-$sizes[1]).$unit);
				self::prop ($css,'body.col-3 #sidebar2','width',$sizes[0].$unit);
				self::prop ($css,'body.col-3 #sidebar2','left',(-$sizes[1]+$sizes[0]).$unit);
				break;
			case 'col-3-left':
				self::prop ($css,'body.col-3-left #primary-content','width',($page_width-$sizes[1]).$unit);
				self::prop ($css,'body.col-3-left #primary-content','left',$sizes[1].$unit);
				self::prop ($css,'body.col-3-left #sidebar','width',$sizes[0].$unit);
				self::prop ($css,'body.col-3-left #sidebar','left',(-($page_width-$sizes[0])).$unit);
				self::prop ($css,'body.col-3-left #sidebar2','width',($sizes[1]-$sizes[0]).$unit);
				self::prop ($css,'body.col-3-left #sidebar2','left',(-($page_width-$sizes[1])+$sizes[0]).$unit);
				break;
			case 'col-3-right':
				self::prop ($css,'body.col-3-right #primary-content','width',$sizes[0].$unit);
				self::prop ($css,'body.col-3-right #sidebar','width',($page_width-$sizes[1]).$unit);
				self::prop ($css,'body.col-3-right #sidebar2','width',($sizes[1]-$sizes[0]).$unit);
				break;
		}
		self::prop($css,'body','background-color',$s['bg_color']);
		
		
		$res = '';
		foreach ($css as $selector => $values) {
			$res .= $selector." {\n";
			foreach ($values as $k => $v) {
				$res .= $k.':'.$v.";\n";
			}
			$res .= "}\n";
		}
		return $res;
	}
	
	protected static function prop(&$css,$selector,$prop,$value)
	{
		if ($value) {
			$css[$selector][$prop] = $value;
		}
	}
}

class pubWidgetMystique
{
	public static function addTplPath($core)
	{
		// TemplateWidgets path definition
		$core->tpl->setPath(
			$core->tpl->getPath(),
			path::real(dirname(__FILE__).'/widgets')
		);
	}
}

class urlMystique 
{
	public static function preview ($args) {
		// Set global context var, so all sidebars will be displayed
		$GLOBALS['_ctx']->mystiquePreviewMode=true;
		$GLOBALS['core']->addBehavior('publicHeadContent',array('urlMystique','addJS'));
		dcUrlHandlers::home($args);
	}
	
	public static function addJS () {
		global $core;
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo '<script type="text/javascript" src="'.$url.'/js/client_config.js"></script>';
	}
}

class tplMystique
{
	public static function info($attr) {
		$info = __('%1$s theme by %2$s adapted for Dotclear by %3$s');
		return sprintf($info,
			'<abbr title="Mystique/1.72">Mystique</abbr>',
			'<a href="http://digitalnature.ro">digitalnature</a>',
			'<a href="http://morefnu.org">Dsls</a>');
	}

	public static function sidebarIf($attr,$content) {
		if (isset($attr['type'])) 
			$type=trim(strtolower($attr['type']));
		else
			$type="nav";
		
		if ($type == 'nav') {
			$cond = '$layout_start != "col-1"';
		} else {
			$cond = '$layout_start == "col-3"';
		}
		$p = '<?php'."\n".
			'$layout_start = substr($core->blog->settings->mystique->mystique_layout,0,5);'."\n".
			'if ($_ctx->mystiquePreviewMode || ($layout_start != "" && ('.$cond.'))):?>'.$content.'<?php endif;?>';
		return $p;
			
	}
	
	public static function configIf($attr,$content) {
		$cond='';
		$prepend='';
		if (isset($attr['twitter_enabled']) && $attr['twitter_enabled']=="1") {
			$cond = '($core->blog->settings->mystique->mystique_twitter_enabled == "1")';
		}
		if (isset($attr['sharethis_enabled']) && $attr['sharethis_enabled']=="1") {
			$cond = '($core->blog->settings->mystique->mystique_sharethis_enabled == "1")';
		}
		if (isset($attr['has_sharethis_module'])) {
			$prepend = 'if (!$_ctx->exists("sharethis_modules"))'.
				'$_ctx->sharethis_modules = unserialize('.
				'$core->blog->settings->mystique->mystique_sharethis_modules);';
			//$module = preg_replace("/[^a-z_]/g","",$attr['has_sharethis_module']);
			$module = $attr['has_sharethis_module'];
			$cond = '($_ctx->sharethis_modules["'.$module.'"] == "1")';
		}
		if ($cond!=='')
			return '<?php '.$prepend.' if('.$cond.'):?>'.$content.'<?php endif; ?>';
			
	}
	public static function twitterAccount($attr) {
		return '<?php echo $core->blog->settings->mystique->mystique_twitter_account; ?>';
	}
	public static function pageLayout($attr) {
		return '<?php echo $core->blog->settings->mystique->mystique_width_type; ?>';
	}
	public static function sidebarLayout($attr) {
		return '<?php echo $core->blog->settings->mystique->mystique_layout; ?>';
	}
	public static function colorCSS($attr) {
		return '<?php echo "color-".$core->blog->settings->mystique->mystique_color_scheme.".css"; ?>';
	}
	
	
	public static function lang($attr, $str_attr) {
		$text = addslashes(__($str_attr));
		
		$variables = array(
			'\$authorLink' => '$_ctx->posts->getAuthorLink()',
			'\$postDate' => '$_ctx->posts->getDate("")',
			'\$postTime' => '$_ctx->posts->getTime("")',
			'\$postCategoryURL' => '\'<a href="\'.$_ctx->posts->getCategoryURL().\'">\'.$_ctx->posts->cat_title.\'</a>\'',
			'\$postRSSLink\(([^)]+)\)' => '\'<a href="\'.$core->blog->url.$core->url->getBase("feed").\'/atom/comments/\'.$_ctx->posts->post_id.\'">$1</a>\'',
			'\$postPingLink\(([^)]+)\)' => '\'<a href="\'.$_ctx->posts->getTrackbackLink().\'">$1</a>\'',
			'\$postCommentLink\(([^)]+)\)' => '\'<a href="#respond">$1</a>\''

			);
		
		foreach ($variables as $k => $v) {
			$text = preg_replace('#'.$k.'#',"'.".$v.".'",$text);
		}

		return "<?php echo '".$text."'; ?>";
		
		
	}
	
	public static function advWidgets($attr)
	{
		$type = isset($attr['type']) ? $attr['type'] : 'nav';
		
		# widgets to disable
		$disable = isset($attr['disable']) ? trim($attr['disable']) : '';
		
		return
		'<?php '.
		"tplMystique::widgetsHandler('".addslashes($type)."','".addslashes($disable)."'); ".
		' ?>';
	}
	
	public static function widgetsHandler($type,$disable='')
	{
		$wtype = 'widgets_'.$type;
		$widgets = $GLOBALS['core']->blog->settings->widgets->{$wtype};
		
		if (!$widgets) { // If widgets value is empty, get defaults
			$widgets = self::defaultWidgets($type);
		} else { // Otherwise, load widgets
			$widgets = dcWidgets::load($widgets);
		}
		
		if ($widgets->isEmpty()) { // Widgets are empty, don't show anything
			return;
		}
		
		$disable = preg_split('/\s*,\s*/',$disable,-1,PREG_SPLIT_NO_EMPTY);
		$disable = array_flip($disable);
		
		foreach ($widgets->elements() as $k => $w)
		{
			if (isset($disable[$w->id()])) {
				continue;
			}
			$content = $w->call($k);
		
			$content=preg_replace("/<h2>/",'<h3 class="title"><span>',$content);
			$content=preg_replace("#</h2>#",'</span></h3><div class="block-div"></div><div class="block-div-arrow"></div>',$content);
			echo '<li class="block">'.$content."</li>";
		}
	}

	public static function menu($attr)
	{
		$block = '<ul>%s</ul>';
		$item = '<li>%s</li>';
		$level = 1;
		if (isset($attr['level'])) {
			$level =  abs((integer) $attr['level'])+0;
		}

		if (isset($attr['block'])) {
			$block = addslashes($attr['block']);
		}
			
		if (isset($attr['item'])) {
			$item = addslashes($attr['item']);
		}
		
		tplMenu::DefineTemplateStyle($attr);
		$a = '';
		$style_widget = false;
		$style_theme = tplMenu::Style($style_widget);
		
		$a = "\$style_theme = array( ";
		foreach ($style_theme as $k => $v) {
			$a .= "'".$k."' => '".addslashes($v)."',";
		}
		$a .= "'end' => '' ); ";

		$res = '<?php ';
		$res .= $a;
		$res .= "echo tplMystique::getMenuList('".$block."','".$item."','".$level."',\$style_theme); ";
		$res .= '?>'."\n";
		
		return $res;
	}
	
	public static function getMenuList($block='<ul>%s</ul>',$item='<li>%s</li>',$level=1,$style_theme=array()) {
		$menu = tplMenu::getList($block,$item,$level,$style_theme);
		
		$menu = preg_replace('#</a>#','</span><span class="pointer"></span></a>',$menu);
		$menu = preg_replace('#(<a )([^<]+>)#','\1 class="fadeThis" \2<span class="title">',$menu);
		
		return $menu;
	
	}
  public static function categories($attr) {
	return '<?php echo tplMystique::getCategories(); ?>';
  }
  
  public static function getCategories() {
		global $core, $_ctx;
		
		$rs = $core->blog->getCategories(array('post_type'=>'post'));
		if ($rs->isEmpty()) {
			return;
		}
		
		$res =
		'<ul class="menuList categories">';
		
		
		$ref_level = $level = $rs->level;
		$first = true;
		while ($rs->fetch())
		{
			$class = ' class="cat-item"';
			$subclass="";
			if (($core->url->type == 'category' && $_ctx->categories instanceof record && $_ctx->categories->cat_id == $rs->cat_id)
			|| ($core->url->type == 'post' && $_ctx->posts instanceof record && $_ctx->posts->cat_id == $rs->cat_id)) {
				$class = ' class="cat-item category-current"';
				$subclass = ' class="category-current"';
			}
			
			if ($rs->level > $level) {
				$res .= str_repeat('<ul class="children"><li'.$subclass.'>',$rs->level - $level);
			} elseif ($rs->level < $level) {
				$res .= str_repeat('</li></ul>',-($rs->level - $level));
			}
			
			if ($rs->level <= $level) {
				$res .= ($first?'':'</li>').'<li '.($level==1?$class:$subclass).'>';
			}
			
			$res .=
			'<a class="fadeThis" href="'.$core->blog->url.$core->url->getBase('category').'/'.
			$rs->cat_url.'"><span class="entry">'.
			html::escapeHTML($rs->cat_title).' <span class="details inline">('.$rs->nb_post.')</span></span></a>'.
			'<a class="rss bubble" href="'.$core->blog->url.$core->url->getBase("feed")."/category/".
				$rs->cat_url.'/atom" title="RSS"></a>';
			
			
			$level = $rs->level;
			$first = false;
		}
		
		$res .= str_repeat('</li></ul>',1-($ref_level - $level));
		
		return $res;
  }

}

$core->tpl->addValue('MystiqueGravatar', array('MystiqueGravatar', 'tplGravatar'));

class MystiqueGravatar {

  const
    URLBASE = 'http://www.gravatar.com/avatar.php?gravatar_id=%s&amp;default=%s&amp;size=%d',
    HTMLTAG = '<img src="%s" class="%s" alt="%s" />',
    DEFAULT_SIZE = '48',
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
?>
