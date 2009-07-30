<?php
if (!defined('DC_RC_PATH')) {return;}

//// Autochargement des classes /////////////////////////////////////

$__autoload['microBlog']          = dirname(__FILE__).'/inc/class.micro.blog.php';
$__autoload['microBlogException'] = dirname(__FILE__).'/inc/class.micro.blog.exception.php';
$__autoload['microBlogService']   = dirname(__FILE__).'/inc/abstract.micro.blog.service.php';
$__autoload['microBlogWidget']    = dirname(__FILE__).'/inc/class.micro.blog.widget.php';
$__autoload['microBlogCache']     = dirname(__FILE__).'/inc/class.micro.blog.cache.php';

$d = new DirectoryIterator(dirname(__FILE__).'/inc/services/');

foreach($d as $file)
{
	if(!$file->isFile()) continue;
	
	$fileName = $file->getFilename();
	
	if(preg_match('#class.mb.[a-z]+.php#',$fileName)){
		$className = ucFirst(substr($fileName,9,-4));
		
		$__autoload['mb'.$className] = $file->getPathname();
	}
}

//// Action sur le blog /////////////////////////////////////////////

require_once dirname(__FILE__).'/inc/class.micro.blog.behaviors.php';

$core->addBehavior('adminAfterPostCreate', array('microBlogBehaviors','afterPostCreate'));
$core->addBehavior('adminBeforePostUpdade',array('microBlogBehaviors','beforePostUpdate'));
$core->addBehavior('adminAfterPostUpdade', array('microBlogBehaviors','afterPostUpdate'));