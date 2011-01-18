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

function urlencode (clearString) {
  var output = '';
  var x = 0;
  if( !clearString ) return '';
  clearString = clearString.toString();
  var regex = /(^[a-zA-Z0-9_.]*)/;
  while (x < clearString.length) {
    var match = regex.exec(clearString.substr(x));
    if (match != null && match.length > 1 && match[1] != '') {
	output += match[1];
      x += match[1].length;
    } else {
      if (clearString[x] == ' ')
	output += '+';
      else {
	var charCode = clearString.charCodeAt(x);
	var hexVal = charCode.toString(16);
	output += '%' + ( hexVal.length < 2 ? '0' : '' ) + hexVal.toUpperCase();
      }
      x++;
    }
  }
  return output;
}

$(function(){
	$('#flvplayerconfig-ok')
		.click(function(){
			sendClose();
			window.close();
		});
	
	function sendClose(){
		var insert_form=$('#flvplayerconfig-insert-form').get(0);
		if(insert_form==undefined){return;}
		var tb=window.opener.the_toolbar;
		var data=tb.elements.flvplayerconfig.data;
		
		ref = {'align':'none', 'loop':'on', 'autoplay':'on', 'autoload':'on', 'volume':'100', 'showmouse':'always', 'videobgcolor':'000000', 'loadonstop':'on', 'phpstream':'on', 'shortcut':'on', 'showtitleandstartimage':'on', 'margin':'5', 'bgcolor':'ffffff', 'bgcolor1':'7c7c7c', 'bgcolor2':'333333', 'showstop':'on', 'showvolume':'on', 'showtime':'0', 'showplayer':'autohide', 'showloading':'autohide', 'showfullscreen':'on', 'showswitchsubtitles':'on', 'playertimeout':'1500', 'playercolor':'000000', 'playeralpha':'100', 'loadingcolor':'ffff00', 'buttoncolor':'ffffff', 'buttonovercolor':'ffff00', 'slidercolor1':'cccccc', 'slidercolor2':'888888', 'sliderovercolor':'ffff00', 'titlecolor':'ffffff', 'titlesize':'20', 'srt':'on', 'srtcolor':'ffffff', 'srtbgcolor':'000000', 'srtsize':'11', 'buffer':'5', 'buffermessage':'Buffering _n_', 'buffercolor':'ffffff', 'bufferbgcolor':'000000', 'buffershowbg':'on', 'onclick':'playpause', 'onclicktarget':'_self', 'ondoubleclick':'none', 'ondoubleclicktarget':'_self', 'showiconplay':'on', 'iconplaycolor':'ffffff', 'iconplaybgcolor':'000000', 'iconplaybgalpha':'75'}
		
		$('input').add('select').each(function(index) {
			if( $(this).attr('id') && $(this).val() != '' )
				if( $(this).val() != ref[$(this).attr('id')] )
					data[$(this).attr('id')] = $(this).val();
		});
		tb.elements.flvplayerconfig.fncall[tb.mode].call(tb);
	};
});