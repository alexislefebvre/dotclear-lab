<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin multiToc for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron and contributors.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->url->register('multitoc','multitoc','^multitoc/(.*)$',array('multiTocUrl','multiToc'));

$core->addBehavior('publicBeforeDocument',array('multiTocBehaviors','addTplPath'));

$core->tpl->addValue('MultiTocUrl', array('multiTocTpl','multiTocUrl'));
$core->tpl->addValue('MultiTocCss', array('multiTocTpl','multiTocCss'));
$core->tpl->addValue('MultiTocGroupTitle', array('multiTocTpl','multiTocGroupTitle'));
$core->tpl->addValue('MultiTocGroupDesc', array('multiTocTpl','multiTocGroupDesc'));
$core->tpl->addValue('MultiTocGroupCount', array('multiTocTpl','multiTocGroupCount'));
$core->tpl->addValue('MultiTocItemUrl', array('multiTocTpl','multiTocItemUrl'));
$core->tpl->addValue('MultiTocItemTitle', array('multiTocTpl','multiTocItemTitle'));
$core->tpl->addValue('MultiTocPageTitle', array('multiTocTpl','multiTocPageTitle'));

$core->tpl->addBlock('MultiTocGroup', array('multiTocTpl','multiTocGroup'));
$core->tpl->addBlock('MultiTocItem', array('multiTocTpl','multiTocItem'));

class multiTocUrl extends dcUrlHandlers
{
	public static function multiToc($args)
	{
		global $core,$_ctx;

		$types = array('cat','tag','alpha');

		if (count($args) == 0) {
			$type = 'cat';
		}
		elseif (count($args) == 1) {
			$type = in_array($args,$types) ? $args : null;
			unset($types);
		}
		else {
			$type = null;
		}

		$_ctx->multitoc_type = $type;

		if ($type === null) {
			self::p404();
		}
		else {
			self::serveDocument('multitoc.html');
		}

		exit;
	}
}

class multiTocBehaviors
{
	public static function addTplPath()
	{
		global $core;
		
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
	}
}

class multiTocTpl
{
	// Fonction template de mise en place de l'URL d'appel de la toc (hors argument facultatif)
	public static function multiTocUrl($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->getQmarkURL()."multitoc/"').'; ?>';
	}
	
	/*
		Cette fonction va chercher la css multitoc.css
		(inspire de la fonction dans wikicomments de pep) 
		*/	
	public static function multiTocCss()
	{
		global $core;

		$plop =
			$core->blog->themes_path.'/'.
			$core->blog->settings->theme.'/styles/multitoc.css';

		$tagada = 
			$core->blog->themes_path.
			'/default/multitoc.css';

		if (file_exists($plop)) { /* s'il y a une multitoc.css dans le thème actif, on la prend */
			$css =
				$core->blog->settings->themes_url.'/'.
				$core->blog->settings->theme.'/styles/multitoc.css';
		} elseif (file_exists($tagada)) { /* si pas dans le thème actif on regarde dans le theme par défaut */
			$css =
				$core->blog->settings->themes_url.'/default/multitoc.css';
		} else { /* et si aucune des deux celle dans le rep du ploug */
			$css =
				$core->blog->url.
				(($core->blog->settings->url_scan == 'path_info')?'?':'').
				'pf=multiToc/multitoc.css';
		}
		$res =
			"\n<?php \n".
			"echo '<style type=\"text/css\" media=\"screen\">@import url(".$css.");</style>';\n".
			"?>\n";

		return $res;
	}

	public static function multiTocGroup($attr,$content)
	{
		$_ctx =& $GLOBALS['_ctx'];

		$p = "\$params = array();\n";
		$p .= "if (\$_ctx->multitoc_type == 'cat') :\n";
			$p .= "\$params['order'] = 'cat_position asc';\n";
			$p .= "\$_ctx->multitoc_group = \$core->blog->getCategories(\$params);\n";
		$p .= "elseif (\$_ctx->multitoc_type == 'tag') :\n";
			$p .= "\$meta = new dcMeta(\$core);\n";
			$p .= "\$_ctx->multitoc_group = \$meta->getMeta('tag');\n";
			$p .= "\$_ctx->multitoc_group->sort('meta_id_lower','asc');\n";
		$p .= "elseif (\$_ctx->multitoc_type == 'alpha') :\n";
			$p .= "\$params['columns'] = array('UPPER(LEFT(post_title,1)) AS post_letter','COUNT(*) as count');\n";
			$p .= "\$params['sql'] = 'GROUP BY post_letter';\n";
			$p .= "\$params['no_content'] = true;\n";
			$p .= "\$params['order'] = 'post_letter ASC';\n";
			$p .= "\$_ctx->multitoc_group = \$core->blog->getPosts(\$params);\n";
		$p .= "endif;\n";

		$res = "<?php\n";
		$res .= $p;
		$res .= "unset(\$params);\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->multitoc_group->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->multitoc_group = null; ?>';
		
		return $res;
	}
	
