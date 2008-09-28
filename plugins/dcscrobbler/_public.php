<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of dcScrobbler for DotClear.
# Copyright (c) 2005-2006 Boris de Laage. All rights reserved.
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
    $uname = $core->blog->settings->get('username');

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
          '<img id="dcscrobblerlogo" src="' .
          $core->blog->url.$core->url->getBase('dcscrobbler-images').
          '/lastfm_button.png" alt="Last.fm" />'.
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
      
      $element = '<li><a href="%s">%s - %s</a></li>';
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
    
      $element = '<li><a href="%s">%s</a> <em>%d</em></li>';
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
