<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('MultiTocUrl', array('multiTocTpl','multiTocUrl'));
$core->tpl->addValue('MultiTocCss', array('multiTocTpl','multiTocCss'));
$core->tpl->addValue('MultiTocGroupTitle', array('multiTocTpl','multiTocGroupTitle'));
$core->tpl->addValue('MultiTocGroupDesc', array('multiTocTpl','multiTocGroupDesc'));
$core->tpl->addValue('MultiTocGroupCount', array('multiTocTpl','multiTocGroupCount'));
$core->tpl->addValue('MultiTocItemUrl', array('multiTocTpl','multiTocItemUrl'));
$core->tpl->addValue('MultiTocItemTitle', array('multiTocTpl','multiTocItemTitle'));
$core->tpl->addValue('MultiTocItemDate', array('multiTocTpl','multiTocItemDate'));
$core->tpl->addValue('MultiTocItemCategory', array('multiTocTpl','multiTocItemCategory'));
$core->tpl->addValue('MultiTocItemAuthor', array('multiTocTpl','multiTocItemAuthor'));
$core->tpl->addValue('MultiTocItemNbComments', array('multiTocTpl','multiTocItemNbComments'));
$core->tpl->addValue('MultiTocItemNbTrackbacks', array('multiTocTpl','multiTocItemNbTrackbacks'));
$core->tpl->addValue('MultiTocPageTitle', array('multiTocTpl','multiTocPageTitle'));

$core->tpl->addBlock('MultiTocGroup', array('multiTocTpl','multiTocGroup'));
$core->tpl->addBlock('MultiTocItem', array('multiTocTpl','multiTocItem'));
$core->tpl->addBlock('MultiTocIf',array('multiTocTpl','multiTocIf'));
$core->tpl->addBlock('MultiTocMetaData',array('multiTocTpl','multiTocMetaData'));