	public static function multiTocGroupTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$res = "<?php if (\$_ctx->multitoc_type == 'cat') :\n";
			$res .= "echo ".sprintf($f,'$_ctx->multitoc_group->cat_title').";\n";
		$res .= "elseif (\$_ctx->multitoc_type == 'tag') :\n";
			$res .= "echo ".sprintf($f,'$_ctx->multitoc_group->meta_id').";\n";
		$res .= "elseif (\$_ctx->multitoc_type == 'alpha') :\n";
			$res .= "echo ".sprintf($f,'$_ctx->multitoc_group->post_letter').";\n";
		$res .= "endif; ?>\n";
		
		return $res;
	}
	
	public static function multiTocGroupDesc($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$res = "<?php if (\$_ctx->multitoc_type == 'cat') :\n";
			$res .= "echo ".sprintf($f,'$_ctx->multitoc_group->cat_desc').";\n";
		$res .= "endif; ?>\n";
		
		return $res;
	}
	
	public static function multiTocGroupCount($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$res = "<?php\n";
		$res .= "\$settings = unserialize(\$core->blog->settings->multitoc_settings);\n";
		$res .= "if (\$_ctx->multitoc_type == 'cat' && \$settings['cat']['display_nb_entry']) :\n";
			$res .= "echo '('.".sprintf($f,'$_ctx->multitoc_group->nb_post').".')';\n";
		$res .= "elseif (\$_ctx->multitoc_type == 'tag' && \$settings['tag']['display_nb_entry']) :\n";
			$res .= "echo '('.".sprintf($f,'$_ctx->multitoc_group->count').".')';\n";
		$res .= "elseif (\$_ctx->multitoc_type == 'alpha' && \$settings['alpha']['display_nb_entry']) :\n";
			$res .= "echo '('.".sprintf($f,'$_ctx->multitoc_group->count').".')';\n";
		$res .= "endif;\n";
		$res .= "?>\n";
		
		return $res;
	}
	
	public static function multiTocItem($attr,$content)
	{

		$p = "\$params = array();\n";
		$p .= "\$params['no_content'] = true;\n";

		$p .= "if (\$_ctx->multitoc_type == 'cat') :\n";
			$p .= "\$params['order'] = 'post_dt asc';\n";
			$p .= "\$params['cat_id'] = \$_ctx->multitoc_group->cat_id;\n";
			$p .= "\$_ctx->multitoc_items = \$core->blog->getPosts(\$params);\n";
		$p .= "elseif (\$_ctx->multitoc_type == 'tag') :\n";
			$p .= "\$params['meta_id'] = \$_ctx->multitoc_group->meta_id;\n";
			$p .= "\$params['meta_type'] = 'tag';\n";
			$p .= "\$params['post_type'] = '';\n";
			$p .= "\$_ctx->multitoc_items = \$meta->getPostsByMeta(\$params);\n";
		$p .= "elseif (\$_ctx->multitoc_type == 'alpha') :\n";
			$p .= "\$params['order'] = 'post_dt ASC';\n";
			$p .= "\$params['sql'] = ' AND SUBSTRING(post_title,1,1) = \''.\$_ctx->multitoc_group->post_letter.'\'';\n";
			$p .= "\$_ctx->multitoc_items = \$core->blog->getPosts(\$params);\n";
		$p .= "endif;\n";

		$res = "<?php\n";
		$res .= $p;
		$res .= 'unset($params);'."\n";
		$res .= "?>\n";

		$res .=
		'<?php while ($_ctx->multitoc_items->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->multitoc_items = null; ?>';

		return $res;
	}
	
	public static function multiTocItemUrl($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->multitoc_items->getURL()').'; ?>';
	}
	
	public static function multiTocItemTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->multitoc_items->post_title').'; ?>';
	}
	
	public static function multiTocPageTitle()
	{
		$res = "<?php\n";
		$res .= "echo __('Table of content');\n";

		$res .= "if (\$_ctx->multitoc_type == 'cat') :\n";
			$res .= "echo ' - '.__('By category');\n";
		$res .= "elseif (\$_ctx->multitoc_type == 'tag') :\n";
			$res .= "echo ' - '.__('By tag');\n";
		$res .= "elseif (\$_ctx->multitoc_type == 'alpha') :\n";
			$res .= "echo ' - '.__('By alpha order');\n";
		$res .= "endif;\n";
		$res .= "?>\n";

		return $res;
	}
}

class multiTocPublic
{
	public static function widget(&$w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$amask = '<a href="%1$s">%2$s</a>';
		$limask = '<li class="%1$s">%2$s</li>';

		$title = (strlen($w->title) > 0) ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '';
		
		$res = '';
		
		$settings = unserialize($core->blog->settings->multitoc_settings);
		
		if ($settings['cat']['enable']) {
			$link = sprintf($amask,$core->blog->getQmarkURL().'multitoc/cat',__('By category'));
			$res .= sprintf($limask,'toc-cat',$link);
		}
		if ($settings['tag']['enable']) {
			$link = sprintf($amask,$core->blog->getQmarkURL().'multitoc/tag',__('By tag'));
			$res .= sprintf($limask,'toc-tag',$link);
		}
		if ($settings['alpha']['enable']) {
			$link = sprintf($amask,$core->blog->getQmarkURL().'multitoc/alpha',__('By alpha list'));
			$res .= sprintf($limask,'toc-alpha',$link);
		}
		
		$res = !empty($res) ? '<ul>'.$res.'</ul>' : '';

		return
			'<div id="info-blog">'.
			$title.
			$res.
			'</div>';
	}	
}

?>