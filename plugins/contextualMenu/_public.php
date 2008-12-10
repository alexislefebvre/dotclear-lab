<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of contextualMenu, a plugin for Dotclear.
# 
# Copyright (c) 2008 Frédéric Leroy
# bestofrisk@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# Menu template functions

require dirname(__FILE__).'/_widgets.php';

$core->tpl->addValue('contextualMenu',array('tplcontextualMenu','menu'));


class tplcontextualMenu
{

	public static function menu($attr)
	{
		$category = '<h3>%s</h3>';
		$block = '<ul class="menu">%s</ul>';
		$item = '<li>%s</li>';

		if (isset($attr['block'])) {
			$block = addslashes($attr['block']);
		}
		
		if (isset($attr['category'])) {
			$category = addslashes($attr['category']);
		}
		
		if (isset($attr['item'])) {
			$item = addslashes($attr['item']);
		}
		
		return
		'<?php '.
		"echo tplcontextualMenu::getList('".$category."','".$block."','".$item."'); ".
		'?>';
	}

	
	public static function getList($category='<h3>%s</h3>',$block='<ul class="menu">%s</ul>',$item='<li>%s</li>')
	{
		require_once dirname(__FILE__).'/class.dc.contextual_menu.php';
		$menu = new dcBlogMenu($GLOBALS['core']->blog);
		
		// On ne veut que les links de type 'menu'
		$params = array();
		$params['link_type'] = 'menu';
		
		try {
			$links = $menu->getLinks($params);
		} catch (Exception $e) {
			return false;
		}
		
		$res = '';
		
		foreach ($menu->getLinksHierarchy($links) as $k => $v)
		{
			if ($k != '') {
				$res .= sprintf($category,html::escapeHTML($k))."\n";
			}
			
			$res .= self::getLinksList($v,$block,$item);
		}
		
		return $res;
	}
	
	private static function getLinksList($links,$block='<ul class="menu">%s</ul>',$item='<li>%s</li>')
	{
		global $core;  // Pour avoir accès a l'url du blog

		$list = '';
		
		// Calcul de l'url courante ($url) sur le format des URIs du plugin
		$blog_url = $core->blog->url;
		$current_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		$explode = explode($blog_url, $current_url);
		
		$url= $explode[1];
		
		# Gestion de la mixité : blocs <ul>/blocs <div>
		# On ouvre un bloc <ul> quand le bloc précédent est un bloc <div> OU qu'on est dans le premier bloc à traiter
		# On ferme un bloc <ul> quand on est dans un bloc <div> et que ce n'est pas le premier bloc à traiter OU lorsque le dernier bloc n'était pas un bloc <div>
		# NB : On utilise 2 variables car c'est plus lisible mais on doit pouvoir en utiliser qu'une seule
		$start_ul = 1;
		$close_ul = 0;
		
		foreach ($links as $v)
		{
			// La page courante fait-elle partie du groupe ?			
			$group = $v['link_group'];
			$group = explode(',', $group);	

			$in_group = self::inGroup($url, $group);
		
			$xfn = $v['link_xfn'];
			
			// Doit-on afficher l'item de menu ?
			if (!self::showLink($in_group, $xfn)) {
				// On n'affiche pas l'item de menu et on passe à l'item suivant
				continue;
			}
		
			// Faut-il remplacer le html standard par le code d'un widget ?
			$title = $v['link_title'];
			
			// Si link_href = index.php alors ce lien correspond à la page d'accueil ==> On harmonise avec le format des URIs dans le plugin
			// IMPORTANT : 
			// PAR CONSTRUCTION, les URIs saisies dans l'admin correspondent à la partie de l'url complémentaire à l'url du blog ($core->blog->url)
			// SAUF pour la page d'accueil dont l'URI est 'index.php' par convention
			$href  = ($v['link_href'] == 'index.php' ? '' : $v['link_href']);
			
			$special_group = $v['link_special_group'];
			$special_group = explode(',', $special_group);
			
			$in_group = self::inGroup($url, $special_group);			
			
			$special_xfn = $v['link_special_xfn'];
			
			// Doit-on afficher l'item de menu de façon spéciale (widget) ?
			if (self::showWidget($in_group, $special_xfn)) {
				if ($close_ul) $list .= '</ul>';
				// On affiche le widget à la place du html standard
				$special_widget = trim($v['link_special_widget']);
				$special_link_title = (int) $v['link_special_link_title'];
				// NB : On ne gère pas le cas où le widget n'est pas défini
				// On laisse Dotclear le gérer (pas de html)
				$special_content = trim($v['link_special_content']);
				$html = self::createWidgetHtml($special_widget, $special_content);
				if ($special_link_title) $html = preg_replace('#<h2>(.+)</h2>#', '<h2><a href="' . $blog_url . $href . '">$1</a></h2>', $html);
				//$item = '</ul>%s<ul class="menu">';
				//$list .= sprintf($item,$html)."\n";
				$list .= $html;
				$start_ul = 1;
				$close_ul = 0;
				continue;
			}
			
			// On commence par un <ul> ?
			if ($start_ul) $list .= '<ul class="menu">';
			$start_ul = 0;
			$close_ul = 1;			
			
			// Affichage de l'item de menu au format html standard
			$desc = $v['link_desc'];
			$lang  = $v['link_lang'];
			
			$active = "";
			// Si il faut tester aussi si page active
			if ($url == html::escapeHTML($href)) {
				$active = ' id="active"';
			}
			
			# Chemins des fichiers template
			$custom_file = dirname(__FILE__).'/template/custom_template.html';
			$default_file = dirname(__FILE__).'/template/default_template.html';

			// On teste si les fichiers templates existent
			$template = '';
			if (file_exists($custom_file)) {
				$template = $custom_file;
			} elseif (file_exists($default_file)) {
				$template = $default_file;
			}				
			
			// Création du bloc <li>
			if ($template != '') {
				// On appelle le template et on bufferise l'output
				ob_start(); 
				include $template;
				$html = ob_get_contents(); 
				ob_end_clean(); 
				// On empile les blocs <li>
				$list .= $html;
			} else {	// Soluce de secours si aucun template trouvé (html à modifier)
				$link = 
					'<a href="'.$blog_url . html::escapeHTML($href).'"'.
					((!$lang) ? '' : ' hreflang="'.html::escapeHTML($lang).'"').
					((!$xfn) ? '' : ' rel="'.html::escapeHTML($xfn).'"').
					$classlien.
					'>'.
					html::escapeHTML($title).
					((!$desc) ? '' : '<br /><span class="small">' . html::escapeHTML($desc).'</span>').						
					'</a>';
				$item = '<li class="page_item"' . $active . '>%s</li>';
				$list .= sprintf($item,$link)."\n";
			}
			
		} // End foreach 
				
		//return sprintf($block,$list)."\n";
		
		// Doit-on fermer le bloc <ul> ?
		if ($close_ul) $list .= '</ul>';
		
		return $list;
	}

