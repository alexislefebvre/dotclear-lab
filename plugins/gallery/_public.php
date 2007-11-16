<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Gallery plugin for DC2 is free sofwtare; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

require dirname(__FILE__).'/_widgets.php';


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
$core->tpl->addValue('GalleryItemFeedURL',array('tplGallery','GalleryItemFeedURL'));


/* StyleSheets URL */
$core->tpl->addValue('GalleryStyleURL',array('tplGallery','GalleryStyleURL'));
$core->tpl->addValue('GalleryStylePath',array('tplGallery','GalleryStylePath'));
$core->tpl->addValue('GalleryJSPath',array('tplGallery','GalleryJSPath'));

/* Templates dir */
$core->addBehavior('publicBeforeDocument',array('behaviorsGallery','addTplPath'));

// Later on, some rest features :)
if (!empty($core->pubrest))
$core->pubrest->register('gallery','restGallery');

class behaviorsGallery
{
  public static function addTplPath(&$core)
  {
    $core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/default-templates');
  }

}

class tplGallery
{
	/* Misc functions -------------------------------------------- */
	public static function GalleryStyleURL($attr,$content)
	{
		global $core;
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$css = $core->blog->url.(($core->blog->settings->url_scan == 'path_info')?'?':'').'pf=gallery/default-templates/'
			.$core->blog->settings->gallery_default_theme.'/gallery.css';
		$res = "\n<?php echo '<style type=\"text/css\" media=\"screen\">@import url(".$css.");</style>';\n?>";
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
			'echo '.sprintf($f,'$_ctx->posts->cat_title').';'."\n".
			'} else {'."\n".
			'echo "'.__('No category').'";'."\n".
			'} ?>';
	}

	# Lists galleries
	public static function GalleryEntries($attr,$content)
	{
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "\$params['limit'] = \$core->blog->settings->nb_post_per_page;\n";
		}
		
