/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of flvplayerconfig, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2010 Rasibus Master and contributors
 * postmaster@rasib.us
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

/**
 * @author neolao (neo@neolao.com)
 * @author kévin lepeltier [lipki] (kevin@lepeltier.info)
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 */
/* =============== UTILS =============== */
var delegate = function(pTarget, pFunction)
{
	var f = function(){
		arguments.callee.func.apply(arguments.callee.target, arguments);
	};
	f.target = pTarget;
	f.func = pFunction;
	return f;
}
var escapeHTML = function(str) {
    str = String(str);
    str = str.replace(/&/gi, '');
    
    var div = document.createElement("div");
    var text = document.createTextNode('');
    div.appendChild(text);
    text.data = str;
    
    var result = div.innerHTML;
    result = result.replace(/"/gi, '&quot;');
    
    return result;
}
var findPosX = function (obj)
{
	var curleft = 0;
	do {
		curleft += obj.offsetLeft || 0;
		obj = obj.offsetParent;
	} while (obj);
	return curleft;
};
var findPosY = function (obj)
{
	var curtop = 0;
	do {
		curtop += obj.offsetTop || 0;
		obj = obj.offsetParent;
	} while (obj);
	return curtop;
};
var twoChar = function (str)
{
	if (str.length == 1) {
		return "0" + str;
	}
	return str;
};

/* =============== TOOLTIP =============== */
var tooltip = new Object();
tooltip.tip = document.createElement("div");
tooltip.tip.className = "tooltip";
tooltip.arrow = document.createElement("img");
tooltip.arrow.className = "tooltip_arrow";
tooltip.arrow.src = "index.php?pf=flvplayerconfig/tooltip_arrow.png";
tooltip.show = function(pTarget, pMessage)
{
	var x = findPosX(pTarget);
	var y = findPosY(pTarget);
	
	this.tip.innerHTML = pMessage;
	document.body.appendChild(this.tip);
	this.tip.style.left = (x - this.tip.offsetWidth + pTarget.offsetWidth + 10) + "px";
	this.tip.style.top = (y - this.tip.offsetHeight - 10) + "px";
	this.tip.appendChild(this.arrow);
};
tooltip.hide = function()
{
	document.body.removeChild(this.tip);
};

/* =============== COLORPICKER =============== */
var colorpicker = new Object();
colorpicker.picker = document.createElement("div");
colorpicker.picker.className = "colorpicker";
colorpicker.colors = document.createElement("img");
colorpicker.colors.src = "index.php?pf=flvplayerconfig/colorpicker.png";
colorpicker.colors.width = 140;
colorpicker.colors.height = 100;
colorpicker.picker.appendChild(colorpicker.colors);
colorpicker.arrow = document.createElement("img");
colorpicker.arrow.className = "colorpicker_arrow";
colorpicker.arrow.src = "index.php?pf=flvplayerconfig/tooltip_arrow.png";
colorpicker.picker.appendChild(colorpicker.arrow);
colorpicker.show = function(pTarget, callback)
{
	colorpicker.callback = document.getElementById(callback);
	
	var x = findPosX(pTarget);
	var y = findPosY(pTarget);
	
	document.body.appendChild(this.picker);
	this.picker.style.left =(x - this.picker.offsetWidth + pTarget.offsetWidth + 10) + "px";
	this.picker.style.top = (y - this.picker.offsetHeight - 10) + "px";
};
colorpicker.hide = function()
{
	document.body.removeChild(this.picker);
};
colorpicker.colors.onmousemove = function(e)
{
	var a = findPosX(colorpicker.colors);
	var b = findPosY(colorpicker.colors);
	var x = e.clientX;
	var y = e.clientY;
	var scrollX = window.scrollX || 0;
	var scrollY = window.scrollY || 0;
	
	if (document.body.scrollLeft) {
		scrollX = document.body.scrollLeft;
	}
	if (document.body.scrollTop) {
		scrollY = document.body.scrollTop;
	}
	
	var mouseX = x - a + scrollX - colorpicker.colors.offsetLeft;
	var mouseY = y - b + scrollY - colorpicker.colors.offsetTop;
	
	if (mouseX < 0) 	mouseX = 0;
	if (mouseX > 140) 	mouseX = 140;
	if (mouseY < 0) 	mouseY = 0;
	if (mouseY > 100) 	mouseY = 100;
	
	var red = 0;
	var green = 0;
	var blue = 0;
	
	if (mouseX >= 0 && mouseX < 20) {
		// FF0000 to FFFF00
		red = 255;
		green = Math.round(mouseX * 255 / 20);
		blue = 0;
	} else if (mouseX >= 20 && mouseX < 40) {
		// FFFF00 to 00FF00
		red = 255 - Math.round((mouseX - 20) * 255 / 20);
		green = 255;
		blue = 0;
	} else if (mouseX >= 40 && mouseX < 60) {
		// 00FF00 to 00FFFF
		red = 0;
		green = 255;
		blue = Math.round((mouseX - 40) * 255 / 20);
	} else if (mouseX >= 60 && mouseX < 80) {
		// 00FFFF to 0000FF
		red = 0;
		green = 255 - Math.round((mouseX - 60) * 255 / 20);
		blue = 255;
	} else if (mouseX >= 80 && mouseX < 100) {
		// 0000FF to FF00FF
		red = Math.round((mouseX - 80) * 255 / 20);
		green = 0;
		blue = 255;
	} else if (mouseX >= 100 && mouseX < 120) {
		// FF00FF to FF0000
		red = 255;
		green = 0;
		blue = 255 - Math.round((mouseX - 100) * 255 / 20);
	} else {
		red = 255 - mouseY * 255 / 100;
		green = 255 - mouseY * 255 / 100;
		blue = 255 - mouseY * 255 / 100;
	}
	
	if (mouseY >= 0 && mouseY < 50 && mouseX < 120) {
		// light
		red += (50 - mouseY) / 50 * 255;
		green += (50 - mouseY) / 50 * 255;
		blue += (50 - mouseY) / 50 * 255;
	} else if (mouseX < 120) {
		// dark
		red -= (mouseY - 50) / 50 * 255;
		green -= (mouseY - 50) / 50 * 255;
		blue -= (mouseY - 50) / 50 * 255;
	}
	
	red = Math.round(red);
	green = Math.round(green);
	blue = Math.round(blue);
	if (red > 255) red = 255;
	if (green > 255) green = 255;
	if (blue > 255) blue = 255;
	if (red < 0) red = 0;
	if (green < 0) green = 0;
	if (blue < 0) blue = 0;
	
	var color = twoChar(red.toString(16)) + twoChar(green.toString(16)) + twoChar(blue.toString(16));
	
	colorpicker.callback.value = color;
};
colorpicker.colors.onclick = function()
{
	colorpicker.hide();
	colorpicker.callback.onchange();
};

