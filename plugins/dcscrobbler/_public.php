<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcScrobbler, a plugin for Dotclear.
# 
# Copyright (c) 2008 Boris de Laage
# bdelaage@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# $Id: _public.php 27 2006-08-23 20:42:45Z bdelaage $
if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';
require_once(dirname(__FILE__).'/class.dc.dcscrobbler.php');

$core->url->register('dcscrobbler-images','dcscrobbler/img','^dcscrobbler/img/([a-zA-Z0-9_-]+).png',array('dcScrobblerWidget','imagesURL'));

class dcScrobblerWidget {

# Widget function
  public static function widget(&$w)
  {
    global $core;

    $core->blog->settings->setNameSpace('dcscrobbler');
    $uname = $core->blog->settings->get('dcs_username');

    if ($w->homeonly && $core->url->type != 'default') {
      return;
    }

    if ($w->title)
      $title = html::escapeHTML($w->title);
    else
      switch ($w->feed) {
      case 'recenttracks':
        $title = __('Recent Tracks');
        break;
      case 'weeklyartistchart':
        $title = __('Weekly Top Artists');
        break;
      case 'weeklytrackchart':
        $title = __('Weekly Top Tracks');
        break;
      case 'topartists':
        $title = __('Top Artists');
        break;
      case 'toptracks':
        $title = __('Top Tracks');
        break;
      }

    $out =
      '<div class="dcscrobbler">'.
      '<h2>'.$title.'</h2>';

    if (!$uname) {
        $out .= '<em>' . __('dcScrobbler is not configured') . '</em>';
      }
    else {

      $out .= '<ul>' .
        self::displayData($w->feed, $w->count, $w->playcount).
        '</ul>';

      if ($w->showlogo) {
        $out .=
          '<p>'.
          '<a href="http://www.last.fm/user/' . $uname .'">'.
          '<img src="' .
          $core->blog->url.$core->url->getBase('dcscrobbler-images').
          '/lastfm_button.png" alt="' . __('Last.fm profile for') . ' ' . $uname .'" title="' . __('Last.fm profile for') . ' ' . $uname .'" />'.
          '</a></p>';
      }
    }

    $out .= '</div>';

    return $out;
  }

  private static function displayData($feed, $count=0, $show_playcount=true)
  {

    $xml = dcScrobbler::getData($feed);

    if (!$xml)
      return '<li><em>'.__('No data').'</em></li>';

    $n = 0;
    $out = '';

    if (preg_match('*track*', $feed)) {
      
      if (sizeof($xml->track) == 0)
        return '<li><em>'.__('No recent tracks').'</em></li>';
      
      $element = '<li><a href="%s"><span class="artist">%s</span> - <span class="title">%s</span></a></li>';
      foreach ($xml->track as $track) {
        $out .= sprintf($element, $track->url, html::escapeHTML($track->artist),
                        html::escapeHTML($track->name));
        if (++$n == $count)
          break;
      }
    }
    else {
      
      if (sizeof($xml->artist) == 0)
        return '<li><em>'.__('No data').'</em></li>';
    
      $element = '<li><a href="%s">%s</a> <span class="playcount">%d</span></li>';
      foreach ($xml->artist as $artist) {
        $out .= sprintf($element, $artist->url, html::escapeHTML($artist->name),
                        html::escapeHTML($artist->playcount));
        if (++$n == $count)
          break;
      }
    }

    return $out;
  }

  public static function imagesURL($arg)
  {
    $file = dirname(__FILE__).'/'.$arg.'.png';
    if (!file_exists($file)) {
      http::head(404,'Not Found');
      exit;
    }
		
    http::cache(array_merge(array($file),get_included_files()));
		
    header('Content-Type: image/png');
    readfile($file);
    exit;
  }
}
?>