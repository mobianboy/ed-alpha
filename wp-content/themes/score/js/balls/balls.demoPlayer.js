// jPlayer for demo tacks. (Needs replacing with ePlayer
$('.demoPlayer .player .object').jPlayer({
      swfPath: '/wp-content/themes/score/js/lib/jquery/plugins/jplayer/js/',
      solution: 'html',
      supplied: 'mp3, oga',
      preload: 'metadata',
      volume: $('#jquery_jplayer_N').data("jPlayer").options.volume,
      muted: false,
      cssSelectorAncestor: '.demoPlayer',
      addClasses:false,
      seekPercent:10,
      cssSelector:{
        seekBar: ".progressBar",
        playBar: ".progressBar .bar"
      },
      size:{width:"0px", height:"0px"},
      errorAlerts: false,
      warningAlerts: false,
      ready: function () {
        if(window.location.href.indexOf("eric") > -1 ||
          window.location.href.indexOf("sdk") > -1 ||
          window.location.href.indexOf("svn") > -1 ||
          window.location.href.indexOf("ryan") > -1 ||
          window.location.href.indexOf("jordan") > -1 ||
          window.location.href.indexOf("jaime") > -1 ){}
        else{
          $(this).data("jPlayer").seekBar = function(){};
        }  
      },
      play: function () {
        $(this).jPlayer("pauseOthers");
      },
      ended: function (){
        var id = arguments[0].currentTarget.parentNode.parentNode.parentNode.parentNode.attributes['data-content'].value;
        balls.demoPlayer.rate(id);
        balls.analytics.track({content:id, action:10});
      },
      timeupdate: function () {
        var curTime = $('.demoPlayer .player .object').data('jPlayer').status.currentTime,
        totalTime = $('.demoPlayer .player .object').data('jPlayer').status.duration;
        timeLeft = Math.floor(totalTime-curTime);
        $(".demoPlayer .player .timeRemaining .time").html(timeLeft);
      }
});
// Balls demoPalyer
// balls.demoPlayer           :   Main object for song demo player
// balls.demoplayer.init      :   Demoplayer initilization for events
// balls.demoPlayer.cleanup   :   Demoplayer reset and cleanup method
// balls.demoPlayer.events    :   DOM mapped events on the body that watch for a delegate action
// balls.demoPlayer.open      :   Closes other widgets and playlists and opens demo player
// balls.demoPlayer.close     :   Closes demo player
// balls.demoPlayer.populate  :   Fill in DOM elements with values for new track (song DOM object, event);
// balls.demoplayer.rate      :   Rating population on demo player and rate demo API handler method
!(function (balls) {    
    balls.demoPlayer = {
      init:function(){
        this.events();
        balls.follow.init();
      },
      cleanup: function () {
        $('.demoPlayer .player .object').jPlayer("stop");
        $('.demoPlayer .albumArt img').attr("src", "");
        $('.demoPlayer .wrapper .song a').html("").attr("data-content", "").attr("href", "/song/");
        $('.demoPlayer').attr("data-content", "");
        $('.demoPlayer .wrapper .artist a').html("").attr("data-content", "").attr("href", "/profile/");
        $(".demoPlayer .rating").remove();
        $('.demoPlayer .wrapper .genre').html("<span>Genre: </span>");
        //this.events();
      },
      events:function () {
        var self = this;
        $("body").on("click", ".explorer ul>li[data-owned='false'] div.play, .dishPicks ul>li[data-owned='false'] div.play", function (e){
          e.preventDefault();
          self.open();
          self.populate($(this).parent().parent().parent(),"play");
        });
        $("body").on("click", ".explorer .song .options ul li a.demo", function (e) {
          e.preventDefault();
          self.open();
          self.populate($(this).parent().parent().parent().parent().parent(), "play");
        });
        $("body").on("click",".demoPlayer .player .playBtn", function(e){
          $('.demoPlayer .player .object').jPlayer("pauseOthers");
          if($(this).find(".play").hasClass("ing")){
            $(".demoPlayer .player .play").removeClass("ing");
            $('.demoPlayer .player .object').jPlayer("pause");
          }else{
            $(".demoPlayer .player .play").addClass("ing");
            $('.demoPlayer .player .object').jPlayer("play");
          }
        });
        $("body").on("hover click", ".demoPlayer .rating .stars div", function(e){
          if(e.type == "mouseenter"){
            for(var x=0;x< parseInt($(this).attr("class"), 10); x++){
              $(".demoPlayer .rating .stars div."+(x+1)).addClass("hover");
            }
          }else if(e.type == "mouseleave"){
             $(".demoPlayer .rating .stars div").removeClass("hover");
          }
        });
        $("body").on("click", ".demoPlayer .btns .close", self.close);
        $('body').on('click', '.demoPlayer .rating .stars div', function () {
          var rating = $(this).attr("class").replace(/hover/, ""),
          id = $(".demoPlayer").attr("data-content");
          balls.analytics.vote(id, rating, function (response) {
            try{
              response = $.parseJSON(response);
            }catch(e){
							balls.error("", "Exception parsing JSON", {logObject:{'response':response, 'e':e}, suppressSystem:userSession.isLive});
              return false;
            }
        
            if(response.count > 0 && response.full_song){
              $(".explorer ul li#"+id+" .slideUpBox .actions .play, .dishPicks ul li#"+id+" .slideUpBox .actions .play").addClass("addToPlaylist").removeClass("play");
              $(".explorer .portlet.songs .explode ul li#"+id+" .options .demo").addClass("queue").removeClass("demo");
              $(".demoPlayer .rating .caption").html("Thank You For Rating This Demo");
              $(".demoPlayer .rating .stars div:nth-child(-n+"+rating+")").addClass("red");
              $(".demoPlayer").off("hover mouseenter mouseleave", ".rating .stars div");
              $(".demoPlayer .rating .links").show();
              elem = ($(".explorer ul li#"+id+".song").length > 0)? $(".explorer ul li#"+id+".song") : $(".explorer ul li#"+id+".track") ;
              elem.attr("data-src", response.full_song).attr("data-owned", "true");
            
              toast.success("Thanks for rating this demo! You can now find and stream the full song from your <a onclick='$(\"#btn_playlists\").click()'>library</a>");

              //populate 'add to playlist' link
              $(".demoPlayer .rating .links .atp")
                .attr("data-artist", elem.attr("data-artist"))
                .attr("data-title", elem.attr("data-title"))
                .attr("data-src",elem.attr("data-src"))
                .attr("data-img", elem.attr("data-img"))
                .attr("data-content", id);
              balls.playlist.song.add({origin:elem});
            }else{
							balls.error("There was a problem saving your rating, please try again.");
            }
          });
        });
      },
      open: function () {
        balls.demoPlayer.cleanup();
        //close normal player
        if($("#the_playlists").hasClass("open")){
          $("#the_playlists").removeClass("open");
        }
        $('.demoPlayer').addClass("open"); 
        $('div.sortable div#the_playlists').removeClass("open");
      },
      close: function () {
        $('.demoPlayer').removeClass("open");
        balls.demoPlayer.cleanup();
      },
      populate : function () {
        var obj = (arguments[0])? arguments[0] : null,
        evnt = (arguments[1])? arguments[1] : null;
        if(obj == null)return false;
        
        $('.demoPlayer .player .object').jPlayer("setMedia", {
          mp3:obj.attr("data-src")+".mp3",
          oga:obj.attr("data-src")+".ogg"
        });
        $('.demoPlayer').attr("data-content", obj.attr("id"));
        $('.demoPlayer .context>.albumArt img').attr("src", obj.attr("data-img"));
        $('.demoPlayer .wrapper .song a').html(obj.attr("data-title")).attr("data-content", obj.attr("id")).attr("data-post-type", "song").attr("href", "/song/"+obj.attr("data-title"));
        $('.demoPlayer .wrapper .artist a').html(obj.attr("data-artist")).attr("data-content", obj.attr("data-owner")).attr("data-post-type", "user").attr("href", "/profile/"+obj.attr("data-artist"));
        $('.demoPlayer .wrapper .genre').html("<span>Genre: </span>").append("<span>"+obj.attr("data-genre")+"</span>"); 
        if(evnt != null){
          $('.demoPlayer .player .object').jPlayer(evnt);
          if(evnt == "play")
            $(".demoPlayer .player .play").addClass("ing");
        }  
      },
      rate: function (){
        var id = (arguments[0])? arguments[0] : null,
        self = this;
        if(id == null)return false;
        require(['balls.follow'], function(){
          //async call 1
          balls.follow.get($(".demoPlayer .context .wrapper .artist a").attr("href").split("/")[2], function(following){
            //On response get rate.php and populate
            $.ajax({url:"/wp-content/themes/score/userbar/remote/rate.php"})
              .done(function (response) {
                $('.demoPlayer').append(response);
                var track = $(".explorer ul li.track#"+id);
                
                if(following && following.toString() != "true"){
                  $('.demoPlayer .rating .links').children().first().removeClass("hidden");
                  $('.demoPlayer .rating .links .follow').attr("data-content", $(".explorer ul li.track#"+id+" div.metaBox .meta a.artist").attr("href"));
                }

                //populate album art, artist, and song
                $(".demoPlayer .rating .preview>.albumArt img").attr("src", $('.demoPlayer .context>.albumArt img').attr("src"));
                $(".demoPlayer .rating .preview .artist").html($('.demoPlayer .wrapper .artist').html());
                $(".demoPlayer .rating .preview .song").html($('.demoPlayer .wrapper .song').html());
              });
          });
        });
      }
    };
})(window.balls);
