// ----- C.P.E. -----
// balls.cpe                  :   Base cpe object litteral
// balls.cpe.elem             :   The Base element that wraps the CPE modal
// balls.cpe.timeout          :   Current Live CPE's commercial timeout length for progress bar/download later
// balls.cpe.on               :   Boolean [true/false] if balls.cpe is in use or not.
// balls.cpe.init             :   Load up a new CPE Ad, and setup for the selected song
// balls.cpe.evnets           :   CPE related Events
// balls.cpe.reset            :   Set to pre-state
// balls.cpe.launch           :   Set all values for related song, make cpe visible, and start Ad
//
// balls.cpe.download
// balls.cpe.download.start   :   Start downloading related track
// balls.cpe.download.stop    :   Stop downloading related track
//
// balls.cpe.getAd            :   Get Ad for CPE playback

!(function (balls){    
    balls.cpe = {
        elem: $("body>section.cpeWashout"),
        timeout: null,
        init:function (target) {
          var self = this; 
          if($(".adVideo .videoFrame").children().length < 1){
            $(".adVideo .videoFrame").jPlayer({
                width:'480px',
                height:'310px',
                supplied:'flv',
                volume: .8,
                muted:false,
                swfPath: '/wp-content/themes/score/js/lib/jquery/plugins/jplayer/js/',
                solution: 'flash, html',
                cssSelectorAncestor: ".adVideo",
                errorAlerts: false,
                warningAlerts: false,
                ready:function(){
                  self.elem.css("top", "0");
                  self.launch(target);
                },
                ended:function () {}
            });
          }else{
            self.launch(target);
          }
          self.events();
        },
        events: function () {
          var base = balls.cpe;
          $("body").on("click", "section.cpeWashout, section.cpeWashout .confirm .okBtn", balls.cpe.reset);
          $("body").on("click", "section.cpeWashout>div", function (e) { e.stopPropagation(); });
        },
        reset: function () { 
          var elem = balls.cpe.elem;
          $(".adVideo .videoFrame").jPlayer("clearMedia");
          //$(".adVideo .videoFrame").jPlayer("destroy");
          //$(".adVideo .videoFrame").children().remove();
          //console.log($(".adVideo .videoFrame"));
          elem.find(".confirm").hide();
          elem.find(".progressBox, .caption").show();
          elem.find(".progress").css("transition", "all 0s ease-in");
          elem.find(".progress").removeClass("go");
          elem.find("img.cpeEndImg").hide();
          $("body>section.cpeWashout").hide();
          clearTimeout(balls.cpe.timeout);
          balls.cpe.timeout = null;
        },
        launch: function () {
          var elem = arguments[0] && typeof arguments[0] == "object"? $(arguments[0]) : null,
          Id = arguments[0] && typeof arguments[0] == "string"? arguments[0] : elem.attr("id"),
          postType = arguments[1]? arguments[1] : elem.attr("data-post-type"),
          title = arguments[2]? arguments[2] : elem.attr("data-title"),
          artist = arguments[3]? arguments[3] : elem.attr("data-artist"),
          length = arguments[4]? arguments[4] : elem.attr("data-length"),
          genre = arguments[5]? arguments[5] : elem.attr("data-genre"),
          src = arguments[6]? arguments[6] : elem.attr("data-src"),
          poster = arguments[7]? arguments[7] : elem.attr("data-img");
          
          //console.log("Elem:"+elem);
          //console.log(elem);

          if(elem == null)return false;
          if(postType == "song"){
            this.elem.find(".songWrapper .albumArt img").attr('src', poster);
            this.elem.find(".songWrapper .title").html(title);
            this.elem.find(".songWrapper .artist").html(artist);
            this.elem.find(".songWrapper .duration").html(length);
            this.elem.find(".songWrapper .genre").html(genre);
          }
          
          this.elem.show();
          this.download.start(postType, Id, src);
        },
        download: { 
          // This needs to start only AFTER the ad watching is done... not in tandum. 
          // There's no way to ensure data integrity and security if we just hold the last bit,
          // and plus we're licensing songs during the CPE experience, not forcing a download. They should get a the 5cents and the option to download, or move on....
          start: function(postType, id, src){
            base = balls.cpe;
            if(!base) return false;
            balls.analytics.track({content:id, action:12});
            $.ajax({url:"/wp-content/plugins/song/api.php", type:"post", data:{"action":"download", "post-type":postType, "content":id},
                  beforeSend: base.getAd,
                  xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.addEventListener("progress", function (e){
                       // console.log(e);
                        if(e.lengthComputable){
                          //compare download to ad completion
                          // use which ever percent is lower.
                          //base.elem.find(".progress").css("width",(e.loaded/e.total)+"%");
                        }
                    }, false);
                    return xhr;
                  }
                }).done(function (response) {
                    //console.log(response);
                    //base.elem.find(".progress").css("width", "100%");
                    /*setTimeout(function () {
                      base.elem.find(".progressBox, .caption").hide();
                      base.elem.find(".confirm").show();
                    }, 30000);*/
                    //send call to DW to add .05,
                }).always().fail();
          },
          stop:{
            //stop download
            //reset 'downloading progress image'
            //clear video
            //hide cpe
          }
        },
        getAd: function () {
          $(".cpeWrapper .videoFrame").jPlayer("pauseOthers");
          var rand = Math.ceil(Math.random()*13),
          base = balls.cpe,
          elem = base.elem,
          rand = (rand < 10)? "0"+rand : rand,
          player = $(".adVideo .videoFrame"),time=0;
          
          elem.find("img.cpeEndImg").attr("src", "http://"+userSession.cdn+"/images/video_bkg_"+Math.floor(Math.random()*6)+".png");
          player.jPlayer("setMedia", {flv:"http://"+userSession.cdn+"/video/cpe/cpe_"+rand+".flv"}).jPlayer("play");
          setTimeout(function () {
            if(!base.timeout){
              player.jPlayer("play");
              time = player.data("jPlayer").status.duration-1;
              //console.log(time);
              time = (time < 30)? 30 : time;
              //console.log(time);
              elem.find(".progress").css("transition", "all "+time+"s ease-in");
              
              elem.find(".progress").addClass("go");
              base.timeout = setTimeout(function () {
                elem.find(".progressBox, .caption").hide();
                elem.find(".confirm").show();
                elem.find("img.cpeEndImg").show();
                clearTimeout(balls.cpe.timeout);
                balls.cpe.timeout = null;
              }, time*1000);
            }
          }, 1000);
          
          return true;
        }
    };
})(window.balls);
