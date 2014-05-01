// Balls eplayer - HTML5 audio player
// balls.ePlayer                :   Main ePlayer object litteral
// balls.ePlayer.player         :   Main player object and DOM element
// balls.ePlayer.preProc        :   preprocessor for loading in data before the player is executed or if the main player is being used while a new player object is found
// balls.ePlayer.preProcQueue   :   Queue for preprocessing
// get balls.ePlayer.type       :   retrieve extention for browser based off available HTML5Player.canPlayType method
// balls.ePlayer.init           :   Main initilization of the HTML5 Player into the browser
// balls.ePlayer.populate       :   Populate metadata for a new track by getting it into the preprocessing queue
// balls.ePlayer.play           :   Play a specific track by sourc - at - length (src, startTime, playTime)
// balls.ePlayer.ended          :   functionality for the callback when the HTML5 ePlayer stoped playing a specific track (good for rating callups for demos etc)
// balls.ePlayer.stop           :   Bring a stop to balls.ePlayer.player and clear data in the player
// balls.ePlayer.pause          :   Pause the HTML5 balls.ePlayer.player
// balls.ePlayer.seek           :   Seeks for the current track in the HTML5 balls.ePlayer.player
// balls.ePlayer.timeUpdate     :   callback for tiemupdate from balls.ePlayer.player object
// balls.ePlayer.volumeSet      :   sets volume of the HTML5 ePlayer to match the userbar/jPlayer volume

!(function (balls) {
  balls.ePlayer = {
    player: new Audio(),
    preProc: new Audio(),
    preProcQueue: [],
    get type(){ return (balls.ePlayer.player.canPlayType("audio/mpeg") == "probably" || balls.ePlayer.player.canPlayType("audio/mpeg") == "maybe")? "mp3" : "ogg";},
    init: function (){
      balls.ePlayer.preProc.onloadedmetadata = function (e) {
        //console.log(e);
        if(e.target.src != ""){
          var src = e.target.src.substr(0, e.target.src.lastIndexOf(".")),
              elem = $("div[data-audio='"+src+"']")[0],
              wrapper = $(elem).parent(),
              duration = wrapper.find(".length"),
              progress = wrapper.find(".progress"),
              currTime = wrapper.find(".progressTime");
          currTime.html((Math.floor(progress[0].offsetWidth*e.target.duration/wrapper.find("img")[0].offsetWidth)).toString().toHHMMSS());
          duration.html(e.target.duration.toString().toHHMMSS());
        }
      };
      setInterval(function() {
        if(balls.ePlayer.preProcQueue.length > 0){
          balls.ePlayer.preProc.src = balls.ePlayer.preProcQueue.pop();
          balls.ePlayer.preProc.load();
        }
      }, 500);
      balls.ePlayer.volumeSet();//grab volume of main jPlayer
    },
    populate: function (src) {
      if(src == null) return false;
      balls.ePlayer.preProcQueue.push(src+"."+balls.ePlayer.type);
    },
    play: function (src, startTime, playTime) {
      var src = src? src+"."+balls.ePlayer.type : null,
          startTime = startTime? parseInt(startTime, 10) : 0,
          playTime = playTime? parseInt(playTime, 10) : 0;
      
      //set progress bar response
      //allow seeking with progress bar

      if(src && balls.ePlayer.player.src != src && balls.ePlayer.player.src != src){
        balls.ePlayer.stop();
        balls.ePlayer.player.src = src; 
        balls.ePlayer.player.load();
        playTime  = playTime? playTime : balls.ePlayer.player.duration;
        
        balls.ePlayer.player.addEventListener('loadedmetadata', function () {
            balls.ePlayer.player.currentTime = startTime;
        }, false);
        balls.ePlayer.player.addEventListener('timeupdate', this.timeUpdate, false);
        balls.ePlayer.player.addEventListener('ended', this.ended, false);
        balls.ePlayer.player.play();
        
        if(playTime && playTime > 0){
          setTimeout(this.pause, playTime*1000);
        }
      }else{
        balls.ePlayer.player.play();
      }
    },
    ended: function (e) {
      src = e.target.currentSrc
      if((/\/demo\//).test(src)){
        //this demo is dome playing: allow rating meow.
        balls.song.allowDemoRate();
      }
    },
    stop: function () {
      balls.ePlayer.player.pause();
      balls.ePlayer.player.src = "";
      balls.ePlayer.player.load();
    },
    pause: function () {
      balls.ePlayer.player.pause();
      balls.event.dispatch("ePlayer-pause");
    },
    seek: function (src) {
      if(this.player.src == src){
        
      }
    },
    timeUpdate: function () {
      var elems = $("div[data-audio='"+balls.ePlayer.player.src.substr(0, balls.ePlayer.player.src.lastIndexOf("."))+"']");
      elems.each(function(){
        var elem = $(this),
            wrapper = $(this).parent()[0],
            progress = $(wrapper).find("div.progress")[0];
            currentTime = $(wrapper).find("span.progressTime")[0];
            duration  = $(wrapper).find("div.length")[0];
        if(progress){
          progress.style.width = Math.ceil((elem.siblings("img")[0].offsetWidth*balls.ePlayer.player.currentTime/balls.ePlayer.player.duration))+"px";
        }
        if(currentTime){
          currentTime.innerHTML = balls.ePlayer.player.currentTime.toString().toHHMMSS();
        }
        if(duration){
          duration.innerHTML = balls.ePlayer.player.duration.toString().toHHMMSS();
        }
      });
    },
    volumeSet: function () {
      var baseVol = $("#jquery_jplayer_N").data("jPlayer").options.volume || null;
      balls.ePlayer.player.volume = baseVol? baseVol : 0.3;
    }
  };
})(window.balls);