	private static function inGroup($url, $group) 
	{	
		$inGroup = 0;
		
		require_once dirname(__FILE__).'/class.dc.contextual_menu.php';
		$menu = new dcBlogMenu($GLOBALS['core']->blog);

		foreach ($group as $id) {
			try {
				$rs = $menu->getLink($id);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
			
			$link = trim($rs->link_href);
			$type = trim($rs->link_type);
			
			if (self::linkMatch($type, $link, $url) ) {
				$inGroup = 1;
				break;
			}
		}
	
		return $inGroup;
	}
	
	private static function linkMatch($type, $link, $url) 
	{
		$linkMatch = 0;
		
		switch ($type) {
			case 'menu':
				$tab_link[] = $link;
			
				// traitement spécifique de la page d'accueil (plusieurs urls possibles)
				if ($link == 'index.php') {
					$tab_link[] = '';
					$tab_link[] = $link . '?';
					$tab_link[] = $link . '/';
				}

				if (in_array($url, $tab_link)) {
					$linkMatch = 1;
				}
				break;
			case 'context':
			default:
				$regex = '#^' . $link . '$#';
				
				if (preg_match($regex, $url)) {
					$linkMatch = 1;
				}			
				break;
		}

		return $linkMatch;
	}
	
	private static function showLink($in_group, $xfn) 
	{	
		$showLink = 1;

		switch (trim($xfn)) {
			case 'group_only':
				if (!$in_group) $showLink = 0;
				break;
			case 'all_except_group':
				if ($in_group) $showLink = 0;
				break;
			case 'all':
			default:
				break;
		} // End switch
	
		return $showLink;
	}
	
	private static function showWidget($in_group, $xfn_special) 
	{	
		$showWidget = 0;

		switch (trim($xfn_special)) {
			case 'group_only':
				if ($in_group) $showWidget = 1;
				break;
			case 'all_except_group':
				if (!$in_group) $showWidget = 1;
				break;
			case 'none':
			default:
				break;
		} // End switch
	
		return $showWidget;
	}

	private static function createWidgetHtml($id, $content) 
	{
		// Paramètres
		$attr['id'] = $id;
		
		// Bufferisation
		ob_start(); 
		publicWidgets::widgetHandler($attr['id'], $content);
		$html = ob_get_contents(); 
		ob_end_clean(); 
		
		// Retour du $html
		return $html;
	}

	# Callback function for widget contextualMenu
	public static function contextualMenu(&$w) 
	{
		$category = '<h3>%s</h3>';
		$block = '<ul class="menu">%s</ul>';
		$item = '<li>%s</li>';
		
		return tplcontextualMenu::getList($category, $block, $item);

	}
	
}
?>