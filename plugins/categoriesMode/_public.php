<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of categoriesMode, a plugin for Dotclear 2.
#
# Copyright (c) 2007-2011 Adjaya and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

# If categoriesMode is not active we stop here :
if (!$core->blog->settings->categoriesmode->categoriesmode_active) { return; }

require_once dirname(__FILE__).'/_widgets.php';

# Adds  news Categories' templates tags :
$GLOBALS['core']->tpl->addValue('CategoryCount',array('tplCategories','CategoryCount'));
$GLOBALS['core']->tpl->addBlock('EntryIfCategoriesMode',array('tplCategories','EntryIfCategoriesMode'));
$GLOBALS['core']->tpl->addValue('CategoriesURL',array('tplCategories','CategoriesURL'));

class tplCategories
{
	/*
	Use tag : {{tpl:CategoryCount}}
	*/
	public static function CategoryCount($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return
		'<?php echo '.sprintf($f,'$_ctx->categories->nb_post').'; ?>';
	}
	/*
	Use : <tpl:EntryIfCategoriesMode>
			</tpl:EntryIfCategoriesMode>
	*/

    public static function EntryIfCategoriesMode($attr,$content)
    {
		return
		"<?php if (\$core->blog->settings->categoriesmode->categoriesmode_active) : ?>".
		$content.
		"<?php endif; ?>";
    }

	/*
	Use tag : {{tpl:CategoriesURL}}
	*/
	public static function CategoriesURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return
		'<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("categories")').'; ?>';
	}
}



# Adds a new template behavior :
$GLOBALS['core']->addBehavior('publicBeforeDocument',array('behaviorCategoriesMode','addTplPath'));

class behaviorCategoriesMode
{
	public static function addTplPath($core)
	{
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
	}
}

# 'categories' urlHandler :
$GLOBALS['core']->url->register('categories','categories','^categories$',array('urlCategories','categories'));

class urlCategories extends dcUrlHandlers
{
	public static function categories($args)
	{
		# The entry
		self::serveDocument('categories.html');
		exit;
	}
}

/* compatibilité avec Breadcrumb */
$core->addBehavior('publicBreadcrumb',array('extCategoriesMode','publicBreadcrumb'));
class extCategoriesMode
{
	public static function publicBreadcrumb($context,$separator)
	{
		// check URL type
		if ($context == 'categories') {
			// It's a CategoriesMode page, return my own part
			return __('Categories page');
		}
	}
}
?>
