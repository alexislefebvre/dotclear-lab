<?php
# Edit, than run this file from a shell !
# $ php test.php

$t1 = microtime(true);

include_once dirname(__FILE__).'/lib/class_Jabber.php';

$host = 'host';
$port = 5222;
$con = ''; # or 'ssl://' or 'tls://'
$user = 'user@server';
$pass = 'pass';
$dest = 'dest@serv.tld';

class jabberNotifier
{
	private $serv;
	private $port;
	private $user;
	private $pass;
	
	private $j;
	
	private $messages;
	
	public function __construct($serv,$port,$user,$pass,$con='')
	{
		$this->serv = $serv;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $pass;
		
		$this->j = new Jabber($serv,$port,$user,$pass,$con);
		
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
		echo "[S] Attemp to commit...\n";
		if ($this->j->connect($timeout)) {
			$this->j->_connection->debug = true;
			echo "[I] Connected ;)\n";
			$this->j->execute($timeout);
			echo "[S] STOPPED\n";
			return true;
		}
		else {
			echo "[E] Connection failure\n";
		}
		return false;
	}
	
	public function handleConnected()
	{
		$this->j->login();
		echo "[I] Logged in\n";
	}
	
	public function handleAuthenticated()
	{
		echo "[I] Authenticated\n";
		foreach ($this->messages as $m)
		{
			$this->j->message($m[0],"normal",NULL,$m[1]);
		}
		echo "[I] All sended\n";
		$this->j->terminated = true;
	}
	
	public function handleAuthFailure()
	{
		echo "[E] Auth Failure\n";
	}
}

$j = new jabberNotifier($host,$port,$user,$pass,$con);
$j->addMessage($dest,'/me makes a test... '.time().'!');
$j->commit(8);

echo "Total execution time : ".(microtime(true) - $t1)." s\n";

?>
