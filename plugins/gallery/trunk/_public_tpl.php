<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

/* Galleries list management */
$core->tpl->addBlock('GalleryEntries',array('tplGallery','GalleryEntries'));
$core->tpl->addBlock('GalleryEntryNext',array('tplGallery','GalleryEntryNext'));
$core->tpl->addBlock('GalleryEntryPrevious',array('tplGallery','GalleryEntryPrevious'));
$core->tpl->addValue('GalleryItemCount',array('tplGallery','GalleryItemCount'));
$core->tpl->addBlock('EntryIfNewCat',array('tplGallery','EntryIfNewCat'));
$core->tpl->addValue('EntryCategoryWithNull',array('tplGallery','EntryCategoryWithNull'));
$core->tpl->addValue('GalleryAttachmentThumbURL',array('tplGallery','GalleryAttachmentThumbURL'));
$core->tpl->addValue('GalleryFeedURL',array('tplGallery','GalleryFeedURL'));

/* Galleries items management */
$core->tpl->addBlock('GalleryItemEntries',array('tplGallery','GalleryItemEntries'));
$core->tpl->addBlock('GalleryPagination',array('tplGallery','GalleryPagination'));
$core->tpl->addValue('GalleryItemThumbURL',array('tplGallery','GalleryItemThumbURL'));
$core->tpl->addBlock('GalleryItemNext',array('tplGallery','GalleryItemNext'));
$core->tpl->addBlock('GalleryItemPrevious',array('tplGallery','GalleryItemPrevious'));
$core->tpl->addBlock('GalleryItemIf',array('tplGallery','GalleryItemIf'));
$core->tpl->addValue('GalleryMediaURL',array('tplGallery','GalleryMediaURL'));
$core->tpl->addValue('GalleryItemURL',array('tplGallery','GalleryItemURL'));
$core->tpl->addBlock('GalleryItemGalleries',array('tplGallery','GalleryItemGalleries'));
$core->tpl->addBlock('GalleryItemGallery',array('tplGallery','GalleryItemGallery'));
$core->tpl->addValue('GalleryItemFilename',array('tplGallery','GalleryItemFilename'));
$core->tpl->addValue('GalleryURLWithPage',array('tplGallery','GalleryURLWithPage'));
$core->tpl->addValue('GalleryItemFeedURL',array('tplGallery','GalleryItemFeedURL'));
$core->tpl->addValue('GalleryItemMeta',array('tplGallery','GalleryItemMeta'));
$core->tpl->addBlock('doOnce',array('tplGalleryUtils','doOnce'));

$core->tpl->addValue('GalleryInclude',array('tplGallery','GalleryInclude'));

/* StyleSheets URL */
$core->tpl->addValue('GalleryStyleURL',array('tplGallery','GalleryStyleURL'));
$core->tpl->addValue('GalleryStylePath',array('tplGallery','GalleryStylePath'));
$core->tpl->addValue('GalleryJSPath',array('tplGallery','GalleryJSPath'));
$core->tpl->addValue('GalleryTemplateURL',array('tplGallery','GalleryTemplateURL'));


class tplGallery
{
	/* Misc functions -------------------------------------------- */
	public static function GalleryStyleURL($attr,$content)
	{
		global $core;
		$res = '<?php if ($_ctx->gallery_theme != null): '."\n".
			'echo \'<style type="text/css" media="screen">@import url(\'.$core->blog->url.\'gallerytheme/\'.$_ctx->gallery_theme.\'/gallery.css);</style>\';'."\n".
			'else:'."\n".
			'echo \'<style type="text/css" media="screen">@import url(\'.$core->blog->url.\'gallerytheme/default/gallery.css\'.\');</style>\';'."\n".
			'endif;?>'."\n";
		return $res;

	}

