<?php

# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { 
	return; 
	}

$core->tpl->addBlock('EntryIfPosition',array('tplMoreTpl','EntryIfPosition'));
$core->tpl->addValue('EntryExcerptAndContent',array('tplMoreTpl','EntryExcerptAndContent'));
$core->tpl->addValue('EntryCategoryShortURL',array('tplMoreTpl','EntryCategoryShortURL'));
$core->tpl->addValue('CategoryEntriesCount',array('tplMoreTpl','CategoryEntriesCount'));
$core->tpl->addValue('EntryCommentCountDigit',array('tplMoreTpl','EntryCommentCountDigit'));
$core->tpl->addValue('EntryTrackbackCountDigit',array('tplMoreTpl','EntryTrackbackCountDigit'));
$core->tpl->addValue('TagEntriesCount',array('tplMoreTpl','TagEntriesCount'));
$core->tpl->addValue('CoreVersion',array('tplMoreTpl','CoreVersion'));
$core->tpl->addValue('MetaSeparator',array('tplMoreTpl','MetaSeparator'));
$core->tpl->addValue('EntryUpdate',array('tplMoreTpl','EntryUpdate'));
$core->tpl->addBlock('PrevOrNextEntries',array('tplMoreTpl','PrevOrNextEntries'));

$GLOBALS['__l10n']['Last update :'] = 'Dernière mise à jour :'; //pour EntryUpdate

class tplMoreTpl
{
	/**
	Fonction interne, ne peut pas être utilisée directement depuis le template
	
	@param	str		<b>string</b>		String to parse
	@param	var		<b>string</b>		Tested variable name
	@return	<b>string</b>
	*/
	public static function testInExpr($str,$var)
	{
		$or = array();
		foreach (explode(',',$str) as $v)
		{
			$v = trim($v);
			$and = explode(' ',$v);
			foreach ($and as $k_=>$v_)
			{
				$v_ = trim($v_);
				$neg = false;
				$op = '==';
				
				# Not
				if (preg_match('/^!/',$v_)) {
					$v_ = substr($v_,1);
					$neg = true;
					$op = '!=';
				}
				
				# Number specification
				if (preg_match('/^[1-9][0-9]*$/',$v_,$m)) {
					$nb = (integer) $m[0];
					$res = $var.$op.$nb;
					$neg = false;
				}
				# Interval specification
				elseif (preg_match('/^([1-9][0-9]*)-([1-9][0-9]*)?$/',$v_,$m)) {
					$n1 = (integer) $m[1];
					if (isset($m[2])) {
						$n2 = (integer) $m[2];
						if ($n1 > $n2) {
							list($n1,$n2) = array($n2,$n1);
						}
						$res = $var.'>='.$n1.' && '.$var.'<='.$n2;
					} else {
						$res = $var.'>='.$n1.'';
					}
				}
				# Step specification
				elseif (preg_match('#^/([1-9][0-9]*)(?:\+([0-9]+))?$#',$v_,$m)) {
					$n1 = (integer) $m[1];
					if (isset($m[2])) {
						$n2 = (integer) $m[2];
						$res = '('.$var.'-'.$n2.')%'.$n1.$op.'0';
					} else {
						$res = $var.'%'.$n1.$op.'0';
					}
					$neg = false;
				}
				# Odd items
				elseif ($v_ == 'odd') {
					$res = $var.'&1';
				}
				# Invalid format : cancel current alternative
				else {
					$res = 'false';
					$neg = false;
				}
				
				$res = ($neg ? '!' : '').'('.$res.')';
				$and[$k_] = $res;
			}
			$or[] = implode(' && ',$and);
		}
		
		return implode(' || ',$or);
	}

	/**
	Cette fonction vérifie la position du billet (1er, 2ème, 3ème, etc.)
	par rapport à l'argument "is" spécifié, qui contient la liste des
	positions acceptées, séparées par des virgules.
	
	Lisez la documentation en ligne pour plus de détails sur la syntaxe à
	utiliser.
	
	Utilisation : <tpl:EntryIfPosition is="x,y,a-b odd,!z,/n+m">
	*/
	public static function EntryIfPosition($attr,$content)
	{
		$is = isset($attr['is']) ? trim($attr['is']) : '';
		$expr = self::testInExpr($is,'$idx');
		return
		'<?php $idx = $_ctx->posts->index()+1; if ('.$expr.'): ?>'.
		$content.
		'<?php endif; unset($idx); ?>';
	}
		
	/**
	Cette fonction recueille le contenu du post_chapo et du post_content
	(par exemple pour lui appliquer un cut_string ou afficher le premier billet in extenso)
	Utilisation : {{tpl:EntryExcerptAndContent}}
	*/
	public static function EntryExcerptAndContent($attr)
	{
		$urls = '0';
		if (!empty($attr['absolute_urls'])) {
			$urls = '1';
		}

		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return
		'<?php echo '.sprintf($f,'$_ctx->posts->getExcerpt('.$urls.').$_ctx->posts->getContent('.$urls.')').'; ?>';
	}

