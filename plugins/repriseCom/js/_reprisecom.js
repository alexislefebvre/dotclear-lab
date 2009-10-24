/* 
--- BEGIN LICENSE BLOCK --- 
This file is part of repriseCom, a plugin for migrate comments 
for gallery from Dotclear1 to DotClear2.
Copyright (C) 2008 Benoit de Marne,  and contributors

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
--- END LICENSE BLOCK ---
*/

function affinfo(txt) {
	var xhr = new getXMLHttpRequest();
	document.getElementById("message").innerHTML = '<b>' + txt + ' : <span style=\"color:#666666\">work in progress</span> ...</b><img src="index.php?pf=repriseCom/img/progress.gif" alt="" />';
}

function loader() {
	var xhr = new getXMLHttpRequest();
	document.getElementById("message").innerHTML = '<b>Execution clean <span style=\"color:#666666\">work in progress</span>...</b><img src="index.php?pf=repriseCom/img/progress.gif" alt="" />';
	//document.getElementById("result").scrollTop = document.getElementById("result").scrollHeight;
}

