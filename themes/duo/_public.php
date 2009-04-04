<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Duo, a theme for Dotclear.
# The code if from the Noviny theme, thanks to Olivier.
#
# Copyright (c) 2009 annso
# contact@as-i-am.fr
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

# Ajax search URL
$core->url->register('ajaxsearch','ajaxsearch','^ajaxsearch(?:(?:/)(.*))?$',array('urlsNoviny','ajaxsearch'));

class urlsNoviny
{
	public static function ajaxsearch($args)
	{
		global $core;
		$res = '';

		try
		{
			if (!$args) {
				throw new Exception;
			}

			$q = rawurldecode($args);
			$rs = $core->blog->getPosts(array(
				'search' => $q,
				'limit' => 5
			));

			if ($rs->isEmpty()) {
				throw new Exception;
			}

			$res = '<ul>';
			while ($rs->fetch())
			{
				$res .= '<li><a href="'.$rs->getURL().'">'.html::escapeHTML($rs->post_title).'</a><br />';
				$res .= '</li>';
			}
			$res .= '</ul>';
		}
		catch (Exception $e) {}

		header('Content-Type: text/plain; charset=UTF-8');
		echo $res;
	}
}
?>
