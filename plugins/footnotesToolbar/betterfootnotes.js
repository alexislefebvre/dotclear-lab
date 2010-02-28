$(document).ready(betterfootnotes_init);

function betterfootnotes_init()
{
    if (!$("div.post div.footnotes").length) {
        return; // no footnotes, bail out
    }
    $("div.post sup").each(function() {
        var note_call = $(this);
        if (note_call.find("a[href|=#pnote]").length == 0) {
            return; // That's not a footnote call
        }
        var note_id = note_call.find("a[href|=#pnote]").attr("href");
        var note_content = $("div.post div.footnotes p a"+note_id)
                                .parent().html()
                                .replace(/^\[<a href="#rev-pnote-\d+" id="pnote-\d+">\d+<\/a>\]\s*/,'');
        // Create the floating note
        var note_float = $("<div/>").html(note_content)
                                .addClass("footnote-float")
                                .appendTo(note_call)
                                .css("left", note_call.offset().left + note_call.width() + 5)
                                .css("top", note_call.offset().top - $(this).height() - 5);
        // Handle hover
        note_call.hover(
            // I wish we had jQuery >= 1.4 to have the delay() function...
            function(e) {
                window.clearTimeout(note_float.data("fade_timeout"));
                note_float.fadeIn();
            }, function(e) {
                note_float.data("fade_timeout",
                    window.setTimeout(function(){note_float.fadeOut("slow");}, 500)
                );
            }
        );
    });
}
