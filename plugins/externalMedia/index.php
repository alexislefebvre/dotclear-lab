<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2007 Olivier Meunier and contributors. All rights
# reserved.
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
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$m_object = $m_title = $m_url = null;
$media_page = !empty($_POST['media_page']) ? $_POST['media_page'] : null;

$services_regs = array(
	'dailymotion' => '#^http://(www.)?dailymotion.com/(.+)#',
	'googlevideo' => '#^http://video.google.([a-z]{2,})/videoplay\?docid=(.+?)(&|$)#',
	'youtube' => '#^http://([a-z]{2,}.)?youtube.com/(.+)#'
);

if ($media_page)
{
	try
	{
		$media_service = false;
		foreach ($services_regs as $k => $v) {
			if (preg_match($v,$media_page)) {
				$media_service = $k;
				break;
			}
		}
		
		if (!$media_service) {
			throw new Exception(__('Unsupported service'));
		}
		
		$http = netHttp::initClient($media_page,$media_path);
		$http->setTimeout(5);
		$http->setUserAgent('Mozilla/5.0 (Macintosh; U; Intel Mac OS X; fr; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
		$http->get($media_path);
		
		if ($http->getStatus() != 200) {
			throw new Exception(__('Invalid page URL'));
		}
		
		$content = $http->getContent();
		
		switch ($media_service)
		{
			case 'dailymotion':
				if (preg_match('#<input\s+id="video_player_embed_code_text".+?value="(.+?)"#ms',$content,$m))
				{
					$cap = html::decodeEntities($m[1]);
					$movie;
					
					if (preg_match('#param\s+name="movie"\s+value="(.+?)"#s',$cap,$M)) {
						$movie = $M[1];
					}
					
					if (preg_match('#<br /><b><a\s+href="(.+?)">(.+?)</a>#s',$cap,$M)) {
						$m_title = html::decodeEntities($M[2]);
						$m_url = $M[1];
					}
					
					if ($movie)
					{
						$m_object =
						'<object type="application/x-shockwave-flash" data="'.$movie.'" width="400" height="316">'."\n".
						'  <param name="movie" value="'.$movie.'" />'."\n".
						'  <param name="wmode" value="transparent" />'."\n".
						'  <param name="FlashVars" value="playerMode=embedded" />'."\n".
						'  <a href="'.$movie.'">'.__('Download video').'</a>'."\n".
						'</object>';
					}
				}
				break;
			case 'googlevideo':
				if (preg_match('#docid=(.+?)(&|$)#',$media_path,$m))
				{
					$movie = 'http://video.google.com/googleplayer.swf?docid='.$m[1];
					
					if (preg_match('#<title>(.+?)</title>#si',$content,$M)) {
						$m_title = $M[1];
					}
					
					$m_object =
					'<object type="application/x-shockwave-flash" data="'.$movie.'" width="400" height="326">'."\n".
					'  <param name="movie" value="'.$movie.'" />'."\n".
					'  <param name="wmode" value="transparent" />'."\n".
					'  <a href="'.$movie.'">'.__('Download video').'</a>'."\n".
					'</object>';
				}
				break;
			case 'youtube':
				if (preg_match('#<input.+?\s+name="embed_code".+?\s+value=\'(.+?)\'#',$content,$m))
				{
					$cap = html::decodeEntities($m[1]);
					$movie = '';
					
					if (preg_match('#param\s+name="movie"\s+value="(.+?)"#s',$cap,$M)) {
						$movie = html::escapeURL($M[1]);
					}
					
					if (preg_match('#<title>Youtube\s+-\s+(.+?)</title>#si',$content,$M)) {
						$m_title = $M[1];
					}
					
					if ($movie)
					{
						$m_object =
						'<object type="application/x-shockwave-flash" data="'.$movie.'" width="425" height="350">'."\n".
						'  <param name="movie" value="'.$movie.'" />'."\n".
						'  <param name="wmode" value="transparent" />'."\n".
						'  <a href="'.$movie.'">'.__('Download video').'</a>'."\n".
						'</object>';
					}
				}
				break;
		}
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
  <title><?php echo __('External media selector') ?></title>
  <script type="text/javascript" src="index.php?pf=externalMedia/popup.js"></script>
</head>

<body>
<?php
echo '<h2>'.__('External media selector').'</h2>';

if (!$m_object)
{
	echo
	'<form action="'.$p_url.'&amp;popup=1" method="post">'.
	'<p>'.__('Please enter the URL of the page containing the video you want to include in your post.').'</p>'.
	'<p><label>'.__('Page URL:').' '.
	form::field('media_page',50,250,html::escapeHTML($media_page)).'</label></p>'.
	
	'<p><input type="submit" value="'.__('ok').'" />'.
	(is_callable(array($core,'formNonce')) ? $core->formNonce() : '').'</p>'.
	'</form>';
}
else
{
	echo
	'<div style="margin: 1em auto; text-align: center;">'.$m_object.'</div>'.
	'<form id="media-insert-form" action="" method="get">';
	
	$i_align = array(
		'none' => array(__('None'),0),
		'left' => array(__('Left'),0),
		'right' => array(__('Right'),0),
		'center' => array(__('Center'),1)
	);
	
	echo '<h3>'.__('Media alignment').'</h3>';
	echo '<p>';
	foreach ($i_align as $k => $v) {
		echo '<label class="classic">'.
		form::radio(array('alignment'),$k,$v[1]).' '.$v[0].'</label><br /> ';
	}
	echo '</p>';
	
	echo
	'<h3>'.__('Media title').'</h3>'.
	'<p><label>'.__('Title:').' '.
	form::field(array('m_title'),50,250,html::escapeHTML($m_title)).'</label></p>';
	
	echo
	'<p><a id="media-insert-cancel" href="#">'.__('Cancel').'</a> - '.
	'<strong><a id="media-insert-ok" href="#">'.__('Insert').'</a></strong>'.
	form::hidden(array('m_object'),html::escapeHTML($m_object)).
	form::hidden(array('m_url'),html::escapeHTML($m_url)).
	'</form>';
}

?>
</body>
</html>