		$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
		
		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}
		if (isset($attr['orderbycat'])) {
			$p .= "\$params['order'] = 'C.cat_position asc';\n";
		}

		
		
		$p .=
		'if ($_ctx->exists("categories")) { '.
			"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
		"}\n";

		$p .=
		'if ($_ctx->exists("nocat")) { '.
			"\$params['sql'] = ' AND C.cat_id is NULL '; ".
		"}\n";
	
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$_ctx->posts = $core->gallery->getGalleries($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '.
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
			'while ($_ctx->posts->fetch()) : ?>'.
			$content.
			'<?php endwhile; $_ctx->posts = null; ?>'.
		"<?php endif; ?>\n";
	}

	# Retrieve previous gallery
	public static function GalleryEntryPrevious($attr,$content)
	{
		return
		'<?php $prev_post = $core->gallery->getNextGallery($_ctx->posts->post_id,strtotime($_ctx->posts->post_dt),-1); ?>'."\n".
		'<?php if ($prev_post !== null) : ?>'.
			
			'<?php $_ctx->posts = $prev_post; unset($prev_post);'."\n".
			'while ($_ctx->posts->fetch()) : ?>'.
			$content.
			'<?php endwhile; $_ctx->posts = null; ?>'.
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
		
		if (!preg_match('#^(rss2|atom|mediarss)$#',$type)) {
			$type = 'rss2';
		}
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("gal")."/feed/'.$type.'"').'; ?>';
       	      /*return '<?php echo '.sprintf($f,'$_ctx->posts->getURL()."/feed/'.$type.'"').'; ?>';*/
	}

	/*public static function GalEntryPrevious($attr,$content)
	{
		return
		'<?php $current_cat = (issetif ($if (!empty($_ctx->current_cat)) $isnewcat=true;'."\n".
		' else
		$content.
		'<?php endif;?>
	}*/

	/* Entries -------------------------------------------- */
	
	# List all items from a gallery
	public static function GalleryItemEntries($attr,$content)
	{
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
		$p .= 'if (!is_null($_ctx->gal_params)) $params = $_ctx->gal_params;'."\n";
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "\$params['limit'] = \$core->blog->settings->gallery_nb_images_per_page;\n";
		}
		
		$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
		
		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}
		
		/*$p .= "\$params['post_type'] ='gal';\n";
		$p .= "\$params['gal_url'] = \$_ctx->posts->post_url;\n";
		$p .= "\$params = array_merge(\$params, \$core->gallery->getGalOrder(\$_ctx->posts));\n";*/
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		//$res .= 'if (isset($_ctx->posts->post_url)) $_ctx->gallery_url = $_ctx->posts->post_url;'."\n";
		
		$res .= '$_ctx->posts = $core->gallery->getGalImageMedia($params); unset($params);'."\n";
		
		$res .=
		'while ($_ctx->posts->fetch()) : '."\n".
		' $_ctx->media = $core->gallery->readMedia($_ctx->posts);?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';
		
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
			'echo '.sprintf($f,'$_ctx->posts->getURL()').'.$append; ?>';
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
		'while ($_ctx->posts->fetch()) : ?>'."\n".
		$content.'<?php endwhile; '.
		'$_ctx->posts = null; ?>';
		
		return $res;
	}

	public static function GalleryItemGallery($attr,$content)
	{
		$res = "<?php\n";
		$res .= 'if (!is_null($_ctx->gallery_url)) {'."\n".
			'  $params["post_url"]=$_ctx->gallery_url;'."\n".
			'  $_ctx->posts = $core->gallery->getGalleries($params);'."\n".
			'} else {'."\n".
			'  $_ctx->posts = $core->gallery->getImageGalleries($_ctx->posts->post_id);'."\n".
			'}'.
			'if (!$_ctx->posts->isEmpty()) : ?>'."\n".
			$content.'<?php endif; '.
			'$_ctx->posts = null; ?>';
		
		return $res;
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


	# Gallery Widget function
	public static function listgalWidget(&$w)
	{
                global $core;

		if (empty($core->meta)) $core->meta = new dcMeta($core);
		if (empty($core->gallery)) $core->gallery = new dcGallery($core);
                if ($w->homeonly && $core->url->type != 'default') {
                        return;
                }

                $title = $w->title ? html::escapeHTML($w->title) : __('Galleries');

		$display = $w->display;
		$orderby = $w->orderby;
		$orderdir = $w->orderdir;
		$display_cat = ($display == 'cat_only') || ($display == 'both');
		$display_gal = ($display == 'gal_only') || ($display == 'both');
		$order="";
		if ($display_cat) {
			$order="C.cat_position asc, ";
		}
		if ($orderby == 'date')
			$order .= 'P.post_dt ';
		else
			$order .= 'P.post_title ';
		$order .= ($orderdir == 'asc') ? 'asc':'desc';

                $params = array(
                        'no_content'=>true,
                        'order'=>$order);

                $rs = $core->gallery->getGalleries($params);

                if ($rs->isEmpty()) {
                        return;
		}

                $res =
                '<div id="galleries">'.
                '<h2><a href="'.$core->blog->url.$core->url->getBase('galleries').'">'.$title.'</a></h2>'.
		$current_cat = "";
		if (!$display_cat) {
			$res .= '<ul>';
		}
		$first=true;
                while ($rs->fetch()) {
			if ($display_cat) {
				if ($rs->cat_title == "") {
					$cat_title=__("No category");
					$cat_link=$core->blog->url.$core->url->getBase('galleries')."/nocat";
				} else {
					$cat_title=$rs->cat_title;
					$cat_link=$core->blog->url.$core->url->getBase('galleries')."/category/".$rs->cat_url;
				}
				if ($current_cat != $cat_title) {
					if (!$first) {
						if ($display_gal) 
							$res .= '</ul>';
					} else {
						$first=false;
					}
					$res .= ' <h3><a href="'.$cat_link.'">'.$cat_title.'</a></h3>';
					$current_cat = $cat_title;
					if ($display_gal)
						$res .= '<ul>';
				}
			}
			if ($display_gal)
				$res .= ' <li><a href="'.$rs->getURL().'">'.html::escapeHTML($rs->post_title).' ('.$core->gallery->getGalItemCount($rs).')'.'</a></li> ';
                }
		if (!$display_cat) {
			$res .= '</ul>';
		}

                $res .= '</div>';

                return $res;
	}

	# Gallery Widget function
	public static function randimgWidget(&$w)
	{
		global $core;

                if ($w->homeonly && $core->url->type != 'default') {
                        return;
                }

		if (empty($core->gallery)) $core->gallery = new dcGallery($core);
                $title = $w->title ? html::escapeHTML($w->title) : __('Random Image');
		$img = $core->gallery->getRandomImage();
		$media = $core->gallery->readMedia($img);
		$p  = '<div id="randomimage">';
		$p .= '<h2>'.$title.'</h2>';
		$p .= '<a href="'.$img->getURL().'">'; 
		$p .= '<img src="'.$media->media_thumb["t"].'" alt="'.html::escapeHTML($img->post_title).'" />';
		$p .= '</a>';
		$p .= '</div>';
		return $p;


	}

	public static function lastimgWidget(&$w)
	{
		global $core;

                if ($w->homeonly && $core->url->type != 'default') {
                        return;
                }

		if (empty($core->gallery)) $core->gallery = new dcGallery($core);
                $title = $w->title ? html::escapeHTML($w->title) : __('Last images');
		$nb_last = $w->limit;
		$display = $w->display;
		$params['limit']=$w->limit;
		$params['order']='P.post_dt DESC';
		$img = $core->gallery->getGalImageMedia($params);
		$p  = '<div id="lastimage">';
		$p .= '<h2>'.$title.'</h2>';
		while ($img->fetch()) {
			$media = $core->gallery->readMedia($img);
			$p .= '<a href="'.$img->getURL().'">'; 
			$p .='<img src="'.$media->media_thumb["sq"].'" style="float:left;"  alt="'.html::escapeHTML($img->post_title).'"/>';
			$p .= '</a>';
		}
		$p .= '<p style="clear: both;"></p></div>';
		return $p;


	}



}


