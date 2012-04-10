<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012 Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class mediaAgora
{
	public static function imagesPath()
	{
		global $core;
		return path::real($core->blog->public_path).'/agora-medias';
	}

	public static function imagesURL()
	{
		global $core;
		return $core->blog->settings->system->public_url.'/agora-medias';
	}

	public static function defaultAvatarExists()
	{
		return file_exists(mediaAgora::imagesPath().'/avatar.jpg');
	}

	public static function myAvatar($size,$class='')
	{
		global $core, $_ctx;

		if (!$core->auth->userID()) {return;}

		$media = new dcMedia($core);
		$sizes = implode('|',array_keys($media->thumb_sizes)).'|o';
		if (!preg_match('/^'.$sizes.'$/',$size)) {
			$size = 's';
		}
		$avatar = '.avatar_'.$size.'.jpg';
		$src = $alt = '';

		if (file_exists(mediaAgora::imagesPath().'/'.md5($core->auth->userID()).'/avatar.jpg'))
		{
			$src = mediaAgora::imagesURL().'/'.md5($core->auth->userID());
			$alt = $core->auth->getInfo('user_cn');
			$src .= '/'.$avatar;
			return '<img alt="'.$alt.'" src="'.$src.'" class="'.$class.'" />';
		}
	}

	public static function avatarHelper($size,$class="")
	{
		global $core, $_ctx;
		$media = new dcMedia($core);
		$sizes = implode('|',array_keys($media->thumb_sizes)).'|o';
		if (!preg_match('/^'.$sizes.'$/',$size)) {
			$size = 's';
		}
		$avatar = '.avatar_'.$size.'.jpg';
		$src = $alt = '';

		if ($_ctx->posts && $_ctx->posts->hasAvatar()) {
			$src = $_ctx->posts->mediaDir();
			$alt = $_ctx->posts->getAuthorCN();
		} elseif ($_ctx->messages && $_ctx->messages->hasAvatar()) {
			$src = $_ctx->messages->mediaDir();
			$alt = $_ctx->messages->getAuthorCN();
		} elseif ($_ctx->users && $_ctx->users->hasAvatar()) {
			$src = $_ctx->users->mediaDir();
			$alt = $_ctx->users->getAuthorCN();
		}
		if ($src) {
			$src .= '/'.$avatar;
			return '<img alt="'.$alt.'" src="'.$src.'" class="'.$class.'" />';
		}
	}

	public static function canWriteImages($create=false)
	{
		global $core;

		$public = path::real($core->blog->public_path);
		$imgs = self::imagesPath();

		if (!function_exists('imagecreatetruecolor') || !function_exists('imagepng') || !function_exists('imagecreatefrompng')) {
			$core->error->add(__('At least one of the following functions is not available: '.
				'imagecreatetruecolor, imagepng & imagecreatefrompng.'));
			return false;
		}

		if (!is_dir($public)) {
			$core->error->add(__('The \'public\' directory does not exist.'));
			return false;
		}

		if (!is_dir($imgs)) {
			if (!is_writable($public)) {
				$core->error->add(sprintf(__('The \'%s\' directory cannot be modified.'),'public'));
				return false;
			}
			if ($create) {
				files::makeDir($imgs);
			}
			return true;
		}

		if (!is_writable($imgs)) {
			$core->error->add(sprintf(__('The \'%s\' directory cannot be modified.'),'public/blowup-images'));
			return false;
		}

		return true;
	}

	public static function checkType($f)
	{
		if (!self::canWriteImages(true)) {
			throw new Exception(__('Unable to create images.'));
		}

		$name = $f['name'];
		$type = files::getMimeType($name);

		if ($type != 'image/jpeg' && $type != 'image/png') {
			throw new Exception(__('Invalid file type.'));
		}

		return true;

		/*if (@move_uploaded_file($f['tmp_name'],$dest) === false) {
			throw new Exception(__('An error occurred while writing the file.'));
		}

		$s = getimagesize($dest);
		if ($s[0] != 800) {
			//throw new Exception(__('Uploaded image is not 800 pixels wide.'));
		}

		return $dest;*/
	}
}
?>
