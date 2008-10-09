<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Public Media.
# Copyright 2008 Moe (http://gniark.net/)
#
# Public Media is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Public Media is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) { return; }


# load locales for the blog language
l10n::set(dirname(__FILE__).'/locales/'.$core->blog->settings->lang.'/public');

/**
@ingroup Public Media
@brief Document
*/
class publicMediaPageDocument extends dcUrlHandlers
{
	/**
	serve the document
	@param	args	<b>string</b>	Argument
	*/
	public static function page($args)
	{
		global $core;

		if (!$core->blog->settings->publicmedia_page_active) {self::p404();}

		# start session
		$session_id = session_id();
		if (empty($session_id)) {session_start();}

		try
		{
			$_ctx =& $GLOBALS['_ctx'];

			$_ctx->media = new dcMedia($core);
			
			# define root of the media page
			$page_root = $core->blog->settings->publicmedia_page_root;

			# used to remove root from path
			$page_root_len = strlen($page_root);

			$page_dir = $page_root;

			$_ctx->mediaPage_currentDir = '/';

			# BreadCrumb
			$breadCrumb = array();

			# if visitor asked a directory
			if ((!empty($args)) && (substr($args,0,1) == '/'))
			{
				$_ctx->mediaPage_currentDir = substr($args,1);
				$page_dir = $page_root.'/'.$_ctx->mediaPage_currentDir;
		
				# BreadCrumb
				$base_url = publicMedia::pageURL().'/';
				$dirs = explode('/',$_ctx->mediaPage_currentDir);
				$path = '';
				foreach ($dirs as $dir)
				{
					$path = (($path == '') ? $dir : $path.'/'.$dir); 
					$breadCrumb[$dir] = $base_url.$path;
				}
			}
			
			$_ctx->mediaPage_BreadCrumb = $breadCrumb;
			unset($breadCrumb);
			
			# file sort
			# default value
			$_ctx->mediaPage_fileSort = $core->blog->settings->publicmedia_page_file_sort;

			# if visitor can choose how to sort files
			if ($core->blog->settings->publicmedia_page_enable_sort === true)
			{
				# from /dotclear/admin/media.php
				if ((!empty($_POST['media_file_sort']))
					&& (in_array($_POST['media_file_sort'],publicMedia::getSortValues())))
				{
					$_SESSION['media_file_sort'] = $_POST['media_file_sort'];
				}
				if (!empty($_SESSION['media_file_sort']))
				{
					$_ctx->media->setFileSort($_SESSION['media_file_sort']);
					$_ctx->mediaPage_fileSort = $_SESSION['media_file_sort'];
				}
				# /from /dotclear/admin/media.php
			}

			# exit if the directory doesn't exist
			$dir_full_path = $_ctx->media->root.'/'.$page_dir;
			$parent_dir_full_path = path::real(dirname($dir_full_path));
			if (!is_dir($dir_full_path)) {self::p404();}

			$_ctx->media->setFileSort($_ctx->mediaPage_fileSort);

			$_ctx->media->chdir($page_dir);
			$_ctx->media->getDir();			
			
			# get relative paths from root of the media page
			foreach ($_ctx->media->dir['dirs'] as $k => $v)
			{
				$item =& $_ctx->media->dir['dirs'][$k];
				if (($item->file == $_ctx->media->root)
					&& ($_ctx->mediaPage_currentDir == '/'))
				{
					# remove link to root directory
					unset($_ctx->media->dir['dirs'][$k]);
				}
				else
				{
					$item->relname =
						substr($item->relname,$page_root_len);
					if ($item->file == $parent_dir_full_path)
					{
						$item->basename = __('parent directory');
					}
				}
			}

			foreach ($_ctx->media->dir['files'] as $k => $v)
			{
				$item =& $_ctx->media->dir['files'][$k];

				$item->relname =
					substr($item->relname,$page_root_len);
			}
		}
		catch (Exception $e)
		{
			$_ctx->mediaPage_Error = $e->getMessage();
		}

		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates/');

		self::serveDocument('media.html','text/html');
	}
}

$core->tpl->addValue('MediaCurrentDir',array('publicMediaPageTpl','currentDir'));

# sort files
$core->tpl->addBlock('MediaIfSortIsEnabled',array('publicMediaPageTpl',
	'ifSortIsEnabled'));

$core->tpl->addValue('MediaFileSortOptions',array('publicMediaPageTpl',
	'fileSortOptions'));

# Bread Crumb
$core->tpl->addValue('MediaBaseURL',array('publicMediaPageTpl','baseURL'));

$core->tpl->addBlock('MediaBreadCrumb',array('publicMediaPageTpl','breadCrumb'));
$core->tpl->addValue('MediaBreadCrumbDirName',array('publicMediaPageTpl',
	'breadCrumbDirName'));
$core->tpl->addValue('MediaBreadCrumbDirURL',array('publicMediaPageTpl',
	'breadCrumbDirURL'));
$core->tpl->addBlock('MediaBreadCrumbSeparator',array('publicMediaPageTpl',
	'breadCrumbSeparator'));

