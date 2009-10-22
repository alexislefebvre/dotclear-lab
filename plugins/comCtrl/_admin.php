<?php
# ***** BEGIN LICENSE BLOCK *****
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

require (dirname(__FILE__).'/class.comctrl.php');
/* register to be in the plugin list */
$_menu['Plugins']->addItem(__('comCtrl'),'plugin.php?p=comCtrl','index.php?pf=comCtrl/comctrl.png',
		preg_match('/plugin.php\?p=comCtrl(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));

?>