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
				<h2>Crédits...</h2>
				<p>
					D'après <a href="http://aiguebrun.adjaya.info/post/20080707/Template-personnalise-par-categorie" title="mot de passe : 'pep'">un billet d'Adjaya</a> trouvé sur <a href="http://forum.dotclear.net/viewtopic.php?id=34414">le forum de Dotclear 2</a>
					<br/>
					Pep avait déjà bossé dessus en postant <a href="http://callmepep.org/bricoland/post/2008/04/29/Template-personnalise-par-categorie">un article sur le bricoland</a>et il a aussi décliné ce billet pour expliquer <a href="http://callmepep.org/bricoland/post/2008/10/17/Template-personnalise-par-categorie-:-au-tour-des-billets">comment attribuer un template personnalisé pour un billet</a>.
					<br/>
					<br/>
					<strong>Une discussion et un <a href="http://dev.dotclear.org/2.0/ticket/503">ticket</a> ont fait suite à ce sujet au sein
de l'équipe de développement de Dotclear : <a href="http://dev.dotclear.org/2.0/ticket/503">http://dev.dotclear.org/2.0/ticket/503</a></strong>
					<br/>
					<br/>
					En attendant que cela soit en natif au sein de Dotclear, sinon vous pouvez toujours utiliser ce bout de code dans vos templates :
					<pre>&lt;tpl:EntryIf category="toto"&gt;
{{tpl:include src="montemplate.html"}}
&lt;/tpl:EntryIf&gt;</pre>
					<br/>
					Apparemment cela ne fonctionne qu'à partir de la version 2.1
				</p>
			</body>
		</html>