# error
$core->tpl->addBlock('MediaIfError',array('publicMediaPageTpl','ifError'));
$core->tpl->addValue('MediaError',array('publicMediaPageTpl','error'));

# media
$core->tpl->addBlock('Media',array('publicMediaPageTpl','media'));

$core->tpl->addBlock('MediaIfNoItem',array('publicMediaPageTpl','ifNoItem'));

$core->tpl->addBlock('MediaHeader',array('publicMediaPageTpl','header'));
$core->tpl->addBlock('MediaFooter',array('publicMediaPageTpl','footer'));

# item switch
$core->tpl->addBlock('MediaItemSwitch',array('publicMediaPageTpl','itemSwitch'));
$core->tpl->addValue('MediaSwitchCase',array('publicMediaPageTpl','itemSwitchCase'));
$core->tpl->addValue('MediaSwitchBreak',array('publicMediaPageTpl',
	'itemSwitchBreak'));
$core->tpl->addValue('MediaSwitchDefault',array('publicMediaPageTpl',
	'itemSwitchDefault'));

# item
$core->tpl->addValue('MediaItemDirURL',array('publicMediaPageTpl','itemDirURL'));
$core->tpl->addValue('MediaItemDirPath',array('publicMediaPageTpl','itemDirPath'));

$core->tpl->addValue('MediaItemTitle',array('publicMediaPageTpl','itemTitle'));
$core->tpl->addValue('MediaItemSize',array('publicMediaPageTpl','itemSize'));
$core->tpl->addValue('MediaItemFileURL',array('publicMediaPageTpl','itemFileURL'));

$core->tpl->addValue('MediaItemBasename',array('publicMediaPageTpl',
	'itemBasename'));
$core->tpl->addValue('MediaItemExtension',array('publicMediaPageTpl',
	'itemExtension'));
$core->tpl->addValue('MediaItemType',array('publicMediaPageTpl','itemType'));
$core->tpl->addValue('MediaItemMediaType',array('publicMediaPageTpl',
	'itemMediaType'));
$core->tpl->addValue('MediaItemMTime',array('publicMediaPageTpl','itemMTime'));
$core->tpl->addValue('MediaItemImageThumbPath',array('publicMediaPageTpl',
	'itemImageThumbPath'));

/**
@ingroup Public Media
@brief Template
*/
class publicMediaPageTpl
{
	/**
	display current directory
	@return	<b>string</b> PHP block
	*/
	public static function currentDir()
	{
		return("<?php echo(\$_ctx->mediaPage_currentDir); ?>");
	}

	/**
	if sort is enabled
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ifSortIsEnabled($attr,$content)
	{
		return
		'<?php if ($core->blog->settings->publicmedia_page_enable_sort === true) : ?>'."\n".
		$content.
		'<?php endif; ?>';
	}

	/**
	display file sort <select ...><option ...>
	@return	<b>string</b> PHP block
	*/
	public static function fileSortOptions()
	{
		return('<?php echo form::combo(\'media_file_sort\',
			publicMedia::getSortValues(),$_ctx->mediaPage_fileSort); ?>');
	}
	
