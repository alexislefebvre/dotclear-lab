<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
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
$core->tpl->addBlock('GalleryEntries',array('tplGallery','GalEntries'));
$core->tpl->addBlock('GalleryEntryNext',array('tplGallery','GalEntryNext'));
$core->tpl->addBlock('GalleryEntryPrevious',array('tplGallery','GalEntryPrevious'));

/* Galleries items management */
$core->tpl->addBlock('GalleryItemEntries',array('tplGallery','GalItemEntries'));
$core->tpl->addBlock('GalleryPagination',array('tplGallery','GalPagination'));
$core->tpl->addValue('GalleryItemThumbURL',array('tplGallery','GalItemThumbURL'));
$core->tpl->addValue('GalleryMediaURL',array('tplGallery','GalMediaURL'));
$core->tpl->addValue('GalleryItemURL',array('tplGallery','GalItemURL'));
$core->tpl->addBlock('GalleryItemNext',array('tplGallery','GalItemNext'));
$core->tpl->addBlock('GalleryItemPrevious',array('tplGallery','GalItemPrevious'));


/* StyleSheets URL */
$core->tpl->addValue('GalleryStyleURL',array('tplGallery','GalStyleURL'));
$core->tpl->addValue('GalleryDynJSURL',array('tplGallery','GalDynJSURL'));

/* Templates dir */
$core->addBehavior('publicBeforeDocument',array('behaviorsGallery','addTplPath'));

