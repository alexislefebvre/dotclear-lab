<?php
if (!defined('DC_CONTEXT_ADMIN')) { return; }
$core->blog->settings->setNamespace('twitterpost');

if (!$core->blog->settings->get(
		'twitterpost_status'
	))
{
	$core->blog->settings->put(
		'twitterpost_status',
		__('default status'),
		'string',
		__('Twitter status'),
		true,
		false
	);
}