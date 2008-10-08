<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'pre2ol', a plugin for Dotclear 2                  *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Carnaval' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('adminBlogPreferencesHeaders',array('pre2olBehaviors','adminBlogPreferencesHeaders'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',	array('pre2olBehaviors','adminBeforeBlogSettingsUpdate'));
$core->addBehavior('adminBlogPreferencesForm',array('pre2olBehaviors','adminBlogPreferencesForm'));

class pre2olBehaviors
{
	public static function adjustColor($c)
	{
		if ($c === '') {
			return '';
		}
		
		$c = strtoupper($c);
		
		if (preg_match('/^[A-F0-9]{3,6}$/',$c)) {
			$c = '#'.$c;
		}
		
		if (preg_match('/^#[A-F0-9]{6}$/',$c)) {
			return $c;
		}
		
		if (preg_match('/^#[A-F0-9]{3,}$/',$c)) {
			return '#'.substr($c,1,1).substr($c,1,1).substr($c,2,1).substr($c,2,1).substr($c,3,1).substr($c,3,1);
		}
		
		return '';
	}

	public static function adminBlogPreferencesHeaders()
	{
		return dcPage::jsColorPicker();
	}

	public static function adminBeforeBlogSettingsUpdate(&$settings)
	{
		global $core;

		$active = $core->blog->settings->pre2ol_enabled;

		$settings->setNameSpace('pre2ol');
		$settings->put('pre2ol_enabled',!empty($_POST['pre2ol_enabled']),'boolean');
		$settings->put('pre2ol_bgcolor1',self::adjustColor($_POST['pre2ol_bgcolor1']),'string');
		$settings->put('pre2ol_bgcolor2',self::adjustColor($_POST['pre2ol_bgcolor2']),'string');
		$settings->put('pre2ol_color',self::adjustColor($_POST['pre2ol_color']),'string');

		# inspirated from lightbox/admin.php
		$settings->setNameSpace('system');

		# only update the blog if the setting have changed
		if ($active == empty($_POST['pre2ol_enabled']))
		{
			$core->blog->triggerBlog();
		}
	}

	public static function adminBlogPreferencesForm(&$core,&$settings)
	{
		echo
		'<fieldset><legend>'.__('Code display').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('pre2ol_enabled','1',$settings->pre2ol_enabled).
		__('Enable functionnality pre2ol').'</label></p>'.
		'<p class="field"><label class="classic" for="pre2ol_bgcolor1">'.
		__('Background color').
		'</label> '.
		form::field('pre2ol_bgcolor1',7,7,
		$core->blog->settings->pre2ol_bgcolor1,'colorpicker').
		'</p>'.
		'<p class="field"><label class="classic" for="pre2ol_bgcolor1">'.
		__('Alternate background color').
		'</label> '.
		form::field('pre2ol_bgcolor2',7,7,
		$core->blog->settings->pre2ol_bgcolor2,'colorpicker').
		'</p>'.
		'<p class="field"><label class="classic" for="pre2ol_color2">'.
		__('Text color').
		'</label> '.
		form::field('pre2ol_color',7,7,
		$core->blog->settings->pre2ol_color,'colorpicker').
		'</p>'.
		'</fieldset>';
	}
}
?>
