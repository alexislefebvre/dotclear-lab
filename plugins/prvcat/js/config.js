/* -- BEGIN LICENSE BLOCK ----------------------------------
 *
 * Copyright (c) 2010-2015 Arnaud Renevier
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
        var has_private = false;

        var checkboxes = $(this).find('input[type="checkbox"]');
        for (var i = checkboxes.length; i-->0; ) {
            var elem = checkboxes[i];
            if (elem.checked) {
                has_private = true;
                break;
            }
        }
        if (!has_private) { // no category checked, do not prevent submission
            return true;
        }

        var pwd_inputs = $(this).find('input[type="password"]');
        var pwd_values = pwd_inputs.map(function() {
            return this.value;
        }).get().sort();
        for ( var i = 1; i < pwd_values.length; i++ ) {
            if ( pwd_values[i] === pwd_values[i-1] ) {
                pwd_values.splice(i--, 1);
            }
        } // pwd now contains an array containing all passwords values with duplicate removed

        if (pwd_values.length >=2 || !pwd_values[0]) { // if pwd_values.length >= 2, it means passwords were different
            pwd_inputs.parent().backgroundFade({sColor:'#f5e5e5',eColor:'#e5bfbf',steps:20});
            pwd_inputs.eq(0).focus();
            return false;
        }
        return true;
    });
}
$(window).load(prvcat_init);
