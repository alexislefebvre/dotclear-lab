<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of podcastFeed, a plugin for Dotclear.
# 
# Copyright (c) 2010 Arnaud Jacquemin <contact@arnaud-jacquemin.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

// Protection pour les fichiers lus côté public
if (!defined('DC_RC_PATH')) { return; }

/**
 * Classe d'implémentation des nouvelles balises nécessaires au plugin.
 */
class PodcastFeedTplTags {
	
	public static function getPodcastEntries($attr, $content) {
		
		$p = '';
		
		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		} else {
			$p .= "\$params['cat_url'] = \$core->blog->settings->podcastFeed->podcastCategoryFilter;\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}
		
		$p .= "\$params['order'] = 'post_dt desc';\n";
		$p .= "\$params['from'] = ' INNER JOIN '.\$core->prefix.'post_media M ON M.post_id = P.post_id ';\n";
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$_ctx->posts = $core->blog->getPosts($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';
		
		return $res;
	}

	public static function getItunesKeywordsForPost($post) {
		$res =
		"<?php\n".
		'$objMeta = new dcMeta($core); '.
		"\$sr = \$objMeta->getMetaRecordset(\$_ctx->posts->post_meta,'tag'); ".
		"\$sr->sort('count','desc'); ".
		"\$iter = 0; \$result = ''; ".
		"while (\$sr->fetch() && \$iter < 12) {".
		"	if (\$iter != 0) {\$result .= ', ';}".
		"	\$result .= \$sr->meta_id;".
		"	\$iter++;".
		'}'.
		'echo $result;'.
		'?>';
		return($res);
	}

	public static function getPodcastTitle() {
		return '<?php echo $core->blog->settings->podcastTitle; ?>';
	}

	public static function getPodcastLink() {
		return '<?php echo $core->blog->settings->podcastLink; ?>';
	}

	public static function getPodcastSubTitle() {
		return '<?php echo $core->blog->settings->podcastSubTitle; ?>';
	}

	public static function getPodcastLanguage() {
		return '<?php echo $core->blog->settings->podcastLanguage; ?>';
	}

	public static function getPodcastAuthor() {
		return '<?php echo $core->blog->settings->podcastAuthor; ?>';
	}

	public static function getPodcastDescription() {
		return '<?php echo $core->blog->settings->podcastDescription; ?>';
	}

	public static function ifPodcastHasItunesSummary($attr, $content) {
		$summary = '$core->blog->settings->podcastItunesSummary';
		$condition = 'strlen('.$summary.') > 0';
		return	'<?php if ('.$condition.'): ?>'.$content.'<?php endif; ?>';
	}

	public static function getPodcastItunesSummary() {
		return '<?php echo $core->blog->settings->podcastItunesSummary; ?>';
	}

	public static function ifPodcastHasOwner($attr, $content) {
		$ownerName = '$core->blog->settings->podcastOwnerName';
		$ownerEmail = '$core->blog->settings->podcastOwnerEmail';
		$condition = 'strlen('.$ownerName.') > 0 && strlen('.$ownerEmail.') > 0';
		return	'<?php if ('.$condition.'): ?>'.$content.'<?php endif; ?>';
	}

	public static function getPodcastOwnerName() {
		return '<?php echo $core->blog->settings->podcastOwnerName; ?>';
	}

	public static function getPodcastOwnerEmail() {
		return '<?php echo $core->blog->settings->podcastOwnerEmail; ?>';
	}

	public static function getPodcastImage() {
		return '<?php echo $core->blog->settings->podcastImage; ?>';
	}

	public static function ifPodcastHasItunesImage($attr, $content) {
		$image = '$core->blog->settings->podcastItunesImage';
		$condition = 'strlen('.$image.') > 0';
		return	'<?php if ('.$condition.'): ?>'.$content.'<?php endif; ?>';
	}

	public static function getPodcastItunesImage() {
		return '<?php echo $core->blog->settings->podcastItunesImage; ?>';
	}

	public static function getPodcastItunesCategories() {
		return '<?php echo $core->blog->settings->podcastCategories; ?>';
	}

	public static function getPodcastItunesExplicit() {
		return '<?php echo $core->blog->settings->podcastExplicit; ?>';
	}

}