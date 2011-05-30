<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2006-2011 Pep, Olivier Mengué and contributors.
# All rights reserved. Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------


# WARNING:
# This file is encoded in iso-8859-15 because it includes some original code from
# DotClear 1 using that encoding. Switching the encoding to UTF-8 will break
# the code.


# Functions from DotClear 1.x -- BEGIN
#   inc/classes/class.blog.php
#
#   Copyright (c) 2004-2007 Olivier Meunier and contributors. All rights
#   reserved.
#

	function decodeUnicodeEntities($str)
	{
		return preg_replace_callback('/&#(\\d+);/','code2utf',$str);
	}

	function code2utf($m)
	{
		if ($m[1] < 128) {
			return chr($m[1]);
		}
		if ($m[1] < 2048) {
			return chr(($m[1] >> 6) + 192).chr(($m[1] & 63) + 128);
		}
		if ($m[1] < 65536) {
			return chr(($m[1] >> 12) + 224).chr((($m[1] >> 6) & 63) + 128).
			chr(($m[1] & 63) + 128);
		}
		if ($m[1] < 2097152) {
			return chr(($m[1] >> 18) + 240).chr((($m[1] >> 12) & 63) + 28).
			chr((($m[1] >> 6) & 63) + 128).chr(($m[1] & 63) + 128);
		}
		return '';
	}

	function removeEntities($string)
	{
		# Tableau des codes de 130 à 140 et 145 à 156
		$tags = array('‚' => '&sbquo;','ƒ' => '&fnof;','„' => '&bdquo;',
		'…' => '&hellip;','†' => '&dagger;','‡' => '&Dagger;','ˆ' => '&circ;',
		'‰' => '&permil;','Š' => '&Scaron;','‹' => '&lsaquo;','Œ' => '&OElig;',
		'‘' => '&lsquo;','’' => '&rsquo;','“' => '&ldquo;','”' => '&rdquo;',
		'•' => '&bull;','–' => '&ndash;','—' => '&mdash;','˜' => '&tilde;',
		'™' => '&trade;','š' => '&scaron;','›' => '&rsaquo;','œ' => '&oelig;',
		'Ÿ' => '&Yuml;','€' => '&euro;');
		
		$vtags = array(
		'‚' => '&#8218;','ƒ' => '&#402;','„' => '&#8222;','…' => '&#8230;',
		'†' => '&#8224;','‡' => '&#8225;','ˆ' => '&#710;','‰' => '&#8240;',
		'Š' => '&#352;','‹' => '&#8249;','Œ' => '&#338;','‘' => '&#8216;',
		'’' => '&#8217;','“' => '&#8220;','”' => '&#8221;','•' => '&#8226;',
		'–' => '&#8211;','—' => '&#8212;','˜' => '&#732;','™' => '&#8482;',
		'š' => '&#353;','›' => '&#8250;','œ' => '&#339;','Ÿ' => '&#376;',
		'€' => '&#8364;');
		
		if (true /* $dc1_blog_encoding == 'UTF-8' */) {
			$tags = get_html_translation_table(HTML_ENTITIES);
			$tags = array_flip($tags);
			array_walk($tags,create_function('&$v','$v = utf8_encode($v);'));
			$tags = array_flip($tags);
			$string = decodeUnicodeEntities($string) ;
		} else {
			$tags = array_merge($tags,get_html_translation_table(HTML_ENTITIES));
		}
		
		foreach($tags as $k => $v) {
			$ASCIItags[$k] = '&#'.ord($k).';';
		}
		
		$string = str_replace($tags,array_flip($tags),$string);
		$string = str_replace($ASCIItags,array_flip($ASCIItags),$string);
		$string = str_replace(array_values($vtags),array_keys($vtags),$string);
		
		return $string;
	}

	function str2url($str)
	{
		$str = removeEntities(utf8_decode($str));
	
		$str = strtr($str,
		"ÀÁÂÃÄÅàáâãäåÇçÒÓÔÕÖØòóôõöøÈÉÊËèéêëÌÍÎÏìíîïÙÚÛÜùúûü¾ÝÿýÑñ",
		"AAAAAAaaaaaaCcOOOOOOooooooEEEEeeeeIIIIiiiiUUUUuuuuYYyyNn");
	
		$str = str_replace('Æ','AE',$str);
		$str = str_replace('æ','ae',$str);
		$str = str_replace('¼','OE',$str);
		$str = str_replace('½','oe',$str);
		
		$str = preg_replace('/[^a-z0-9_\s\'\:\/\[\]-]/','',strtolower($str));
		
		$str = preg_replace('/[\s\'\:\/\[\]-]+/',' ',trim($str));
	
		$res = str_replace(' ','-',$str);
		
		return $res;
	}

# Functions from DotClear 1.x -- END



$url = $core->blog->url;
preg_match('|^([a-z]{3,}://)[^/]*(.*?)/?$|',$url,$matches);
$proto = $matches[1];
$path_dc1 = $path_dc2 = $matches[2];
if ($core->blog->settings->dc1redirect->dc1_old_url) {
    $path_dc1 = $core->blog->settings->dc1redirect->dc1_old_url;
}

