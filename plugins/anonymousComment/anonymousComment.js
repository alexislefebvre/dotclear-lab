$(document).ready(anonymous_init);

var anonymous_prev = Array();
anonymous_prev["name"] = "";
anonymous_prev["mail"] = "";
anonymous_prev["site"] = "";
anonymous_prev["subscribe"] = "";
function anonymous_init()
{
    // Preview mode: already set
    if ($('#c_name').val() == anonymous_name 
            && $('#c_mail').val() == anonymous_mail) {
        // hide the fields and re-check the box
        $('#c_anonymous').attr("checked","checked");
        // can't use anonymize() directly because I want no animation
        $('#c_name').parent().hide();
        $('#c_mail').parent().hide();
        $('#c_site').parent().hide();
        $('#subscribeToComments').removeAttr("checked").attr("disabled",true);
    }
    // Setup callback on click
    $('input#c_anonymous').click(function() {
        anonymize($(this).attr("checked"));
    });
}
function anonymize(state)
{
    if (state) { // Anonymize
        $('#c_name').each(function() {
            anonymous_prev["name"] = $(this).val();
            $(this).parent().slideUp("fast", function() {
                $('input', $(this)).val(anonymous_name);
            });
        });
        $('#c_mail').each(function() {
            anonymous_prev["mail"] = $(this).val();
            $(this).parent().slideUp("fast", function() {
                $('input', $(this)).val(anonymous_mail);
            });
        });
        $('#c_site').each(function() {
            anonymous_prev["site"] = $(this).val();
            $(this).parent().slideUp("fast", function() {
                $('input', $(this)).val("");
            });
        });
        // Unset the remember cookie
        $('#c_remember').removeAttr("checked");
        $.cookie('comment_info','',{expires:-30,path:'/'});
        // It make no sense to subscribe if you're anonymous
        anonymous_prev["subscribe"] = $('#subscribeToComments').attr("checked");
        $('#subscribeToComments').removeAttr("checked").attr("disabled",true);

    } else { // Restore commenter info
        $('#c_name').each(function() {
            $(this).val(anonymous_prev["name"]);
            $(this).parent().slideDown("fast");
        });
        $('#c_mail').each(function() {
            $(this).val(anonymous_prev["mail"]);
            $(this).parent().slideDown("fast");
        });
        $('#c_site').each(function() {
            $(this).val(anonymous_prev["site"]);
            $(this).parent().slideDown("fast");
        });
        // Restore subscription
        $('#subscribeToComments').attr("disabled",false).attr("checked",anonymous_prev["subscribe"]);
    }
}
