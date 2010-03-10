$(function() {
     var favicon_url = '';

     $("#blog_off_flag").change(function()
     {
          if (this.checked) {
               $("#misc_options").show();
          }
          else {
               $("#misc_options").hide();
          }
     });
     
     if (!document.getElementById('blog_off_flag').checked) {
          $("#misc_options").hide();
     }
});