$(document).ready(threading_init);

var threading_comments = Array();

function threading_init()
{ // Init: collect comments, add the switch
    if (!$("#comments").length) {
        return; // no comments, bail out
    }
    threading_get_comments();
    var switch_tag = document.createElement("p");
    switch_tag.className = "threading";
    $("#comments dl").before(switch_tag)
    $("#comments > p").html(
        '<label>'+threading_switch_text+' <input type="checkbox" id="threading-switch" /></label>')
        .find("input")
        .click(function()
        {
            if ($(this).attr("checked")) {
                threading_show_threads();
            } else {
                threading_show_list();
            }
        })
        .each(function()
        {
            if (threading_by_default) {
                $(this).attr("checked", true);
                threading_show_threads();
            }
        });
}
function threading_get_comments()
{ // Collect all comments into an array of objects
    var comments = $("#comments dl");
    $("dt", comments).each(function()
    {
        var comment = { "id": $(this).attr("id"),
                        "replyto": null,
                        "dt": $(this),
                        "dd": $(this).next("dd")
                      }
        var comment_match = comment.dd.html().match(/<p>@<a href="#(c\d+)"( rel="nofollow")?>/i);
        if (comment_match) {
            comment.replyto = comment_match[1];
        }
        threading_comments.push(comment);
    });
}
function threading_show_threads()
{ // Show comments by thread
    var comments = $("#comments dl");
    for (var i=0 ; i < threading_comments.length ; i++) {
        if (!threading_comments[i].replyto) { // no parent: insert directly
            comments.append(threading_comments[i].dt);
            comments.append(threading_comments[i].dd);
            continue;
        } else {
            // Set margin-left
            var parent_margin = $("dt#"+threading_comments[i].replyto).css("margin-left");
            var margin = parseInt(parent_margin.slice(0,-2)) + threading_indent;
            if (margin > threading_max_levels * threading_indent) { // not too deep
                margin = threading_max_levels * threading_indent;
            }
            margin = margin + "px";
            threading_comments[i].dt.css("margin-left",margin);
            threading_comments[i].dd.css("margin-left",margin);
            // Insert comment
            $("dt#"+threading_comments[i].replyto).next().each(function()
            {
                // use indentation level to reconstruct threads
                var cur_level = parseInt($(this).css("margin-left").slice(0,-2));
                // find the next same-level or higher-level comment to insert before
                var next_same_level = null;
                var next_comments = $(this).nextAll("dt");
                for (var j=0 ; j<next_comments.length ; j++)
                {
                    var this_level = parseInt($(next_comments[j]).css("margin-left").slice(0,-2));
                    if (this_level <= cur_level) {
                        next_same_level = $(next_comments[j]);
                        break;
                    }
                }
                if (next_same_level) {
                    next_same_level.before(threading_comments[i].dt);
                    next_same_level.before(threading_comments[i].dd);
                } else { // no same-level or higher-level comment found: insert at the end
                    comments.append(threading_comments[i].dt);
                    comments.append(threading_comments[i].dd);
                }
            });
        }
    }
}
function threading_show_list()
{ // Show comments in list (default)
    var comments = $("#comments dl");
    comments.empty();
    for (var i=0 ; i < threading_comments.length ; i++) {
        // Reset margin-left
        threading_comments[i].dt.css("margin-left","0px");
        threading_comments[i].dd.css("margin-left","0px");
        // Insert comment
        comments.append(threading_comments[i].dt);
        comments.append(threading_comments[i].dd);
    }
}
