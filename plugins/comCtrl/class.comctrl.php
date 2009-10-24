<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear 'comCtrl' plugin.
# by Laurent Alacoque <laureck@users.sourceforge.net>
# It was quite greatly inspired by the code from "mymeta" plugin by Bruno Hondelatte,  and contributors
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

class comCtrl
{
	public function getRanking($comment_id){
		global $core;
		$strReq='SELECT comment_ranking FROM '.
			$core->blog->prefix . 'comctrl '.
			'WHERE comment_id =\''. ((int) $comment_id) . '\';';
			$myrs = $core->con->select($strReq);
			$ranking=$myrs->comment_ranking;
			return $ranking;
	}
	
	public function getAKAList($comment_ip,$as_array=false){
		global $core;
		$strReq='SELECT DISTINCT lower(comment_author) FROM '.
			$core->blog->prefix . 'comment '.
			'WHERE comment_ip =\''. $comment_ip . '\';';
			$myrs = $core->con->select($strReq);
			if ($as_array)	return ($myrs->rows());
			else {
				$result= array();
				foreach ($myrs->rows() as $k => $v){
					$result[]=$v[0];
				}
				return implode(', ',$result);
			}
		
	}
	public function getIPWhithMultipleAuthors($nb_authors=1){
		global $core;
		$result=array();
		if (!is_numeric($nb_authors)) return array();
			$strReq='SELECT comment_ip, count(r.*) FROM ( SELECT DISTINCT comment_ip,'.
			' lower(comment_author) FROM '.
			$core->blog->prefix.'comment) as r GROUP BY comment_ip'.
			' Having count(r.*) >\''.$nb_authors.'\' ORDER BY count(r.*) DESC;';
			$myrs = $core->con->select($strReq);

			return ($myrs->rows());
	}
	
	public function getIPCommentCount($nb_authors=1){
		global $core;
			$strReq='SELECT comment_ip,count(*) as comment_count '.
			'FROM '.$core->blog->prefix.'comment GROUP BY '.
			'comment_ip HAVING count(*) > \'1\' '.
			'ORDER BY count(*) DESC;';
			$myrs = $core->con->select($strReq);

			return ($myrs->rows());
	}
	
		
	public static function tplRanking($arg){
		return(
		'<?php '.
		'$myCcRanking=comCtrl::getRanking($_ctx->comments->comment_id); '.
		'if (is_numeric($myCcRanking)) echo("'.html::escapeHTML($arg['prefix']).'" . $myCcRanking ."'.html::escapeHTML($arg['postfix']).'");'.
		'?>'
		);

	}
	public static function tplIPHash()
	{
		return('<?php '.
		'$size=16; '.
		'$atr=$_ctx->comments->comment_ip; '.
		'$crc=abs(crc32($atr)); '.
		'$color=sprintf("%02x%02x%02x",$crc & 0xFF, ($crc>>8) & 0xFF, ($crc>>16) & 0xFF); '.
		'echo("$color"); ?>');
	}

	public static function tplAKAList($arg){
		return(
		'<?php '.
		'$myCcList=comCtrl::getAKAList($_ctx->comments->comment_ip); '.
		'if ($myCcList) echo("'.html::escapeHTML($arg['prefix']).'" . $myCcList ."'.html::escapeHTML($arg['postfix']).'");'.
		'?>'
		);

	}
	public static function tplRankingIf($attr,$content)
	{
		// if no condition nor value : check if has Ranking
		if (!isset($attr['condition']) && (!isset($attr['value']))) $attr['condition'] = 'hasRanking';
		// default condition when there is a value but no condition
		if (!isset($attr['condition']) && (isset($attr['value']))) $attr['condition']='equal';
		// 0 is value by default
		if (!isset($attr['value'])) $attr['value']=0;
		// check value consistency
		if (!is_numeric($attr['value'])) return "";
		else {$val=(int) $attr['value'];}
		
		$preop="";
		switch($attr['condition']){
			case 'equal': $operator='=='; break;
			case 'above': $operator='>'; break;
			case 'below': $operator='<'; break;
			case 'hasRanking': $preop="is_numeric"; $op="";$val=""; break;
			default: return "";
		}
		if (isset($attr['negate'])) {
			$preop='!('.$preop;
			$val=$val.")";
		}
		return(
			'<?php '.
			'if('.$preop.'(comCtrl::getRanking($_ctx->comments->comment_id)) '.
			"$operator $val".'){ ?>'.
			$content.
			' <?php } ?>'
		);
	}

}

/* this is borrowed and adapted from dotclear core */
/* This code is responsible from producing the comment table on plugin page */
class adminCommentCtrlList extends adminGenericList
{
	// defines the table layout
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No comment').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
		$sort_by_date=
		'plugin.php?n='.$nb_per_page.
		'&amp;p=comCtrl'.
		'&amp;status='.$status.
		'&amp;sortby=comment_dt'.
		'&amp;order=desc';

		$sort_by_post=
		'plugin.php?n='.$nb_per_page.
		'&amp;p=comCtrl'.
		'&amp;status='.$status.
		'&amp;sortby=post_title'.
		'&amp;order=desc';

