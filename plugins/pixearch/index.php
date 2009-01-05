<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2007 Olivier Meunier and contributors.
# All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
# This file is part of Pixearch
# Hadrien Lanneau http://www.alti.info/
#
global $core;

include_once dirname(__FILE__).'/inc/Pixearch.class.php';

//------------------------------------------------------------------------------
// Global datas
//------------------------------------------------------------------------------
$core->blog->settings->setNamespace('pixearch');

//------------------------------------------------------------------------------
// Set config
//------------------------------------------------------------------------------
if (!empty($_POST['pixearch_default_search']))
{
	$flickrApiKey		= $_POST['pixearch_flickr_apikey'];
	$photobucketApiKey	= $_POST['pixearch_photobucket_apikey'];
	$photobucketSecret	= $_POST['pixearch_photobucket_secret'];
	$defaultSearch		= $_POST['pixearch_default_search'];
	
	try
	{
		$core->blog->settings->put(
			'pixearch_flickr_apikey',
			$flickrApiKey,
			'string',
			__('Flickr API Key'),
			true,
			false
		);
		$core->blog->settings->put(
			'pixearch_photobucket_apikey',
			$photobucketApiKey,
			'string',
			__('PhotoBucket API Key'),
			true,
			false
		);
		$core->blog->settings->put(
			'pixearch_photobucket_secret',
			$photobucketSecret,
			'string',
			__('PhotoBucket Secret Key'),
			true,
			false
		);
		$core->blog->settings->put(
			'pixearch_default_search',
			$defaultSearch,
			'string',
			__('Default search engine'),
			true,
			false
		);
		$msg = __('Configuration updated');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

//------------------------------------------------------------------------------
// Get config
//------------------------------------------------------------------------------
if (is_null($flickrApiKey))
{
	try
	{
		$flickrApiKey = $core->blog->settings->get(
			'pixearch_flickr_apikey'
		);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
if (is_null($photobucketApiKey))
{
	try
	{
		$photobucketApiKey = $core->blog->settings->get(
			'pixearch_photobucket_apikey'
		);
		$photobucketSecret = $core->blog->settings->get(
			'pixearch_photobucket_secret'
		);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
if (is_null($defaultSearch))
{
	try
	{
		$defaultSearch = $core->blog->settings->get(
			'pixearch_default_search'
		);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
$deviantartApiKey = true;
$picasaApiKey = true;


$query = !empty($_POST['pixearch_query']) ?
	$_POST['pixearch_query'] :
	$_GET['query'];
//------------------------------------------------------------------------------
// Common headers
//------------------------------------------------------------------------------
?>
<html>
<head>
	<title><?php echo __('Pixearch') ?></title>
	<script type="text/javascript" src="index.php?pf=pixearch/js/popup.js"></script>
	<link rel="stylesheet" type="text/css" href="index.php?pf=pixearch/styles/style.css" />
	
	<?php if ($query) { ?>
	<!-- Coverflow -->
	<script type="text/javascript" src="index.php?pf=pixearch/js/coverflow.js"></script>
	<?php } ?>
</head>

<body>
<?php
//------------------------------------------------------------------------------
// Popup Window
//------------------------------------------------------------------------------

if (!empty($_GET['popup']) and
	!isset($_GET['config']))
{
	// Popup
	echo dcPage::jsPageTabs(
			(
				empty($_POST['saveconfig']) and
				!empty($_GET['picture_id'])
			) ?
		'insert' :
		'search'
	);
	$source = $_POST['source'];
	if (empty($source))
	{
		$source = $_GET['source'];
	}
	if (empty($source))
	{
		$source = $defaultSearch;
	}
//------------------------------------------------------------------------------
// Search form
//------------------------------------------------------------------------------
	include(dirname(__FILE__) . '/tpl/popup_search.html');

//------------------------------------------------------------------------------
// Display selected image
//------------------------------------------------------------------------------

	if (!empty($_GET['picture_id']))
	{
		switch ($source)
		{
			case 'photobucket':
				$pictureClass = 'PxSPhotoBucketPicture';
				break;
			case 'deviantart':
				$pictureClass = 'PxSDeviantArtPicture';
				break;
			case 'picasa':
				$pictureClass = 'PxSPicasaPicture';
				break;
			case 'flickr':
			default:
				$pictureClass = 'PxSFlickrPicture';
		}
		
		include(dirname(__FILE__) . '/tpl/popup_insert.html');
	}
?>
</form>
<?php
}

//------------------------------------------------------------------------------
// Admin configuration
//------------------------------------------------------------------------------

else
{
	include(dirname(__FILE__) . '/tpl/config.html');
}

//------------------------------------------------------------------------------
// Footer
//------------------------------------------------------------------------------

include(dirname(__FILE__) . '/tpl/footer.html');
?>
	
	
</body>
</html>