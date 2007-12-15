<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Live Counter', a plugin for Dotclear 2            *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Live Counter' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

class connectedCounter
{
	private $file = null;
	private $data = array();
	private $timeout = 5;
	private $userid = null;
	
	public $result = array();
	private $newdata = '';
	public $counter = 0;
	
	/**
	Class constructor
	
	@param	file		<b>string</b>		Storage data file name
	@param	timeout	<b>integer</b>		Single connection time (min)
	@return	<b>boolean</b> true if specified file is writable, false otherwise
	*/
	public function __construct($file,$timeout=5)
	{
		# Creating data file if it doesn't exist
		if (!( is_writable($file) || (@touch($file) && is_writable($file)) )) {
			return false;
		}

		$this->file = $file;
		$this->data = file($file);
		$this->timeout = $timeout*60;
		$this->u_id = http::browserUID('liveCounter');
		$this->u_name = $this->u_site = $this->u_info = '';
		
		if (!empty($_COOKIE['comment_info'])) {
			$c_cookie = @unserialize($_COOKIE['comment_info']);
			if (!empty($c_cookie['site'])) {
				$this->u_site = $c_cookie['site'];
			}
			if (!empty($c_cookie['name'])) {
				$this->u_name = $c_cookie['name'];
				$this->u_info = base64_encode(serialize(array(
					'name'=>$this->u_name,
					'site'=>$this->u_site,
					'flag'=>'visitor')));
			}
		}
		
		return true;
	}
	
	/**
	Count connected visitors
	
	@return	<b>integer</b> Number of connected visitors, or false on fail
	*/
	public function count()
	{
		if ($this->counter) {
			return $this->counter;
		}
		
		$c_ts = time();
		$r_ts = $c_ts - $this->timeout;
		
		$c = 1;
		foreach ($this->data as $k=>$v)
		{
			list($u_id,$time,$u_info) = explode(',',$v);
			
			if ($time < $r_ts || $u_id == $this->u_id) {
				continue;
			}
			
			if ($u_info && ($res = @unserialize(base64_decode($u_info))) && !empty($res['name'])) {
				$this->result[] = array(
					'name'=>$res['name'],
					'site'=>(!empty($res['site']) ? $res['site'] : ''),
					'flag'=>(!empty($res['flag']) ? $res['flag'] : 'visitor'));
			}
			$this->newdata .= $this->data[$k]; $c++;
		}
		if ($this->u_name) {
			$this->result[] = array(
				'name'=>$this->u_name,
				'site'=>(!empty($this->u_site) ? $this->u_site : ''),
				'flag'=>(!empty($this->u_flag) ? $this->u_flag : 'visitor'));
		}
		array_reverse($this->result);
		$this->newdata .= $this->u_id.','.$c_ts.','.$this->u_info."\n";
		
		return $this->counter = $c;
	}
	
	/**
	Write new data to file
	
	@return	<b>boolean</b> true on success
	*/
	public function writeNewData()
	{
		return $this->newdata
			? (boolean) file_put_contents($this->file,$this->newdata)
			: false;
	}
}
?>