class multiTocUrl extends dcUrlHandlers
{
	public static function multiToc($args)
	{
		global $core,$_ctx;
		
		$settings = unserialize($core->blog->settings->multiToc->multitoc_settings);
		
		if ($settings['cat']['enable']) {
			$types[] = 'cat';
		}
		if ($settings['tag']['enable']) {
			$types[] = 'tag';
		}
		if ($settings['alpha']['enable']) {
			$types[] = 'alpha';
		}
		
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

class multiTocTpl
{

	public static function multiTocUrl($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("multitoc")').'; ?>';
	}
	
	public static function multiTocCss()
	{
		global $core;
		
		$plop =
			$core->blog->themes_path.'/'.
			$core->blog->settings->system->theme.'/styles/multitoc.css';
			
		$tagada = $core->blog->themes_path.'/default/multitoc.css';
		
		if (file_exists($plop)) {
			$css =
				$core->blog->settings->system->themes_url.'/'.
				$core->blog->settings->system->theme.'/styles/multitoc.css';
		} elseif (file_exists($tagada)) {
			$css =
				$core->blog->settings->system->themes_url.'/default/multitoc.css';
		} else {
			$css =
				$core->blog->url.
				(($core->blog->settings->system->url_scan == 'path_info')?'?':'').
				'pf=multiToc/css/multitoc.css';
		}
		$res =
			"\n<?php \n".
			"echo '<style type=\"text/css\" media=\"screen\">@import url(".$css.");</style>';\n".
			"?>\n";
			
		return $res;
	}
	
	public static function multiTocGroup($attr,$content)
	{
		$p = "\$_ctx->multitoc_settings = unserialize(\$core->blog->settings->multiToc->multitoc_settings);\n";
		$p .= "\$params = array();\n";
		$p .= "if (\$_ctx->multitoc_type == 'cat') :\n";
			$p .= "\$_ctx->multitoc_group = \$core->blog->getCategories(array('post_type'=>'post'));\n";
		$p .= "elseif (\$_ctx->multitoc_type == 'tag') :\n";
			$p .= "\$meta = new dcMeta(\$core);\n";
			$p .= "\$meta_rs = \$meta->getMetadata(array('meta_type' => 'tag'));\n";
			$p .= "\$_ctx->multitoc_group = \$meta->computeMetaStats(\$meta_rs);\n";
			$p .= "\$_ctx->multitoc_group->sort('meta_id_lower',\$_ctx->multitoc_settings['tag']['order_group']);\n";
		$p .= "elseif (\$_ctx->multitoc_type == 'alpha') :\n";
			$p .= "if (\$core->con->driver() == 'pgsql') :\n";
				$p .= "\$_ctx->multitoc_group = ".
				"multiTocPublic::multiTocGroupPGSQL(\$core,".
				"\$_ctx->multitoc_settings['alpha']['order_group']);\n";
			$p .= "else :\n";
				$p .= "\$params['columns'] = array('UPPER(SUBSTRING(post_title,1,1)) AS post_letter','COUNT(*) as count');\n";
				$p .= "\$params['sql'] = 'GROUP BY post_letter';\n";
				$p .= "\$params['no_content'] = true;\n";
				$p .= "\$params['order'] = \$_ctx->multitoc_settings['alpha']['order_group'];\n";
				$p .= "\$_ctx->multitoc_group = \$core->blog->getPosts(\$params);\n";
			$p .= "endif;\n";
		$p .= "endif;\n";
		
		$res = "<?php\n";
		$res .= $p;
		$res .= "unset(\$params);\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->multitoc_group->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->multitoc_group = null; $_ctx->multitoc_settings = null; ?>';
		
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
		$res .= "\$mask = '<span class=\"toc-group-count\">%s</span>';\n";
		$res .= "if (\$_ctx->multitoc_type == 'cat' && \$_ctx->multitoc_settings['cat']['display_nb_entry']) :\n";
			$res .= "echo sprintf(\$mask,'('.".sprintf($f,'$_ctx->multitoc_group->nb_post').".')');\n";
		$res .= "elseif (\$_ctx->multitoc_type == 'tag' && \$_ctx->multitoc_settings['tag']['display_nb_entry']) :\n";
			$res .= "echo sprintf(\$mask,'('.".sprintf($f,'$_ctx->multitoc_group->count').".')');\n";
		$res .= "elseif (\$_ctx->multitoc_type == 'alpha' && \$_ctx->multitoc_settings['alpha']['display_nb_entry']) :\n";
			$res .= "echo sprintf(\$mask,'('.".sprintf($f,'$_ctx->multitoc_group->count').".')');\n";
		$res .= "endif;\n";
		$res .= "?>\n";
		
		return $res;
	}
	
	public static function multiTocItem($attr,$content)
	{
	
		$p = "\$params = array();\n";
		$p .= "\$params['no_content'] = true;\n";
		
		$p .= "if (\$_ctx->multitoc_type == 'cat') :\n";
			$p .= "\$params['order'] = \$_ctx->multitoc_settings['cat']['order_entry'];\n";
			$p .= "\$params['cat_id'] = \$_ctx->multitoc_group->cat_id;\n";
			$p .= "\$_ctx->multitoc_items = \$core->blog->getPosts(\$params);\n";
		$p .= "elseif (\$_ctx->multitoc_type == 'tag') :\n";
			$p .= "\$params['meta_id'] = \$_ctx->multitoc_group->meta_id;\n";
			$p .= "\$params['meta_type'] = 'tag';\n";
			$p .= "\$params['post_type'] = '';\n";
			$p .= "\$params['order'] = \$_ctx->multitoc_settings['tag']['order_entry'];\n";
			$p .= "\$_ctx->multitoc_items = \$meta->getPostsByMeta(\$params);\n";
		$p .= "elseif (\$_ctx->multitoc_type == 'alpha') :\n";
			$p .= "\$params['order'] = \$_ctx->multitoc_settings['alpha']['order_entry'];\n";
			$p .= "\$params['sql'] = ' AND UPPER(SUBSTRING(post_title,1,1)) = \''.\$_ctx->multitoc_group->post_letter.'\'';\n";
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
	
	public static function multiTocItemDate($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$mask = isset($attr['mask']) ? sprintf($f,'"'.$attr['mask'].'"') : '\'<span class="toc-item-date">%s</span> - \'';
		
		$res = "<?php\n";
		$res .= "\$mask = ".$mask.";\n";
		$res .= "if ((\$_ctx->multitoc_type == 'cat' && \$_ctx->multitoc_settings['cat']['display_date'])\n";
		$res .= "|| (\$_ctx->multitoc_type == 'tag' && \$_ctx->multitoc_settings['tag']['display_date'])\n";
		$res .= "|| (\$_ctx->multitoc_type == 'alpha' && \$_ctx->multitoc_settings['alpha']['display_date'])\n";
		$res .= ") :\n";
			$res .= "echo sprintf(\$mask,\$_ctx->multitoc_items->getDate(\$_ctx->multitoc_settings[\$_ctx->multitoc_type]['format_date']));\n";
		$res .= "endif;\n";
		$res .= "?>\n";
		
		return $res;
	}
	
	public static function multiTocItemAuthor($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$mask = isset($attr['mask']) ? sprintf($f,'"'.$attr['mask'].'"') : '\' - <span class="toc-item-author">%s</span>\'';
		
		$res = "<?php\n";
		$res .= "\$mask = ".$mask.";\n";
		$res .= "if ((\$_ctx->multitoc_type == 'cat' && \$_ctx->multitoc_settings['cat']['display_author'])\n";
		$res .= "|| (\$_ctx->multitoc_type == 'tag' && \$_ctx->multitoc_settings['tag']['display_author'])\n";
		$res .= "|| (\$_ctx->multitoc_type == 'alpha' && \$_ctx->multitoc_settings['alpha']['display_author'])\n";
		$res .= ") :\n";
			$res .= "echo sprintf(\$mask,\$_ctx->multitoc_items->getAuthorLink());\n";
		$res .= "endif;\n";
		$res .= "?>\n";
		
		return $res;
	}

	public static function multiTocItemCategory($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$mask = isset($attr['mask']) ? sprintf($f,'"'.$attr['mask'].'"') : '\' - <span class="toc-item-cat">%s</span>\'';
		
		$res = "<?php\n";
		$res .= "\$mask = ".$mask.";\n";
		$res .= "if (((\$_ctx->multitoc_type == 'cat' && \$_ctx->multitoc_settings['cat']['display_cat'])\n";
		$res .= "|| (\$_ctx->multitoc_type == 'tag' && \$_ctx->multitoc_settings['tag']['display_cat'])\n";
		$res .= "|| (\$_ctx->multitoc_type == 'alpha' && \$_ctx->multitoc_settings['alpha']['display_cat']))\n";
		$res .= "&& \$_ctx->multitoc_items->cat_title !== null\n";
		$res .= ") :\n";
			$res .= 
			"\$link = sprintf('<a href=\"%1\$s\">%2\$s</a>',".
			sprintf($f,'$core->blog->url.$core->url->getBase("category")."/".$_ctx->multitoc_items->cat_url').",".
			sprintf($f,'$_ctx->multitoc_items->cat_title').");\n".
			"echo sprintf(\$mask,\$link);\n";
		$res .= "endif;\n";
		$res .= "?>\n";
		
		return $res;
	}

	public static function multiTocItemNbComments($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$mask = isset($attr['mask']) ? sprintf($f,'"'.$attr['mask'].'"') : '\' - <span class="toc-item-com">%s</span>\'';
		
		$res = "<?php\n";
		$res .= "\$mask = ".$mask.";\n";
		$res .= "if ((\$_ctx->multitoc_type == 'cat' && \$_ctx->multitoc_settings['cat']['display_nb_com'])\n";
		$res .= "|| (\$_ctx->multitoc_type == 'tag' && \$_ctx->multitoc_settings['tag']['display_nb_com'])\n";
		$res .= "|| (\$_ctx->multitoc_type == 'alpha' && \$_ctx->multitoc_settings['alpha']['display_nb_com'])\n";
		$res .= ") :\n";
			$res .= "echo sprintf(\$mask,\$_ctx->multitoc_items->nb_comment);\n";
		$res .= "endif;\n";
		$res .= "?>\n";
		
		return $res;
	}

	public static function multiTocItemNbTrackbacks($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$mask = isset($attr['mask']) ? sprintf($f,'"'.$attr['mask'].'"') : '\' - <span class="toc-item-tb">%s</span>\'';
		
		$res = "<?php\n";
		$res .= "\$mask = ".$mask.";\n";
		$res .= "if ((\$_ctx->multitoc_type == 'cat' && \$_ctx->multitoc_settings['cat']['display_nb_tb'])\n";
		$res .= "|| (\$_ctx->multitoc_type == 'tag' && \$_ctx->multitoc_settings['tag']['display_nb_tb'])\n";
		$res .= "|| (\$_ctx->multitoc_type == 'alpha' && \$_ctx->multitoc_settings['alpha']['display_nb_tb'])\n";
		$res .= ") :\n";
			$res .= "echo sprintf(\$mask,\$_ctx->multitoc_items->nb_trackback);\n";
		$res .= "endif;\n";
		$res .= "?>\n";
		
		return $res;
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

	public static function multiTocIf($attr,$content)
	{
		$if = array();
		
		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';
		
		if (isset($attr['type'])) {
			$if[] = "\$_ctx->multitoc_type == '".addslashes($attr['type'])."'";
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	public static function multiTocMetaData($attr,$content)
	{
		$type = isset($attr['type']) ? addslashes($attr['type']) : 'tag';
		
		$sortby = 'meta_id_lower';
		if (isset($attr['sortby']) && $attr['sortby'] == 'count') {
			$sortby = 'count';
		}
		
		$order = 'asc';
		if (isset($attr['order']) && $attr['order'] == 'desc') {
			$order = 'desc';
		}
		
		$res =
		"<?php\n".
		'$objMeta = new dcMeta($core); '.
		"\$_ctx->meta = \$objMeta->getMetaRecordset(\$_ctx->multitoc_items->post_meta,'".$type."'); ".
		"\$_ctx->meta->sort('".$sortby."','".$order."'); ".
		'?>';
		
		$res .= "<?php if (\$_ctx->multitoc_settings[\$_ctx->multitoc_type]['display_tag']) : ?>\n";
		$res .= 
		'<?php while ($_ctx->meta->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->meta = null; unset($objMeta); ?>';
		$res .= "<?php endif; ?>\n";
		
		return $res;
	}
}

class multiTocPublic
{
	public static function multiTocGroupPGSQL($core,$order_group)
	{
		$params = array();
		$params['columns'] = array(
			'UPPER(SUBSTRING(post_title,1,1)) AS post_letter'
		);
		$params['no_content'] = true;
		$params['order'] = $order_group;
		
		$rs = $core->blog->getPosts($params);
		
		$array = array();
		
		# the "post_letter" of the previous post in the list
		$previous_letter = '';
		
		# read the "post_letter" field of the posts
		# and count occurences of each letter
		while ($rs->fetch())
		{
			# this letter
			$letter = $rs->post_letter;
			
			# the letter has changed
			if ($letter != $previous_letter)
			{
				# the letter has changed but the previous letter was
				# the empty string, ignore this case
				if (!empty($previous_letter))
				{
					# store the previous letter,
					# which counter has been incremented
					$array[] = array(
						'post_letter' => $previous_letter,
						'count' => $letter_count
					);
				}
				
				# initialize the counter
				$letter_count = 1;
				# remember this letter to count it
				$previous_letter = $letter;
			}
			else
			{
				# the letter has not changed, increment the counter
				$letter_count++;
			}
		}
		
		# don't forget the last letter
		$array[] = array(
			'post_letter' => $letter,
			'count' => $letter_count
		);
		
		return(staticRecord::newFromArray($array));
	}
}