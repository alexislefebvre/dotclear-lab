<?php
/*
 * Centova - http://www.centova.com
 *
 * This file was contributed (in part or whole) by a third party, and is
 * released under the GNU LGPL.  Please see the CREDITS and LICENSE sections
 * below for details.
 * 
 *****************************************************************************
 *
 * DETAILS
 *
 * Connection handling class used by class_Jabber.php.  Used as a generic
 * socket interface to connect to Jabber servers.
 *
 *
 * CREDITS & COPYRIGHTS
 *
 * This class was originally based on Class.Jabber.PHP v0.4 (Copyright 2002,
 * Carlo "Gossip" Zottmann).
 *
 * The code for this class has since been nearly completely rewritten by Steve
 * Blinch and eSite Media Inc.  All such modified code is Copyright 2002-2004, 
 * eSite Media Inc.
 *
 * The original Class.Jabber.PHP was released under the GNU General Public
 * License (GPL); however, we have received written permission from the
 * original author and copyright holder, Carlo Zottmann, to relicense our
 * version of this class and release it under the GNU Lesser General Public
 * License (LGPL).  This allows compatibility with Centova's
 * proprietary software license.
 *
 *
 * LICENSE
 *
 * class_ConnectionSocket.php - Connection Socket Library
 * Part of class_Jabber.php - Jabber Client Library
 * Copyright (C) 2002-2004, eSite Media Inc.
 * Copyright (C) 2002, Carlo Zottmann
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation; either version 2.1 of the License, or (at your
 * option) any later version.
 * 
 * This library is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library; if not, write to the Free Software Foundation,
 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */
class ConnectionSocket
{
	public $socket = null;
	public $error = '';
	public $debug = false;
	
	function socket_open($hostname,$port,$timeout)
	{
		if ($this->socket = @fsockopen($hostname, $port, $errno, $errstr, $timeout))
		{
			socket_set_blocking($this->socket, 0);
			socket_set_timeout($this->socket, 31536000);
			return true;
		}

		$this->error = $errstr.' (#'.$errno.')';
		return false;
	}
	
	function socket_close()
	{
		return fclose($this->socket);
	}
	
	function socket_write($data)
	{
		if ($this->debug) {
			echo '=> '.$data."\n";
		}
		return fwrite($this->socket,$data);
	}
	
	function socket_read($byte_count)
	{
		set_magic_quotes_runtime(0);
		$buffer = fread($this->socket,$byte_count);
		set_magic_quotes_runtime(get_magic_quotes_runtime());
		if ($this->debug && $buffer) {
			echo '<= '.$buffer."\n";
		}
		return $buffer;
	}
}
?>