	/**
	Cette fonction affiche le nom "URL" de la categorie dans le contexte d'un billet
	(par exemple pour affecter une class dans la div post)
	Utilisation : {{tpl:EntryCategoryShortURL}} -> Ma-jolie-categorie
	*/
	public static function EntryCategoryShortURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return
		'<?php echo '.sprintf($f,'$_ctx->posts->cat_url').'; ?>';
	}

	/**
	Cette fonction affiche en chiffres le nombre de bilets d'une catégorie
	(par exemple pour le content-info de category.html)
	Utilisation : {{tpl:CategoryEntriesCount}} -> 3
	*/
	public static function CategoryEntriesCount($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return
		'<?php echo '.sprintf($f,'$_ctx->categories->nb_post').'; ?>';
	}

	/**
	Cette fonction affiche le nombre de commentaires en chiffres et sans mention
	Utilisation : {{tpl:EntryCommentCountDigit}} -> 4
	*/
	public static function EntryCommentCountDigit($attr)
	{
		$none = '0';
		$one = '1';
		$more = '%d';

		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}

		return
		"<?php if (\$_ctx->posts->nb_comment == 0) {\n".
		"  printf(__('".$none."'),(integer) \$_ctx->posts->nb_comment);\n".
		"} elseif (\$_ctx->posts->nb_comment == 1) {\n".
		"  printf(__('".$one."'),(integer) \$_ctx->posts->nb_comment);\n".
		"} else {\n".
		"  printf(__('".$more."'),(integer) \$_ctx->posts->nb_comment);\n".
		"} ?>";
	}

	/**
	Cette fonction affiche le nombre de trackbacks en chiffres
	Utilisation : {{tpl:EntryTrackbackCountDigit}} -> 2
	*/
	public static function EntryTrackbackCountDigit($attr)
	{
		$none = '0';
		$one = '1';
		$more = '%d';

		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}

		return
		"<?php if (\$_ctx->posts->nb_trackback == 0) {\n".
		"  printf(__('".$none."'),(integer) \$_ctx->posts->nb_trackback);\n".
		"} elseif (\$_ctx->posts->nb_trackback == 1) {\n".
		"  printf(__('".$one."'),(integer) \$_ctx->posts->nb_trackback);\n".
		"} else {\n".
		"  printf(__('".$more."'),(integer) \$_ctx->posts->nb_trackback);\n".
		"} ?>";
	}
	/**
	Cette fonction affiche le nombre de billets correspondant à un tag
	Utilisation (dans la page tags.html, tag.html ou une boucle <tpl:Metadata>) :
	{{tpl:TagEntriesCount}} -> 12
	*/	
	public static function TagEntriesCount($attr)
	{
	    $f = $GLOBALS['core']->tpl->getFilters($attr);
	    $n = '$_ctx->meta->count';
	    return '<?php echo '.sprintf($f, $n).'; ?>';
	}
	/**
		CoreVersion

		Cette fonction affiche la version du noyau Dotclear d'après le champ core de la table version
		(par exemple pour préciser la version après "Propulsé par Dotclear")
		Utilisation : {{tpl:CoreVersion}} -> 2.0 RC1
		*/
		public static function CoreVersion($attr)
		{
			$f = $GLOBALS['core']->tpl->getFilters($attr);

			return
			'<?php echo '.sprintf($f,'$GLOBALS["core"]->getVersion()').'; ?>';
		}

		/**
		MetaSeparator

		Cette fonction affiche un séparateur (qui peut être spécifié en paramètre) entre
		les tags d'un billet. Cela permet par exemple d'utiliser une virgule comme
		séparateur de tags et de ne pas avoir une virgule superflue qui traîne après
		le dernier tag.

		Paramètre du tag :
		  - separator : indique le texte à utiliser comme séparateur (valeur par défaut : ' - ')

		Exemples d'utilisation :

		Le bloc de code :
		  <tpl:EntryMetaData><a href="{{tpl:MetaURL}}">{{tpl:MetaID}}</a>{{tpl:MetaSeparator}}</tpl:EntryMetaData>
		affiche une liste de tous les tags du billet en les séparant simplement par un tiret.
		*/
		public static function MetaSeparator($attr)
		{
			$ret = isset($attr['separator']) ? $attr['separator'] : ' - ';
			$ret = html::escapeHTML($ret);
			return '<?php if (! $_ctx->meta->isEnd()) { ' . "echo '".addslashes($ret)."'; } ?>";
		}

		/**
		EntryUpdate - auteur : Moe

		Cette fonction affiche la date et l'heure de la dernière mise à jour du billet
		Le format d'affichage répond à la syntaxe de la fonction strftime():
			http://fr.php.net/manual/fr/function.strftime.php 

		Exemples d'utilisation :

		{{tpl:EntryUpdateDate}} affichera "date_format, time_format"
		où date_format et time_format sont les formats de l'affichage de
		la date et de l'heure définis dans les "Paramètres du blog" 

		{{tpl:lang Last update :}} {{tpl:EntryUpdate format="%A %e %B %Y, %H:%M:%S"}}
		affichera :
		"Dernière mise à jour : vendredi 30 novembre 2007, 16:53:05"
		*/
		public static function EntryUpdate($attr)
		{
			global $core;

			$format = (!empty($attr['format'])) ? $attr['format'] : 
				$core->blog->settings->date_format.', '.$core->blog->settings->time_format; 
			$f = $GLOBALS['core']->tpl->getFilters($attr);

			return('<?php echo '.'dt::dt2str(\''.$format.'\','.sprintf($f,'$_ctx->posts->post_upddt').
				',\''.$core->blog->settings->blog_timezone.'\'); ?>');
		}

		/**
		PrevOrNextEntries

		Cette fonction crée un bloc balise pour le template post.html qui permet d'afficher les x billets précédant ou suivant le billet courant. Possibilité de filtrer par langue, catégorie et de définir le nombre de résultats à retourner

		Utilisation : <tpl:PrevOrNextEntries>[...]</tpl:PrevOrNextEntries>

		Paramètres :
		- Option "cat" accepte 0 pour exécuter la requête sur tous les posts ou 1 pour que ce ne soit que des billets de la même catégorie que le post en cours qui s'affichent => défaut 0
		- Option "lng" (pour les blogs multilangues) accepte 0 pour trier tous les posts de toutes les langues ou 1 pour les billets de la même langue que le post en cours => défaut 0
		- Option "dir" accepte 0 pour les x posts précédents ou 1 pour les x posts suivants => défaut 0
		- Option "qty" accepte une valeur numérique qui correspondra au nombre de posts retournés par DC (dans la limite du nb total de posts publiés sans password) => défaut 2

		Précision :
		- Les 4 options sont utilisables simultanément vous pouvez donc afficher le billet suivant rédigé dans la même langue et publié dans la même catégorie à l'aide du code suivant :
		- Si vous souhaitez utiliser la valeur par défaut de l'option, il est inutile, et même recommandé dans une démarche d'optimisation, de ne pas utiliser cet argument. Par exemple si vous souhaitez afficher les deux billets précédents parmi toutes les catégories et toutes les langues, le bloc de base indiqué dans "Utilisation" est suffisant.
		*/

		public static function PrevOrNextEntries($attr,$content)
		{
			function getPrevOrNextPosts($post,$cat,$lng,$dir,$qty)
			{
			global $core;
			if($cat==1) { $params['sql'] = $post->cat_id ? ' AND P.cat_id = '.$post->cat_id : ' AND P.cat_id IS NULL'; }
			if($lng==1) { $params['sql'] .= $post->post_lang ? ' AND P.post_lang = \''.$core->con->escape($post->post_lang).'\'' : ' AND P.post_lang IS NULL'; }
			if($dir==1) { $sign='>'; $order='ASC'; } else { $sign='<'; $order='DESC'; }
			$dt = $post->post_dt; $post_id = $post->post_id;
			$params['post_type'] = $post->post_type; $params['limit'] = $qty;  $params['order'] = 'post_dt '.$order.', P.post_id '.$order;
			$params['sql'] .= ' AND ((post_dt = \''.$core->con->escape($dt).'\' AND P.post_id '.$sign.' '.$post_id.') OR post_dt '.$sign.' \''.$core->con->escape($dt).'\') ';
			$rs = $core->blog->getPosts($params);
			if ($rs->isEmpty()) {
			return null;
			}

			return $rs;
			}

				$cat = !empty($attr['cat']) ? $attr['cat'] : '0';
				$lng = !empty($attr['lng']) ? $attr['lng'] : '0';
				$dir = !empty($attr['dir']) ? $attr['dir'] : '0';
				$qty = !empty($attr['qty']) ? $attr['qty'] : '2';
				return '<?php $prev_post = getPrevOrNextPosts($_ctx->posts,'.$cat.','.$lng.','.$dir.','.$qty.'); ?>'."\n".
				'<?php if ($prev_post !== null) : ?>'.

					'<?php $_ctx->posts = $prev_post; unset($prev_post);'."\n".
					'while ($_ctx->posts->fetch()) : ?>'.
					$content.
					'<?php endwhile; $_ctx->posts = null; ?>'.
				"<?php endif; ?>\n";
		}
}
?>