	public static function GalleryStylePath($attr,$content)
	{
		global $core;
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$css = $core->blog->url.(($core->blog->settings->url_scan == 'path_info')?'?':'').'pf=gallery/default-templates/'
		.$core->blog->settings->gallery_default_theme;
		$res = "\n<?php echo '".$css."';\n?>";
		return $res;

	}

	public static function GalleryJSPath($attr,$content)
	{
		global $core;
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$js = $core->blog->url.(($core->blog->settings->url_scan == 'path_info')?'?':'').'pf=gallery/default-templates/'
		.$core->blog->settings->gallery_default_theme.'/js';
		$res = "\n<?php echo '".$js."';\n?>";
		return $res;

	}
	public static function GalleryTemplateURL($attr,$content) 
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url').'."gallerytemplate/"; ?>';
		
	}
	/* Gallery lists templates */

	# Returns whether an item category is new or not
	public static function EntryIfNewCat($attr,$content)
	{
		global $core;
		$p = '<?php $newcat=false;'."\n".
			'$post_cat = (!is_null($_ctx->posts->cat_id))?($_ctx->posts->cat_id):-1;'."\n".
			'if (!isset($current_cat)) {'."\n".
			'$newcat=true; $current_cat=$post_cat;'."\n".
			'} elseif ($post_cat !== $current_cat) {'."\n".
			'$newcat=true; $current_cat=$post_cat;'."\n".
			'}'."\n".
			'if ($newcat) :?>'.$content.'<?php endif; ?>';
		return $p;
		
	}

	public static function EntryCategoryWithNull($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php if (!is_null($_ctx->posts->cat_id)) {'."\n".
			'echo html::escapeHTML('.sprintf($f,'$_ctx->posts->cat_title').');'."\n".
			'} else {'."\n".
			'echo "'.__('No category').'";'."\n".
			'} ?>';
	}

	# Lists galleries
	public static function GalleryEntries($attr,$content)
	{
		global $core;
		$lastn = 0;
		$sortby = 'post_dt';
		$order = 'desc';
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "\$params['limit'] = \$core->blog->settings->gallery_nb_galleries_per_page;\n";
		}
		
		if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0") {
			$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
		} else {
			$p .= "\$params['limit'] = array(0, \$params['limit']);\n";
		}
		$p .= "\$params['post_type'] = 'gal';\n";
		
		if (!isset($attr['sortby']) && !isset($attr['order']) && !isset($attr['orderbycat'])) {
			$attr['sortby']=$core->blog->settings->gallery_galleries_sort;
			$attr['order']=$core->blog->settings->gallery_galleries_order;
			$attr['orderbycat']=$core->blog->settings->gallery_galleries_orderbycat?"yes":"no";
		}
		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'title': $sortby = 'post_title'; break;
				case 'selected' : $sortby = 'post_selected'; break;
				case 'author' : $sortby = 'user_id'; break;
				case 'date' : $sortby = 'post_dt'; break;
			}
		}
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}
		if (isset($attr['orderbycat']) && strtolower($attr['orderbycat'])=="yes") {
			$p .= "\$params['order'] = 'C.cat_lft asc, ".$sortby." ".$order."';\n";
		} else {
			$p .= "\$params['order'] = '".$sortby." ".$order."';\n";
		}

		if (!empty($attr['url'])) {
			$p .= "\$params['post_url'] = '".addslashes($attr['url'])."';\n";
		}

		
		
		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";

			$p .=
			'if ($_ctx->exists("nocat")) { '.
				"\$params['sql'] = ' AND C.cat_id is NULL '; ".
			"}\n";
		}
	
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$_ctx->posts = $core->gallery->getGalleries($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->posts->fetch()) : $core->gallery->fillGalleryContext($_ctx);?>'.
			$content.
			'<?php $core->gallery->emptyGalleryContext($_ctx); endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';
		
		return $res;
	}

	# Retrieve next gallery
	public static function GalleryEntryNext($attr,$content)
	{
		return
		'<?php $next_post = $core->gallery->getNextGallery($_ctx->posts->post_id,strtotime($_ctx->posts->post_dt),1); ?>'."\n".
		'<?php if ($next_post !== null) : ?>'.
			
			'<?php $_ctx->posts = $next_post; unset($next_post);'."\n".
			'while ($_ctx->posts->fetch()) : $core->gallery->fillGalleryContext($_ctx); ?>'.
			$content.
			'<?php endwhile; $core->gallery->emptyGalleryContext($_ctx); $_ctx->posts = null; ?>'.
		"<?php endif; ?>\n";
	}

	# Retrieve previous gallery
	public static function GalleryEntryPrevious($attr,$content)
	{
		return
		'<?php $prev_post = $core->gallery->getNextGallery($_ctx->posts->post_id,strtotime($_ctx->posts->post_dt),-1); ?>'."\n".
		'<?php if ($prev_post !== null) : ?>'.
			
			'<?php $_ctx->posts = $prev_post; unset($prev_post);'."\n".
			'while ($_ctx->posts->fetch()) : $core->gallery->fillGalleryContext($_ctx); ?>'.
			$content.
			'<?php endwhile; $core->gallery->emptyGalleryContext($_ctx); $_ctx->posts = null; ?>'.
		"<?php endif; ?>\n";
	}

	# Retrieve URL for a given gallery item thumbnail
	# attributes :
	#   * size : gives the size of requested thumb (default : 's')
	#   * bestfit : retrieve standard URL if thumbnail does not exist
	public static function GalleryAttachmentThumbURL($attr) 
	{
		$size = isset($attr['size']) ? addslashes($attr['size']) : 's';
		$bestfit = isset($attr['bestfit']);
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		if ($bestfit) {
			$append=' else echo '.sprintf($f,'$attach_f->file_url').';';
		} else {
			$append='';
		}
		return '<?php '.
		'if (isset($attach_f->media_thumb[\''.$size.'\'])) {'.
			'echo '.sprintf($f,'$attach_f->media_thumb[\''.$size.'\']').';'.
		'}'.$append.
		'?>';
	}

	public static function GalleryFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'rss2';
		
		if (!preg_match('#^(rss2|atom|mediarss|custom)$#',$type)) {
			$type = 'rss2';
		}
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("gal")."/feed/'.$type.'"').'; ?>';
	      /*return '<?php echo '.sprintf($f,'$_ctx->posts->getURL()."/feed/'.$type.'"').'; ?>';*/
	}

	public static function GalleryItemMeta($attr)
	{
		if (empty($attr['name']))
			return '';
		$value = addslashes($attr['name']);
		

		$p = '<?php if ($_ctx->media->type == "image/jpeg") {'."\n".
			'if (isset($_ctx->media->media_meta))'."\n".
			'echo $_ctx->media->media_meta->{\''.$value."'};\n".
			"}\n".
			'?>';
		return $p;
	}

	public static function GalleryItemFilename($attr)
	{
		if (isset($attr['full_path']) && $attr['include_dirname']==1)
			return  '<?php echo ($_ctx->media->relname);?>'."\n";
		else
			return  '<?php echo ($_ctx->media->basename);?>'."\n";
	}

	public static function GalleryInclude($attr) {
		if (!isset($attr['src'])) { return; }
		$rel_src = path::clean($attr['src']);

		return
		'<?php try { '.
		'$theme = $_ctx->gallery_theme;'.
		'if ($theme == "") {'.
		'	$src = "gal_simple/'.$rel_src.'";'.
		'} else {'.
		'	$src = "gal_".$theme."/'.$rel_src.'";'.
		'}'.
		'echo $core->tpl->getData(str_replace("\'","\\\'",$src)); '.
		'unset($src); unset($theme);'.
		'} catch (Exception $e) {} ?>';

	}

	/* Entries -------------------------------------------- */
	
	# List all items from a gallery
	public static function GalleryItemEntries($attr,$content)
	{
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = (integer) $attr['lastn'];
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
		if (empty($attr['no_context'])) {
			$p .= 'if (!is_null($_ctx->gal_params)) $params = $_ctx->gal_params;'."\n";
		}
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else if ($lastn == 0) {
			$p .= "\$params['limit'] = \$core->blog->settings->gallery_nb_images_per_page;\n";
		}
		if ($lastn >= 0) {
			if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0") {
				$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
			} else {
				$p .= "\$params['limit'] = array(0,\$params['limit']);\n";
			}
		}
		if (!empty($attr['url'])) {
			$p .= "\$params['post_url'] = '".addslashes($attr['url'])."';\n";
		}
		if (!empty($attr['gallery_url'])) {
			$p .= "\$params['gal_url'] = '".addslashes($attr['gal_url'])."';\n";
		}
		
		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		
		$res .= '$_ctx->posts = $core->gallery->getGalImageMedia($params); unset($params);'."\n";
		
		$res .=
		'while ($_ctx->posts->fetch()) : '."\n".
		' $_ctx->media = $core->gallery->readMedia($_ctx->posts);?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; $_ctx->media = null;?>';
		
		return $res;
	}

	# Enable paging for galleries items lists
	public static function GalleryPagination($attr,$content)
	{
		$p = "<?php\n";
		$p .= '$params = $_ctx->post_params;'."\n";
		$p .= '$_ctx->pagination = $core->gallery->getGalImageMedia($params, true);  unset($params);'."\n";
		$p .= "?>\n";
		
		return
		$p.
		'<?php if ($_ctx->pagination->f(0) > $_ctx->posts->count()) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	# Retrieve URL for a given gallery item thumbnail
	# attributes :
	#   * size : gives the size of requested thumb (default : 's')
	#   * bestfit : retrieve standard URL if thumbnail does not exist
	public static function GalleryItemThumbURL($attr) 
	{
		$size = isset($attr['size']) ? addslashes($attr['size']) : 's';
		$bestfit = isset($attr['bestfit']);
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		if ($bestfit) {
			$append=' else echo '.sprintf($f,'$_ctx->media->file_url').';';
		} else {
			$append='';
		}
		return '<?php '.
		'if (isset($_ctx->media->media_thumb[\''.$size.'\'])) {'.
			'echo '.sprintf($f,'$_ctx->media->media_thumb[\''.$size.'\']').';'.
		'}'.$append.
		'?>';
	}

	# Retrieve URL for a given gallery item 
	public static function GalleryMediaURL($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return
		'<?php '.
			'echo '.sprintf($f,'$_ctx->media->file_url').';'.
		'?>';
	}

	public static function GalleryItemNext($attr,$content) {
		$nb = isset($attr['nb']) ? (integer)($attr['nb']) : 1;
		return
		'<?php $next_post = $core->gallery->getNextGalleryItem($_ctx->posts,1,$_ctx->gallery_url,'.$nb.'); ?>'."\n".
		'<?php if ($next_post !== null) : ?>'.

			'<?php $_ctx->posts = $next_post; unset($next_post);'."\n".
			'while ($_ctx->posts->fetch()) : '.
			'$_ctx->media = $core->gallery->readMedia($_ctx->posts) ; ?>'.
			$content.'<?php $_ctx->media = null; endwhile; $_ctx->posts = null; ?>'.
		"<?php endif; ?>\n";
	}

	public static function GalleryItemPrevious($attr,$content) {
		$nb = isset($attr['nb']) ? (integer)($attr['nb']) : 1;
		return
		'<?php $next_post = $core->gallery->getNextGalleryItem($_ctx->posts,-1,$_ctx->gallery_url,'.$nb.'); ?>'."\n".
		'<?php if ($next_post !== null) : ?>'.

			'<?php $_ctx->posts = $next_post; unset($next_post);'."\n".
			'for ($i=$_ctx->posts->count()-1; $i >=0; $i-- ) : '.
			'$_ctx->posts->index($i);'."\n".
			'$_ctx->media = $core->gallery->readMedia($_ctx->posts) ; ?>'.
			$content.'<?php $_ctx->media = null; endfor; $_ctx->posts = null; ?>'.
		"<?php endif; ?>\n";
	}


	public static function GalleryItemURL($attr)
	{
		global $_ctx;
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$querychar=($GLOBALS['core']->blog->settings->url_scan == 'path_info')?'?':'&amp;';
		return '<?php if (!is_null($_ctx->gallery_url)): $append="'.
			$querychar.'gallery=".$_ctx->gallery_url; else: $append=""; endif;'.
			'echo '.sprintf($f,'$_ctx->posts->getURL()').'.$append; unset($append); ?>';
	}

	public static function GalleryItemIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';
		if (isset($attr['gallery_set'])) {
			$sign= (boolean) $attr['gallery_set'] ? '' : '!';
			$if[] = $sign.'is_null($_ctx->gallery_url)';
		}
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}

	}

	public static function GalleryItemCount($attr) {
		return '<?php echo $core->gallery->getGalItemCount($_ctx->posts); ?>';
	}

	public static function GalleryItemGalleries($attr,$content)
	{
		$res = "<?php\n";
		$res .= '$_ctx->posts = $core->gallery->getImageGalleries($_ctx->posts->post_id);'."\n";
		$res .=
		'while ($_ctx->posts->fetch()) : $core->gallery->fillGalleryContext($_ctx); ?>'."\n".
		$content.'<?php endwhile; '.
		'$core->gallery->emptyGalleryContext($_ctx); $_ctx->posts = null; ?>';
		
		return $res;
	}

	public static function GalleryItemGallery($attr,$content)
	{
		$res = "<?php\n";
		$res .= 'if (!is_null($_ctx->gallery_url)) {'."\n".
			'  $params["post_url"]=$_ctx->gallery_url;'."\n".
			'  $_ctx->posts = $core->gallery->getGalleries($params); unset($params);'."\n".
			'} else {'."\n".
			'  $_ctx->posts = $core->gallery->getImageGalleries($_ctx->posts->post_id);'."\n".
			'}'.
			'if (!$_ctx->posts->isEmpty()) : $core->gallery->fillGalleryContext($_ctx); ?>'."\n".
			$content.'<?php endif; '.
			'$core->gallery->emptyGalleryContext($_ctx); $_ctx->posts = null; ?>';
		
		return $res;
	}

	public static function GalleryURLWithPage($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$p = '<?php $gal_url =  '.sprintf($f,'$_ctx->posts->getURL()').';'."\n".
			'if ($_ctx->exists("current_item_url")){'."\n".
			'$page = $core->gallery->getGalleryItemPage('.
			'$_ctx->current_item_url,$_ctx->posts);'."\n".
			'if ($page > 1) $gal_url .= "/page/".$page;}'."\n".
			'echo $gal_url; ?>';
		return $p;
		

	}
	public static function GalleryItemFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'rss2';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'rss2';
		}
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("galitem")."/feed/'.$type.'"').'; ?>';
	      /*return '<?php echo '.sprintf($f,'$_ctx->posts->getURL()."/feed/'.$type.'"').'; ?>';*/
	}

}

class tplGalleryUtils
{
	/* Misc functions -------------------------------------------- */
	public static function doOnce($attr,$content)
	{
		if (!isset($attr['id'])) return;
		$id = $attr['id'];
		$p =  '<?php '.
		'if (!isset($GLOBALS["dcOnce"])) $GLOBALS["dcOnce"] = array();'."\n".
		'if (!in_array("'.$id.'",$GLOBALS["dcOnce"])): '."\n".
		'  $GLOBALS["dcOnce"][] = "'.$id.'"; ?>'."\n".$content.'<?php endif; ?>'."\n";
		return $p;

	}
}
?>
