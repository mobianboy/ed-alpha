// Balls Analytics
// balls.analytics          :   Main Object Litteral for balls analytics
// balls.analytics.init     :   Account balls module initilization
// balls.analytics.track    :   Track a certain analytics for a track/song being played
// balls.analytics.vote     :   Vote from 1-5 on a postID (probably a song)
//
!(function () {
  balls.analytics = {
    /*one: login,
    two: logout,
    three: ArticleView,
    four: prof view,
    five: follow,
    six: friend,
    seven: shout,
    eight: comment,
    nine: player play,
    ten: demo player,
    eleven:rate,
    twleve:CPE - init,
    thirteen: CPE - Finish (5c),
    fourteen: download,*/
    init: function() {
  
    },
    track: function () {
      var data = (arguments[0])? arguments[0] : null, //item they're taking action on.
      cb = (arguments[1] && typeof arguments[1] == "function")? arguments[1] : function () {};
      $.ajax({url:'/wp-content/plugins/reward/api.php', type:"post", data:data})
        .done(function (response) { if(cb)cb(response) })
        .fail(function(response){
					require(['balls.error'], function(){
						balls.error("", "Failed call to reward api, see below for response", {logObject:response, suppressSystem:userSession.isLive});
					});
        });
    },
    vote: function(){
      var id = (arguments[0])? arguments[0] : null, //item they're taking action on.
      rating = (arguments[1])? arguments[1] : null,
      cb = (arguments[2] && typeof arguments[2] == "function")? arguments[2] : null;
      this.track({action:11, value:rating, content:id}, cb); 
    }
  };
})(window.balls);
