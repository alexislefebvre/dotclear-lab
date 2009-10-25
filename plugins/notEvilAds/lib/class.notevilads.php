<?php
/***************************************************************\
 *  This is 'Not Evil Ads', a PHP script for websites          *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along 'Not evil ads' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

# WARNING :
# Not Evil Ads is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# Main
class notEvilAds
{
	public $ads;			# Array with HTML code for ads
	private $settings;		# Array with Not Evil Ads settings
	private $status;		# Show ads ? true = yes, false = no.
	
	public function __construct($settings,$ads=null)
	{
		$this->settings = $settings;
		$this->ads = $ads ? $ads : array();
		$this->status = (isset($_COOKIE[$this->settings['cookiename']]))
			? (boolean) $_COOKIE[$this->settings['cookiename']]
			: $this->settings['default'];
	}

	/* Not Evil Ads public interface
	--------------------------------------------------- */

	public function showAd($id)
	{
		if (isset($this->ads[$id]))
			return $this->ads[$id];
		return null;
	}
	
	public function getStatus()
	{
		return $this->status;
	}
	
	public function sendXMLStatus($elements=false)
	{
		$status = (int) $this->status;
		$elements = $elements
			? $elements
			: implode(',',array_keys($this->ads));
		
		header("Content-type: text/xml");
		return '<?xml version="1.0" encoding="utf-8"?>'."\n".
			"<response>\n".
			"	<status>".$status."</status>\n".
			"	<cookiename>".$this->settings['cookiename']."</cookiename>\n".
			"	<cookiedays>".$this->settings['cookiedays']."</cookiedays>\n".
			"	<cookiepath>".$this->settings['cookiepath']."</cookiepath>\n".
			"	<cookiedome>".$this->settings['cookiedome']."</cookiedome>\n".
			"	<easycookie>".$this->settings['easycookie']."</easycookie>\n".
			"	<identifiers>".$elements."</identifiers>\n".
			"</response>\n";
	}
	
	public function sendHTMLCode($id)
	{
		$status = (int) $this->status;
		$content = isset($this->ads[$id]['htmlcode'])
			? ($this->ads[$id]['notajax']
				? '<p><em>'.__('Preview not avaible.').'</em></p>'.
					'<!--NEA_DISABLE_AJAX-->'
				: $this->ads[$id]['htmlcode'])
			: '';
		
		header("Content-type: text/plain");
		return $content;
	}
	
	public function setStatus($status)
	{
		$status = ($status) ? 1 : 0;
		$time = time() + $this->settings['cookiedays'] * 86400;

		if ($this->settings['easycookie'])
			if(!setcookie($this->settings['cookiename'],$status,$time,'/'))
				return false;
		else
			if(!setcookie(
				$this->settings['cookiename'],
				$status,
				$time,
				$this->settings['cookiepath'],
				$this->settings['cookiedome'],
				true))
				return false;

		$_COOKIE[$this->settings['cookiename']] = $status;
		$this->status = (boolean) $status;
		return true;
	}
	
	/* Store and load functions (for saving settings)
	--------------------------------------------------- */

	public static function loadAds($ads)
	{
		$ads = @unserialize(base64_decode($ads));
		if (is_array($ads))
			return $ads;
		
		return 'CORRUPTED';
	}

	public static function loadSettings($settings)
	{
		$settings = @unserialize($settings);
		if ($settings === false)
			return 'CORRUPTED';
		
		if (isset($settings['default']) && isset($settings['nothome'])
			&& isset($settings['identifiers']) && isset($settings['cookiename'])
			&& isset($settings['cookiedays']) && isset($settings['cookiepath'])
			&& isset($settings['cookiedome']) && isset($settings['notajax']))
			return $settings;

		return 'MISSING';
	}
	
	public static function storeAds($ads=false)
	{
		return base64_encode(serialize($ads));
	}
	
	public static function storeSettings($settings=false)
	{
		return serialize($settings);
	}
	
	public static function getUpdatedIdentifiers($ads)
	{
		$identifiers = array();
		foreach ($ads as $k=>$v)
		{
			if ($v['notevil'])
				$identifiers[] = $k;
		}
		return implode(',',$identifiers);
	}
}
?>