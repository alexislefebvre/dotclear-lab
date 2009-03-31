<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Katzenjammer, a theme for Dotclear.
#
# Copyright (c) 2009
# annso
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$core->tpl->addValue('ScrollDownLink',array('tplKatzenjammer','ScrollDownLink'));
$core->tpl->addValue('ScrollAnchor',array('tplKatzenjammer','ScrollAnchor'));

class tplKatzenjammer
{
	/*
	Cette fonction  crée un lien qui défile vers le prochain article
	Utilisation : {{tpl:ScrollDownLink}}
	*/
	public static function ScrollDownLink($attr)
	{

		$f = $GLOBALS['core']->tpl->getFilters($attr);


		$res =
		'<?php echo "<div class=\"scroller\">"; ?>'.
		'<?php echo "<a href=\"#item-"; ?>'.
		'<?php echo $_ctx->posts->post_id; ?>'.
		'<?php echo "\">"; ?>'.
		'<?php echo "<img src=\""; ?>'.
		'<?php echo '.sprintf($f,'$core->blog->settings->themes_url."/".$core->blog->settings->theme').'; ?>'.
		'<?php echo "/img/down.jpg\" alt=\"Scroll down\" />"; ?>'.
		'<?php echo "</a>"; ?>'.
		'<?php echo "</div>"; ?>';

		return $res;
	}


	/*
	Cette fonction crée une ancre au nom de l'id de l'article
	Utilisation : {{tpl:ScrollAnchor}}
	*/
	public static function ScrollAnchor($attr)
	{
		return '
		<?php
			$res = \'<a name="item-\'.$_ctx->posts->post_id.\'"></a> \';
			echo $res;
		?>';
	}

}

?>
