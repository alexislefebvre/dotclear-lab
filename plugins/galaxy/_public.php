<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear Galaxy plugin.
#
# Dotclear Galaxy plugin is free software: you can redistribute it
# and/or modify  it under the terms of the GNU General Public License
# version 2 of the License as published by the Free Software Foundation.
#
# Dotclear Galaxy plugin is distributed in the hope that it will be
# useful, but WITHOUT ANY WARRANTY; without even the implied warranty
# of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Dotclear Galaxy plugin.
# If not, see <http://www.gnu.org/licenses/>.
#
# Copyright (c) 2010 Mounir Lamouri.
# Based on the Dotclear metadata plugin by Olivier Meunier.
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('templateBeforeBlock',array('behaviorsGalaxy','templateBeforeBlock'));

class behaviorsGalaxy
{
	public static function templateBeforeBlock(&$core,$b,$attr)
  {
    if (empty($attr['no_context']) && $b == 'Entries')
    {
      return
      '<?php if ($_ctx->exists("planet_id")) { '.
        "@\$params['from'] .= ', '.\$core->prefix.'galaxy GALAXY ';\n".
        "@\$params['sql'] .= 'AND GALAXY.post_id = P.post_id ';\n".
        "\$params['sql'] .= \"AND GALAXY.planet_id = '\".\$core->con->escape(\$_ctx->planet_id).\"' \";\n".
      "} ?>\n";
		}
  }
} 

class urlGalaxy extends dcUrlHandlers
{
	public static function planetFeed($args)
	{
		if (!preg_match('#^(.+)/(atom|rss2)?$#',$args,$m))
		{
			self::p404();
		}
		else
		{
			$planet = $m[1];
			$type = $m[2];

			// If $planet is not a valid planet id, it will show an empty feed.
			// It prevents us to check in the bad only to throw a 404 error.
			$GLOBALS['_ctx']->planet_id = $planet;
			$GLOBALS['_ctx']->feed_subtitle = ' - '.__('Planet').' - '.$planet;

			if ($type == 'atom')
		 	{
				$mime = 'application/atom+xml';
			}
			else
			{
				$mime = 'application/xml';
			}

			self::serveDocument($type.'.xml',$mime);
		}
	}
}
?>
