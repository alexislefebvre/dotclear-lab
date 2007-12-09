<?php
$t1 = microtime(true);

include_once dirname(__FILE__).'/lib/class_Jabber.php';

$serv = 'serv';
$port = 5222;
$user = 'user';
$pass = 'pass';
$dest = 'somebody@somewhere';

class jabberNotifier
{
	private $serv;
	private $port;
	private $user;
	private $pass;
	
	private $j;
	
	private $messages;
	
	public function __construct($serv,$port,$user,$pass)
	{
		$this->serv = $serv;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $pass;
		
		$this->j = new Jabber();
		
		$this->messages = array();
		
		$this->j->set_handler("connected",$this,"handleConnected");
		$this->j->set_handler("authenticated",$this,"handleAuthenticated");
		$this->j->set_handler("authfailure",$this,"handleAuthFailure");
	}
	
	public function addMessage($to,$str)
	{
		$this->messages[] = array($to,$str);
	}
	
	public function commit($timeout=4)
	{
		echo "Attemp to commit...\n";
		if($this->j->connect($this->serv,$this->port,$timeout))
		{
			echo "Connected ;)\n";
			$this->j->execute($timeout);
			echo "STOPPED\n";
			return true;
		}
		return false;
	}
	
	public function handleConnected()
	{
		$this->j->login($this->user,$this->pass);
		echo "Logged in\n";
	}
	
	public function handleAuthenticated()
	{
		echo "Authenticated\n";
		foreach ($this->messages as $m)
		{
			$this->j->message($m[0],"chat",NULL,$m[1]);
		}
		echo "All sended\n";
		$this->j->terminated = true;
	}
	
	public function handleAuthFailure()
	{
		echo "Auth Failure\n";
	}
}

$j = new jabberNotifier($serv,$port,$user,$pass);
$j->addMessage($dest,'Test...'.time().'!');
$j->commit(8);

echo "Total execution time : ".(microtime(true) - $t1)." s\n";
//*/
?>
