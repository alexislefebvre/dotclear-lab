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

/**
 * Class that provide some function for Dotclear behaviors
 * 
 * @author jeremie
 * @package microBlog
 */
class microBlogBehaviors
{
	/**
	 * Stack of different post status;
	 * 
	 * @var array
	 */
	private $post_status;
	
	/**
	 * Access to the microBlog environnement
	 * 
	 * @var microBlog
	 */
	private $micro_blog;
	
	/**
	 * Référence to the Dotclear core object
	 * 
	 * @var dcCore;
	 */
	private $dc_core;
	
	/**
	 * The unique instance of the class
	 * 
	 * Required by the Singleton patern
	 * 
	 * @var microBlogBehaviors
	 */
	private static $instance;
	
	
	/**
	 * Class constructor
	 * 
	 * It is private because this class implement 
	 * the Singleton patern
	 */
	private function __construct(dcCore $core)
	{
		$this->dc_core     = $core;
		$this->post_status = array();
		$this->micro_blog  = microBlog::init($core);
	}
	
	
	/**
	 * Method that give access to the unique microBlogBeavior object
	 * 
	 * Required by the Singleton patern
	 * 
	 * @return unknown_type
	 */
	public static function ini(dcCore $core)
	{
		if (is_null(self::$instance)) {
			self::$instance = new microBlogBehaviors($core);
		}
		
		return self::$instance;
	}
	
	
	/**
	 * Method that must be bind with the afterPostCreate behavior
	 * 
	 * @param $Post
	 * @param $post_id
	 */
	public function afterPostCreate(&$Post,$post_id)
	{
		if ($Post->post_status == 1) {
			$this->pushNote($Post->post_url);
		}
	}
	
	
	/**
	 * Method that must be bind with the beforePostUpdate behavior
	 * 
	 * @param $Post
	 * @param $post_id
	 */
	public static function beforePostUpdate(&$Post,$post_id)
	{
		$this->status[$post_id] = $Post->post_status;
	}
	
	
	/**
	 * Method that must be bind with the afterPostUpdate behavior
	 * 
	 * @param $Post
	 * @param $post_id
	 */
	public function afterPostUpdate(&$Post,$post_id)
	{
		$new = $Post->post_status;
		$old = $this->status[$post_id];
		
		if ($new == 1 && $new != $old){
			$this->pushNote($Post->post_url);
		}
		
		unset($this->status[$post_id]);
	}
	
	
	/**
	 * Method that push a note to all the allowed services
	 * 
	 * @param $post_url string
	 */
	private function pushNote($post_url)
	{
		$txt = __('New Blog Post: ')
		   	 . $this->dc_core->blog->url
		   	 . $this->dc_core->url->getBase('publicpage')
		   	 . $post_url;

		$services = $this->micro_blog->getServicesList();
		while($services->fetch())
		{
			$p = $this->micro_blog->getServiceParams($services->id);
			
			if ($p['sendNoteOnNewBlogPost']) {
				$this->micro_blog
				     ->getServiceAccess($services->id)
				     ->sendNote($txt);
			}
		}
	}
}