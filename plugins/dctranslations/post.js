$(function()
  {
      var i=0;
      var stor=new Array();
      var xstor=new Array();
      var formatField = $('#post_format').get(0);
      while (document.getElementById('translation-'+i+'-area')) {
 	  stor[i]=new jsToolBar(document.getElementById('post_translation_'+i));
  	  xstor[i]=new jsToolBar(document.getElementById('post_translation_excerpt_'+i));
  	  var place='#translation-'+i+'-area';
  	  var area='post_translation_'+i;
	  $(place+' #translation-'+i+'-langlabel').toggleWithLegend($(place).children().not('.classic'),{
		  fn: eval("a=function () { stor["+i+"].switchMode(formatField.value);xstor["+i+"].switchMode(formatField.value);}"),
		      cookie: 'dcx_post_translation_'+i,
//		      //		      hide: $('#'+area).val() == '',
		      nb: i
		      });
	  eval('var fun=function() {stor['+i+'].switchMode(this.value);xstor['+i+'].switchMode(this.value);};');
	  $(formatField).change(fun);
	  i++;
      };
      $(".transgreyout").bind('change',function(event) {
	      var xinput = event.target;
	      var checked = xinput.checked;
	      var area=$(xinput).parents('.area');
	      $('input[type=text]',$(area).children()).attr('disabled',checked);
	      $('textarea',$(area).children()).attr('disabled',checked);
	      $(area).children().not('.classic').css('display',checked?'none':'block');
	      var first=$(area).children().find('a:first').css('display',checked?'none':'inline');
	      if (checked) {
		  $(first).parent().prepend('<img class="todelete classic" src="images/check-off.png" alt="X" />');
	      } else {
		  $(area).find('.todelete').remove();
	      }
	  });
  });