// Later on, some rest features :)
#$core->pubrest->register('gallery','restGallery');

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
	public static function GalStyleURL($attr,$content)
	{
		global $core;
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$css = $core->blog->url.(($core->blog->settings->url_scan == 'path_info')?'?':'').'pf=gallery/default-templates/gallery.css';
		$res = "\n<?php echo '<style type=\"text/css\" media=\"screen\">@import url(".$css.");</style>';\n?>";
                return $res;

	}

	public static function GalDynJSURL($attr,$content)
	{
		global $core;
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$js = $core->blog->url.(($core->blog->settings->url_scan == 'path_info')?'?':'').'pf=gallery/default-templates/imgbrowser.js';
		$res = "\n<?php echo '<script type=\"text/javascript\" src=\"".$js."\">';\n?>";
                return $res;

	}
	/* Gallery lists templates */

	# Lists galleries
	public static function GalEntries($attr,$content)
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
		
		
		$p .=
		'if ($_ctx->exists("categories")) { '.
			"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
		"}\n";
	
		$p .= "\$params['post_type'] ='gal';\n";
		

		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$_ctx->posts = $core->blog->getPosts($params); 		$_ctx->posts->extend("rsExtGallery"); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';
		
		return $res;
	}

	# Retrieve next gallery
	public static function GalEntryNext($attr,$content)
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
	public static function GalEntryPrevious($attr,$content)
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

	/* Entries -------------------------------------------- */
	
	# List all items from a gallery
	public static function GalItemEntries($attr,$content)
	{
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
/*			$p .= "\$params['limit'] = \$core->blog->settings->nb_post_per_page;\n";*/
			$p .= "\$params['limit'] = 24;\n";
		}
		
		$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
		
		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}
		
		$p .= "\$params['post_type'] ='gal';\n";
		$p .= "\$params = array_merge(\$params, \$core->gallery->getGalFilters(\$_ctx->posts));\n";
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$_ctx->gallery_url = $_ctx->posts->post_url;'."\n";
		
		$res .= '$_ctx->posts = $core->gallery->getGalImageMedia($params); unset($params);'."\n";
		$res .= "/*\$_ctx->posts->extend('rsExtImage'); */?>\n";
		
		$res .=
		'<?php while ($_ctx->posts->fetch()) : '."\n".
		' $_ctx->media = $core->gallery->readMedia($_ctx->posts);?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';
		
		return $res;
	}

	# Enable paging for galleries items lists
	public static function GalPagination($attr,$content)
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
	public static function GalItemThumbURL($attr) 
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
	public static function GalMediaURL($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
                return
                '<?php '.
                        'echo '.sprintf($f,'$_ctx->media->file_url').';'.
                '?>';
	}

        public static function GalItemNext($attr,$content) {
                return
                '<?php $next_post = $core->gallery->getNextGalleryItem($_ctx->posts->post_id,strtotime($_ctx->posts->post_dt),1,$_ctx->gallery_url); ?>'."\n".
                '<?php if ($next_post !== null) : ?>'.

                        '<?php $_ctx->posts = $next_post; unset($next_post);'."\n".
                        'while ($_ctx->posts->fetch()) : '.
                        '$_ctx->media = $core->gallery->readMedia($_ctx->posts) ; ?>'.
                        $content.'<?php $_ctx->media = null; endwhile; $_ctx->posts = null; ?>'.
                "<?php endif; ?>\n";
	}

        public static function GalItemPrevious($attr,$content) {
                return
                '<?php $next_post = $core->gallery->getNextGalleryItem($_ctx->posts->post_id,strtotime($_ctx->posts->post_dt),-1,$_ctx->gallery_url); ?>'."\n".
                '<?php if ($next_post !== null) : ?>'.

                        '<?php $_ctx->posts = $next_post; unset($next_post);'."\n".
                        'while ($_ctx->posts->fetch()) : '.
                        '$_ctx->media = $core->gallery->readMedia($_ctx->posts) ; ?>'.
                        $content.'<?php $_ctx->media = null; endwhile; $_ctx->posts = null; ?>'.
                "<?php endif; ?>\n";
	}


        public static function GalItemURL($attr)
        {
		global $_ctx;
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$querychar=($GLOBALS['core']->blog->settings->url_scan == 'path_info')?'?':'&';
                return '<?php if (!is_null($_ctx->gallery_url)): $append="'.
			$querychar.'gallery=".$_ctx->gallery_url; else: $append=""; endif;'.
			'echo '.sprintf($f,'$_ctx->posts->getURL()').'.$append; ?>';
        }


	# Gallery Widget function
	public static function listgalWidget(&$w)
	{
                global $core;

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
			$order="C.cat_title asc, ";
		}
		if ($orderby == 'date')
			$order .= 'P.post_dt ';
		else
			$order .= 'P.post_title ';
		$order .= ($orderdir == 'asc') ? 'asc':'desc';

                $params = array(
			'post_type'=>'gal',
                        'no_content'=>true,
                        'order'=>$order);

                $rs = $core->blog->getPosts($params);

                if ($rs->isEmpty()) {
                        return;
		}
		$rs->extend('rsExtGallery');

                $res =
                '<div id="galleries">'.
                '<h2>'.$title.'</h2>'.
		$current_cat = "";
		if (!$display_cat) {
			$res .= '<ul>';
		}
                while ($rs->fetch()) {
			if ($display_cat) {
				if ($rs->cat_title == "")
					$cat_title=__("No category");
				else
					$cat_title=$rs->cat_title;
				if ($current_cat != $cat_title) {
					$res .= ' <h3>'.$cat_title.'</h3>';
					$current_cat = $cat_title;
				}
				if ($display_gal)
					$res .= '<ul>';
			}
			if ($display_gal)
				$res .= ' <li><a href="'.$rs->getURL().'">'.html::escapeHTML($rs->post_title).'</a></li> ';
			if ($display_cat && $display_gal) {
				$res .= ' </ul>';
			}
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

		$params['limit']=10;
		if (!empty($get['tag'])) {
			$params['tag']=$get['tag'];
		}
		if (!empty($get['galId'])) {
			$params['gal_id']=$get['galId'];
		}
		$rs = $core->gallery->getGalImageMedia($params);

		$rsp = new xmlTag();
		while ($rs->fetch()) {
			$media = $core->gallery->readmedia($rs);
			$imgTag = new xmlTag('image');
			$imgTag->id=$rs->post_id;
			$imgTag->thumb=$media->media_thumb["t"];
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
}
?>
