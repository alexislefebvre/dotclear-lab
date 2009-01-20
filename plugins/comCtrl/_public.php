<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear 'comCtrl' plugin 
# by Laurent Alacoque <laureck@users.sourceforge.net>
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

// {{tpl:ccRanking prefix="rank" postfix=".png"}} => nothing if no ranking, 'rank<#>.png' with <#> in (-2,-1,0,1,2)
$core->tpl->addValue('ccRanking',array('comCtrl','tplRanking'));
// {{tpl:ccIPHash}} => 3 bytes Hex Hash string of the comment poster IP (can be used in css color field)
// example : <dd class="comment_content" style="border-color:#{{tpl:ccIPHash}};"> will show the comment
// surrounded by a colored border that will be unique for the poster machine IP 
$core->tpl->addValue('ccIPHash',array('comCtrl','tplIPHash'));
//example : {{tpl:CommentAuthor}}{{tpl:AKAList prefix=" wich seem to share the following identities : " postfix=" (posted from same machine)"}}
// example : suppose the same machine posts comment with three author_name: maurice, jacques and hubert
// the previous code will substitute to "hubert wich seem to share the following identities : maurice, jacques, hubert (posted from same machine)"
$core->tpl->addValue('ccAKAList',array('comCtrl','tplAKAList'));

// the following is used to act differently based on Ranking
// ________ Check for ranking existence ______
// <tpl:ccRankingIf>This will be showed if this comment is ranked</tpl:ccRankingIf>
// <tpl:ccRankingIf condition="hasRanking">same as above</tpl:ccRankingIf>
// ________ Check for specific ranking ________
// <tpl:ccRankingIf value="1">This will be showed only if comment is ranked and ranking = 1</tpl:ccRankingIf>
// <tpl:ccRankingIf condition="equal" value="1">This will be showed only if comment is ranked and ranking = 1</tpl:ccRankingIf>
// ________ Check for ranking threshold ________
// <tpl:ccRankingIf condition="above" value="0">This will be showed if comment is ranked and ranking is strictly greater than 0 (1 or 2)</tpl:ccRankingIf>
// <tpl:ccRankingIf condition="below" value="0" negate="true">Shows up if ranking does not exist (mind the gotcha) OR is not stricly less than 0 (i.e. is >=0)
$core->tpl->addBlock('ccRankingIf',array('comCtrl','tplRankingIf'));

?>
