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

/* Icon inside sidebar administration menu */
$_menu['Blog']->addItem(__('Galleries'),'plugin.php?p=gallery','index.php?pf=gallery/icon.png',
		preg_match('/plugin.php\?p=gallery.*$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));

# Select methods
$core->rest->addFunction('galGetMediaWithoutPost', array('galleryRest','galGetMediaWithoutPost'));
$core->rest->addFunction('galGetOrphanImg', array('galleryRest','galGetOrphanImg'));
$core->rest->addFunction('galGetNewMedia', array('galleryRest','galGetNewMedia'));
$core->rest->addFunction('galGetGalFromMediaDir', array('galleryRest','galGetGalFromMediaDir'));

# Update methods
$core->rest->addFunction('galAddImg', array('galleryRest','galAddImg'));
$core->rest->addFunction('galCreateImgForMedia', array('galleryRest','imgCreateImgForMedia'));
$core->rest->addFunction('galMediaCreate', array('galleryRest','galMediaCreate'));
$core->rest->addFunction('galRefreshGal', array('galleryRest','galRefreshGal'));

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

	# Retrieves Images not belonging to any gallery
	public static function galGetOrphanImg(&$core,$get,$post) {
		if (empty($get['galId'])) {
			throw new Exception('No gallery ID');
		}
		if (empty($get['mediaDir'])) {
			throw new Exception('No media dir');
		}
		$core->gallery = new dcGallery($core);
		$img = $core->gallery->getItemsWithoutGal($get['mediaDir'],$get['galId']);
		$rsp = new xmlTag();
		while ($img->fetch()) {
			$imgTag = new xmlTag('image');
			$imgTag->id=$img->post_id;
			$rsp->insertNode($imgTag);
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

	# Retrieves gallery having media_dir as media dir
	public static function galGetGalFromMediaDir(&$core,$get,$post) {
		if (empty($get['mediaDir'])) {
			throw new Exception('No media dir');
		}
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$dir=$get['mediaDir'];
		$ids = $core->gallery->getGalFromMediaDir($dir);
		$rsp = new xmlTag();
		while ($ids->fetch()) {
			$idTag = new xmlTag('gallery');
			$idTag->id=$ids->post_id;
			$idTag->name=$ids->post_title;
			$rsp->insertNode($idTag);
		}
		return $rsp;
	}

	##################### UPDATE REST METHODS ######################

	# Create a image-post for a given media
	public static function imgCreateImgForMedia (&$core,$get,$post) {
		if (empty($post['mediaId'])) {
			throw new Exception('No media ID');
		}
		$gallery = new dcGallery($core);
		$media = $gallery->getFile ($post['mediaId']);
		$gallery->createPostForMedia($media);
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
	
	# Refresh a gallery (add missing images to gallery)
	public static function galRefreshGal(&$core,$get,$post) {
		if (empty($post['galId'])) {
			throw new Exception('No gallery id');
		}
		$core->media = new dcMedia($core);
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$core->gallery->refreshGallery($post['galId']);
		return true;
	}

}

?>
