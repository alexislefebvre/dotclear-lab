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

if (!defined('DC_RC_PATH')) {return;}

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
						
			# define root of DL Manager
			$page_root = $core->blog->settings->dlmanager_root;

			# used to remove root from path
			$page_root_len = strlen($page_root);
			
			# remove slash at the beginning of the string
			if ($page_root_len > 0) {$page_root_len += 1;}
			
			$page_dir = $page_root;

			$_ctx->dlManager_currentDir = '/';
			
			# if visitor asked a directory
			if ((!empty($args)) && (substr($args,0,1) == '/'))
			{
				$_ctx->dlManager_currentDir = substr($args,1);
				$page_dir = $page_root.'/'.$_ctx->dlManager_currentDir;
				
				unset($breadCrumb);
			}
			
			# BreadCrumb
			$_ctx->dlManager_BreadCrumb = dlManager::breadCrumb($_ctx->dlManager_currentDir);
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
				
				if (($item->file == $parent_dir_full_path)
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
			
			$_ctx->dlManager_dirs = $core->media->dir['dirs'];
			
			$files_array = $core->media->dir['files'];
			
			$_ctx->dlManager_pager = new pager(
				# page
				((isset($_GET['page'])) ? $_GET['page'] : 1),count($files_array),
				$core->blog->settings->dlmanager_nb_per_page,10);
			
			$_ctx->dlManager_pager->html_prev = '&#171; '.__('previous page');
			$_ctx->dlManager_pager->html_next = __('next page').' &#187;';
						
			$core->media->dir['files'] = array();
			
			for ($i=$_ctx->dlManager_pager->index_start, $j=0;
				$i<=$_ctx->dlManager_pager->index_end; $i++, $j++)
			{
				$item =& $files_array[$i];

				$item->relname = substr($item->relname,$page_root_len);
				
				$core->media->dir['files'][] = $files_array[$i];
			}
			$_ctx->dlManager_files = $core->media->dir['files'];
			
			# download counter
			$_ctx->dlManager_count_dl =
				unserialize($core->blog->settings->dlmanager_count_dl);
			if (!is_array($_ctx->dlManager_count_dl))
			{
				$_ctx->dlManager_count_dl = array();
			}
			
			unset($files_array);
		}
		catch (Exception $e)
		{
			$_ctx->form_error = $e->getMessage();
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
				
		$file = $core->media->getFile(str_replace('/js','',$args));
		
		if ((empty($file->file)) || (!is_readable($file->file)))
		{
			self::p404();
		}
		
		$_ctx->items = $file;
		$_ctx->file_url = $file->file_url;
			
		# define root of DL Manager
		$page_root = $core->blog->settings->dlmanager_root;
		
		# used to remove root from path
		$page_root_len = strlen($page_root);
		
		# remove slash at the beginning of the string
		if ($page_root_len > 0) {$page_root_len += 1;}
		
		if (!dlManager::inJail($file->relname)) {self::p404();}
	  
	  $_ctx->items->relname =
			dirname(substr($_ctx->items->relname,$page_root_len));
		if ($_ctx->items->relname == '.')
		{
			$_ctx->items->relname = '';
		}
		
		# if visitor asked a directory
		$_ctx->dlManager_currentDir = $_ctx->items->relname;
		$page_dir = $page_root.'/'.$_ctx->dlManager_currentDir;
		
		# BreadCrumb
		$_ctx->dlManager_BreadCrumb = dlManager::breadCrumb($_ctx->dlManager_currentDir);
		# /BreadCrumb
		
		# download counter
		$_ctx->dlManager_count_dl =
			unserialize($core->blog->settings->dlmanager_count_dl);
		if (!is_array($_ctx->dlManager_count_dl))
		{
			$_ctx->dlManager_count_dl = array();
		}
		
		$core->tpl->setPath($core->tpl->getPath(),
			dirname(__FILE__).'/default-templates/');

		if (preg_match('#^.*\/js$#',$args)) {
			self::serveDocument('_media_player_content.html','text/html');
		} 
		else {
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
			
		$file = $core->media->getFile($args);
		
		if (empty($file->file))
		{
			self::p404();
		}
		
		$page_root = $core->blog->settings->dlmanager_root;
		
		if (!dlManager::inJail($file->relname)) {self::p404();}
	  
		if (is_readable($file->file))
		{
			if ($core->blog->settings->dlmanager_counter)
			{
				$count = unserialize($core->blog->settings->dlmanager_count_dl);
				if (!is_array($count)) {$count = array();}
				$count[$file->media_id] = array_key_exists($file->media_id,$count)
					? $count[$file->media_id]+1 : 1;
				
				$settings =& $core->blog->settings;
				
				$settings->setNamespace('dlmanager');
				$settings->put('dlmanager_count_dl',serialize($count),'string',
					'Download counter');
			}
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

# items
$core->tpl->addBlock('DLMItems',array('dlManagerPageTpl','items'));

$core->tpl->addBlock('DLMIfNoItem',array('dlManagerPageTpl','ifNoItem'));

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

$core->tpl->addBlock('DLMIfDownloadCounter',array('dlManagerPageTpl','ifDownloadCounter'));

# image meta
$core->tpl->addBlock('DLMItemImageMeta',array('dlManagerPageTpl',
	'itemImageMeta'));
$core->tpl->addValue('DLMItemImageMetaName',array('dlManagerPageTpl',
	'itemImageMetaName'));
$core->tpl->addValue('DLMItemImageMetaValue',array('dlManagerPageTpl',
	'itemImageMetaValue'));

# zip content
$core->tpl->addBlock('DLMItemZipContent',array('dlManagerPageTpl',
	'itemZipContent'));
$core->tpl->addValue('DLMItemZipContentFile',array('dlManagerPageTpl',
	'itemZipContentFile'));

# find entries containing a media
$core->tpl->addBlock('DLMItemEntries',array('dlManagerPageTpl',
	'itemEntries'));

# 
$core->tpl->addValue('DLMPageLinks',array('dlManagerPageTpl',
	'pageLinks'));

if ($core->blog->settings->dlmanager_attachment_url)
{
	# redefine {{tpl:AttachmentURL}}
	$core->tpl->addValue('AttachmentURL',array('dlManagerPageTpl',
		'AttachmentURL'));
}

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
		return('<?php while ($_ctx->dlManager_BreadCrumb->fetch()) : ?>'.
			$content.
		'<?php endwhile; ?>');
	}
	
	/**
	display current directory
	@return	<b>string</b> PHP block
	*/
	public static function breadCrumbDirURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo('.sprintf($f,'$_ctx->dlManager_BreadCrumb->url').'); ?>');
	}

	/**
	display current directory
	@return	<b>string</b> PHP block
	*/
	public static function breadCrumbDirName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo('.sprintf($f,'$_ctx->dlManager_BreadCrumb->name').'); ?>');
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

		return('<?php if (count($_ctx->{\'dlManager_'.$type.'\'}) == 0) : ?>'.
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
		
		return
		'<?php '.
		'$_ctx->items = dlManager::getItems($_ctx->{\'dlManager_'.$type.'\'}); '.
		'while ($_ctx->items->fetch()) : ?>'."\n".
		$content.
		'<?php endwhile; unset($_ctx->items); ?>';
	}
	
	/**
	Item directory URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemDirURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return('<?php echo '.sprintf($f,'$_ctx->items->dir_url').'; ?>');
	}
	
	/**
	Item directory path
	@return	<b>string</b> PHP block
	*/
	public static function itemDirPath()
	{
		global $core;
		return('<?php echo '.
			# empty can't be used with $_ctx->items->relname, use strlen() instead
			'dlManager::pageURL().'.'((strlen($_ctx->items->relname) > 0) ?'.
			'\'/\'.$_ctx->items->relname : \'\'); ?>');
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
				$if[] = '$_ctx->items->type '.$sign.'= "'.$type.'"';
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
				$if[] = '$_ctx->items->media_type '.$sign.'= "'.$type.'"';
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
			'\'/\'.$_ctx->items->media_type; ?>');
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
		
		return('<?php echo '.sprintf($f,'$_ctx->items->media_title').'; ?>');
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
			$format_open.'$_ctx->items->size'.$format_close).'; ?>');
	}
	
	/**
	Item file URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemFileURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->items->file_url').'; ?>');
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
			'\'/\'.'.sprintf($f,'$_ctx->items->media_id').'); ?>');
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
			'\'/\'.'.sprintf($f,'$_ctx->items->media_id').'); ?>');
	}
	
	/**
	Item basename
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemBasename($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->items->basename').'; ?>');
	}
	
	/**
	Item extension
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemExtension($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->items->extension').'; ?>');
	}
	
	/**
	Item type : text/plain
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemType($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->items->type').'; ?>');
	}

	/**
	Item media type : text
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemMediaType($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->items->media_type').'; ?>');
	}

	/**
	Item mtime
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemMTime($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->items->media_dtstr').'; ?>');
	}
	
	/**
	Item download counter
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemDlCount()
	{
		return 
			'<?php echo (array_key_exists($_ctx->items->media_id,'.
				'$_ctx->dlManager_count_dl) ? $_ctx->dlManager_count_dl[$_ctx->items->media_id] : "0"); ?>';
	}

	/**
	Test if the download counter is active
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content of the loop
	@return	<b>string</b> PHP block
	*/
	public static function ifDownloadCounter($attr,$content)
	{
		return('<?php if ($core->blog->settings->dlmanager_counter) : ?>'.
		$content.
		'<?php endif; ?>');
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

		return('<?php if (isset($_ctx->items->media_thumb[\''.
			$size.'\'])) :'.
		'echo($_ctx->items->media_thumb[\''.$size.'\']);'.
		'else :'.
		'echo($_ctx->items->file_url);'.
		'endif; ?>');
	}
	
	/**
	Loop on image meta
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content of the loop
	@return	<b>string</b> PHP block
	*/
	public static function itemImageMeta($attr,$content)
	{
		return
		'<?php '.
		'$_ctx->meta = dlManager::getImageMeta($_ctx->items); '.
		'while ($_ctx->meta->fetch()) : ?>'."\n".
		$content.
		'<?php endwhile; unset($_ctx->meta); ?>';
	}
	
	/**
	Image meta name
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemImageMetaName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->meta->name').'; ?>');
	}
	
	/**
	Image meta value
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemImageMetaValue($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->meta->value').'; ?>');
	}
	
	/**
	Loop on zip content
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemZipContent($attr,$content)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return
		'<?php '.
		'$_ctx->files = dlManager::getZipContent($_ctx->items); '.
		'while ($_ctx->files->fetch()) : ?>'."\n".
		$content.
		'<?php endwhile; unset($_ctx->files); ?>';
	}
	
	/**
	Zip content file
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemZipContentFile($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->files->file').'; ?>');
	}
	
	/**
	loop on posts which contain this item
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function itemEntries($attr,$content)
	{
		return("<?php ".
		'$_ctx->posts = dlManager::findPosts($_ctx->items->media_id);'.
		"while (\$_ctx->posts->fetch()) : ?>"."\n".
		$content.
		"<?php endwhile; unset(\$_ctx->posts); ?>");
	}
	
	/**
	redefine {{tpl:AttachmentURL}} to point to download/id
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function AttachmentURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo($core->blog->url.$core->url->getBase(\'download\').'.
			'\'/\'.'.sprintf($f,'$attach_f->media_id').'); ?>');
	}
	
	/**
	get page links
	@return	<b>string</b> PHP block
	*/
	public static function pageLinks()
	{
		return('<?php echo($_ctx->dlManager_pager->getLinks()); ?>');
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
		
		# from /dotclear/admin/media.php
		if ($w->file_sort) {
			$core->media->setFileSort($w->file_sort);
		}
		# /from /dotclear/admin/media.php

		$core->media->chdir($w->root);
		$core->media->getDir();
		
		if (!dlManager::inJail($w->root)) {return;}
		
		$items_str = $str = '';

		if ($w->display_dirs)
		{
			$items = $core->media->dir['dirs'];
			
			# define root of DL Manager
			$page_root = $core->blog->settings->dlmanager_root;

			# used to remove root from path
			$page_root_len = strlen($page_root);
			
			# remove slash at the beginning of the string
			if ($page_root_len > 0) {$page_root_len += 1;}
			
			foreach ($items as $item) {
				if (!empty($item->relname))
				{
					$item->relname =
							substr($item->relname,$page_root_len);
					
					$items_str .= sprintf($w->item,$core->blog->url.
						$core->url->getBase('media').'/'.$item->relname,
						$item->basename,$item->basename,'');
				}
			}
			
			if (!empty($items_str))
			{
				if ($w->dirs_title)
				{
					$str .= '<h3>'.html::escapeHTML($w->dirs_title).'</h3>';
				}
				$str .= sprintf($w->block,$items_str);
			}
		}
		
		if ($w->display_files)
		{
			$items_str = '';
			$items = $core->media->dir['files'];
			
			foreach ($items as $item) {
				$mediaplayer = '';
				if ($item->media_type == 'image')
				{
					$mediaplayer =
						'<a href="'.$core->blog->url.$core->url->getBase('mediaplayer').'/'.
						$item->media_id.'" title="'.__('Preview :').' '.$item->media_title.'">'.
						'<img src="'.$core->blog->getQmarkURL().
						'pf=dlManager/images/image.png" alt="'.__('Preview').'" />'.
						'</a>';
				} elseif ($item->type == 'audio/mpeg3' )
				{
					$mediaplayer = '<a href="'.$core->blog->url.$core->url->getBase('mediaplayer').'/'.
						$item->media_id.'" title="'.__('Preview :').' '.$item->media_title.'">'.
					'<img src="'.$core->blog->getQmarkURL().
					'pf=dlManager/images/music.png" alt="'.__('Preview').'" />'.
					'</a>';
				} elseif ($item->type == 'video/x-flv')
				{
					$mediaplayer = '<a href="'.$core->blog->url.$core->url->getBase('mediaplayer').'/'.
						$item->media_id.'" title="'.__('Preview :').' '.$item->media_title.'">'.
					'<img src="'.$core->blog->getQmarkURL().
					'pf=dlManager/images/film.png" alt="'.__('Preview').'" />'.
					'</a>';
				} elseif ($item->type == 'application/zip')
				{
					$mediaplayer = '<a href="'.$core->blog->url.$core->url->getBase('mediaplayer').'/'.
						$item->media_id.'" title="'.__('Preview :').' '.$item->media_title.'">'.
					'<img src="'.$core->blog->getQmarkURL().
					'pf=dlManager/images/briefcase.png" alt="'.__('Preview').'" />'.
					'</a>';
				}
				
				$items_str .= sprintf($w->item,$core->blog->url.
					$core->url->getBase('download').'/'.$item->media_id,
					$item->media_title,$item->basename,$mediaplayer);
			}
			
			if (!empty($items_str))
			{
				if ($w->files_title)
				{
					$str .= '<h3>'.html::escapeHTML($w->files_title).'</h3>';
				}
				$str .= sprintf($w->block,$items_str);
			}
		}
		unset($items);

		# output
		$header = (strlen($w->title) > 0)
			? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;

		$link = (strlen($w->link) > 0) ? '<p class="text"><a href="'.
			dlManager::pageURL().'">'.html::escapeHTML($w->link).'</a></p>' : null;

		return '<div class="dlmanager">'.$header.$str.$link.'</div>';
	}
}

?>