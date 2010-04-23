<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shareOn, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

require_once dirname(__FILE__).'/_widgets.php';

$core->addBehavior('publicHeadContent',
	array('shareOnPublicBehavior','publicHeadContent')
);
$core->addBehavior('publicEntryBeforeContent',
	array('shareOnPublicBehavior','publicEntryBeforeContent')
);
$core->addBehavior('publicEntryAfterContent',
	array('shareOnPublicBehavior','publicEntryAfterContent')
);

if (!$core->blog->settings->shareOn_active)
{
	$core->tpl->addValue('shareOnButton',array('tplShareOn','disable'));
}
else
{
	$core->tpl->addValue('shareOnButton',array('tplShareOn','button'));
}

class shareOnPublicBehavior
{
	public static function publicHeadContent($core)
	{
		$s = $core->blog->settings->shareOn_style;
		if (!$s) return;

		echo 
		"\n<!-- CSS for shareOn --> \n".
		"<style type=\"text/css\"> \n".
		html::escapeHTML($s)."\n".
		"</style>\n";
	}

	public static function publicEntryBeforeContent($core,$_ctx)
	{
		return self::publicEntryContent($core,$_ctx,'before');
	}

	public static function publicEntryAfterContent($core,$_ctx)
	{
		return self::publicEntryContent($core,$_ctx,'after');
	}

	protected static function publicEntryContent($core,$_ctx,$place)
	{
		if (!$core->blog->settings->shareOn_active
		 || empty($core->shareOnButtons)) return;

		if ('home.html' == $_ctx->current_tpl && $place == $core->blog->settings->shareOn_home_place 
		 || 'category.html' == $_ctx->current_tpl && $place == $core->blog->settings->shareOn_cat_place 
		 || 'tag.html' == $_ctx->current_tpl && $place == $core->blog->settings->shareOn_tag_place 
		 || 'post.html' == $_ctx->current_tpl && $place == $core->blog->settings->shareOn_post_place 
		) {
			require_once dirname(__FILE__).'/inc/class.shareon.php';

			$li = '';	
			foreach($core->shareOnButtons as $button_id => $button)
			{
				$o = new $button($core);
				$res = $o->generateHTMLButton($_ctx->posts->getURL(),$_ctx->posts->post_title);

				if (!empty($res)) $li .= '<li class="button-'.$button_id.'">'.$res.'</li>';
			}

			if (empty($li)) return;
			
			$title = !$core->blog->settings->shareOn_title ?
				'' : '<h3>'.$core->blog->settings->shareOn_title.'</h3>';

			echo '<div class="shareonentry">'.$title.'<ul>'.$li.'</ul></div>';
		}
	}
}

class tplShareOn
{
	public static function disable($a,$b=null)
	{
		return '';
	}

	public static function button($attr)
	{
		global $core;

		if (empty($attr['button'])) return;

		$small = '';
		if (isset($attr['small']) && $attr['small'] == 1) { $small = 'true'; }
		if (isset($attr['small']) && $attr['small'] == 0) { $small = 'false'; }

		$url = empty($attr['url']) ?
			'$_ctx->posts->getURL()' : "'".$attr['url']."'";

		$title = empty($attr['title']) ?
			'$_ctx->posts->post_title' : "'".$attr['title']."'";

		require_once dirname(__FILE__).'/inc/class.shareon.php';

		return 
		"<?php \n".
		"if (\$core->blog->settings->shareOn_active ".
		" && !empty(\$core->shareOnButtons) ".
		" && isset(\$core->shareOnButtons['".$attr['button']."']) ".
		" && \$_ctx->exists('posts')) { \n".
		"  \$shareOnButton = new \$core->shareOnButtons['".$attr['button']."'](\$core); \n".
		(!empty($small) ? "  \$shareOnButton->_small = ".$small."; \n" : '').
		"  echo \$shareOnButton->generateHTMLButton(".$url.",".$title."); \n".
		"} \n".
		"?> \n";
	}
}
?>