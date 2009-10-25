<?php
/***************************************************************\
 *  This is JabberMessage                                      *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Oleksandr Syenchuk                                         *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along JabberMessage (see COPYING.txt);             *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

/* JabberMessage is based on 'Jabber Client Library', Version 0.8
 * Copyright 2002-2005, eSite Media Inc.
 * Portions Copyright 2002, Carlo Zottmann
 * http://www.centova.com
 */

require_once dirname(__FILE__).'/class_ConnectionSocket.php';
require_once dirname(__FILE__).'/class_XMLParser.php';

class Jabber
{
	public $_server_ip = '';
	public $_connect_timeout = 4;

	public $_iq_version_name = 'Jabber Notifications';
	public $_iq_version_version = '2.1';

	public $_connector = 'ConnectionSocket';
	public $_authenticated = false;
	
	public $_packet_queue = array();

	public $_iq_handlers = array();
	public $_event_handlers = array();

	// if true, roster updates generate only one "rosterupdate" event,
	// regardless of how many contacts were actually updated/added;
	// useful for the initial roster download
	public $roster_single_update = false;

	// if true, service updates generate only one "serviceupdate" event,
	// regardless of how many services were actually updated/added;
	// useful for retrieving a service list
	public $service_single_update = false;
	
	// if true, contacts without "@"'s in their name will be assumed
	// to be services and will not be listed in the roster; if the
	// corresponding JID is found in the $this->services array, its
	// "status" and "show" elements will be updated to reflect the
	// presence/availability of the service (and the "serviceupdate"
	// event will be fired)
	public $handle_services_internally = false;
	
	public $protocol_version = false; // set this to an XMPP protocol revision to include it in the <stream:stream> tag
	
	private $host;
	private $port;
	private $jid;
	private $password;
	private $resource;
	
	private $username;
	private $server;
	private $con;
	
	public function __construct($host,$port,$jid,$password,$con='',$resource='blog')
	{
		$this->xml = new XMLParser();
		
		$this->host = $host;
		$this->port = (int) $port;
		$this->password = $password;
		$this->resource = $resource;
		
		$pos = strpos($jid,'@');
		if ($pos === false) {
			$this->jid = $jid.'@'.$this->host.'/'.$resource;
			$this->server = $host;
			$this->username = $jid;
		}
		else {
			$this->jid = $jid.'/'.$resource;
			$this->server = substr($jid,$pos+1);
			$this->username = substr($jid,0,$pos);
		}
		
		if ($con !== '' && $con !== 'ssl://' && $con !== 'tls://') {
			$con = '';
		}
		$this->con = $con;
	}

	
	# set a handler method for a specific Jabber event
	function set_handler($handler_name,&$handler_object,$method_name) {
		$this->_event_handlers[$handler_name] = array(&$handler_object,$method_name);
	}
	
	function set_handler_object(&$handler_object,$handlers) {
		foreach ($handlers as $handler_name=>$method_name) {
			$this->set_handler(
				$handler_name,
				$handler_object,
				$method_name
			);
		}
	}
	
	// same as above, but accepts a plain ol' function instead of a method
	function set_handler_function($handler_name,$method_name) {
		$this->_event_handlers[$handler_name] = $method_name;
	}
	
	// calls the specified handler with the specified parameters; accepts:
	//
	// $handler_name - the name of the handler (as defined with ::set_handler())
	//                 to call
	// (optional) other parameters - the parameters to pass to the handler method
	function _call_handler()
	{
		if (func_num_args() < 1) {
			return false;
		}

		$arg_list = func_get_args(); 
		$handler_name = array_shift($arg_list);
		
		if (!empty($this->_event_handlers[$handler_name])) {
			call_user_func_array(&$this->_event_handlers[$handler_name],$arg_list);
		}
	}
	
	private static function wait($sleeptime=10000)
	{
		usleep($sleeptime);
	}

	// returns a unique ID to be sent with packets
	function _unique_id($prefix)
	{
		return $prefix.'_'.md5(uniqid(mt_rand()));
	}

	// splits a JID into its three components; returns an array
	// of (username,domain,resource)
	function _split_jid($jid) {
		preg_match("/(([^\@]+)\@)?([^\/]+)(\/(.*))?$/",$jid,$matches);
		return array($matches[2],$matches[3],(!empty($matches[5]) ? $matches[5] : null));
	}
	
	function _bare_jid($jid) {
		list($u_username,$u_domain,$u_resource) = $this->_split_jid($jid);
		return ($u_username?$u_username."@":"").$u_domain;
	}

	

	// ==== Core Jabber Methods ==============================================================

