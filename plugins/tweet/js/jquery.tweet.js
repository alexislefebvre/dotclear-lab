(function($) {
 
  $.fn.tweet = function(o){
    var s = {
      username: ["seaofclouds"],              // [string]   required, unless you want to display our tweets. :) it can be an array, just do ["username1","username2","etc"]
      list: null,                              //[string]   optional name of list belonging to username
      avatar_size: null,                      // [integer]  height and width of avatar if displayed (48px max)
      count: 3,                               // [integer]  how many tweets to display?
      intro_text: null,                       // [string]   do you want text BEFORE your your tweets?
      outro_text: null,                       // [string]   do you want text AFTER your tweets?
      join_text:  null,                       // [string]   optional text in between date and tweet, try setting to "auto"
      auto_join_text_default: "i said,",      // [string]   auto text for non verb: "i said" bullocks
      auto_join_text_ed: "i",                 // [string]   auto text for past tense: "i" surfed
      auto_join_text_ing: "i am",             // [string]   auto tense for present tense: "i was" surfing
      auto_join_text_reply: "i replied to",   // [string]   auto tense for replies: "i replied to" @someone "with"
      auto_join_text_url: "i was looking at", // [string]   auto tense for urls: "i was looking at" http:...
      loading_text: null,                     // [string]   optional loading text, displayed while tweets load
      query: null,                            // [string]   optional search query
      text_less_min: "less than a minute ago",// [string]   text for tweets less than a minute old
      text_one_min: "about a minute ago",     // [string]   text for tweets between one and two minutes old
      text_n_mins: "%t minutes ago",    // [string]   text for tweets less than an hour old
      text_one_hour: "about an hour ago",     // [string]   text for tweets between 60 and 90 minutes old
      text_n_hours: "about %t hours ago",    // [string]   text for tweets less than a day old
      text_one_day: "1 day ago",     // [string]   text for tweets between 24 and 48 hours old
      text_n_days: "%t days ago"    // [string]   text for tweets more than two days old
   };
   
    if(o) $.extend(s, o);

    $.fn.extend({
      linkUrl: function() {
        var returning = [];
        var regexp = /((ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?)/gi;
        this.each(function() {
          returning.push(this.replace(regexp,"<a href=\"$1\">$1</a>"));
        });
        return $(returning);
      },
      linkUser: function() {
        var returning = [];
        var regexp = /[\@]+([A-Za-z0-9-_]+)/gi;
        this.each(function() {
          returning.push(this.replace(regexp,"<a href=\"http://twitter.com/$1\">@$1</a>"));
        });
        return $(returning);
      },
      linkHash: function() {
        var returning = [];
        var regexp = / [\#]+([A-Za-z0-9-_]+)/gi;
        this.each(function() {
          returning.push(this.replace(regexp, ' <a href="http://search.twitter.com/search?q=&tag=$1&lang=all&from='+s.username.join("%2BOR%2B")+'">#$1</a>'));
        });
        return $(returning);
      },
      capAwesome: function() {
        var returning = [];
        this.each(function() {
          returning.push(this.replace(/\b(awesome)\b/gi, '<span class="awesome">$1</span>'));
        });
        return $(returning);
      },
      capEpic: function() {
        var returning = [];
        this.each(function() {
          returning.push(this.replace(/\b(epic)\b/gi, '<span class="epic">$1</span>'));
        });
        return $(returning);
      },
      makeHeart: function() {
        var returning = [];
        this.each(function() {
          returning.push(this.replace(/(&lt;)+[3]/gi, '<span class="heart">♥</span>'))
        });
        return $(returning);
      }
    });

    function parse_date(date_str) {
      // The non-search twitter APIs return inconsistently-formatted dates, which Date.parse
      // cannot handle in IE. We therefore perform the following transformation:
      // "Wed Apr 29 08:53:31 +0000 2009" => "Wed, Apr 29 2009 08:53:31 +0000"
      return Date.parse(date_str.replace(/^([a-z]{3})( [a-z]{3} \d\d?)(.*)( \d{4})$/i, '$1,$2$4$3'));
    }

    function relative_time(time_value) {
      var parsed_date = parse_date(time_value);
      var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
      var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
      if(delta < 60) {
      return s.text_less_min;
      } else if(delta < 120) {
      return s.text_one_min;
      } else if(delta < (45*60)) {
      return s.text_n_mins.replace('%t', parseInt(delta / 60).toString());
      //return (parseInt(delta / 60)).toString() + s.text_n_mins;
      } else if(delta < (120*60)) {
      return s.text_one_hour;
      } else if(delta < (24*60*60)) {
      return s.text_n_hours.replace('%t', parseInt(delta / 3600).toString());
//      return (parseInt(delta / 3600)).toString() + s.text_n_hours;
      } else if(delta < (48*60*60)) {
      return s.text_one_day;
      } else {
      return s.text_n_days.replace('%t', parseInt(delta / 86400).toString());
//      return (parseInt(delta / 86400)).toString() + s.text_n_days;
      }
    }

    function build_url() {
      var proto = ('https:' == document.location.protocol ? 'https:' : 'http:');
      if (s.list) {
        return proto+"//api.twitter.com/1/"+s.username[0]+"/lists/"+s.list+"/statuses.json?per_page="+s.count+"&callback=?";
      } else if (s.query == null && s.username.length == 1) {
        return proto+'//api.twitter.com/1/statuses/user_timeline.json?screen_name='+s.username[0]+'&count='+s.count+'&callback=?';
      } else {
        var query = (s.query || 'from:'+s.username.join(' OR from:'));
        return proto+'//search.twitter.com/search.json?&q='+escape(query)+'&rpp='+s.count+'&callback=?';
      }
    }

    return this.each(function(){
      var list = $('<ul class="tweet_list">').appendTo(this);
      var intro = '<p class="tweet_intro">'+s.intro_text+'</p>';
      var outro = '<p class="tweet_outro">'+s.outro_text+'</p>';
      var loading = $('<p class="loading">'+s.loading_text+'</p>');

      if(typeof(s.username) == "string"){
        s.username = [s.username];
      }

      if (s.loading_text) $(this).append(loading);
      $.getJSON(build_url(), function(data){
        if (s.loading_text) loading.remove();
        if (s.intro_text) list.before(intro);
        $.each((data.results || data), function(i,item){
          var from_user = item.from_user || item.user.screen_name;
	  var from_user_full_name = item.user.name;
          // auto join text based on verb tense and content
          if (s.join_text == "auto") {
            if (item.text.match(/^(@([A-Za-z0-9-_]+)) .*/i)) {
              var join_text = s.auto_join_text_reply.replace('%u','<a href="http://twitter.com/'+from_user+'">'+from_user+'</a>');
              var join_text = s.auto_join_text_reply.replace('%U','<a href="http://twitter.com/'+from_user+'">'+from_user_full_name+'</a>');
            } else if (item.text.match(/(^\w+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&\?\/.=]+) .*/i)) {
              var join_text = s.auto_join_text_url.replace('%u','<a href="http://twitter.com/'+from_user+'">'+from_user+'</a>');
              var join_text = s.auto_join_text_url.replace('%U','<a href="http://twitter.com/'+from_user+'">'+from_user_full_name+'</a>');
            } else if (item.text.match(/^((\w+ed)|just) .*/im)) {
              var join_text = s.auto_join_text_ed.replace('%u','<a href="http://twitter.com/'+from_user+'">'+from_user+'</a>');
              var join_text = s.auto_join_text_ed.replace('%u','<a href="http://twitter.com/'+from_user+'">'+from_user_full_name+'</a>');
            } else if (item.text.match(/^(\w*ing) .*/i)) {
              var join_text = s.auto_join_text_ing.replace('%u','<a href="http://twitter.com/'+from_user+'">'+from_user+'</a>');
              var join_text = s.auto_join_text_ing.replace('%U','<a href="http://twitter.com/'+from_user+'">'+from_user_full_name+'</a>');
            } else {
              var join_text = s.auto_join_text_default.replace('%u','<a href="http://twitter.com/'+from_user+'">'+from_user+'</a>');
              var join_text = s.auto_join_text_default.replace('%U','<a href="http://twitter.com/'+from_user+'">'+from_user_full_name+'</a>');
            }
          } else {
            var join_text = s.join_text.replace('%u','<a href="http://twitter.com/'+from_user+'">'+from_user+'</a>');
            var join_text = s.join_text.replace('%U','<a href="http://twitter.com/'+from_user+'">'+from_user_full_name+'</a>');
          };

          var profile_image_url = item.profile_image_url || item.user.profile_image_url;
          var join_template = '<span class="tweet_join"> '+join_text+' </span>';
          var join = ((s.join_text) ? join_template : ' ');
          var avatar_template = '<a class="tweet_avatar" href="http://twitter.com/'+from_user+'"><img src="'+profile_image_url+'" height="'+s.avatar_size+'" width="'+s.avatar_size+'"'+((s.avatar_alt) ? 'alt="'+s.avatar_alt.replace('%u', from_user)+'" title="'+s.avatar_alt.replace('%u', from_user)+'"' : '')+' /></a>';
          var avatar = (s.avatar_size ? avatar_template : '');
          var date = '<a href="http://twitter.com/'+from_user+'/statuses/'+item.id+'" title="view tweet on twitter">'+relative_time(item.created_at)+'</a>';
          var text = '<span class="tweet_text">' +$([item.text]).linkUrl().linkUser().linkHash().makeHeart().capAwesome().capEpic()[0]+ '</span>';

          // until we create a template option, arrange the items below to alter a tweet's display.
	  if (s.date_after) { 
          list.append('<li>' + avatar + join + text + '<br />' + date + '</li>');
	  } else {
          list.append('<li>' + avatar + date + join + text + '</li>');
	  };

          list.children('li:first').addClass('tweet_first');
          list.children('li:odd').addClass('tweet_even');
          list.children('li:even').addClass('tweet_odd');
        });
        if (s.outro_text) list.after(outro);
      });

    });
  };
})(jQuery);
