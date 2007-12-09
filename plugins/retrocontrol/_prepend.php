<?php
/***************************************************************\
 *  This is R�trocontr�le, a plugin for DotClear.              *
 *                                                             *
 *  Copyright (c) 2006-2007                                    *
 *  Oleksandr Syenchuk, Alain Vagner and contributors.         *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with R�trocontr�le (see COPYING.txt);        *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

# WARNING :
# R�trocontr�le is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

global $__autoload, $core;
$__autoload['dcFilterRetrocontrol'] = dirname(__FILE__).'/class.dc.filter.retrocontrol.php';
$__autoload['retrocontrol'] = dirname(__FILE__).'/class.retrocontrol.php';
$core->spamfilters[] = 'dcFilterRetrocontrol';

if ($core->blog->settings->get('rc_timeoutCheck')) {
	$core->addBehavior('coreBlogGetPosts',array('retrocontrol','adjustTrackbackURL'));
	$core->addBehavior('publicBeforeTrackbackCreate',array('retrocontrol','checkTimeout'));
	$core->url->register('trackback','trackback','^trackback/([0-9]+/[0-9a-z]+)$',array('retrocontrol','preTrackback'));
}

class rsExtPostRetrocontrol
{
	public static function getTrackbackLink(&$rs)
	{
		$ts = (int) $rs->getTS();
		$key = base_convert((time() - $ts) ^ $ts,10,36);
		$chk = substr(md5($rs->post_id.DC_MASTER_KEY.$key),1,4);
		
		return $rs->core->blog->url.$rs->core->url->getBase('trackback').'/'.$rs->post_id.'/'.$chk.$key;
	}
}
?>
