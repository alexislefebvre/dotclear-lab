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
 * Class that provide some function to enable the micro-blogging widget
 * 
 * This class implement the Singleton Design Patern
 * 
 * @author jeremie
 * @package microBlog
 */
class microBlogWidget
{
	private static $sList = array();
	
	/**
	 * Instance of micorBlogWidget
	 * 
	 * Required for the singleton patern
	 * 
	 * @var microBlogWidget
	 */
	private static $instance;
	
	/**
	 * Instance of a dcCore object
	 * 
	 * @var dcCore
	 */
	private $dc_core;
	
	
	/**
	 * Class constructor
	 * 
	 * Set to privat due to the Singleton patern
	 * 
	 * @param dcCore $core
	 */
	private function __construct(dcCore $core)
	{
		$this->dc_core = $core;
	}
	
	/**
	 * Access to the microBlogWidget Object
	 * 
	 * @param dcCore $core
	 * @return unknown_type
	 */
	public static function init(dcCore $core)
	{
		if(is_null(self::$instance)) {
			self::$instance = new microBlogWidget($core);
		}
		
		return self::$instance;
	}
	
	
	/**
	 * Initialisation du widget
	 * 
	 * @param $w
	 */
	public function initWidgets(dcWidgets $w)
	{
		$w->create('wMicroBlog', __("Micro-Blogging"),
			array($this,'mbWidget'));
		
		// Titre du Widget
		$w->wMicroBlog->setting('title', __('Title:'), 'Micro-blogging', 'text');
		
		// Service à afficher
		$w->wMicroBlog->setting('service', __('Service:'), null, 'combo', $this->mbServiceList());
		
		// Type de streamLife à afficher
		$w->wMicroBlog->setting('stream', __('Streamlife type:'), null, 'combo', 
			array(
				'Mes notes' => 'user',
				'Les notes de mes amis' => 'friends',
				'Recherche' => 'search'
			));

		// Requête pour les stream "Recherche"
		$w->wMicroBlog->setting('query', __('Search query:'), '', 'text');
			
		// Nombre de notes à afficher
		$w->wMicroBlog->setting('nbr', __('Streamlife size:'), '5', 'text');
		
		// Ignorer les notes commençant par la chaine
		$w->wMicroBlog->setting('ignore', __('Ignore notes begining with:'), '@', 'text');
	}
	
	public function mbWidget(dcWidget $w)
	{
		$out  = '<div class="microblog">'."\n";
		
		try{
			$MB      = microBlog::init($this->dc_core);
			$service = $MB->getServiceAccess($w->service);
			$titre   = $w->title;
			$stream  = $w->stream;
			$size    = $w->nbr;
			$ignore  = $w->ignore;
			$liste   = array();
			$filter  = create_function('$val', 'return 0 !== strpos($val, "'.$ignore.'");');
		
			for ($i=0; $i<5; $i++)
			{
			
			
				if ($stream == 'user')
					$tmp = $service->getUserTimeline($size);
				else if ($stream == 'friends')
					$tmp = $service->getFriendTimeLine($size);
				else if ($stream == 'search')
					$tmp = $service->search($query, $size);
				else
					break;
			
				$tmp = array_filter($tmp, $filter);
				
				$liste = array_merge($liste, $tmp);
			
				if (count($liste) >= $size){
					$liste = array_slice($liste, 0, $size);
					break;
				}
			}
		
		
		
			if (!empty($titre)) {
				$out .= '	<h2>' . $titre . '</h2>'."\n";
			}
		
			if (count($liste) > 0)
			{
				$out .= '	<ul>'."\n";
				foreach ($liste as $txt)
				{
					$txt  = $service->formatOutput($txt);
					$out .= '		<li>'.$txt.'</li>'."\n";
				}
				$out .= '	</ul>'."\n";
			}
		}
		catch (Exception $e)
		{
			$out = '<p>'.__('The micro-blogging service is not available yet.').'</p>';
		}
		
		$out .= '</div>';
		
		return $out;
	}
	
	/**
	 * Return the formated list of all the available services
	 * 
	 * TODO CHECK IF THIS METHOD IS NECESSARY
	 * 
	 * @return array
	 */
	private function mbServiceList()
	{
		if (empty(self::$sList))
		{
			$MicroBlog = microBlog::init($this->dc_core);
			$MBl       = $MicroBlog->getServicesList();
			
			while($MBl->fetch())
			{
				$id = eval('return mb'.ucFirst($MBl->service).'::getServiceName() . " ('.$MBl->user.')";');
				self::$sList[$id] = $MBl->id;
			}
		}
		
		return self::$sList;
	}

}