	// Connects to the specified Jabber server.
	//
	// Returns true if the socket was opened, otherwise false.
	// A "connected" event is also fired when the server responds to our <stream> packet.
	//
	// $con_timeout - Maximum number of seconds to wait for a connection
	// $alternate_ip    - If $server_host does not resolve to your Jabber server's IP,
	//                    specify the correct IP to connect to here
	//	
	//
	function connect($con_timeout=4,$alternate_ip=false)
	{
		$connector = $this->_connector;
		
		$this->_connection = new $connector();
		$this->_server_ip = $alternate_ip ? $alternate_ip : $this->host;
		$this->_connect_timeout = (int) $con_timeout;
		
		$this->roster = array();
		$this->services = array();
		
		$this->_is_win32 = (substr(strtolower(php_uname()),0,3)=="win");
		$this->_sleep_func = $this->_is_win32 ? "win32_sleep" : "posix_sleep";
		
		return $this->_connect_socket();
	}
	
	function _connect_socket() {
		if ($this->_connection->socket_open($this->con.$this->_server_ip,$this->port,$this->_connect_timeout)) {
			$this->_send("<?xml version='1.0' encoding='UTF-8' ?" . ">\n");
			
			$xmpp_version = ($this->protocol_version) ? " version='{$this->protocol_version}'" : '';
			
			$this->_send("<stream:stream to='{$this->server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams'{$xmpp_version}>\n");
			return true;
		} else {
			$this->error = $this->_connection->error;
			return false;
		}
	}
	
	# Disconnect from the server
	function disconnect()
	{
		$this->_send('</stream:stream>');
		
		return $this->_connection->socket_close();
	}
	
	# Logs in to the server
	function login()
	{
		# setup handler to automatically respond to the request
		$auth_id	= $this->_unique_id('auth');
		$this->_set_iq_handler('_on_authentication_methods',$auth_id,'result');
		$this->_set_iq_handler('_on_authentication_result',$auth_id,'error');
		
		# request available authentication methods
		$payload	= '<username>'.$this->username.'</username>';
		$packet	= $this->_send_iq(null,'get',$auth_id,'jabber:iq:auth',$payload);
		
		return true;
	}
	
	function xmlentities($string, $quote_style=ENT_QUOTES)
	{
		return htmlspecialchars($string,$quote_style);
	}	
	
	# Sends a message
	function message($to,$type='normal',$id='',$body='',$thread='',$subject='')
	{
		if (!($to && ($body || $subject))) {
			return false;
		}
		
		if (!$id) {
			$id = $this->_unique_id('msg');
		}
		
		$body = $this->xmlentities($body);
		$subject = $this->xmlentities($subject);
		$thread = $this->xmlentities($thread);
		
		$xml = '<message to="'.$to.'" type="'.$type.'" id="'.$id.'">';
		
		if ($subject)	$xml .= "<subject>".$subject."</subject>\n";
		if ($thread)	$xml .= "<thread>".$thread."</thread>\n";
		if ($body)	$xml .= "<body>".$body."</body>\n";
		
		$xml .= "</message>\n";
		
		if ($this->_send($xml)) {
			return true;
		}
		
		return false;
	}
	
	# Begins execution loop (timeout in seconds)
	function execute($timeout)
	{
		$ref_ts = microtime(true);
		$this->terminated = false;

		do
		{
			# check to see if there are any packets waiting
			if (!$this->_receive()) {
				continue;
			}
			
			# Read packets
			while (count($this->_packet_queue))
			{
				$packet = $this->_get_next_packet();

				if (!$packet) {
					continue;
				}
				
				# check the packet type, and dispatch the appropriate handler
				if (!empty($packet['iq'])) {
					$this->_handle_iq($packet);
				}
				elseif (!empty($packet['stream:stream'])) {
					$this->_handle_stream($packet);
				}
				elseif (!empty($packet['stream:features'])) {
					$this->_handle_stream_features($packet);
				}
				elseif (!empty($packet['stream:error'])) {
					$this->_handle_stream_error($packet);
				}
			}
		} while ($this->wait() || ((microtime(true)-$ref_ts) < $timeout && !$this->terminated));

		$this->_call_handler('terminated');
	}

	// ==== Event Handlers (Raw Packets) ====
	
	// Sets a handler for a particular IQ packet ID (and optionally packet type).
	// Assumes that $method is the name of a method of $this
	function _set_iq_handler($method,$id,$type=NULL) {
		if (is_null($type)) $type = "_all";
		$this->_iq_handlers[$id][$type] = array(&$this,$method);
	}
	