		$sort_by_status=
		'plugin.php?n='.$nb_per_page.
		'&amp;p=comCtrl'.
		'&amp;status='.$status.
		'&amp;sortby=comment_status'.
		'&amp;order=desc';

		$sort_by_author=
		'plugin.php?n='.$nb_per_page.
		'&amp;p=comCtrl'.
		'&amp;status='.$status.
		'&amp;sortby=comment_author'.
		'&amp;order=desc';
			
			$html_block =
			'<table><tr>'.
			'<th><a href="'.$sort_by_date.'">'.__('Date').'</a></th>'.
			'<th colspan="2"><a href="'.$sort_by_status.'">'.__('Status').'</a></th>'.
			'<th><a href="'.$sort_by_author.'">'.__('Author').'</a></th>'.
			'<th><a href="'.$sort_by_post.'">'.__('Post').' / '.__('Comment').'</a></th>'.
			'<th>'.__('Type').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->commentLine();
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	// defines the table line
	private function commentLine()
	{
		global $author, $status, $sortby, $order, $nb_per_page;
			$strReq =
			'SELECT comment_ranking FROM '.
			$this->core->blog->prefix . 'comctrl '.
			'WHERE comment_id =\''. $this->rs->comment_id . '\';';
			$myrs = $this->core->con->select($strReq);
			$ranking=$myrs->comment_ranking;

			/* choose image rating */
			if(!isset($ranking)) {$imfile="norating.png";}
			else
			{
				switch ($ranking)
				{
					case -2: $imfile="rating-2.png"; break;
					case -1: $imfile="rating-1.png"; break;
					case  0: $imfile="rating0.png";  break;
					case 1: $imfile="rating1.png";   break;
					case 2: $imfile="rating2.png";   break;
				}
			}
			if(isset($imfile)) {
				$imrating='<img width="12" height="12" src="index.php?pf=comCtrl/'.$imfile .'" alt="" />';
			}

		$author_url =
		'plugin.php?n='.$nb_per_page.
		'&amp;p=comCtrl'.
		'&amp;status='.$status.
		'&amp;sortby='.$sortby.
		'&amp;order='.$order.
		'&amp;author='.rawurlencode($this->rs->comment_author);
		
		$ip_url=
		'plugin.php?n='.$nb_per_page.
		'&amp;p=comCtrl'.
		'&amp;status='.$status.
		'&amp;sortby='.$sortby.
		'&amp;order='.$order.
		'&amp;ip='.rawurlencode($this->rs->comment_ip);
		
		if(!empty($_GET['ip'])) {$byip=true;}
		else{$byip=false;}
		
		$post_url = $this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id);
		$post_read_url=$this->rs->getPostURL();
		$comment_url = 'comment.php?id='.$this->rs->comment_id;
		
		$comment_dt =
		dt::dt2str($this->core->blog->settings->date_format.' - '.
		$this->core->blog->settings->time_format,$this->rs->comment_dt);
		
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->comment_status) {
			case 1:
				$img_status = sprintf($img,__('published'),'check-on.png');
				break;
			case 0:
				$img_status = sprintf($img,__('unpublished'),'check-off.png');
				break;
			case -1:
				$img_status = sprintf($img,__('pending'),'check-wrn.png');
				break;
			case -2:
				$img_status = sprintf($img,__('junk'),'junk.png');
				break;
		}
		
		$comment_author = html::escapeHTML($this->rs->comment_author);
		if (mb_strlen($comment_author) > 20) {
			$comment_author = mb_strcut($comment_author,0,17).'...';
		}
		
		$res = '<tr class="line'.($this->rs->comment_status != 1 ? ' offline' : '').(isset($ranking) ? " ccRank$ranking":'').'"'.
		' id="c'.$this->rs->comment_id.'">';
		
		$res .=
		'<td class="nowrap"><small>'.dt::dt2str(__('%d/%m %H:%M'),$this->rs->comment_dt).'</small></td>'.
		'<td class="nowrap status">'. //print_r($this->rs) .
		form::checkbox(array('comments[]'),$this->rs->comment_id,$byip?'checked':'','','',0).'</td>'.
		'<td class="nowrap status">'.$img_status.'<br />'.$imrating.'</td>'.
		'<td class="nowrap"><a href="'.$author_url.'">'.$comment_author.'</a><br /><small>'.
		'<a href="'.$ip_url.'">'.html::escapeHTML($this->rs->comment_ip).'</a><br />'.
		'<a href="mailto:'.$this->rs->comment_email .'">'.
		$this->rs->comment_email .
		'</a>'.
		'</small>'.
		'</td>'.

		'<td class="maximal"><strong><a href="'.$post_read_url.'">'.html::escapeHTML($this->rs->post_title).'</a></strong> '.
		($this->rs->post_type != 'post' ? ' ('.html::escapeHTML($this->rs->post_type).') ' : '').
		'<small><a href="'.$post_url.'">'.__('edit').'</a></small>'.
		'<div class="comContent">'.$this->rs->comment_content.
		' <small><a href="'.$comment_url.'">'.__('edit').'</a></small>'.
		'</div>'.
		'</td>'.
		'<td class="nowrap">'.($this->rs->comment_trackback ? __('trackback') : __('comment')).'</td>';
		
		$res .= '</tr>';
		
		return $res;
	}
}

?>