# The old DotClear directory where rss.php/atom.php and images were located
$path_dc1_dc = $core->blog->settings->dc1redirect->dc1_old_dc_url;
if (empty($path_dc1_dc)) {
    $path_dc1_dc = dirname($core->blog->settings->system->public_url);
}


# Template for DC1 feeds PHP script replacement
$feed_tpl = <<<EOD
<?php
//
// This file exists to redirect DotClear 1.x feeds URLs for DotClear 2.x
// Created manually following plugin 'dc1redirect' instructions

\$url = '$proto'.\$_SERVER['HTTP_HOST'].'${path_dc2}/feed';

if (!empty(\$_GET['lang'])) {
	\$url .= '/'.\$_GET['lang'];
}
if (!empty(\$_GET['cat'])) {
	\$url .= '/category/'.\$_GET['cat'];
}
\$url .= '/%s';
if (!empty(\$_GET['type']) && \$_GET['type'] == 'co') {
	\$url .= '/comments';
}

if (preg_match('/cgi/',PHP_SAPI)) {
	header('Status: 301 Moved Permanently');
} else {
	header('Moved Permanently',true,301);
}
header('Location: '.\$url); exit;
?>
EOD;

# Prepare tags URL redirections
$tags = $core->meta->getMetadata(array('type' => 'tag', 'order' => 'meta_id ASC'))->rows();

$tags = array_unique(
		array_map(
			create_function('$a', "return \$a['meta_id'];"),
			$tags),
		SORT_STRING);
natcasesort($tags);

$tags_url_base = $core->url->getBase('tag').'/';

$tags_htaccess = array();
foreach ($tags as $t) {
	# URL path for DC2 (from the TagURL function in plugins/tags/_public.php)
	$t_dc2 = $tags_url_base.rawurlencode($t);
	# URL path for DC1
	# FIXME handle QueryString configs too
	$t_dc1 = 'tag/'.str2url($t);

	if ("$path_dc2/$t_dc2" != "$path_dc1/$t_dc1") {
		$tags_htaccess[] = "RedirectPermanent $path_dc1/$t_dc1 $url$t_dc2";
	} else {
		$tags_htaccess[] = "# $t => $t_dc2";
	}
}

$tags_htaccess = implode("\n", $tags_htaccess);

# Prepare categories redirections
$categories = $core->blog->getCategories(array('post_type' => 'post'))->rows();

$categories = array_unique(
		array_map(
			create_function('$c', "return \$c['cat_url'];"),
			$categories),
		SORT_STRING);
natcasesort($categories);

$categories_url_base = $core->url->getBase('category').'/';

foreach ($categories as $c) {
	# URL path for DC2 (from the TagURL function in plugins/tags/_public.php)
	$c_dc2 = $categories_url_base.$c;
	$c_dc1 = $c;

	if ("$path_dc2/$c_dc2" != "$path_dc1/$c_dc1") {
		$categories_htaccess[] = "RedirectPermanent $path_dc1/$c_dc1 $url$c_dc2";
	} else {
		$categories_htaccess[] = "# $c => $c_dc2";
	}
}

$categories_htaccess = implode("\n", $categories_htaccess);

?>
<html>
<head>
	<title><?php echo __('Dotclear 1 URLs'); ?></title>
</head>

<body>
<?php
echo '<h2>'.__('Dotclear 1 URLs').'</h2>';
echo
'<p>'.__('Some redirection can not be directly handled by the "dc1redirect" plugin. '.
'This page helps you to do the manual modifications required.').'</p>';


echo '<h3>'.__('Feeds').'</h3>';

echo
'<p>'.sprintf(__('In order to avoid broken feeds, you should replace your old '.
'Atom and RSS feeds by redirections to new ones. The following files replace '.
'your Dotclear 1 %s/atom.php and %s/rss.php files.'), $path_dc1_dc, $path_dc1_dc).'</p>';
echo
'<p>'.__('Note: The script content has been updated after dc1redirect-1.0.1 '.
'to use HTTP status 301 instead of 302. So you should update those files if '.
'you created them earlier.').'</p>';

echo
'<h4>'.'atom.php</h4>'.
'<p>'.form::textarea('atom',60,24,html::escapeHTML(sprintf($feed_tpl,'atom')),'maximal').'</p>'.
'<h4>rss.php</h4>'.
'<p>'.form::textarea('rss2',60,24,html::escapeHTML(sprintf($feed_tpl,'rss2')),'maximal').'</p>';


echo '<h3>'.__('Tags').'</h3>';

echo
'<p>'.__('With DC1 URLs for tags were in lower case, while DC2 now preserves '.
'case and special characters. You may want to preserve old links to tags by '.
'adding those lines to .htaccess:').'</p>';

echo
'<p><label class="classic">.htaccess:</label><br/>'.
form::textarea('tags_htaccess',60,50,html::escapeHTML($tags_htaccess),'maximal').'</p>';

/*
# Categories are already handled by _public.php

echo '<h3>'.__('Categories').'</h3>'.
'<p>'.__('Add those lines to .htaccess to redirect category URLs:').'</p>'.
'<p><label class="classic">.htaccess:</label><br/>'.
form::textarea('categories_htaccess',60,10,html::escapeHTML($categories_htaccess),'maximal').'</p>';
*/

# Nothing to do for languages: URLs are the same in DC2 as in DC1

# TODO images

?>
</body>
</html>
