<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of DL Manager.
# Copyright 2008 Moe (http://gniark.net/) and Tomtom (http://blog.zenstyle.fr)
#
# DL Manager is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# DL Manager is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) { return; }


# load locales for the blog language
l10n::set(dirname(__FILE__).'/locales/'.$core->blog->settings->lang.'/public');

/**
@ingroup Download manager
@brief Document
*/
class dlManagerPageDocument extends dcUrlHandlers
{
	/**
	serve the document
	@param	args	<b>string</b>	Argument
	*/
	public static function page($args)
	{
		global $core;

		if (!$core->blog->settings->dlmanager_active) {self::p404();}

		# start session
		$session_id = session_id();
		if (empty($session_id)) {session_start();}
		
		$_ctx =& $GLOBALS['_ctx'];
		
		try
		{
			# exit if the public_path (and Media root) doesn't exist
			if (!is_dir($core->blog->public_path)) {self::p404();}
			
			if (!is_object($core->media))
			{
				$core->media = new dcMedia($core);
			}
			
			# define root of DL Manager
			$page_root = $core->blog->settings->dlmanager_root;

			# used to remove root from path
			$page_root_len = strlen($page_root);
			
			# remove slash at the beginning of the string
			if ($page_root_len > 0) {$page_root_len += 1;}
			
			$page_dir = $page_root;

			$_ctx->dlManager_currentDir = '/';

			# BreadCrumb
			$breadCrumb = array();

			# if visitor asked a directory
			if ((!empty($args)) && (substr($args,0,1) == '/'))
			{
				$_ctx->dlManager_currentDir = substr($args,1);
				$page_dir = $page_root.'/'.$_ctx->dlManager_currentDir;
		
				# BreadCrumb
				$base_url = dlManager::pageURL().'/';
				$dirs = explode('/',$_ctx->dlManager_currentDir);
				$path = '';
				
				foreach ($dirs as $dir)
				{
					$dir = trim($dir);
					if (!empty($dir))
					{
						$path = (($path == '') ? $dir : $path.'/'.$dir); 
						$breadCrumb[$dir] = $base_url.$path;
					}
				}
			}
			
			$_ctx->dlManager_BreadCrumb = $breadCrumb;
			unset($breadCrumb);
			# /BreadCrumb
			
			# file sort
			# default value
			$_ctx->dlManager_fileSort = $core->blog->settings->dlmanager_file_sort;

			# if visitor can choose how to sort files
			if ($core->blog->settings->dlmanager_enable_sort === true)
			{
				# from /dotclear/admin/media.php
				if ((!empty($_POST['media_file_sort']))
					&& (in_array($_POST['media_file_sort'],dlManager::getSortValues())))
				{
					$_SESSION['media_file_sort'] = $_POST['media_file_sort'];
				}
				if (!empty($_SESSION['media_file_sort']))
				{
					$core->media->setFileSort($_SESSION['media_file_sort']);
					$_ctx->dlManager_fileSort = $_SESSION['media_file_sort'];
				}
				# /from /dotclear/admin/media.php
			}

			# exit if the directory doesn't exist
			$dir_full_path = $core->media->root.'/'.$page_dir;
			$parent_dir_full_path = path::real(dirname($dir_full_path));
			if (!is_dir($dir_full_path)) {self::p404();}

			$core->media->setFileSort($_ctx->dlManager_fileSort);

			$core->media->chdir($page_dir);
			$core->media->getDir();			
			
			# get relative paths from root of DL Manager
			foreach ($core->media->dir['dirs'] as $k => $v)
			{
				$item =& $core->media->dir['dirs'][$k];
				$item->media_type = 'folder';
				
				if (($item->file == $core->media->root)
					&& ($_ctx->dlManager_currentDir == '/'))
				{
					# remove link to root directory
					unset($core->media->dir['dirs'][$k]);
				}
				else
				{
					$item->relname =
						substr($item->relname,$page_root_len);
					
					# parent directory
					if ($item->file == $parent_dir_full_path)
					{
						$item->basename = __('parent directory');
					}
				}
			}

			foreach ($core->media->dir['files'] as $k => $v)
			{
				$item =& $core->media->dir['files'][$k];

				$item->relname =
					substr($item->relname,$page_root_len);
			}
		}
		catch (Exception $e)
		{
			$_ctx->dlManager_Error = $e->getMessage();
		}

		$core->tpl->setPath($core->tpl->getPath(),
			dirname(__FILE__).'/default-templates/');

		self::serveDocument('media.html','text/html');
	}

