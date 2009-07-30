<?php
class microBlogWidget
{
	private static $sList = array();
	
	/**
	 * Initialisation du widget
	 * 
	 * @param $w
	 */
	public static function initWidgets(&$w)
	{
		$w->create('wMicroBlog', __("MicroBlogging"),
			array('microBlogWidget','mbWidget'));
		
		// Titre du Widget
		$w->wMicroBlog->setting('title', __('Title:'), 'Micro-blogging', 'text');
		
		// Service à afficher
		$w->wMicroBlog->setting('service', __('Service:'), null, 'combo', self::mbServiceList());
		
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
	
	public static function mbWidget(&$w)
	{
		try{
		$MB      = microBlog::init();
		$service = $MB->getServiceAccess($w->service);
		$titre   = $w->title;
		$stream  = $w->stream;
		$size    = $w->nbr;
		$ignore  = $w->ignore;
		$liste   = array();
		$filter  = create_function('$val', 'return 0 !== strpos($val, "'.$ignore.'");');
		
		for($i=0; $i<5; $i++)
		{
			
			
			if($stream == 'user')
				$tmp = $service->getUserTimeline($size);
			else if($stream == 'friends')
				$tmp = $service->getFriendTimeLine($size);
			else if($stream == 'search')
				$tmp = $service->search($query, $size);
			else
				break;
			
			$tmp = array_filter($tmp, $filter);
				
			$liste = array_merge($liste, $tmp);
			
			if(count($liste) >= $size){
				$liste = array_slice($liste, 0, $size);
				break;
			}
		}
		
		$out  = '<div class="microblog">'."\n";
		
		if(!empty($titre)){
		$out .= '	<h2>' . $titre . '</h2>'."\n";
		}
		
		if(count($liste) > 0)
		{
			$out .= '	<ul>'."\n";
			foreach($liste as $txt)
			{
				$out .= '		<li>'.htmlentities($txt).'</li>'."\n";
			}
			$out .= '	</ul>'."\n";
		}
		
		$out .= '</div>';
		}
		catch(Exception $e)
		{
			$out = $e->getMessage();
			$out .= '<pre>'.$e->getTraceAsString().'</pre>';
		}
		
		return $out;
	}
	
	public static function mbServiceList()
	{
		if(empty(self::$sList))
		{
			$MicroBlog = microBlog::init();
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