	// handle IQ packets
	function _handle_iq(&$packet) {
		$iq_id = $packet['iq']['@']['id'];
		$iq_type = $packet['iq']['@']['type'];
		
		// see if we already have a handler setup for this ID number; the vast majority of IQ
		// packets are handled by their ID number, since they are usually in response to a
		// request we submitted
		if ($this->_iq_handlers[$iq_id]) {
			
			// OK, is there a handler for this specific packet type as well?
			if (!empty($this->_iq_handlers[$iq_id][$iq_type])) {
				// yup - try  the handler for our packet type
				$iqt = $iq_type;
			} else {
				// nope - try the catch-all handler
				$iqt = "_all";
			} 
			
			$handler_method = $this->_iq_handlers[$iq_id][$iqt];
			unset($this->_iq_handlers[$iq_id][$iqt]);
			
			if ($handler_method) {
				call_user_func($handler_method,&$packet);
			}
		} else {
			// this packet didn't have an ID number (or the ID number wasn't recognized), so
			// see if we can salvage it.
			switch($iq_type) {
				case "get":
					if (!$packet['iq']['#']['query']) return;
					
					$xmlns = $packet['iq']['#']['query'][0]['@']['xmlns'];
					switch($xmlns) {
						case "jabber:iq:version":
							// handle version inquiry/response
							$this->_handle_version_packet($packet);
							break;
						case "jabber:iq:time":
							// handle time inquiry/response
							$this->_handle_time_packet($packet);
							break;
						default:
							// unknown XML namespace; borkie borkie!
							break;
					}
					break;
					
				case "set": // handle <iq type="set"> packets
					if (!$packet['iq']['#']['query']) return;
					
					$xmlns = $packet['iq']['#']['query'][0]['@']['xmlns'];
					switch($xmlns) {
						case "jabber:iq:roster":
							$this->_on_roster_result($packet);
							break;
						default:
							// unknown XML namespace; borkie borkie!
							break;
					}
					break;

				default:
					// don't know what to do with other types of IQ packets!
					break;

			}
		}
	}
	
	
	// handle Stream packets
	function _handle_stream(&$packet) {
		if ($packet["stream:stream"]['@']['from'] == $this->server
			&& $packet["stream:stream"]['@']['xmlns'] == "jabber:client"
			&& $packet["stream:stream"]['@']["xmlns:stream"] == "http://etherx.jabber.org/streams")
		{
			$this->_stream_id = $packet["stream:stream"]['@']['id'];
			$this->_call_handler('connected');
		}
	}

	// handle Stream features packets
	function _handle_stream_features(&$packet) {
		$this->features = &$packet;
	}
	
	// handle stream error
	function _handle_stream_error(&$packet) {
		$this->_call_handler('stream_error',$packet);
	}



	// ==== Event Handlers (Event Specific) ==================================================

	// receives a list of authentication methods and sends an authentication
	// request with the most appropriate one
	function _on_authentication_methods(&$packet)
	{
		$auth_id = $packet['iq']['@']['id'];
		
		if (isset($packet['iq']['#']['query'][0]['#']['digest'])) {
			$this->_sendauth_digest($auth_id);
		}
		elseif (isset($packet['iq']['#']['query'][0]['#']['password'])) {
			$this->_sendauth_plaintext($auth_id);
		}
		
		$this->_set_iq_handler("_on_authentication_result",$auth_id);
	}
	
	// receives the results of an authentication attempt
	function _on_authentication_result(&$packet) {
		$auth_id = $packet['iq']['@']['id'];
		$result_type = $packet['iq']['@']['type'];
		
		if ($result_type=="result") {
			$this->_call_handler("authenticated");
			$this->_authenticated = true;
		} elseif ($result_type=="error") {
			$this->_handle_iq_error(&$packet,"authfailure");
		}
	}
	
	// handle a jabber:iq:version packet (either a request, or a response)
	function _handle_version_packet(&$packet) {
		$packet_type = $packet['iq']['@']['type'];
		$from = $packet['iq']['@']['from'];
		$packetid = $packet['iq']['@']['id'];

		if ($packet_type=="get") {
			// did we get an inquiry?  if so, send our version info
			$payload	= "<name>{$this->_iq_version_name}</name><version>{$this->_iq_version_version}</version>";
			if ($this->_iq_version_os) $payload .= "<os>{$this->_iq_version_os}</os>";
			$packet		= $this->_send_iq($from, 'result', $packetid, "jabber:iq:version", $payload);
		}
		// other types of packets are probably just error responses (eg: the remote
		// client doesn't support jabber:iq:version requests) so we ignore those
		
		return true;
	}
	
