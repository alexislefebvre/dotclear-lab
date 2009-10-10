<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of translater, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Admin service de retrieve translation of a string (from translater jquery)
class translaterRest
{
	public static function getProposal($core,$get)
	{
		$from = !empty($get['langFrom']) ? trim($get['langFrom']) : '';
		$to = !empty($get['langTo']) ? trim($get['langTo']) : '';
		$tool = !empty($get['langTool']) ? trim($get['langTool']) : '';
		$str_in = !empty($get['langStr']) ? trim($get['langStr']) : '';

		$str_in = text::toUTF8($str_in);
		$str_out = '';

		$rsp = new xmlTag();
		try
		{
			$O = new dcTranslater($core);

			if (empty($from) || empty($to) || empty($tool))
			{
					throw new Exception(__('Missing params'));
			}

			if (!empty($str_in))
			{
				if (!$O->proposal->initTool($tool,$from,$to))
				{
					throw new Exception(__('Failed to init translation tool'));
				}
				$str_out = $O->proposal->get($str_in);
			}

			$x = new xmlTag('proposal');
			$x->lang_from = $from;
			$x->lang_to = $to;
			$x->tool = $tool;
			$x->str_from = $str_in;
			$x->str_to = text::toUTF8(html::decodeEntities($str_out));
			$rsp->insertNode($x);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
		return $rsp;
	}
}
?>