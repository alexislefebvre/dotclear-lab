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

$core->tpl->addValue('EntryExcerptAndContent',array('tplMoreTpl','EntryExcerptAndContent'));
$core->tpl->addValue('EntryCategoryShortURL',array('tplMoreTpl','EntryCategoryShortURL'));
$core->tpl->addValue('CategoryEntriesCount',array('tplMoreTpl','CategoryEntriesCount'));
$core->tpl->addValue('EntryCommentCountDigit',array('tplMoreTpl','EntryCommentCountDigit'));
$core->tpl->addValue('EntryTrackbackCountDigit',array('tplMoreTpl','EntryTrackbackCountDigit'));
$core->tpl->addValue('TagEntriesCount',array('tplmoreTpl','TagEntriesCount'));

class tplMoreTpl
{
	/*
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

	/*
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

	/*
	Cette fonction affiche en chiffres le nombre de bilets d'une catégorie (en chiffres)
	(par exemple pour le content-info de category.html)
	Utilisation : {{tpl:CategoryEntriesCount}} -> 3
	*/
	public static function CategoryEntriesCount($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return
		'<?php echo '.sprintf($f,'$_ctx->categories->nb_post').'; ?>';
	}

	/*
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

	/*
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

	/*
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
}
?>