	// handle a jabber:iq:time packet (either a request, or a response)
	function _handle_time_packet(&$packet) {
		$packet_type = $packet['iq']['@']['type'];
		$from = $packet['iq']['@']['from'];
		$packetid = $packet['iq']['@']['id'];

		if ($packet_type=="get") {
			// did we get an inquiry?  if so, send our time info
			$utc = gmdate('Ymd\TH:i:s');
			$tz = date("T");
			$display = date("D M d H:i:s Y");
			
			$payload	= "<utc>{$utc}</utc><tz>{$tz}</tz><display>{$display}</display>";
			$packet		= $this->_send_iq($from, 'result', $packetid, "jabber:iq:time", $payload);
		}
		// other types of packets are probably just error responses (eg: the remote
		// client doesn't support jabber:iq:time requests) so we ignore those
		
		return true;
	}


	// handles a generic IQ error; fires the specified error handler method
	// with the error code/message retrieved from the IQ packet
	function _handle_iq_error(&$packet,$error_handler="error") {
		$error = &$packet['iq']['#']['error'][0];
		$xmlns = &$packet['iq']['#']['query'][0]['@']['xmlns'];
		$this->_call_handler(
			$error_handler,
			$error['@']['code'],
			$error['#'],
			$xmlns,
			$packet
		);
	}
	
	// handles a generic error; fires the specified error handler method
	// with the error code/message retrieved from the packet
	function _handle_error(&$packet,$error_handler="error") {
		$packet = array_shift($packet);
		$error = &$packet['#']['error'][0];
		$xmlns = &$packet['#']['query'][0]['@']['xmlns'];
		$this->_call_handler(
			$error_handler,
			$error['@']['code'],
			$error['#'],
			$xmlns,
			$packet
		);
	}
	
	
	// ==== Authentication Methods ===========================================================

	function _sendauth_digest($auth_id)
	{
		$payload =
		'<username>'.$this->username.'</username>'.
		'<resource>'.$this->resource.'</resource>'.
		'<digest>'.sha1($this->_stream_id.$this->password).'</digest>';
		
		$this->_send_iq(null,'set',$auth_id,'jabber:iq:auth',$payload);
	}

	function _sendauth_plaintext($auth_id)
	{
		$payload = 
		'<username>'.$this->username.'</username>'.
		'<resource>'.$this->resource.'</resource>'.
		'<password>'.$this->password.'</password>';
		
		$this->_send_iq(null,'set',$auth_id,'jabber:iq:auth',$payload);
	}	


	// ==== Packet Handling & Connection Methods =============================================

	// generates and transmits an IQ packet
	function _send_iq($to=null,$type='get',$id=null,$xmlns=null,$payload=null,$from=null)
	{
		if (!preg_match("/^(get|set|result|error)$/", $type)) {
			return false;
		}
		elseif ($id && $xmlns) {
			$xml =
			'<iq type="'.$type.'" id="'.$id.'"'.($to ? ' to="'.$to.'"' : '').($from ? ' from="'.$from.'"' : '').'>'.
			'	<query xmlns="'.$xmlns.'">'.$payload.'</query>'.
			'</iq>';
			
			return $this->_send($xml);
		}
		else {
			return false;
		}
	}
	
	// writes XML data to the socket
	function _send($xml)
	{
		if (empty($xml)) {
			return true;
		}
		
		return $this->_connection->socket_write($xml);
 	}
	
	function _receive() {
		$incoming = '';
		$packet_count = 0;

		$sleepfunc = $this->_sleep_func;

		$iterations = 0;
		do {
			$line = $this->_connection->socket_read(16384);
			if (strlen($line)==0) break;
			
			$incoming .= $line;
			$iterations++;
			
		// the iteration limit is just a brake to prevent infinite loops if
		// something goes awry in socket_read()
		} while($iterations<100);

		$incoming = trim($incoming);

		if ($incoming != "") {
			
			$temp = $this->_split_incoming($incoming);
			$packet_count = count($temp);

			for ($a = 0; $a < $packet_count; $a++) {
				$this->_packet_queue[] = $this->xml->xmlize($temp[$a]);
				//var_dump($this->xml->xmlize($temp[$a]));echo "\n---\n";
			}
		}

		return $packet_count;
	}	
	
	function _get_next_packet() {
		return array_shift($this->_packet_queue);
	}
	
	function _split_incoming($incoming) {
		$temp = preg_split("/<(message|iq|presence|stream)/", $incoming, -1, PREG_SPLIT_DELIM_CAPTURE);
		$array = array();

		for ($a = 1; $a < count($temp); $a = $a + 2) {
			$array[] = "<" . $temp[$a] . $temp[($a + 1)];
		}

		return $array;
	}
}
?>