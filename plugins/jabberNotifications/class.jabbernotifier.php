<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is Jabber Notifications, a plugin for Dotclear 2      *
 *                                                             *
 *  Copyright (c) 2007,2008,2011                               *
 *  Alex Pirine, Olivier Tétard and contributors.              *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with Jabber Notifications (see COPYING.txt); *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

class jabberNotifier
{
	private $j;
	private $serv;
	private $port;
	private $con;
	private $user;
	private $pass;
	
	private $message;
	private $dest;
	
	public $status;
	public $done = false;
	
	public function __construct($serv,$port,$user,$pass,$con)
	{
		$this->serv = $serv;
		$this->port = $port;
		$this->con = $con;
		$this->user = $user;
		$this->pass = $pass;
		
		$this->j = new Jabber($serv,$port,$user,$pass,$con);
		$this->messages = array();
		$this->status = 'init';
		
		$this->j->set_handler('connected',$this,'handleConnected');
		$this->j->set_handler('authenticated',$this,'handleAuthenticated');
		$this->j->set_handler('authfailure',$this,'handleAuthFailure');
		$this->j->set_handler('stream_error',$this,'handleStreamError');
	}
	
	public function setMessage($msg)
	{
		$this->message = $msg;
	}
	
	public function addDestination($to)
	{
		if (is_array($to)) {
			foreach ($to as $v)
			{
				if (is_string($v) && trim($v)) {
					$this->dest[] = $v;
				}
			}
		}
		else {
			$this->dest[] = $to;
		}
	}
	
	public function commitThroughGateway($url)
	{
		$data = array(
			'serv'=>$this->serv,
			'port'=>$this->port,
			'con'=>$this->con,
			'user'=>$this->user,
			'pass'=>$this->pass,
			'message'=>$this->message,
			'dest[]'=>$this->dest);
		try
		{
			$client = netHttp::initClient($url,$path,5);
			$client->setUserAgent('Dotclear - http://www.dotclear.net/');
			$client->useGzip(false);
			$client->setPersistReferers(false);
			$client->post($path,$data,'UTF-8');
			$this->status = substr($client->getContent(),0,255);
		}
		catch (Exception $e)
		{
			return $this->status = 'gatewayConnectFailure';
		}
	}
	
	public function commit($timeout=4)
	{
		$this->status = 'ok'; # Will be changed on error
		
		if ($this->j->connect($timeout)) {
			$this->j->execute($timeout);
			$this->j->disconnect();
			if (!$this->done && $this->status == 'ok') {
				$this->status = 'notDone';
			}
		} else {
			$this->status = 'connectFailure';
		}
	}
	
	public function handleConnected()
	{
		if (!$this->j->login()) {
			$this->status = 'loginFailure';
		}
	}
	
	public function handleAuthenticated()
	{
		foreach ($this->dest as $dest)
		{
			if (!$this->j->message($dest,"chat",NULL,$this->message)) {
				$this->status = 'messageFailure';
			}
		}
		$this->done = true;
		$this->j->terminated = true;
	}
	
	
	public function handleAuthFailure()
	{
		$this->status = 'authFailure';
	}
	
	public function handleStreamError()
	{
		$this->status = 'streamError';
	}
}
?>