class restGallery {
	public static function getImages(&$core,$get,$post)
	{
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);

		$maxrequest=100;
		if (!empty($get['tag'])) {
			$params['tag']=$get['tag'];
		}
		if (!empty($get['galId'])) {
			$params['gal_id']=$get['galId'];
		}
		if (!empty($get['start'])) {
			$start=(integer)$get['start'];
		} else {
			$start=0;
		}
		if (!empty($get['limit']) && ($get['limit'] <= $maxrequest)) {
			$limit = (integer)$get['limit'];
		} else {
			$limit = $maxrequest;
		}
		$params['limit']=array($start,$limit);
		$rs = $core->gallery->getGalImageMedia($params);

		$rsp = new xmlTag();
		while ($rs->fetch()) {
			$media = $core->gallery->readmedia($rs);
			$imgTag = new xmlTag('image');
			$imgTag->id=$rs->post_id;
			$imgTag->thumb=$media->media_thumb["sq"];
			$imgTag->url=$media->file_url;
			$imgTag->post_url=$rs->getURL();
			$imgTag->title=$rs->post_title;

			$rsp->insertNode($imgTag);
		}

		return $rsp;
	}

	public static function getAllImageTags(&$core,$get,$post)
	{
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$params['limit']=100;
		$rs = $core->meta->getMeta('tag',null,null,null,'galitem');

		$rsp = new xmlTag();
		while ($rs->fetch()) {
			$metaTag = new xmlTag('tag');
			$metaTag->id = $rs->meta_id;
			$metaTag->count = $rs->count;
			$rsp->insertNode($metaTag);
		}
		return $rsp;
	}
	public static function getCategories(&$core,$get,$post)
	{
		$params['post_type']='galitem';
		$rs = $core->blog->getCategories($params);
		$rsp = new xmlTag();
		while ($rs->fetch()) {
			$catTag = new xmlTag('cat');
			$catTag->id = $rs->cat_id;
			$catTag->title = $rs->cat_title;
			$rsp->insertNode($catTag);
		}
		return $rsp;
	}
	public static function getDates(&$core,$get,$post)
	{
		$params['post_type']='galitem';
		$params['type']='month';
		$rs = $core->blog->getDates($params);
		$rsp = new xmlTag();
		while ($rs->fetch()) {
			$dateTag = new xmlTag('date');
			$dateTag->dt = $rs->dt;
			$dateTag->count = $rs->nb_post;
			$rsp->insertNode($dateTag);
		}
		return $rsp;
	}
}

