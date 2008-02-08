<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
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
#
# D'apr� le plugin LastComment de Vincent Simonin et un billet de documentation 
# plugin du m�e auteur 
# visible sur http://www.forx.fr/post/2006/07/07/Doctclear-2-%3A-creation-de-plugin-premiere-partie
#

$core->tpl->addValue('lastestPosts',array('tplLastestPosts','lastestPosts'));

class tplLastestPosts
{
	public static function lastestPosts($attr)
	{
		$limit = 10;
			
		if (isset($attr['limit'])) {
			$limit = (integer) $attr['limit'];
		}
		
		if (!isset($attr['nb_letter'])) {
			$nb_letter = ',null';
		}
		else {
			$nb_letter = ','.(integer) $attr['nb_letter'];
		}
		
		return
		'<?php '.
		"echo tplLastestPosts::getPosts(".$limit.$nb_letter."); ".
		'?>';
	}
	
	public static function getPosts($limit=10, $nb_letter=null, $categ_show=0, $protect_show=0 )
	{
		global $core;
		require_once dirname(__FILE__).'/class.dc.lastestPosts.php';
		//echo "je passe avant";
		$lc = new lastestPosts($GLOBALS['core']->blog);
		try {
			$Posts = $lc->getLastestPosts($limit,$categ_show,$protect_show);
		} catch (Exception $e) {
			return false;
		}
		
		$string = "";
		$titlecateg= "";
		$cpt_billet= 0;
		//echo "Prêt pour la boucle <br />";
		foreach($Posts->rows() as $k => $v)
		{
			//echo "je passew<br />";
			/*
			$co_title = html::clean($v['post_title']);
			if ((integer) $nb_letter !== 0) {				
				if (strlen($co_title) > $nb_letter) {
					$co_title = trim(html::escapeHTML(substr(html::decodeEntities($co_title),0,$nb_letter))).'...';
				}
			}
			*/
			// Correction bug UTF8
			$co_title = html::clean($v['post_title']);
			if ((integer) $nb_letter !== 0) {				
				if (mb_strlen($co_title, 'UTF-8') > $nb_letter) {
					$co_title = trim(html::escapeHTML(mb_substr(html::decodeEntities($co_title), 0, $nb_letter, 'UTF-8'))).'…';
				}
			}
			
			if (!empty($co_title)) {	
				// Si il faut afficher les catégories
				if ($categ_show==1){
					// Si il faut afficher la nouvelle categorie		
					if ($titlecateg<>$v['cat_title']){
						$string .='<h3 class="toc-group">'.$v['cat_title'].'</h3>';
						$titlecateg=$v['cat_title'];
					}	
				}
				$cpt_billet++;
				if ($cpt_billet<=$limit){
					$string .= '<li>'
					.'<a href="'.$core->blog->url.$core->url->getBase("post").'/'.$v['post_url'].'"'
					.' title="'.__("Go to the post").'">'
					.$co_title
					.'</a>'
					.'</li>';
				}
			}
		}
		// Maintenant on place les ul et /ul
		if (substr($string,0,4)=="<li>"){
			$string = '<ul>'.$string;
			$string = str_replace ('</li><h3','</li></ul><h3',$string);
		}
		$string = str_replace ('</h3><li>','</h3><ul><li>',$string);
		if (substr($string,strlen($string)-5,5)=="</li>"){
			$string = $string.'</ul>';
		}
		return $string;	
	}

	# Widget function
	public static function LastestPostsWidget(&$w)
	{
		global $core;
				
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$limit = 10;		
		if ($w->limit != null) {
			$limit = $w->limit;
		}
		
		$nb_letter = null;
		if ($w->nb_letter != null) {
			$nb_letter = (integer) $w->nb_letter;
		}

		$categ_show = $w->categ_show;
		$protect_show = $w->protect_show;
		
				
		$title = $w->title ? html::escapeHTML($w->title) : __('Lastest Posts');
		
		return
		'<div id="lastestPosts">'.
		'<h2>'.$title.'</h2>'.
		'<div id="toc">'.
		self::getPosts($limit, $nb_letter, $categ_show, $protect_show).
		'</div>'.
		'</div>';
	}
}
?>
