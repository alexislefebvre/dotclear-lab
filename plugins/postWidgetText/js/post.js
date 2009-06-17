$(function(){if(!document.getElementById){return;}
if(document.getElementById('edit-entry')){var formatAddField=$('#post_format').get(0);$(formatAddField).change(function(){postWidgetTextTb.switchMode(this.value);});var postWidgetTextTb=new jsToolBar(document.getElementById('post_wtext'));postWidgetTextTb.context='post';}
$('#edit-entry').onetabload(function(){$('#post-wtext label').toggleWithLegend($('#post-wtext').children().not('label'),{fn:function(){postWidgetTextTb.switchMode(formatAddField.value);},cookie:'dcx_post_wtext',hide:$('#post_wtext').val()==''});});
});