class urlGallery extends dcUrlHandlers
{
	public static function gallery($args)
	{
		$n = self::getPageNumber($args);
		if (preg_match('%(^|/)feed/(mediarss|rss2|atom)/([0-9]+)$%',$args,$m)){
			$args = preg_replace('#(^|/)feed/(mediarss|rss2|atom)/([0-9]+)$#','',$args);
			$type = $m[2];
			$page = "feed/img-".$type.".xml";
			$mime = 'application/xml';
			$params['post_id'] = $m[3];
		} elseif (preg_match('%(^|/)feed/(mediarss|rss2|atom)/comments/([0-9]+)$%',$args,$m)){
			$args = preg_replace('#(^|/)feed/(mediarss|rss2|atom)/comments/([0-9]+)$#','',$args);
			$type = $m[2];
			$page = "feed/gal-".$type."-comments.xml";
			$mime = 'application/xml';
			$params['post_id'] = $m[3];
		} elseif ($args != '') {
			$page=$GLOBALS['core']->blog->settings->gallery_default_theme.'/gallery.html';
		$params['post_url'] = $args;
			$mime='text/html';
		} else {
			self::p404();
		}
		if ($n) {
			$GLOBALS['_page_number'] = $n;
			$GLOBALS['core']->url->type = $n > 1 ? 'defaut-page' : 'default';
		}

		$GLOBALS['core']->blog->withoutPassword(false);
		$GLOBALS['core']->meta = new dcMeta($GLOBALS['core']);;
		$GLOBALS['core']->gallery = new dcGallery($GLOBALS['core']);;
		$GLOBALS['_ctx']->posts = $GLOBALS['core']->gallery->getGalleries($params);
		/*$GLOBALS['_ctx']->posts->extend('rsExtGallery');*/
		$gal_params = $GLOBALS['core']->gallery->getGalOrder($GLOBALS['_ctx']->posts);
		$gal_params['gal_url']=$GLOBALS['_ctx']->posts->post_url;
		$GLOBALS['_ctx']->gal_params = $gal_params;
		$GLOBALS['_ctx']->comment_preview = new ArrayObject();
		$GLOBALS['_ctx']->comment_preview['content'] = '';
		$GLOBALS['_ctx']->comment_preview['rawcontent'] = '';
		$GLOBALS['_ctx']->comment_preview['name'] = '';
		$GLOBALS['_ctx']->comment_preview['mail'] = '';
		$GLOBALS['_ctx']->comment_preview['site'] = '';
		$GLOBALS['_ctx']->comment_preview['preview'] = false;
		$GLOBALS['_ctx']->comment_preview['remember'] = false;
		
		$GLOBALS['core']->blog->withoutPassword(true);
		
		$post_comment =
			isset($_POST['c_name']) && isset($_POST['c_mail']) &&
			isset($_POST['c_site']) && isset($_POST['c_content']);
		
		
		if ($GLOBALS['_ctx']->posts->isEmpty())
		{
			# No entry
			self::p404();
		}
		
		$post_id = $GLOBALS['_ctx']->posts->post_id;
		$post_password = $GLOBALS['_ctx']->posts->post_password;
		
		# Getting commenter informations from cookie
		if (!empty($_COOKIE['comment_info'])) {
			$c_cookie = unserialize($_COOKIE['comment_info']);
			foreach ($c_cookie as $k => $v) {
				$GLOBALS['_ctx']->comment_preview[$k] = $v;
			}
			$GLOBALS['_ctx']->comment_preview['remember'] = true;
		}
		
		# Password protected entry
		if ($post_password != '')
		{
			# Get passwords cookie
			if (isset($_COOKIE['dc_passwd'])) {
				$pwd_cookie = unserialize($_COOKIE['dc_passwd']);
			} else {
				$pwd_cookie = array();
			}
			
			# Check for match
			if ((!empty($_POST['password']) && $_POST['password'] == $post_password)
			|| (isset($pwd_cookie[$post_id]) && $pwd_cookie[$post_id] == $post_password))
			{
				$pwd_cookie[$post_id] = $post_password;
				setcookie('dc_passwd',serialize($pwd_cookie),0,'/');
			}
			else
			{
				self::serveDocument('password-form.html','text/html',false);
				exit;
			}
		}
		
		# Posting a comment
		if ($post_comment)
		{
			# Spam trap
			if (!empty($_POST['f_mail'])) {
				http::head(412,'Precondition Failed');
				header('Content-Type: text/plain');
				echo "So Long, and Thanks For All the Fish";
				exit;
			}
			
			$name = $_POST['c_name'];
			$mail = $_POST['c_mail'];
			$site = $_POST['c_site'];
			$content = $_POST['c_content'];
			$preview = !empty($_POST['preview']);
			
			# Storing commenter informations in cookie
			if (!empty($_POST['c_remember'])) {
				$c_cookie = array('name' => $name,'mail' => $mail,
				'site' => $site);
				
				$c_cookie = serialize($c_cookie);
				setcookie('comment_info',$c_cookie,strtotime('+3 month'),'/');
			}
			
			if ($content != '')
			{
				if ($GLOBALS['core']->blog->settings->wiki_comments) {
					$GLOBALS['core']->initWikiComment();
				} else {
					$GLOBALS['core']->initWikiSimpleComment();
				}
				$content = $GLOBALS['core']->wikiTransform($content);
				$content = $GLOBALS['core']->HTMLfilter($content);
			}
			
			$GLOBALS['_ctx']->comment_preview['content'] = $content;
			$GLOBALS['_ctx']->comment_preview['rawcontent'] = $_POST['c_content'];
			$GLOBALS['_ctx']->comment_preview['name'] = $name;
			$GLOBALS['_ctx']->comment_preview['mail'] = $mail;
			$GLOBALS['_ctx']->comment_preview['site'] = $site;
			
			if ($preview)
			{
				$GLOBALS['_ctx']->comment_preview['preview'] = true;
			}
			else
			{
				# Post the comment
				$cur = $GLOBALS['core']->con->openCursor($GLOBALS['core']->prefix.'comment');
				$cur->comment_author = $name;
				$cur->comment_site = html::clean($site);
				$cur->comment_email = html::clean($mail);
				$cur->comment_content = $content;
				$cur->post_id = $GLOBALS['_ctx']->posts->post_id;
				$cur->comment_status = $GLOBALS['core']->blog->settings->comments_pub ? 1 : -1;
				$cur->comment_ip = http::realIP();
				
				$redir = $GLOBALS['_ctx']->posts->getURL();
				$redir .= strpos($redir,'?') !== false ? '&' : '?';
				
				try
				{
					if (!text::isEmail($cur->comment_email)) {
						throw new Exception(__('You must provide a valid email adress.'));
					}
					
					# --BEHAVIOR-- publicBeforeCommentCreate
					$GLOBALS['core']->callBehavior('publicBeforeCommentCreate',$cur);
					
					$comment_id = $GLOBALS['core']->blog->addComment($cur);
					
					# --BEHAVIOR-- publicAfterCommentCreate
					$GLOBALS['core']->callBehavior('publicAfterCommentCreate',$cur,$comment_id);
					
					if ($cur->comment_status == 1) {
						$redir_arg = 'pub=1';
					} else {
						$redir_arg = 'pub=0';
					}
					
					header('Location: '.$redir.$redir_arg);
					exit;
				}
				catch (Exception $e)
				{
					$GLOBALS['_ctx']->form_error = $e->getMessage();
					$GLOBALS['_ctx']->form_error;
				}
			}
		}
		
		# The entry
		self::serveDocument($page,$mime);
		exit;
	}
	
