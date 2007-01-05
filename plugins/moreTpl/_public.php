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

$core->tpl->addValue('EntryExcerptAndContent',array('tplMoreTpl','EntryExcerptAndContent'));
$core->tpl->addValue('EntryCategoryShortURL',array('tplMoreTpl','EntryCategoryShortURL'));
$core->tpl->addValue('CategoryEntriesCount',array('tplMoreTpl','CategoryEntriesCount'));
$core->tpl->addValue('EntryCommentCountDigit',array('tplMoreTpl','EntryCommentCountDigit'));
$core->tpl->addValue('EntryTrackbackCountDigit',array('tplMoreTpl','EntryTrackbackCountDigit'));

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
}
?>