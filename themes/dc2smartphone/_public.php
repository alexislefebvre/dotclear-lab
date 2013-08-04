<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dc2smartphone, a theme for Dotclear.
# Copyright (c) 2009
# Guillaume Bizet guillaumebizet@gmail.com
# Pierre Van Glabeke contact@brol.info
#
# Licensed under the Creative Commons license.
# A copy of this license is available at
# http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

$core->tpl->addValue('TagEntriesCount',array('tplMoreTpl','TagEntriesCount'));
class tplMoreTpl
{

/*
	Cette fonction affiche le nombre de billets correspondant à un tag
	Utilisation (dans la page tags.html, tag.html ou une boucle <tpl:Tag>) :
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
