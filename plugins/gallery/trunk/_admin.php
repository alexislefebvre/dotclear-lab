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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

/* Icon inside sidebar administration menu */
$_menu['Blog']->addItem(__('Galleries'),'plugin.php?p=gallery','index.php?pf=gallery/icon.png',
		preg_match('/plugin.php\?p=gallery.*$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));


// > beta7 new behavior
$core->addBehavior('adminDashboardIcons',array('adminGallery','dashboardIcon')); 

# Select methods
$core->rest->addFunction('galGetMediaWithoutPost', array('galleryRest','galGetMediaWithoutPost'));
$core->rest->addFunction('galGetMediaWithoutThumbs', array('galleryRest','galGetMediaWithoutThumbs'));
$core->rest->addFunction('galGetNewMedia', array('galleryRest','galGetNewMedia'));
$core->rest->addFunction('galGetGalleries', array('galleryRest','galGetGalleries'));
$core->rest->addFunction('galGetCurrentMedia', array('galleryRest','galGetCurrentMedia'));

# Update methods
$core->rest->addFunction('galAddImg', array('galleryRest','galAddImg'));
$core->rest->addFunction('galCreateImgForMedia', array('galleryRest','imgCreateImgForMedia'));
$core->rest->addFunction('galMediaCreate', array('galleryRest','galMediaCreate'));
$core->rest->addFunction('galMediaCreateThumbs', array('galleryRest','galMediaCreateThumbs'));
$core->rest->addFunction('galDeleteOrphanMedia', array('galleryRest','galDeleteOrphanMedia'));
$core->rest->addFunction('galGetOrphanMediaCount', array('galleryRest','galGetOrphanMediaCount'));
$core->rest->addFunction('galDeleteOrphanItems', array('galleryRest','galDeleteOrphanItems'));
$core->rest->addFunction('galGetOrphanItemsCount', array('galleryRest','galGetOrphanItemsCount'));
$core->rest->addFunction('galUpdate', array('galleryRest','galUpdate'));
$core->rest->addFunction('galFixImgExif', array('galleryRest','galFixImgExif'));

#Advanced items methods
$core->rest->addFunction('galGetImages', array('galleryRest','galGetImages'));

require dirname(__FILE__).'/_widgets.php';

class galleryRest 
{
	##################### SELECT REST METHODS ######################

	# Retrieves media not assotiated to post
	public static function galGetMediaWithoutPost($core,$get,$post) {
		if (empty($get['mediaDir'])) {
			throw new Exception('No media dir');
		}
		$core->gallery = new dcGallery($core);
		$subdirs=(isset($get['recurse_dir']) && $get['recurse_dir']=="yes");
		$media = $core->gallery->getMediaWithoutGalItems($get['mediaDir'],$subdirs);
		$rsp = new xmlTag();
		while ($media->fetch()) {
			$mediaTag = new xmlTag('media');
			$mediaTag->id=$media->media_id;
			$mediaTag->file=$media->media_file;
			$rsp->insertNode($mediaTag);
		}
		return $rsp;
	}

	# Retrieves media not associated to post
	public static function galGetMediaWithoutThumbs($core,$get,$post) {
		if (empty($get['mediaDir'])) {
			throw new Exception('No media dir');
		}
		$core->gallery = new dcGallery($core);
		$subdirs=(isset($get['recurse_dir']) && $get['recurse_dir']=="yes");
		$media = $core->gallery->getMediaWithoutThumbs($get['mediaDir'],$subdirs);
		$rsp = new xmlTag();
		foreach ($media as $id => $file) {
			$mediaTag = new xmlTag('media');
			$mediaTag->id=$id;
			$mediaTag->file=$file;
			$rsp->insertNode($mediaTag);
		}
		return $rsp;
	}

	# Retrieves new media not yet registered in a media_dir
	public static function galGetNewMedia($core,$get,$post) {
		if (empty($get['mediaDir'])) {
			throw new Exception('No media dir');
		}
		$core->gallery = new dcGallery($core);
		$dir=$get['mediaDir'];
		$files = $core->gallery->getNewMedia($dir);
		$rsp = new xmlTag();
		foreach ($files as $file){
			$fileTag = new xmlTag('file');
			$fileTag->name=$file;
			$rsp->insertNode($fileTag);
		}
		return $rsp;
	}
	
	# Retrieves galleries
	public static function galGetGalleries($core,$get,$post) {

		$core->gallery = new dcGallery($core);
		$gals = $core->gallery->getGalleries(array());
		$rsp = new xmlTag();
		while ($gals->fetch()) {
			$galTag = new xmlTag('gallery');
			$galTag->id=$gals->post_id;
			$galTag->title=$gals->post_title;
			$rsp->insertNode($galTag);
		}
		return $rsp;
	}

	public static function galGetCurrentMedia($core,$get,$post) {
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$params=array();
		if (empty($get['mediaDir'])) {
			throw new Exception('No media dir');
		}
		$dir=$get['mediaDir'];
		$rs=$core->gallery->getCurrentMedia($dir);
		$rsp = new xmlTag();
		while ($rs->fetch()) {
			$media = new xmlTag('media');
			$media->id = $rs->media_id;
			$media->name = basename($rs->media_file);
			$rsp->insertNode($media);
		}
		return $rsp;
		
	}


	##################### UPDATE REST METHODS ######################

	# Create a image-post for a given media
	public static function imgCreateImgForMedia ($core,$get,$post) {
		if (empty($post['mediaId'])) {
			throw new Exception('No media ID');
		}
		$updateTimeStamp=(isset($post['updateTimeStamp']) && $post['updateTimeStamp']=="yes");
		$gallery = new dcGallery($core);
		$media = $gallery->getFile ($post['mediaId']);
		if ($media==null)
			return true;
		$gallery->createPostForMedia($media,$updateTimeStamp);
		return true;
	}

	# Add image to a gallery
	public static function galAddImg($core,$get,$post) {
		if (empty($post['postId'])) {
			throw new Exception('No gallery ID');
		}
		if (empty($post['imgId'])) {
			throw new Exception('No image ID');
		}
		$core->gallery = new dcGallery($core);
		$core->meta->setPostMeta($post['postId'],'galitem',$post['imgId']);	
		$rsp = new xmlTag();
	}


	# Create a new media
	public static function galMediaCreate($core,$get,$post) {
		if (empty($post['mediaDir'])) {
			throw new Exception('No media dir');
		}
		if (empty($post['mediaName'])) {
			throw new Exception('No media name');
		}
		$core->gallery = new dcGallery($core);
		$core->gallery->chdir($post['mediaDir']);
		$id=$core->gallery->createFile($post['mediaName']);
		if ($id !== false){
			if (!empty($post['withPost'])) {
				$updateTimeStamp=(isset($post['updateTimeStamp']) && $post['updateTimeStamp']=="yes");
				$media = $core->gallery->getFile ($id);
				$core->gallery->createPostForMedia($media,$updateTimeStamp);
			}
			return $id;
		} else {
			throw new Exception ('Could not create file');
		}
	}

	# Recreate media thumbnails
	public static function galMediaCreateThumbs($core,$get,$post) {
		if (empty($post['mediaId'])) {
			throw new Exception('No media ID');
		}
		$core->gallery = new dcGallery($core);
		$core->gallery->createThumbs((integer)$post['mediaId']);
		return true;
	}


	// Retrieve images with no media associated
	public static function galDeleteOrphanItems($core,$get,$post) {
		if (empty($post['confirm'])) {
			throw new Exception('No confirmation specified');
		}
		if ($post['confirm']!=="yes") {
			return true;
		}
		$count_only=false;
		if (!empty($post['count_only'])) {
			$count_only=true;
		}

		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$count = $core->gallery->deleteOrphanItems($count_only);
		return $count;
	}

	// Retrieve images with no media associated
	public static function galGetOrphanItemsCount($core,$get,$post) {
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$count = $core->gallery->deleteOrphanItems(true);
		return $count;
	}

	// Retrieve images with no media associated
	public static function galDeleteOrphanMedia($core,$get,$post) {
		if (empty($post['mediaDir'])) {
			throw new Exception('No media dir');
		}
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$count = $core->gallery->deleteOrphanMedia($post['mediaDir']);
		return $count;
	}
	
	// Retrieve images with no media associated
	public static function galGetOrphanMediaCount($core,$get,$post) {
		if (empty($get['mediaDir'])) {
			throw new Exception('No media dir');
		}
		$count_only=false;
		if (!empty($post['count_only'])) {
			$count_only=true;
		}
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$count = $core->gallery->deleteOrphanMedia($get['mediaDir'],true);
		return $count;
	}

	// Retrieve images with no media associated
	public static function galUpdate($core,$get,$post) {
		if (empty($post['galId'])) {
			throw new Exception('No gallery ID');
		}
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$redo = $core->gallery->refreshGallery($post['galId']);
		if ($redo) {
			$rsp = new xmlTag();
			$redoTag = new xmlTag('redo');
			$redoTag->value="1";
			$rsp->insertNode($redoTag);
			return $rsp;
		} else {
			return true;
		}
	}

	// Retrieve images with no media associated
	public static function galFixImgExif($core,$get,$post) {
		if (empty($post['imgId'])) {
			throw new Exception('No image ID');
		}
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$redo = $core->gallery->fixImageExif($post['imgId']);
		if ($redo) {
			$rsp = new xmlTag();
			$redoTag = new xmlTag('redo');
			$redoTag->value="1";
			$rsp->insertNode($redoTag);
			return $rsp;
		} else {
			return true;
		}
	}


	// Advanced items section

	public static function galGetImages($core,$get,$post) {
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$count_only=false;
		$params=array();
		if (!empty($get['count'])) {
			$count_only=true;
		}
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
		if (!empty($get['limit']) && ($get['limit'] <= 200)) {
			$limit = (integer)$get['limit'];
		} else {
			$limit = 200;
		}
		$params['limit']=array($start,$limit);
		$rs = $core->gallery->getGalImageMedia($params,$count_only);

		$rsp = new XmlTag('images');
		if ($count_only) {
			$rsp->count=$rs->f(0);
			} else {
		while ($rs->fetch()) {
			$media = $core->gallery->readMedia($rs);
			$image = new xmlTag('image');
			$image->id = $rs->post_id;
			$sizes = new xmlTag('sizes');
			$image->media_url = $media->file_url;
			$image->media_id = $media->media_id;
			$image->media_type = $media->media_type;
			$image->url = $rs->getURL();
			$image->title = $rs->post_title;
			foreach ($media->media_thumb as $k => $v) {
				$thumb = new xmlTag('thumb');
				$thumb->size = $k;
				$thumb->url = $v;
				$image->insertNode($thumb);
			}
			$rsp->insertNode($image);
		}
		}

		return $rsp;
		
	}
}

class adminGallery {
	public static function dashboardIcon($core, $icons)
	{
		$rs = $core->blog->getPosts(array('post_type' => 'gal'),true);
		$gal_count = $rs->f(0);
		$str_gals = ($gal_count > 1) ? __('%d galleries') : __('%d gallery');
		$rs = $core->blog->getPosts(array('post_type' => 'galitem'),true);
		$img_count = $rs->f(0);
		$str_imgs = ($gal_count > 1) ? __('%d images') : __('%d image');		$icons['gallery'] = array(sprintf ($str_gals,$gal_count).'</a> <br /><a href="plugin.php?p=gallery&amp;m=items">'.sprintf($str_imgs,$img_count),'plugin.php?p=gallery','index.php?pf=gallery/gallery64x64.png');
	}
}
?>