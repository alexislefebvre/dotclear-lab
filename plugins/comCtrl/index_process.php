<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear 'comCtrl' plugin.
# by Laurent Alacoque <laureck@users.sourceforge.net>
# It was quite borrowed, adapted and inspired by the code from "comments_action.php" from dotclear
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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

// actual processing based on post data
// update our comment ranking table if needed
// update regular comment status if needed
// redirect to previous page

/********************************************
* Where to redirect after ?
*********************************************/

/* redirection page, after update */
if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false)
{
	/* we were asked to go to $_POST['redir'] and its not outside this site*/
	$redir = $_POST['redir'];
}
else
{
	/* by default, we redirect to our plugin page*/
	$redir =
		'comments.php?p=comCtrl&type='.$_POST['type'].
		'&author='.$_POST['author'].
		'&status='.$_POST['status'].
		'&sortby='.$_POST['sortby'].
		'&ip='.$_POST['ip'].
		'&order='.$_POST['order'].
		'&page='.$_POST['page'].
		'&nb='.(integer) $_POST['nb'];
}


/********************************************
* Ranking update
*********************************************/

/* If we have some ranking and some comments... */
if (isset($_POST['ranking']) && !empty($_POST['comments'])){
	$comments= $_POST['comments'];
	$ranking= $_POST['ranking'];
	/* if the ranking is nothing, do nothing */
	if(preg_match('/^nothing$/',$ranking)){
		// we do not want to update ranking
	}
	else {
		/* ranking is not 'nothing' */
		/* insert or update each comment */
		foreach ($comments as $k => $v){
			// $v contains the comment id
			//get the current ranking associated to this comment, if it exists
			$strReq ='SELECT comment_ranking FROM '.$core->blog->prefix . 'comctrl '.'WHERE comment_id =\''. $v . '\';';
			$myrs = $core->con->select($strReq);
			// if this comment does not have a ranking, count will be 0, we should INSERT
			if($myrs->count()==0){
				// no ranking for this comment, insert
				$strReq= 'INSERT INTO '.$core->blog->prefix . 'comctrl'.
				'(comment_id,comment_ranking) '.
				'VALUES('.((int) $v) .','.((int) $ranking) .');';
				// execute query
				 if(!$core->con->execute($strReq)){
				 	// something went wrong, inform user
				 	$core->error->add(__("Could insert new comment ranking ($ranking) for comment #$v"));
				 }
			}
			else{
				// ranking already exists for this comment, update
				$strReq= 'UPDATE '.$core->blog->prefix . 'comctrl '.
				'SET comment_ranking = '. ((int) $ranking).
				' WHERE comment_id = '. ((int) $v) .';';
				// execute query
				 if(!$core->con->execute($strReq)){
				 	//something went wrong, inform user
				 	$core->error->add(__("Could not update comment #$v to ranking $ranking"));
				 }
			}//insert or update
		}//comment for each loop	
	}//nothing or ranking
}//Do ranking


/********************************************
* Should we also change comment regular online state ?
*********************************************/
/* the following was stolen from comment_actions.php */
/* I know I should not do this since there are hardcoded states and it's not my table
/* I tried to require(comment_actions.php) but the 'require(prepend.php)' at the top of it makes it a fatal error (would have worked if it were require_once)
/* I tried to POST data to comment_actions instead (but this would've required to rely on libcurl)
/* Also tried http::redirect with no success
/* Please be advised that the following code will break if dotclear change its hardcoded status/meaning association
*/
if (!empty($_POST['action']) && !empty($_POST['comments']))
{
	if(preg_match('/^nothing$/',$_POST['action'])){
		// we do not want to update comment regular state
		// redirect to our plugin
		http::redirect($redir);
	}
	else {
		// we want to update comment regular state
		// call comments_actions.php => I found this impossible so I just copy pasted
		// everything relevant from comments_actions.php
		$comments=$_POST['comments'];
		$action=$_POST['action'];
		$params=array();
		$params['sql'] = 'AND C.comment_id IN('.implode(',',$comments).') ';
		$params['no_content'] = true;
	
		$co = $core->blog->getComments($params);
	
		if (preg_match('/^(publish|unpublish|pending|junk)$/',$action))
		{
			switch ($action) {
				case 'unpublish' : $status = 0; break;
				case 'pending' : $status = -1; break;
				case 'junk' : $status = -2; break;
				default : $status = 1; break;
			}
		
			while ($co->fetch())
			{
				try {
					$core->blog->updCommentStatus($co->comment_id,$status);
				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			}
		
			if (!$core->error->flag()) {
				http::redirect($redir);
			}
		}
		elseif ($action == 'delete')
		{
			while ($co->fetch())
			{
				try {
					$core->blog->delComment($co->comment_id);
				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			}
		
			if (!$core->error->flag()) {
				http::redirect($redir);
			}
		}
	}
}
else {
	// nothing to do with regular state
	// redirect to our plugin
	http::redirect($redir);
}
//phpinfo(INFO_VARIABLES);
?>