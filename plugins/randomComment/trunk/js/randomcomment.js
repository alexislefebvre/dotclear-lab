$(document).ready(function(){getRandomComment()})
function getRandomComment(){$.ajax({type:'GET',url:random_comment_url,success:function(str){$('#rd_content').html(str);$('#rd_content').fadeIn(1000,function(){setTimeout('reloadRandomComment()',random_comment_ttl)})},async:true})}
function reloadRandomComment(){$('#rd_content').fadeOut(1000,function(){getRandomComment()})}