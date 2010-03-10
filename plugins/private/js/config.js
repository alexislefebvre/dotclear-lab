$(function() {
     var favicon_url = '';

     $("#private_flag").change(function()
     {
          if (this.checked) {
               $("#misc_options").show();
          }
          else {
               $("#misc_options").hide();
          }
     });
     
     if (!document.getElementById('private_flag').checked) {
          $("#misc_options").hide();
     }
});