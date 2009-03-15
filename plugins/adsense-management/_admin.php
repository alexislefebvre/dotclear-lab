<?php
# ***** BEGIN LICENSE BLOCK *****
# Widget Adsense for DotClear.
# Copyright (c) 2007 Gerits Aurelien. All rights
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
$core->addBehavior('initWidgets',array('AdsenseBehavior','initWidgets'));
$core->auth->check('usage,contentadmin',$core->blog->id);
$core->auth->setPermissionType('admin_adsense',__('See the Widget Adsense'));
# Ajout du menu plugin
$_menu['Plugins']->addItem(__('Google Adsense'),'plugin.php?p=adsense','index.php?pf=adsense/icon.png',
		preg_match('/plugin.php\?p=adsense(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin_adsense',$core->blog->id));

class AdsenseBehavior
{
 	 public static function initWidgets(&$w)
     {
      $w->create('Adsense',__('Google Adsense'),array('AdsenseTpl','AdsenseWidgets'));
      $w->Adsense->setting('title',__('Title:'),'');
      $w->Adsense->setting('homeonly',__('Home page only'),0,'check');
	  $w->Adsense->setting('google_ad_client',__('Google ad client:(16 digits)'),'');
				#$w->Adsense->setting('google_ui_features',__('Round:'),'');
	  $w->Adsense->setting('color',__('Colors:'),'default','combo',
				array(__('Default')=>'default',__('Bord de mer')=>'borddemer',__('Ombre')=>'ombre',__('Encre')=>'encre',__('Graphite')=>'graphite',__('ClashDesign')=>'clashdesign',__('Fashion')=>'fashion',__('Yellow-Grey')=>'yellow_grey'));
	  $w->Adsense->setting('google_ui_features',__('Styles of the angles:'),'classic','combo',
				array(__('Right angles')=>'classic',__('Angles slightly rounded')=>'smallround',__('Angles very rounded')=>'round'));
	  $w->Adsense->setting('position',__('Position:'),'left','combo',
				array(__('Left')=>'left',__('Center')=>'center',__('Right')=>'right'));
	  $w->Adsense->setting('format',__('Format:'),'leaderboard','combo',
				array(__('Leaderboard (728 x 90)')=>'leaderboard',__('Banni&egrave;re (468 x 60)')=>'banniere',__('Demi-banni&egrave;re (234 x 60)')=>'demibanniere',__('Skyscraper (120 x 600)')=>'skyscraper',__('Skyscraper Large(160 x 600)')=>'skyscraperlarge',__('Banni&egrave;re verticale (120 x 240)')=>'banniereverticale',__('Grand Rectangle (336 x 280)')=>'grandrectangle',__('Rectangle moyen (300 x 250)')=>'rectanglemoyen',__('Carr&eacute; (250x250)')=>'carre',__('Petit Carr&eacute; (200x200)')=>'petitcarre',__('Petit rectangle (180 x 150)')=>'petitrectangle',__('Bouton (125 x 125)')=>'bouton'));
        }
}
?>