	/**
	serve the media player document
	@param	args	<b>string</b>	Argument
	*/
	public static function player($args)
	{
		global $core;

		if (!$core->blog->settings->dlmanager_active) {self::p404();}

		$_ctx =& $GLOBALS['_ctx'];
		

		# exit if the public_path (and Media root) doesn't exist
		if (!is_dir($core->blog->public_path)) {self::p404();}
		
		if (!is_object($core->media))
		{
			$core->media = new dcMedia($core);
		}
		
		$file = $core->media->getFile($args);
		
		if (empty($file->file))
		{
			self::p404();
		}
	
		$page_root = $core->blog->settings->dlmanager_root;
		
		if (!empty($page_root))
		{
			if (strpos($file->relname,$page_root) !== 0)
			{
				self::p404();
			}
		}		
	       
		if (is_readable($file->file))
		{
			$_ctx->dlManager_item = $file;
			$_ctx->file_url = $file->file_url;
			
			# compatibility with Dotclear revisions < 2445
			global $attach_f;
			$attach_f = new ArrayObject();
			$attach_f->file_url = $file->file_url;

			$core->tpl->setPath($core->tpl->getPath(),
				dirname(__FILE__).'/default-templates/');
	
			self::serveDocument('media_player.html','text/html');
		}
	}
	
	/**
	serve file
	@param	args	<b>string</b>	Argument
	*/
	public static function wrapper($args)
	{
		global $core;

		if (empty($args) || !$core->blog->settings->dlmanager_active) {
			self::p404();
		}
		
		if (!is_object($core->media))
		{
			$core->media = new dcMedia($core);
		}

		$file = $core->media->getFile($args);
		
		if (empty($file->file))
		{
			self::p404();
		}
		
		$page_root = $core->blog->settings->dlmanager_root;
		
		if (!empty($page_root))
		{
			if (strpos($file->relname,$page_root) !== 0)
			{
				self::p404();
			}
		}		
	  
		if (is_readable($file->file))
		{
			$count = unserialize($core->blog->settings->dlmanager_count_dl);
			if (!is_array($count)) {$count = array();}
			$count[$file->media_id] = array_key_exists($file->media_id,$count)
				? $count[$file->media_id]+1 : 1;
			if (!is_object($core->blog->settings))
			{
				$settings = new dcSettings($core,$core->blog->id);
			}
			else
			{
				$settings =& $core->blog->settings;
			}
			$settings->setNamespace('dlmanager');
			$settings->put('dlmanager_count_dl',serialize($count),'string',
				'Download counter');
			//$core->callBehavior('publicDownloadedFile',(integer)$args);
			header('Content-type: '.$file->type);
			header('Content-Disposition: attachment; filename="'.$file->basename.'"');
			readfile($file->file);
			exit;
		}

		self::p404();
	}
	
