<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Noviny, a Dotclear 2 theme.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

# We need some extra template tags for this theme
$core->tpl->addValue('NovinyMenu',array('tplNoviny','NovinyMenu'));

# We don't want all tags page
$core->url->unregister('tags');

# Ajax search URL
$core->url->register('ajaxsearch','ajaxsearch','^ajaxsearch(?:(?:/)(.*))?$',array('urlsNoviny','ajaxsearch'));

class tplNoviny
{
	public static function NovinyMenu($attr,$content)
	{
		$list = !empty($attr['list']) ? $attr['list'] : '';
		$item = !empty($attr['item']) ? $attr['item'] : '';
		$active_item = !empty($attr['active_item']) ? $attr['active_item'] : '';
		
		return "<?php echo tplNoviny::NovinyMenuHelper('".addslashes($list)."','".addslashes($item)."','".addslashes($active_item)."'); ?>";
	}
	
	public static function NovinyMenuHelper($list,$item,$active_item)
	{
		global $core;
		
		$menu = @unserialize($core->blog->settings->noviny_nav);
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

class urlsNoviny
{
	public static function ajaxsearch($args)
	{
		global $core;
		$res = '';
		
		try
		{
			if (!$args) {
				throw new Exception;
			}
			
			$q = rawurldecode($args);
			$rs = $core->blog->getPosts(array(
				'search' => $q,
				'limit' => 5
			));
			
			if ($rs->isEmpty()) {
				throw new Exception;
			}
			
			$res = '<ul>';
			while ($rs->fetch())
			{
				$res .= '<li><a href="'.$rs->getURL().'">'.html::escapeHTML($rs->post_title).'</a></li>';
			}
			$res .= '</ul>';
		}
		catch (Exception $e) {}
		
		header('Content-Type: text/plain; charset=UTF-8');
		echo $res;
	}
}
?>