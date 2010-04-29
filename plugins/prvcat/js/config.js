/* -- BEGIN LICENSE BLOCK ----------------------------------
 *
 * Copyright (c) 2010 Arnaud Renevier
 * published under the modified BSD license.
 * -- END LICENSE BLOCK ------------------------------------ */

function prvcat_init() {
    $("#prvcat-form li > input[type='checkbox']").each(function() {
        $(this).change(function() {
            if (this.checked) {
                var descendants = $(this).parent().find("input[type='checkbox']");
                descendants.each(function() { 
                    this.checked = true;
                });
            }
        });
    });
    $("#prvcat-form").submit(function() {
        var pwd_input = $(this).find('input[type="password"]');
        if (!(pwd_input.val())) {
            var checkboxes = $(this).find('input[type="checkbox"]');
            for (var i = checkboxes.length; i-->0; ) {
                var elem = checkboxes[i];
                if (elem.checked) { // at least one category checked, we need a password
                    pwd_input.parent().backgroundFade({sColor:'#f5e5e5',eColor:'#e5bfbf',steps:20});
                    pwd_input.focus();
                    return false;
                }
            }
        }
        return true;
    });
}
$(window).load(prvcat_init);