	public static function galleries($args)
	{
                if (preg_match('#(^|/)category/(.+)$#',$args,$m)){
			$params['cat_url']=$m[2];
			$GLOBALS['_ctx']->categories = $GLOBALS['core']->blog->getCategories($params);
		}
                if (preg_match('#(^|/)nocat$#',$args,$m)){
			$GLOBALS['_ctx']->nocat = true;
		}
		$GLOBALS['core']->meta = new dcMeta($GLOBALS['core']);;
		$GLOBALS['core']->gallery = new dcGallery($GLOBALS['core']);
		self::serveDocument('default/galleries.html');
		exit;
	}

	public static function image($args)
	{
		if (preg_match('%(^|/)feed/(mediarss|rss2|atom)/comments/([0-9]+)$%',$args,$m)){
			$args = preg_replace('#(^|/)feed/(mediarss|rss2|atom)/comments/([0-9]+)$#','',$args);
			$type = $m[2];
			$page = $type."-comments.xml";
			$mime = 'application/xml';
			$params['post_id'] = $m[3];
		} elseif ($args != '') {
			$page='default/image.html';
			$params['post_url'] = $args;
			$mime='text/html';
		} else {
			self::p404();
		}
		/*if ($args == '') {
			self::p404();
		}*/
		
		$GLOBALS['core']->blog->withoutPassword(false);
		
		$params['post_type'] = 'galitem';
		//$params['post_url'] = $args;
		$GLOBALS['core']->meta = new dcMeta($GLOBALS['core']);;
		$GLOBALS['core']->gallery = new dcGallery($GLOBALS['core']);
		/*$GLOBALS['core']->meta = new dcMeta($GLOBALS['core']);*/
		$GLOBALS['_ctx']->gallery_url = isset($_GET['gallery'])?$_GET['gallery']:null;
		$GLOBALS['_ctx']->posts = $GLOBALS['core']->gallery->getGalImageMedia($params);
		
		$GLOBALS['_ctx']->comment_preview = new ArrayObject();
		$GLOBALS['_ctx']->comment_preview['content'] = '';
		$GLOBALS['_ctx']->comment_preview['rawcontent'] = '';
		$GLOBALS['_ctx']->comment_preview['name'] = '';
		$GLOBALS['_ctx']->comment_preview['mail'] = '';
		$GLOBALS['_ctx']->comment_preview['site'] = '';
		$GLOBALS['_ctx']->comment_preview['preview'] = false;
		$GLOBALS['_ctx']->comment_preview['remember'] = false;
		
		$GLOBALS['core']->blog->withoutPassword(true);
		$GLOBALS['_ctx']->media=$GLOBALS['core']->gallery->readMedia($GLOBALS['_ctx']->posts);
/*		$GLOBALS['_ctx']->galitems = $GLOBALS['core']->media->getPostMedia($GLOBALS['_ctx']->posts->post_id);
		$GLOBALS['_ctx']->galitem=$GLOBALS['_ctx']->galitems[0];*/
		$post_comment =
			isset($_POST['c_name']) && isset($_POST['c_mail']) &&
			isset($_POST['c_site']) && isset($_POST['c_content']);
		
		
		if ($GLOBALS['_ctx']->posts->isEmpty())
		{
			# No entry
			self::p404();
		}
		
		$post_id = $GLOBALS['_ctx']->posts->post_id;
		$post_password = $GLOBALS['_ctx']->posts->post_password;
		
		# Getting commenter informations from cookie
		if (!empty($_COOKIE['comment_info'])) {
			$c_cookie = unserialize($_COOKIE['comment_info']);
			foreach ($c_cookie as $k => $v) {
				$GLOBALS['_ctx']->comment_preview[$k] = $v;
			}
			$GLOBALS['_ctx']->comment_preview['remember'] = true;
		}
		
		# Password protected entry
		if ($post_password != '')
		{
			# Get passwords cookie
			if (isset($_COOKIE['dc_passwd'])) {
				$pwd_cookie = unserialize($_COOKIE['dc_passwd']);
			} else {
				$pwd_cookie = array();
			}
			
			# Check for match
			if ((!empty($_POST['password']) && $_POST['password'] == $post_password)
			|| (isset($pwd_cookie[$post_id]) && $pwd_cookie[$post_id] == $post_password))
			{
				$pwd_cookie[$post_id] = $post_password;
				setcookie('dc_passwd',serialize($pwd_cookie),0,'/');
			}
			else
			{
				self::serveDocument('password-form.html','text/html',false);
				exit;
			}
		}
		
		# Posting a comment
		if ($post_comment)
		{
			# Spam trap
			if (!empty($_POST['f_mail'])) {
				http::head(412,'Precondition Failed');
				header('Content-Type: text/plain');
				echo "So Long, and Thanks For All the Fish";
				exit;
			}
			
			$name = $_POST['c_name'];
			$mail = $_POST['c_mail'];
			$site = $_POST['c_site'];
			$content = $_POST['c_content'];
			$preview = !empty($_POST['preview']);
			
			# Storing commenter informations in cookie
			if (!empty($_POST['c_remember'])) {
				$c_cookie = array('name' => $name,'mail' => $mail,
				'site' => $site);
				
				$c_cookie = serialize($c_cookie);
				setcookie('comment_info',$c_cookie,strtotime('+3 month'),'/');
			}
			
			if ($content != '')
			{
				if ($GLOBALS['core']->blog->settings->wiki_comments) {
					$GLOBALS['core']->initWikiComment();
				} else {
					$GLOBALS['core']->initWikiSimpleComment();
				}
				$content = $GLOBALS['core']->wikiTransform($content);
				$content = $GLOBALS['core']->HTMLfilter($content);
			}
			
			$GLOBALS['_ctx']->comment_preview['content'] = $content;
			$GLOBALS['_ctx']->comment_preview['rawcontent'] = $_POST['c_content'];
			$GLOBALS['_ctx']->comment_preview['name'] = $name;
			$GLOBALS['_ctx']->comment_preview['mail'] = $mail;
			$GLOBALS['_ctx']->comment_preview['site'] = $site;
			
			if ($preview)
			{
				$GLOBALS['_ctx']->comment_preview['preview'] = true;
			}
			else
			{
				# Post the comment
				$cur = $GLOBALS['core']->con->openCursor($GLOBALS['core']->prefix.'comment');
				$cur->comment_author = $name;
				$cur->comment_site = html::clean($site);
				$cur->comment_email = html::clean($mail);
				$cur->comment_content = $content;
				$cur->post_id = $GLOBALS['_ctx']->posts->post_id;
				$cur->comment_status = $GLOBALS['core']->blog->settings->comments_pub ? 1 : -1;
				$cur->comment_ip = http::realIP();
				
				$redir = $GLOBALS['_ctx']->posts->getURL();
				$redir .= strpos($redir,'?') !== false ? '&' : '?';
				
				try
				{
					if (!text::isEmail($cur->comment_email)) {
						throw new Exception(__('You must provide a valid email adress.'));
					}
					
					# --BEHAVIOR-- publicBeforeCommentCreate
					$GLOBALS['core']->callBehavior('publicBeforeCommentCreate',$cur);
					
					$comment_id = $GLOBALS['core']->blog->addComment($cur);
					
					# --BEHAVIOR-- publicAfterCommentCreate
					$GLOBALS['core']->callBehavior('publicAfterCommentCreate',$cur,$comment_id);
					
					if ($cur->comment_status == 1) {
						$redir_arg = 'pub=1';
					} else {
						$redir_arg = 'pub=0';
					}
					
					header('Location: '.$redir.$redir_arg);
					exit;
				}
				catch (Exception $e)
				{
					$GLOBALS['_ctx']->form_error = $e->getMessage();
					$GLOBALS['_ctx']->form_error;
				}
			}
		}
		//self::serveDocument('image.html');
		self::serveDocument($page,$mime);
		exit;
	}

