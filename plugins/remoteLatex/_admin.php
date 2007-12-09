<?php
/***************************************************************\
 *  This is RemoteLatex, a plugin for DotClear.                *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk                                         *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with RemoteLatex (see COPYING.txt);          *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

# WARNING :
# RemoteLatex is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

$core->addBehavior('coreInitWikiPost',array('remoteLatex','coreInitWikiPost'));

class remoteLatex
{
	public static function coreInitWikiPost(&$wiki2xhtml)
	{
		$wiki2xhtml->registerFunction('macro:latex',
			array('remoteLatex','render'));
	}
	
	public static function render($texte,$args)
	{
		$texte = rawurlencode($texte);
		$texServer = 'http://math.spip.org/tex.php?';
		return '<img src="'.$texServer.$texte.'" />';
	}
}
?>
