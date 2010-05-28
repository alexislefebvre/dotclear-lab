<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# This file manage admin link creation of kUtRL (called from index.php)

if (!defined('DC_CONTEXT_ADMIN')){return;}

$s_admin_service = (string) $s->kutrl_admin_service;
$s_limit_to_blog = (boolean) $s->kutrl_limit_to_blog;

if (isset($core->kutrlServices[$s_admin_service])) {
	$kut = new $core->kutrlServices[$s_admin_service]($core,$s_limit_to_blog);
}

# Create a new link
if ($action == 'createlink') {

	try {
		if (!isset($core->kutrlServices[$s_admin_service]))
			throw new Exception('Unknow service');

		$url = trim($core->con->escape($_POST['str']));
		$hash = empty($_POST['custom']) ? null : $_POST['custom'];

		if (empty($url))
			throw new Exception(__('There is nothing to shorten.'));

		if (!$kut->testService())
			throw new Exception(__('Service is not well configured.'));

		if (null !== $hash && !$kut->allow_customized_hash)
			throw new Exception(__('This service does not allowed custom hash.'));

		if (!$kut->isValidUrl($url))
			throw new Exception(__('This link is not a valid URL.'));

		if (!$kut->isLongerUrl($url))
			throw new Exception(__('This link is too short.'));

		if (!$kut->isProtocolUrl($url))
			throw new Exception(__('This type of link is not allowed.'));

		if ($s_limit_to_blog && !$kut->isBlogUrl($url))
			throw new Exception(__('Short links are limited to this blog URL.'));

		if ($kut->isServiceUrl($url))
			throw new Exception(__('This link is already a short link.'));

		if (null !== $hash && false !== ($rs = $kut->isKnowHash($hash)))
			throw new Exception(__('This custom short url is already taken.'));

		if (false !== ($rs = $kut->isKnowUrl($url)))
		{
			$url = $rs->url;
			$new_url = $kut->url_base.$rs->hash;
			$msg = 
			'<p class="message">'.
			sprintf(__('Short link for %s is %s'),
				'<strong>'.html::escapeHTML($url).'</strong>',
				'<a href="'.$new_url.'">'.$new_url.'</a>'
			).'</p>';
		}
		else
		{
			if (false === ($rs = $kut->hash($url,$hash)))
			{
				if ($kut->error->flag()) {
					throw new Exception($kut->error->toHTML());
				}
				throw new Exception(__('Failed to create short link. This could be caused by a service failure.'));
			}
			else
			{
				$url = $rs->url;
				$new_url = $kut->url_base.$rs->hash;
				$msg = 
				'<p class="message">'.
				sprintf(__('Short link for %s is %s'),
					'<strong>'.html::escapeHTML($url).'</strong>',
					'<a href="'.$new_url.'">'.$new_url.'</a>'
				).'</p>';

				# Send new url by libDcTwitter
				if ($s->kutrl_twit_onadmin) {
					$twit = kutrlLibDcTwitter::getMessage('kUtRL');
					$twit = str_replace(
						array('%L','%B','%U'),
						array($new_url,$core->blog->name,$core->auth->getInfo('user_cn'))
						,$twit
					);
					kutrlLibDcTwitter::sendMessage('kUtRL',$twit);
				}
			}
		}
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

echo '
<html>
<head><title>kUtRL, '.__('Links shortener').'</title>'.$header.'</head>
<body>
<h2>kUtRL'.
' &rsaquo; <a href="'.$p_url.'&amp;part=links">'.__('Links').'</a>'.
' &rsaquo; '.__('New link').
'</h2>'.$msg;

if (!isset($core->kutrlServices[$s_admin_service])) {

	echo '<p>'.__('You must set an admin service.').'</p>';
}
else
{
	echo '
	<form id="create-link" method="post" action="'.$p_url.'">

	<h3>'.sprintf(__('Shorten link using service "%s"'),$kut->name).'</h3>
	<p class="classic"><label for="str">'.__('Long link:').
	form::field('str',100,255,'').'</label></p>';

	if ($kut->allow_customized_hash) {

		echo
		'<p class="classic"><label for="custom">'.
		__('Custom short link:').
		form::field('custom',50,32,'').'</label></p>'.
		'<p class="form-note">'.__('Only if you want a custom short link.').'</p>';

		if ($s_admin_service == 'local') {
			echo '<p class="form-note">'.
			__('You can use "bob!!" if you want a semi-custom link, it starts with "bob" and "!!" will be replaced by an increment value.').
			'</p>';
		}
	}

	echo '
	<div class="clear">
	<p><input type="submit" name="save" value="'.__('save').'" />'.
	$core->formNonce().
	form::hidden(array('p'),'kUtRL').
	form::hidden(array('part'),'link').
	form::hidden(array('action'),'createlink').'
	</p></div>
	</form>';
}
dcPage::helpBlock('kUtRL');
echo $footer.'</body></html>';
?>