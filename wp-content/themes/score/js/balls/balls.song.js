// Balls Song module
// balls.song                 :   Song main object litteral
// balls.song.api             :   API URI
// balls.song.on              :   Array of executed song methods
// balls.song.init            :   Initilize song module and dependancies [comment]
// balls.song.single          :   Event watchers for song single
// balls.song.archive         :   Event wathcers for song archive
// balls.song.allowDemoRate   :   Turn on demo rating

!(function (balls) {
  balls.song = {
    api:"/wp-content/plugins/song/api.php",
    on: [],
    init: function (){
      if(balls.song.on['init'])return true;
        Util.init("comment", true);
      return this.on['init'] = true;
    },
    single: function (){

      balls.ePlayer.init();
      $("div[data-audio]").each(function(){
        balls.ePlayer.populate($(this).attr("data-audio"));
      });
      
      Util.init("eip");

      if(balls.song.on['single'])return true;
      
      /*$(document).on("ePlayer-pause", function(e){
        console.log("Pause:", e);
      });*/

      /* Page Player Actions*/
      $("body").on("click", ".header .song .waveform .play", function () {
        balls.ePlayer.play($(this).attr("data-audio"));
        $(this).removeClass().addClass("pause");
      });
      $("body").on("click", ".header .song .waveform .pause", function(){
        balls.ePlayer.pause();
        $(this).removeClass().addClass("play");
      });
      $("body").on("click", ".header .song .waveform .stop", function () {
        balls.ePlayer.stop();
        $(this).removeClass().addClass("play");
      });
      /* End Page Player Actions */

      $("body").on("click", ".header .song .buttons .atp", function () {
        balls.player.dialog.add(this.parentNode.parentNode.parentNode.parentNode.parentNode);
      });
      $("body").on("click", ".header .song .buttons .download", function () {
        balls.cpe.init(this.parentNode.parentNode.parentNode.parentNode.parentNode);
      });
      $("body").on("click", ".header .song .buttons .dish", function () {
        var msgs = [
              "Hey! Check out ",
              "Love this track! ",
              "Can't get enough! ",
              "My jam of the day, ",
              "Set to repeat and let the day melt away. ",
              "My face just melted, check out ",
              "Moar! ",
              "This song takes me to outter space, ",
              "OMG THIS TRACK CHANGED MY LIFE! "
        ];
        require(['balls.shout'], function(){
          balls.shout.suggest(
            msgs[Math.floor(Math.random()*msgs.length)] +
            $("body section[id] section.header header h1")[0].innerHTML +
            " by " +
            $("body section[id] section.header header h2 a")[0].innerHTML +
            " " + document.URL
          );
        });
      });
      $("body").on("hover click", ".header .song .rate.able .stars div", function(e){
        if(e.type == "mouseenter"){
          if(!$(this).hasClass("red")){
            for(var x=0;x< parseInt($(this).attr("class"), 10); x++){
              $(".header .song .rate.able .stars div."+(x+1)).addClass("hover");
            }
          }
        }else if(e.type == "mouseleave"){
          $(".header .song .rate.able .stars div").removeClass("hover");
        }else if(e.type == "click"){
          var rating = $(this).attr("class").replace(/hover/, ''),
              id = $(this).closest("[id^='/song/']").attr("id").replace(/\/song\//);
          balls.analytics.vote(id, rating, function(response){
            try{
              response = $.parseJSON(response);
            }catch(e){
							require(['balls.error'], function(){
								balls.error("", "Error parsing response from analytics.vode", {logObject:e, suppressSystem:userSession.isLive});
							});
            }
            if(response.count > 0 && response.full_song){
              for(var x=0;x<rating; x++){
                $(".header .song .rate.able .stars div."+(x+1)).addClass("red");
              }
              toast.success("Thanks for rating this song! You can now find and stream the full song from your <a onclick='$(\"#btn_playlists\").click()'>library</a>");
            }else{
							require(['balls.error'], function(){
								balls.error("Sorry we had some trouble saving  your rating, please try again.", "Bad response from analytics in song.single rate click", {logObject:response, suppressSystem:userSession.isLive});
							});
            }
          });
        }
      });

      return this.on['single'] = true;
    },
    archive: function (){
      if(balls.song.on['archive'])return true;
      
      $("body").on("click", "li.track", function(e){
        $(this).find(".actions").toggleClass("hidden");
        $(this).siblings().find(".actions").addClass("hidden");
      });
      $("body").on("click", "li.track .metaBox div.cpePopUp", function (e) {
        e.stopPropagation();
        balls.cpe.init(this);
      });
      $("body").on("click", "li.track .actions .addToPlaylist", function (e) {
        e.stopPropagation();
        balls.player.dialog.add(this.parentNode.parentNode.parentNode);
      });
      $("body").on("click", ".demoPlayer .rating .links .atp", function (e) {
        e.stopPropagation();
        balls.player.dialog.add(this);
      });
      return this.on['archive'] = true;
    },
    allowDemoRate: function () {
      toast.info("Did you like the demo? Feel free to rate it so you can listen to the whole song!");
      $("body .header .song .rate").hasClass("able")? "" : $("body .header .song .rate").addClass("able");
    }
  };
})(window.balls);