	/**
	serve files icons
	@param	args	<b>string</b>	Argument
	*/
	public static function icon($args)
	{
		global $core;

		if (empty($args) || (!$core->blog->settings->dlmanager_active)
			|| (!preg_match('/^[a-z]+$/',$args)))
		{
			self::p404();
		}
		
		$icon_path = path::real(DC_ROOT.'/admin/images/media/'.$args.'.png');
		
		try
		{
			if (is_readable($icon_path))
			{
				# from /dotclear/inc/load_plugin_file.php
				http::cache(array_merge(array($icon_path),get_included_files()));
				header('Content-type: '.files::getMimeType($icon_path));
				header('Content-Length: '.filesize($icon_path));
				readfile($icon_path);
				exit;
				# /from /dotclear/inc/load_plugin_file.php
			}
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
}

$core->tpl->addValue('DLMCurrentDir',array('dlManagerPageTpl','currentDir'));

# sort files
$core->tpl->addBlock('DLMIfSortIsEnabled',array('dlManagerPageTpl',
	'ifSortIsEnabled'));

$core->tpl->addValue('DLMFileSortOptions',array('dlManagerPageTpl',
	'fileSortOptions'));

# Bread Crumb
$core->tpl->addValue('DLMBaseURL',array('dlManagerPageTpl','baseURL'));

$core->tpl->addBlock('DLMBreadCrumb',array('dlManagerPageTpl','breadCrumb'));
$core->tpl->addValue('DLMBreadCrumbDirName',array('dlManagerPageTpl',
	'breadCrumbDirName'));
$core->tpl->addValue('DLMBreadCrumbDirURL',array('dlManagerPageTpl',
	'breadCrumbDirURL'));
$core->tpl->addBlock('DLMBreadCrumbSeparator',array('dlManagerPageTpl',
	'breadCrumbSeparator'));

# error
$core->tpl->addBlock('DLMIfError',array('dlManagerPageTpl','ifError'));
$core->tpl->addValue('DLMError',array('dlManagerPageTpl','error'));

# items
$core->tpl->addBlock('DLMItems',array('dlManagerPageTpl','items'));

$core->tpl->addBlock('DLMIfNoItem',array('dlManagerPageTpl','ifNoItem'));

$core->tpl->addBlock('DLMHeader',array('dlManagerPageTpl','header'));
$core->tpl->addBlock('DLMFooter',array('dlManagerPageTpl','footer'));

# item
$core->tpl->addBlock('DLMItemIf',array('dlManagerPageTpl','itemIf'));
$core->tpl->addValue('DLMItemDirURL',array('dlManagerPageTpl','itemDirURL'));
$core->tpl->addValue('DLMItemDirPath',array('dlManagerPageTpl','itemDirPath'));

$core->tpl->addValue('DLMItemIconPath',array('dlManagerPageTpl','itemIconPath'));
$core->tpl->addValue('DLMItemTitle',array('dlManagerPageTpl','itemTitle'));
$core->tpl->addValue('DLMItemSize',array('dlManagerPageTpl','itemSize'));
$core->tpl->addValue('DLMItemFileURL',array('dlManagerPageTpl','itemFileURL'));
$core->tpl->addValue('DLMItemDlURL',array('dlManagerPageTpl','itemDlURL'));
$core->tpl->addValue('DLMItemPlayerURL',array('dlManagerPageTpl','itemPlayerURL'));

$core->tpl->addValue('DLMItemBasename',array('dlManagerPageTpl',
	'itemBasename'));
$core->tpl->addValue('DLMItemExtension',array('dlManagerPageTpl',
	'itemExtension'));
$core->tpl->addValue('DLMItemType',array('dlManagerPageTpl','itemType'));
$core->tpl->addValue('DLMItemMediaType',array('dlManagerPageTpl',
	'itemMediaType'));
$core->tpl->addValue('DLMItemMTime',array('dlManagerPageTpl','itemMTime'));
$core->tpl->addValue('DLMItemDlCount',array('dlManagerPageTpl','itemDlCount'));
$core->tpl->addValue('DLMItemImageThumbPath',array('dlManagerPageTpl',
	'itemImageThumbPath'));

/**
@ingroup Download manager
@brief Template
*/
class dlManagerPageTpl
{
	/**
	display current directory
	@return	<b>string</b> PHP block
	*/
	public static function currentDir()
	{
		return("<?php echo(\$_ctx->dlManager_currentDir); ?>");
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
		'<?php if ($core->blog->settings->dlmanager_enable_sort === true) : ?>'."\n".
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
			dlManager::getSortValues(),$_ctx->dlManager_fileSort); ?>');
	}
	
	/**
	display base URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function baseURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo('.sprintf($f,'dlManager::pageURL()').'); ?>');
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
		'$_ctx->dlManagerBCIndex = 0;'.
		'foreach ($_ctx->dlManager_BreadCrumb as $k => $v) {'.
			'?>'.
			$content.
		'<?php $_ctx->dlManagerBCIndex += 1; }'.
		'unset($_ctx->dlManagerBCIndex,$k,$v);'.
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

		return('<?php if ($_ctx->dlManagerBCIndex <'.$equal.
			' (count($_ctx->dlManager_BreadCrumb)-1)) : ?>'.
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
		"<?php if (\$_ctx->dlManager_Error !== null) : ?>"."\n".
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
		
		return("<?php if (\$_ctx->dlManager_Error !== null) :"."\n".
		'echo('.sprintf($f,'$_ctx->dlManager_Error').');'.
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

		return('<?php if (count($core->media->dir[\''.$type.'\']) == 0) : ?>'.
		$content.
		'<?php endif; ?>');
	}
	
	/**
	loop on items
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function items($attr,$content)
	{
		$type = ($attr['type'] == 'dirs') ? 'dirs' : 'files';
		return("<?php ".
		'$_ctx->dlManager_items = $core->media->dir[\''.$type.'\'];'.
		"if (\$_ctx->dlManager_items !== null) :"."\n".
		'$_ctx->dlManager_index = 0;'.
		"foreach (\$_ctx->dlManager_items as \$_ctx->dlManager_item) { ".
		"?>"."\n".
		$content.
		'<?php $_ctx->dlManager_index += 1; } '."\n".
		" endif;"."\n".
		'unset($_ctx->dlManager_item,$_ctx->dlManager_index); ?>');
	}

	/**
	Header
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function header($attr,$content)
	{
		return('<?php if ($_ctx->dlManager_index == 0) : ?>'.
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
		return('<?php if ($_ctx->dlManager_index == '.
		'(count($_ctx->dlManager_items)-1)) : ?>'.
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
		return('<?php echo '.sprintf($f,'$_ctx->dlManager_item->dir_url').'; ?>');
	}
	
	/**
	Item directory path
	@return	<b>string</b> PHP block
	*/
	public static function itemDirPath()
	{
		global $core;
		return('<?php echo '.
			'dlManager::pageURL().'.
			'((!empty($_ctx->dlManager_item->relname)) ?'.
			'\'/\'.$_ctx->dlManager_item->relname : \'\'); ?>');
	}
	
	/**
	Item if
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	\see /dotclear/inc/public/class.dc.template.php > EntryIf()
	*/
	public static function itemIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';

		if (isset($attr['type'])) {
			$type = trim($attr['type']);
			$sign = '=';
			if (substr($type,0,1) == '!')
			{
				$sign = '!';
				$type = substr($type,1);
			}
			$types = explode(',',$type);
			foreach ($types as $type)
			{
				$if[] = '$_ctx->dlManager_item->type '.$sign.'= "'.$type.'"';
			}
		}
		
		if (isset($attr['media_type'])) {
			$type = trim($attr['media_type']);
			$sign = '=';
			if (substr($type,0,1) == '!')
			{
				$sign = '!';
				$type = substr($type,1);
			}
			$types = explode(',',$type);
			foreach ($types as $type)
			{
				$if[] = '$_ctx->dlManager_item->media_type '.$sign.'= "'.$type.'"';
			}
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.
				$content.
				'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/**
	Get operator
	@param	op	<b>string</b>	Operator
	@return	<b>string</b> Operator
	\see /dotclear/inc/public/class.dc.template.php > getOperator()
	*/
	protected static function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}
	
	/**
	Item icon path
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function itemIconPath($attr)
	{		
		return('<?php echo $core->blog->url.$core->url->getBase(\'icon\').'.
			'\'/\'.$_ctx->dlManager_item->media_type; ?>');
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
		
		return('<?php echo '.sprintf($f,
			'$_ctx->dlManager_item->media_title').'; ?>');
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
			$format_open.'$_ctx->dlManager_item->size'.$format_close).'; ?>');
	}
	
	/**
	Item file URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemFileURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->dlManager_item->file_url').'; ?>');
	}
	
	/**
	Item download URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemDlURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo($core->blog->url.$core->url->getBase(\'download\').'.
			'\'/\'.'.sprintf($f,'$_ctx->dlManager_item->media_id').'); ?>');
	}
	
	/**
	Item player URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemPlayerURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo($core->blog->url.$core->url->getBase(\'mediaplayer\').'.
			'\'/\'.'.sprintf($f,'$_ctx->dlManager_item->media_id').'); ?>');
	}
	
	/**
	Item basename
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemBasename($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->dlManager_item->basename').'; ?>');
	}
	
	/**
	Item extension
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemExtension($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->dlManager_item->extension').'; ?>');
	}
	
	/**
	Item type : text/plain
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemType($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->dlManager_item->type').'; ?>');
	}

	/**
	Item media type : text
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemMediaType($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->dlManager_item->media_type').'; ?>');
	}

	/**
	Item mtime
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemMTime($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->dlManager_item->media_dtstr').'; ?>');
	}
	
	/**
	Item download counter
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemDlCount($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return 
			'<?php $count = unserialize($core->blog->settings->dlmanager_count_dl); '.
			'if (empty($count)) {$count = array();}'.
			'echo '.sprintf($f,'array_key_exists($_ctx->dlManager_item->media_id,'.
				'$count) ? $count[$_ctx->dlManager_item->media_id] : "0"').
			'; ?>';
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

		return('<?php if (isset($_ctx->dlManager_item->media_thumb[\''.
			$size.'\'])) :'.
		'echo($_ctx->dlManager_item->media_thumb[\''.$size.'\']);'.
		'else :'.
		'echo($_ctx->dlManager_item->file_url);'.
		'endif; ?>');
	}
}

/**
@ingroup Download manager
@brief Widget
*/
class dlManagerWidget
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

		$items_str = $str = '';

		foreach ($items as $item) {
			$mediaplayer = '';
			if ($item->media_type == 'image' || $item->type == 'audio/mpeg3' || $item->type == 'video/x-flv')
			{
				$mediaplayer = '<a href="'.$core->blog->url.$core->url->getBase('mediaplayer').'/'.
					$item->media_id.'" title="'.__('Preview :').' '.$item->media_title.'">'.
				'<img src="'.$core->blog->getQmarkURL().'pf=dlManager/images/control_play.png" alt="'.__('Preview').'" />'.
				'</a>';
			}
			
			$items_str .= sprintf($w->item,$core->blog->url.
				$core->url->getBase('download').'/'.$item->media_id,
				$item->media_title,$item->basename,$mediaplayer);
		}
		unset($items);

		# output
		$header = (strlen($w->title) > 0)
			? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;

		if (!empty($items_str))
		{
			$str = sprintf($w->block,$items_str);
		}

		$link = (strlen($w->link) > 0) ? '<p class="text"><a href="'.
			dlManager::pageURL().'">'.html::escapeHTML($w->link).'</a></p>' : null;

		return '<div class="dlmanager">'.$header.$str.$link.'</div>';
	}
}

?>
