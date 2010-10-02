/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of dctranslations, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2010 Jean-Claude Dubacq, Franck Paul and contributors
 * carnet.franck.paul@gmail.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

$(function()
  {
      $("#translationadder").bind('click',function(event) {addnew();});
      var i=1;
      var formatField = $('#post_format').get(0);
      while (document.getElementById('translation-'+i+'-area')) {
	  decorate(i,formatField);
	  i++;
      };
      $('#translation-0-area').css('display','none');
      $("#translationfield .transgreyout").bind('change',function(event) {
	      var xinput = event.target;
	      var checked = xinput.checked;
	      var area=$(xinput).parents('.area');
	      $('input[type=text]',$(area).children()).attr('disabled',checked);
	      $('textarea',$(area).children()).attr('disabled',checked);
	      $(area).children().not('.classic').not('input[type=hidden]').css('display',checked?'none':'block');
	      var first=$(area).children().find('a:first').css('display',checked?'none':'inline');
	      if (checked) {
		  $(first).parent().prepend('<img class="todelete classic" src="images/check-off.png" alt="delete" />');
	      } else {
		  $(area).find('.todelete').remove();
	      }
	  });
  });


function addnew() {
    var i = parseInt($('#translation_count').val())+1;
    $('#translation_count').val(i);
    var newname = 'translation-'+i+'-area';
    $('#translation-0-area').clone(true).attr('id',newname).insertBefore('#translationadder');
    var reg=/_0_/g;
    var formatField = $('#post_format').get(0);
    $('#'+newname+' *[id]').attr('id',function(index) {
	    return (this.id.replace(/_0/g,'_'+i));
	});
    $('#'+newname+' *[name]').attr('name',function(index) {
	    return (this.name.replace(/_0/g,'_'+i));
	});
    decorate(i,formatField);
    $('#'+newname).css('display','block');
}
function decorate(i,formatField) {
    var stor=new jsToolBar(document.getElementById('post_translation_'+i));
    var xstor=new jsToolBar(document.getElementById('post_translation_'+i+'_excerpt'));
    var place='#translation-'+i+'-area';
    var area='post_translation_'+i;
    $(place+' #translation_'+i+'_langlabel').toggleWithLegend($(place).children().not('.classic').not('input[type=hidden]'),{
	    fn: eval("a=function () { stor.switchMode(formatField.value);xstor.switchMode(formatField.value);}"),
		cookie: 'dcx_post_translation_'+i,
		nb: i
		});
    var fun=function() {
	stor.switchMode(this.value);
	xstor.switchMode(this.value);
    };
    $(formatField).change(fun);
}