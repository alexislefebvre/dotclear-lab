<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Micro-Blogging, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jeremie Patonnier
# jeremie.patonnier@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

//// Autochargement des classes /////////////////////////////////////

$__autoload['microBlog']          = dirname(__FILE__).'/inc/class.micro.blog.php';
$__autoload['microBlogException'] = dirname(__FILE__).'/inc/class.micro.blog.exception.php';
$__autoload['microBlogService']   = dirname(__FILE__).'/inc/abstract.micro.blog.service.php';
$__autoload['microBlogBehaviors'] = dirname(__FILE__).'/inc/class.micro.blog.behaviors.php';
$__autoload['microBlogWidget']    = dirname(__FILE__).'/inc/class.micro.blog.widget.php';
$__autoload['microBlogCache']     = dirname(__FILE__).'/inc/class.micro.blog.cache.php';

$d = new DirectoryIterator(dirname(__FILE__).'/inc/services/');

foreach ($d as $file)
{
	if (!$file->isFile()) continue;
	
	$fileName = $file->getFilename();
	
	if (preg_match('#class.mb.[a-z]+.php#',$fileName)){
		$className = ucFirst(substr($fileName,9,-4));
		
		$__autoload['mb'.$className] = $file->getPathname();
	}
}

//// Action sur le blog /////////////////////////////////////////////

$core->addBehavior('adminAfterPostCreate', array(microBlogBehaviors::ini($core),'afterPostCreate'));
$core->addBehavior('adminBeforePostUpdade',array(microBlogBehaviors::ini($core),'beforePostUpdate'));
$core->addBehavior('adminAfterPostUpdade', array(microBlogBehaviors::ini($core),'afterPostUpdate'));