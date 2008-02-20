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
	public $use_msg_composing = true;
	public $use_msg_delivered = false;
	public $use_msg_displayed = false;
	public $use_msg_offline = false;

	public $_server_host = '';
	public $_server_ip = '';
	public $_server_port = 5222;
	public $_connect_timeout = 4;
	public $_username = '';
	public $_password = '';
	public $_resource = '';

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

	// If true, the server software name and version will automatically be queried
	// and stored in $this->server_software and $this->server_version at login
	public $auto_server_identify = true;
	
	public $server_software = '';
	public $server_version = '';
	public $server_os = '';
	
	public $protocol_version = false; // set this to an XMPP protocol revision to include it in the <stream:stream> tag
	
	private $host;
	private $port;
	private $jid;
	private $password;
	
	private $username;
	private $server;
	private $con;
	
	public function __construct($host,$port,$jid,$password,$con='')
	{
		$this->_unique_counter = 0;
		$this->xml = new XMLParser();
		
		$this->host = $host;
		$this->port = (int) $port;
		$this->password = $password;
		
		$pos = strpos($jid,'@');
		if ($pos === false) {
			$this->jid = $jid.'@'.$this->host;
			$this->server = $host;
			$this->username = $jid;
		}
		else {
			$this->jid = $jid;
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
	
	private static function wait()
	{
		# Experimental sleeptime value
		$sleeptime = 10000;
		usleep($sleeptime);
	}

	// returns a unique ID to be sent with packets
	function _unique_id($prefix) {
		$this->_unique_counter++;
		return $prefix."_" . md5(time() . $_SERVER['REMOTE_ADDR'] . $this->_unique_counter);
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
	// $server_host     - Hostname of your Jabber server (portion after the "@" in your JID)
	// $server_port     - Port for your Jabber server
	// $connect_timeout - Maximum number of seconds to wait for a connection
	// $alternate_ip    - If $server_host does not resolve to your Jabber server's IP,
	//                    specify the correct IP to connect to here
	//	
	//
	function connect($con_timeout=4,$alternate_ip=false)
	{
		$connector = $this->_connector;
		
		$this->_connection = new $connector();
		$this->_server_host = $this->host;
		$this->_server_port = $this->port;
		$this->_server_ip = $alternate_ip ? $alternate_ip : $this->host;
		$this->_connect_timeout = (int) $con_timeout;
		
		$this->roster = array();
		$this->services = array();
		
		$this->_is_win32 = (substr(strtolower(php_uname()),0,3)=="win");
		$this->_sleep_func = $this->_is_win32 ? "win32_sleep" : "posix_sleep";
		
		return $this->_connect_socket();
	}
	
	function _connect_socket() {
		if ($this->_connection->socket_open($this->con.$this->_server_ip,$this->_server_port,$this->_connect_timeout)) {
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
		$this->_send("</stream:stream>");
		
		return $this->_connection->socket_close();
	}
	
	
	# Logs in to the server
	function login($username,$password,$resource='blog')
	{
		if (!($username && $password)) {
			return false;
		}
		
		// setup handler to automatically respond to the request
		$auth_id	= $this->_unique_id('auth');
		$this->_set_iq_handler('_on_authentication_methods',$auth_id,'result');
		$this->_set_iq_handler('_on_authentication_result',$auth_id,'error');
		
		// prepare our shiny new JID
		$this->_username = $this->username;
		$this->_password = $password;
		$this->_resource = $resource;
		$this->jid = $this->_username.'@'.$this->_server_host.'/'.$this->_resource;
		
		// request available authentication methods
		$payload	= '<username>'.$this->_username.'</username>';
		$packet	= $this->_send_iq(NULL,'get',$auth_id,'jabber:iq:auth',$payload);
		
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
		
		$xml = "<message to='".$to."' type='".$type."' id='".$id."'>\n";
		
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
				elseif (!empty($packet['message'])) {
					$this->_handle_message($packet);
				}
				elseif (!empty($packet['presence'])) {
					$this->_handle_presence($packet);
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
	
	function varset($v)
	{
		return is_string($v) ? strlen($v)>0 : !empty($v);
	}
	
	// handle Message packets
	function _handle_message(&$packet) {
		// events that we recognize
		$events = array("composing","offine","delivered","displayed");
		
		// grab the message details
		$type = $packet['message']['@']['type'];
		if (!$type) $type = "chat";

		$from = $packet['message']['@']['from'];
		$to = $packet['message']['@']['to'];
		$id = $packet['message']['@']['id'];
		
		list($f_username,$f_domain,$f_resource) = $this->_split_jid($from);
		$from_jid = ($f_username?"{$f_username}@":"").$f_domain;
		
		$body = $packet['message']['#']['body'][0]['#'];
		$subject = $packet['message']['#']['subject'][0]['#'];
		$thread = $packet['message']['#']['thread'][0]['#'];
		
		// handle extended message info (to a certain extent, anyway)...
		// if any of the tags in $events are passed under an x element in the
		// jabber:x:event namespace, $extended[tagname] is set to TRUE
		$extended = false;
		$extended_id = NULL;
		$x = $packet['message']['#']['x'];
		
		if (is_array($x)) {
			foreach ($x as $key=>$element) {
				if ($element['@']['xmlns']=="jabber:x:event") {
					foreach ($element['#'] as $tag=>$element_content) {
						if (in_array($tag,$events)) {
							$extended[$tag] = true;
						}
						if ($tag=="id") {
							$extended_id = is_array($element_content)
								? $element_content['0']['#'] : null;
							if (!$extended) $extended = array();
						}
					}
				}
			}
		}
		
		// if a message contains an x tag in the jabber:x:event namespace,
		// and doesn't contain a body or subject, then it's an event notification
		if (!$this->varset($body) && !$this->varset($subject) && is_array($extended)) {
			
			// is this a composing event (which needs special handling)?
			if ($extended['composing']) {
				$this->_call_handler("msgevent_composing_start",$from);
				$this->roster[$from_jid]["composing"] = true;
			} else {
				if ($this->roster[$from_jid]["composing"]) {
					$this->_call_handler("msgevent_composing_stop",$from);
					$this->roster[$from_jid]["composing"] = false;
				}
			}

			foreach ($extended as $event=>$value) {
				$this->_call_handler("msgevent_$event",$from);
			}
			
			// don't process the rest of the message event, as it's not really a message
			return;
		}
		
		
		// process the message
		switch($type) {
			case "error":
				$this->_handle_error(&$packet);
				break;
			case "groupchat":
				$this->_call_handler("message_groupchat",$packet);
				break;
			case "headline":
				$this->_call_handler("message_headline",$from,$to,$body,$subject,$x,$packet);
				break;
			case "chat":
			case "normal":
			default:
				if ($this->roster[$from_jid]["composing"]) $this->roster[$from_jid]["composing"] = false;
				if (($type!="chat") && ($type!="normal")) $type = "normal";
				$this->_call_handler("message_$type",$from,$to,$body,$subject,$thread,$id,$extended,$packet);
				break;
				
		}
	}
	
	// handle Presence packets
	function _handle_presence(&$packet) {
		$type = $packet['presence']['@']['type'];
		if (!$type) $type = "available";

		$from = $packet['presence']['@']['from'];
		
		list($f_username,$f_domain,$f_resource) = $this->_split_jid($from);
		$from_jid = ($f_username?"{$f_username}@":"").$f_domain;
		
		$is_service = (!strlen($f_username));

		$exists = ($is_service && $this->handle_services_internally) ? isset($this->services[$from_jid]) : isset($this->roster[$from_jid]);
		
		$nothing = false;
		$rosteritem = &$nothing;
		
		if ($exists) {
			if ($is_service && $this->handle_services_internally) {
				$use_services_array = true;
				$rosteritem = &$this->services[$from_jid];
			} else {
				$use_services_array = false;
				$rosteritem = &$this->roster[$from_jid];
			}
		} else {
			// Ignore roster updates for JIDs not in our roster, except
			// for subscription requests...
			
			if ($type=="available") {
				// ... but make note of the presence of non-roster items here, in case
				// the roster item is sent AFTER the presence packet... then we can apply the
				// presence when the roster item is received
				$show = $this->_show($packet['presence']['#']['show'][0]['#']);
				$this->presence_cache[$from_jid] = array(
					"status"=>$packet['presence']['#']['status'][0]['#'],
					"show"=>$show ? $show : "on"
				);
				
				return;
			}
			
			if ($type!="subscribe") {
				return;
			}
		}
		$call_update = false;

		switch($type) {
			case "error":
				$this->_handle_error(&$packet);
				break;
			case "probe":
				$this->_call_handler('probe',$packet);
				break;
			case "subscribe":
				// note: $rosteritem is not set here
				$this->_call_handler('subscribe',$packet);
				break;
			case "subscribed":
				$this->_call_handler('subscribed',$packet);
				break;
			case "unsubscribe":
				$this->_call_handler('unsubscribe',$packet);
				break;
			case "unsubscribed":
				$this->_call_handler('unsubscribed',$packet);
				break;
			case "unavailable":
				$rosteritem["show"] = "off";
				$call_update = true;
				break;
			case "available":
				$rosteritem["status"] = $packet['presence']['#']['status'][0]['#'];
				$show = $this->_show($packet['presence']['#']['show'][0]['#']);
				$rosteritem["show"] = $show ? $show : "on"; // away, chat, xa, dnd, or "" = online

				$call_update = true;
				break;
		}
		if ($call_update) {
			if ($use_services_array) {
				$this->_call_handler("serviceupdate",$from,false);
			} else {
				$this->_call_handler("rosterupdate",$from,false);
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
		
		//*
		if (isset($packet['iq']['#']['query'][0]['#']['digest'])) {
			$this->_sendauth_digest($auth_id);
		}
		elseif (isset($packet['iq']['#']['query'][0]['#']['password'])) {
			$this->_sendauth_plaintext($auth_id);
		}
		
		//*/
		//$this->_sendauth_digest($auth_id);
		//$this->_sendauth_plaintext($auth_id);
		
		$this->_set_iq_handler("_on_authentication_result",$auth_id);
	}
	
	// receives the results of an authentication attempt
	function _on_authentication_result(&$packet) {
		$auth_id = $packet['iq']['@']['id'];
		$result_type = $packet['iq']['@']['type'];
		
		if ($result_type=="result") {
			if ($this->auto_server_identify) $this->request_version($this->_server_host);
			
			$this->_call_handler("authenticated");
			$this->_authenticated = true;
		} elseif ($result_type=="error") {
			$this->_handle_iq_error(&$packet,"authfailure");
		}
	}
	
	// receives the results of a service browse query
	function _on_browse_result(&$packet) {
		$packet_type = $packet['iq']['@']['type'];
		
		// did we get a result?  if so, process it, and remember the service list	
		if ($packet_type=="result") {
			
			$this->services = array();

			if ($packet['iq']['#']['service']) {
				// Jabberd uses the 'service' element
				$servicekey = $itemkey = 'service';
			} elseif ($packet['iq']['#']['item']) {
				// Older versions of Merak use 'item'
				$servicekey = $itemkey = 'item';
			} elseif ($packet['iq']['#']['query']) {
				// Newer versions of Merak use 'query'
				$servicekey = 'query';
				$itemkey = 'item';
			} else {
				// try to figure out what to use
				$k = array_keys($packet['iq']['#']);
				$servicekey = $k[0];
				if (!$servicekey) return;
			}
			// if the item key is incorrect, try to figure that out as well
			if ($packet['iq']['#'][$servicekey] && !$packet['iq']['#'][$servicekey][0]['#'][$itemkey]) {
				$k = array_keys($packet['iq']['#'][$servicekey][0]['#']);
				$itemkey = $k[0];
			}
			
			$number_of_services = count($packet['iq']['#'][$servicekey][0]['#'][$itemkey]);

			$services_updated = false;
			for ($a = 0; $a < $number_of_services; $a++)
			{
				$svc = &$packet['iq']['#'][$servicekey][0]['#'][$itemkey][$a];

				$jid = strtolower($svc['@']['jid']);
				$is_new = !isset($this->services[$jid]);
				$this->services[$jid] = array(	
											"type"			=> strtolower($svc['@']['type']),
											"status"		=> "Offline",
											"show"			=> "off",
											"name"			=> $svc['@']['name']
				);
				$number_of_namespaces = count($packet['iq']['#'][$servicekey][0]['#'][$itemkey][$a]['#']['ns']);
				for ($b = 0; $b < $number_of_namespaces; $b++) {
						$this->services[$jid]['namespaces'][$b] = $packet['iq']['#'][$servicekey][0]['#'][$itemkey][$a]['#']['ns'][$b]['#'];
				}

				if ($this->service_single_update) {
					$services_updated = true;
				} else {
					$this->_call_handler("serviceupdate",$jid,$is_new);
				}
			}
			
			if ($this->service_single_update && $services_updated) {
				$this->_call_handler("serviceupdate",NULL,$is_new);
			}
			
		// choke on error
		} elseif ($packet_type=="error") {
			$this->_handle_iq_error($packet);
			
		// confusion sets in
		}
	}
	
	// request software version from a JabberID
	function request_version($jid) {
		// setup handler to automatically respond to the request (it would anyway,
		// because of how we handle version packets, but... hey, why not be thorough)
		$ver_id	= $this->_unique_id("ver");
		$this->_set_iq_handler("_handle_version_packet",$ver_id,"result");

		return $this->_send_iq($jid, 'get', $ver_id, "jabber:iq:version");		
	}
	
	// handle a jabber:iq:version packet (either a request, or a response)
	function _handle_version_packet(&$packet) {
		$packet_type = $packet['iq']['@']['type'];
		$from = $packet['iq']['@']['from'];
		$packetid = $packet['iq']['@']['id'];

		if ($packet_type=="result") {
			// did we get a result?  if so, process it, and update the contact's version information
			$jid = $this->_bare_jid($from);
			
			$version = &$packet['iq']['#']['query'][0]['#'];
			if ($jid==$this->_server_host) {
				//$this->server_software = $version['name'][0]['#'];
				//$this->server_version = $version['version'][0]['#'];
				//$this->server_os = $version['os'][0]['#'];
				
				$this->is_merak = strtolower(substr($this->server_software,0,5))=="merak";
			} elseif ($this->roster[$jid]) {
				$this->roster[$jid]["version"] = $version;
			}

			$this->_call_handler("rosterupdate",$jid,false);

		} elseif ($packet_type=="get") {
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

		if ($packet_type=="result") {
			// did we get a result?  if so, process it, and update the contact's time information
			$jid = $this->_bare_jid($from);
			
			$timeinfo = &$packet['iq']['#']['query'][0]['#'];
			$this->roster[$jid]["time"] = $timeinfo;

			$this->_call_handler("rosterupdate",$jid,false);
		} elseif ($packet_type=="get") {
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

	
	function _on_private_data(&$packet) {
		$packet_type = $packet['iq']['@']['type'];
		if ($packet_type == 'result') {

			$rootnode = $packet['iq']['#']['query'][0]['#'];
			unset($rootnode[0]);
			$rootnode = array_shift($rootnode);
			$data = $rootnode[0];
			$namespace = $data['@']['xmlns'];
			$rawvalues = $data['#'];
			
			$values = array();
			if (is_array($rawvalues)) {
				foreach ($rawvalues as $k=>$v) {
					$values[$k] = $v[0]['#'];
				}
			}
			
			$this->_call_handler("privatedata",$packet['iq']['@']['id'],$namespace,$values);
			
		} elseif ($packet_type == 'error' && isset($packet['iq']['#']['error'][0]['#'])) {
			$this->_handle_iq_error(&$packet,"privatedatafailure");
		} else {
			$this->_call_handler("privatedatafailure",-2,"Unrecognized response from server");
		}				
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

	function _sendauth_digest($auth_id) {

		$payload = "<username>{$this->_username}</username>
	<resource>{$this->_resource}</resource>
	<digest>" . sha1($this->_stream_id . $this->_password) . "</digest>";

		$this->_send_iq(NULL, 'set', $auth_id, "jabber:iq:auth", $payload);
	}

	function _sendauth_plaintext($auth_id) {

		$payload = "<username>{$this->_username}</username>
	<password>{$this->_password}</password>
	<resource>{$this->_resource}</resource>";

		$this->_send_iq(NULL, 'set', $auth_id, "jabber:iq:auth", $payload);
	}	


	// ==== Helper Methods ===================================================================
	
	function _show($show) {
		// off is not valid, but is used internally
		$valid_shows = array("","away","chat","dnd","xa","off");
		if (!in_array($show,$valid_shows)) $show = "";
		
		return $show;
	}
	
	function standardize_transport($transport,$force=true) {
		$transports = array("msn","aim","yim","icq","jab");
		if (!in_array($transport,$transports)) {
			if ($transport=="aol") {
				$transport = "aim";
			} elseif ($transport=="yahoo") {
				$transport = "yim";
			} else {
				if ($force) $transport = "jab";
			}
		}
		return $transport;
	}
		
	function get_transport($domain) {
		$transport = $this->services[$domain]["type"];
		return $this->standardize_transport($transport);
	}





	// ==== Packet Handling & Connection Methods =============================================

	// generates and transmits an IQ packet
	function _send_iq($to = NULL, $type = 'get', $id = NULL, $xmlns = NULL, $payload = NULL, $from = NULL) {
		if (!preg_match("/^(get|set|result|error)$/", $type)) {
			unset($type);

			return false;
		
		} elseif ($id && $xmlns) {
			$xml = "<iq type='$type' id='$id'";
			$xml .= ($to) ? " to='$to'" : '';
			$xml .= ($from) ? " from='$from'" : '';
			$xml .= ">
	<query xmlns='$xmlns'>
		$payload
	</query>
</iq>";

			return $this->_send($xml);
		}
		else {
			return false;
		}
	}	
	
	
	// writes XML data to the socket; trims and UTF8 encodes $xml before
	// sending unless $pristine is true
	function _send($xml,$pristine = false) {
	   	// need UTF8 encoding to prevent character coding issues when
	    // users enter international characters
	    /*
	    if (!$pristine) {
			$xml = trim(utf8_encode($xml));
	    	if (!$xml) return false;
	    }
	    */
		if(strlen($xml)==0) return true;
		
		$res = $this->_connection->socket_write($xml);

		return $res;
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