	public static function images($args)
	{
		$n = self::getPageNumber($args);
		if (preg_match('#(^|/)([A-Za-z]+)/(.+)$#',$args,$m)) {
			$filter_type = $m[2];
			$filter = $m[3];
		} else {
			self::p404();
		}
		switch ($filter_type) {
			case "tag":
				$gal_params['tag']=$filter;
				break;
			case "category":
				$gal_params['cat_url']=$filter;
				break;
			default:
				self::p404();
				return;
		}
		
		if ($n) {
			$GLOBALS['_page_number'] = $n;
			$GLOBALS['core']->url->type = $n > 1 ? 'defaut-page' : 'default';
		}

		$GLOBALS['core']->blog->withoutPassword(false);
		$GLOBALS['core']->meta = new dcMeta($GLOBALS['core']);;
		$GLOBALS['core']->gallery = new dcGallery($GLOBALS['core']);;
		
		$params['post_url'] = $args;
		$GLOBALS['_ctx']->posts = $GLOBALS['core']->gallery->getGalleries($params);
		$GLOBALS['_ctx']->gal_params = $gal_params;
		
		$post_comment =
			isset($_POST['c_name']) && isset($_POST['c_mail']) &&
			isset($_POST['c_site']) && isset($_POST['c_content']);
		
		
		
		# The entry
		self::serveDocument('default/images.html');
		exit;
	}
	public static function browse($args)
	{
		self::serveDocument('browser.html');
		exit;
	}


}


?>
