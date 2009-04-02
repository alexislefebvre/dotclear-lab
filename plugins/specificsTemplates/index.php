<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of specificsTemplates, a plugin for Dotclear.
# 
# Copyright (c) 2009 Thierry Poinot
# dev@thierrypoinot.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }
?>
		<html>
			<head>
				<title><?php echo __('Specifics Templates'); ?></title>
			</head>
			<body>
				<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
				<?php echo __('Specifics Templates').' > '.__('How to use'); ?></h2>
				<h3>Comment créer un template pour une categorie spécifique ?</h3>
				<p>
					Le champs URL de la categorie peut vous servir à créer un template pour une categorie.
				</p>
				<p class="message">
					<strong>Par exemple si on a une categorie <code>News</code> avec l'url <code>news</code> et l'id <code>4</code>.</strong>
					<br/>
					Le template personnalisé qui sera cherché en premier sera <code>category-news.html</code>.
					<br/>
					Si il ne le trouve pas, il cherchera le template <code>category-4.html</code>.
					<br/>
					Enfin si il ne trouve aucun des deux template précédemment cités, il ira chercher le template par défaut : <code>category.html</code>.
				</p>
				<h3>Comment créer un template pour une page spécifique ?</h3>
				<p>De la même manière que pour les catégories !</p>
				<h3>Remarque importante !</h3>
				<p class="error">
					Si l'url de vos categories ou pages contiennent un de ses caractères : <code>\/:*?"<>|</code> vous ne pourrez pas utiliser le template avec l'url de la page ou de la catégorie, car ses caractères sont interdit dans les noms de fichiers.
				</p>
			</body>
		</html>