<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 2 Gallery plugin.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

/* Icon inside sidebar administration menu */
$_menu['Blog']->addItem(__('Galleries'),'plugin.php?p=gallery','index.php?pf=gallery/icon.png',
		preg_match('/plugin.php\?p=gallery.*$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));


if (isset($__dashboard_icons)) {
	// <= beta7 compatibility
	$rs = $core->blog->getPosts(array('post_type' => 'gal'),true);
	$gal_count = $rs->f(0);
	$str_gals = ($gal_count > 1) ? __('%d galleries') : __('%d gallery');
	$__dashboard_icons[] = array(sprintf ($str_gals,$gal_count),'plugin.php?p=gallery','index.php?pf=gallery/gallery64x64.png');
} else {
	// > beta7 new behavior
	$core->addBehavior('adminDashboardIcons',array('adminGallery','dashboardIcon')); 
}

# Select methods
$core->rest->addFunction('galGetMediaWithoutPost', array('galleryRest','galGetMediaWithoutPost'));
$core->rest->addFunction('galGetMediaWithoutThumbs', array('galleryRest','galGetMediaWithoutThumbs'));
$core->rest->addFunction('galGetNewMedia', array('galleryRest','galGetNewMedia'));
$core->rest->addFunction('galGetGalleries', array('galleryRest','galGetGalleries'));

# Update methods
$core->rest->addFunction('galAddImg', array('galleryRest','galAddImg'));
$core->rest->addFunction('galCreateImgForMedia', array('galleryRest','imgCreateImgForMedia'));
$core->rest->addFunction('galMediaCreate', array('galleryRest','galMediaCreate'));
$core->rest->addFunction('galMediaCreateThumbs', array('galleryRest','galMediaCreateThumbs'));
$core->rest->addFunction('galDeleteOrphanMedia', array('galleryRest','galDeleteOrphanMedia'));
$core->rest->addFunction('galDeleteOrphanItems', array('galleryRest','galDeleteOrphanItems'));
$core->rest->addFunction('galUpdate', array('galleryRest','galUpdate'));

require dirname(__FILE__).'/_widgets.php';

class galleryRest 
{
	##################### SELECT REST METHODS ######################

	# Retrieves media not assotiated to post
	public static function galGetMediaWithoutPost(&$core,$get,$post) {
		if (empty($get['mediaDir'])) {
			throw new Exception('No media dir');
		}
		$core->gallery = new dcGallery($core);
		$media = $core->gallery->getMediaWithoutGalItems($get['mediaDir']);
		$rsp = new xmlTag();
		while ($media->fetch()) {
			$mediaTag = new xmlTag('media');
			$mediaTag->id=$media->media_id;
			$mediaTag->file=$media->media_file;
			$rsp->insertNode($mediaTag);
		}
		return $rsp;
	}

	# Retrieves media not assotiated to post
	public static function galGetMediaWithoutThumbs(&$core,$get,$post) {
		if (empty($get['mediaDir'])) {
			throw new Exception('No media dir');
		}
		$core->gallery = new dcGallery($core);
		$media = $core->gallery->getMediaWithoutThumbs($get['mediaDir']);
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
	public static function galGetNewMedia(&$core,$get,$post) {
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


	##################### UPDATE REST METHODS ######################

	# Create a image-post for a given media
	public static function imgCreateImgForMedia (&$core,$get,$post) {
		if (empty($post['mediaId'])) {
			throw new Exception('No media ID');
		}
		$updateTimeStamp=(isset($post['updateTimeStamp']) && $post['updateTimeStamp']=="yes");
		$gallery = new dcGallery($core);
		$media = $gallery->getFile ($post['mediaId']);
		$gallery->createPostForMedia($media,$updateTimeStamp);
		return true;
	}

	# Add image to a gallery
	public static function galAddImg(&$core,$get,$post) {
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
	public static function galMediaCreate(&$core,$get,$post) {
		if (empty($post['mediaDir'])) {
			throw new Exception('No media dir');
		}
		if (empty($post['mediaName'])) {
			throw new Exception('No media name');
		}
		$core->gallery = new dcGallery($core);
		$core->gallery->chdir($post['mediaDir']);

		if ($core->gallery->createFile($post['mediaName']) !== false){
			return true;
		} else {
			throw new Exception ('Could not create file');
		}
	}

	# Recreate media thumbnails
	public static function galMediaCreateThumbs(&$core,$get,$post) {
		if (empty($post['mediaId'])) {
			throw new Exception('No media ID');
		}
		$core->gallery = new dcGallery($core);
		$core->gallery->createThumbs((integer)$post['mediaId']);
		return true;
	}


	// Retrieve images with no media associated
	public static function galDeleteOrphanItems(&$core,$get,$post) {
		if (empty($post['confirm'])) {
			throw new Exception('No confirmation specified');
		}
		if ($post['confirm']!=="yes") {
			return true;
		}

		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$count = $core->gallery->deleteOrphanItems();
		return true;
	}

	// Retrieve images with no media associated
	public static function galDeleteOrphanMedia(&$core,$get,$post) {
		if (empty($post['mediaDir'])) {
			throw new Exception('No media dir');
		}
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$count = $core->gallery->deleteOrphanMedia($post['mediaDir']);
		return true;
	}
	
	// Retrieve images with no media associated
	public static function galUpdate(&$core,$get,$post) {
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
}

class adminGallery {
	public static function dashboardIcon(&$core, &$icons)
	{
		$rs = $core->blog->getPosts(array('post_type' => 'gal'),true);
		$gal_count = $rs->f(0);
		$str_gals = ($gal_count > 1) ? __('%d galleries') : __('%d gallery');
		$icons['gallery'] = array(sprintf ($str_gals,$gal_count),'plugin.php?p=gallery','index.php?pf=gallery/gallery64x64.png');
	}
}
?>