	/**
	display base URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function baseURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo('.sprintf($f,'publicMedia::pageURL()').'); ?>');
	}
	
	/**
	BreadCrumb
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function breadCrumb($attr,$content)
	{
		return("<?php ".
		'$_ctx->mediaBCIndex = 0;'.
		'foreach ($_ctx->mediaPage_BreadCrumb as $k => $v) {'.
			'?>'.
			$content.
		'<?php $_ctx->mediaBCIndex += 1; }'.
		'unset($_ctx->mediaBCIndex,$k,$v);'.
		"?>");
	}
	
	/**
	display current directory
	@return	<b>string</b> PHP block
	*/
	public static function breadCrumbDirURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo('.sprintf($f,'$v').'); ?>');
	}

	/**
	display current directory
	@return	<b>string</b> PHP block
	*/
	public static function breadCrumbDirName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo('.sprintf($f,'$k').'); ?>');
	}

	/**
	BreadCrumb separator
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function breadCrumbSeparator($attr,$content)
	{
		$equal = (((isset($attr['last'])) && ($attr['last'] == 1)) ? '=' : '');

		return('<?php if ($_ctx->mediaBCIndex <'.$equal.
			' (count($_ctx->mediaPage_BreadCrumb)-1)) : ?>'.
		$content.
		'<?php endif; ?>');
	}

	/**
	if there is an error
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ifError($attr,$content)
	{
		return
		"<?php if (\$_ctx->mediaPage_Error !== null) : ?>"."\n".
		$content.
		"<?php endif; ?>";
	}

	/**
	display an error
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function error($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return("<?php if (\$_ctx->mediaPage_Error !== null) :"."\n".
		'echo('.sprintf($f,'$_ctx->mediaPage_Error').');'.
		"endif; ?>");
	}
	
	/**
	No item
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ifNoItem($attr,$content)
	{
		$type = ($attr['type'] == 'dirs') ? 'dirs' : 'files';

		return('<?php if (count($_ctx->media->dir[\''.$type.'\']) == 0) : ?>'.
		$content.
		'<?php endif; ?>');
	}
	
	/**
	loop on media
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function media($attr,$content)
	{
		$type = ($attr['type'] == 'dirs') ? 'dirs' : 'files';
		return("<?php ".
		'$_ctx->media_items = $_ctx->media->dir[\''.$type.'\'];'.
		"if (\$_ctx->media_items !== null) :"."\n".
		'$_ctx->media->index = 0;'.
		"foreach (\$_ctx->media_items as \$_ctx->media_item) { ".
		"?>"."\n".
		$content.
		'<?php $_ctx->media->index += 1; } '."\n".
		" endif;"."\n".
		'unset($_ctx->media_item,$_ctx->media->index); ?>');
	}

	/**
	Header
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function header($attr,$content)
	{
		return('<?php if ($_ctx->media->index == 0) : ?>'.
		$content.
		'<?php endif; ?>');
	}

	/**
	Footer
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function footer($attr,$content)
	{
		return('<?php if ($_ctx->media->index == (count($_ctx->media_items)-1)) : ?>'.
		$content.
		'<?php endif; ?>');
	}
	
	/**
	Item directory URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemDirURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return('<?php echo '.sprintf($f,'$_ctx->media_item->dir_url').'; ?>');
	}
	
	/**
	Item directory path
	@return	<b>string</b> PHP block
	*/
	public static function itemDirPath()
	{
		global $core;
		return('<?php echo '.
			'publicMedia::pageURL().'.
			'((!empty($_ctx->media_item->relname)) ?'.
			'\'/\'.$_ctx->media_item->relname : \'\'); ?>');
	}

	/**
	Switch
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function itemSwitch($attr,$content)
	{
		if (!isset($attr['value'])) {return;}

		return('<?php switch($_ctx->media_item->'.$attr['value'].') : ?>'.
		$content.
		'<?php endswitch; ?>');
	}

	/**
	Switch case
	@return	<b>string</b> PHP block
	*/
	public static function itemSwitchCase($attr)
	{
		if (!isset($attr['case'])) {return;}

		return('<?php case \''.$attr['case'].'\': ?>');
	}

	/**
	Switch break
	@return	<b>string</b> PHP block
	*/
	public static function itemSwitchBreak()
	{
		return('<?php break; ?>');
	}
	
	/**
	Switch default
	@return	<b>string</b> PHP block
	*/
	public static function itemSwitchDefault()
	{
		return('<?php default: ?>');
	}
	
	/**
	Item title
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function itemTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->media_item->media_title').'; ?>');
	}
	/**
	Item size
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemSize($attr)
	{
		$format_open = $format_close = '';
		if (isset($attr['format']) && $attr['format'] == '1')
		{
			$format_open =  'files::size(';
			$format_close = ')';
		}
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo '.sprintf($f,
			$format_open.'$_ctx->media_item->size'.$format_close).'; ?>');
	}
	/**
	Item file URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemFileURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->media_item->file_url').'; ?>');
	}		
	/**
	Item basename
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemBasename($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->media_item->basename').'; ?>');
	}
	/**
	Item extension
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemExtension($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->media_item->extension').'; ?>');
	}
	/**
	Item type : text/plain
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemType($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->media_item->type').'; ?>');
	}

	/**
	Item media type : text
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemMediaType($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->media_item->media_type').'; ?>');
	}

	/**
	Item mtime
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemMTime($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->media_item->media_dtstr').'; ?>');
	}

	/**
	Item image thumbnail
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemImageThumbPath($attr)
	{
		global $core;

		$size = 'sq';

		if ((isset($attr['size']))
			&& array_key_exists($attr['size'],$core->media->thumb_sizes))
		{$size = $attr['size'];}

		return('<?php if (isset($_ctx->media_item->media_thumb[\''.
			$size.'\'])) :'.
		'echo($_ctx->media_item->media_thumb[\''.$size.'\']);'.
		'else :'.
		'echo($_ctx->media_item->file_url);'.
		'endif; ?>');
	}
}

/**
@ingroup Public Media
@brief Widget
*/
class publicMediaWidget
{
	/**
	show widget
	@param	w	<b>object</b>	Widget
	@return	<b>string</b> XHTML
	*/
	public static function show(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		if (!is_object($core->media))
		{
			$core->media = new dcMedia($core);
		}

		# from /dotclear/admin/media.php
		if ($w->file_sort) {
			$core->media->setFileSort($w->file_sort);
		}
		# /from /dotclear/admin/media.php

		$core->media->chdir($w->root);
		$core->media->getDir();
		
		$items = $core->media->dir['files'];

		$items_str = '';

		foreach ($items as $media_item) {
			$items_str .= sprintf($w->item,$media_item->file_url,
				$media_item->media_title,$media_item->basename);
		}
		unset($items);

		# output
		$header = (strlen($w->title) > 0)
			? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;

		$str = sprintf($w->block,$items_str);

		$link = (strlen($w->link) > 0) ? '<p class="text"><a href="'.publicMedia::pageURL().'">'.
			html::escapeHTML($w->link).'</a></p>' : null;

		return '<div class="media">'.$header.$str.$link.'</div>';
	}
}

?>