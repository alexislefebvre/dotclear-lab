<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of acronyms, a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addBlock('Acronyms',array('tplAcronyms','Acronyms'));
$core->tpl->addBlock('AcronymsHeader',array('tplAcronyms','AcronymsHeader'));
$core->tpl->addBlock('AcronymsFooter',array('tplAcronyms','AcronymsFooter'));
$core->tpl->addValue('Acronym',array('tplAcronyms','Acronym'));
$core->tpl->addValue('AcronymTitle',array('tplAcronyms','AcronymTitle'));

class tplAcronyms
{
	public static function Acronyms($attr,$content)
	{
		$res =
		"<?php\n".
		'$objAcronyms = new dcAcronyms($core); '.
		'$arrayAcronyms = array(); '.
		'foreach ($objAcronyms->getList() as $acronym=>$title) {'.
		"	\$arrayAcronyms[] = array('acronym'=>\$acronym,'title'=>\$title);".
		'}'.
		'$_ctx->acronyms = staticRecord::newFromArray($arrayAcronyms); '.
		'?>';

		$res .=
		'<?php while ($_ctx->acronyms->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->acronyms = null; unset($objAcronyms,$arrayAcronyms); ?>';

		return $res;
	}

	public static function AcronymsHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->acronyms->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}

	public static function AcronymsFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->acronyms->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}

	public static function Acronym($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->acronyms->acronym').'; ?>';
	}

	public static function AcronymTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->acronyms->title').'; ?>';
	}

} # class tplAcronyms


class acronymsURL extends dcUrlHandlers
{
        public static function acronyms($args)
        {
        	global $core;

        	if (!$core->blog->settings->acronyms_public_enabled) {
				self::p404();
				exit;
        	}

			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
			self::serveDocument('acronyms.html');
			exit;
        }

} # class acronymsURL
?>
