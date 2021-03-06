/***********
 * Balls Video Module
 *
 * video.init()                         : Module Initialization calls this.loadYTAPI and this.events
 * video.events()                       : Creates Gallery Click events and 'ballsModalClose' event watcher
 * video.loadYTAPI(function)            : Loads YouTube iFrame API sets argument function to YouTube API ready function.
 * video.getYouTubeURL(videoID)         : creates YouTube embed URL
 * video.getYouTubeDataURL(videoID)     : gets URL for YouTube legacy data API
 * video.getVideoData(videoID)          : returns YouTube JSON object using getYouTubeDataURL
 * 
 * video.player           { get; }      : Creates or returns YouTube Player object from iframe#yt-player
 * video.playerExists     { get; }      : Boolean, true if YouTube Player exists, false otherwise
 * video.deletePlayer()                 : Destroys YouTube player object
 * video.stopVideo()                    : Stops currently playing video
 *
 * video.videoIDs         { get; }      : Returns an array of video IDs found in the video gallery or video explode depending on which exists
 * video.openGallery(userID, videoID)   : Opens video gallery for user == userID. Loads video if videoID exists
 * video.load(videoID)                  : Plays videoID in the video gallery, if no video id is passed in it auto selects the first video in the list of videos
 *
 * video.parseURL(input)                : Takes string input and parses for YouTube video ids, returns an Array of found ids, or empty Array.
 * 
 * video.add(input)                     : Takes string input that is passed to video.parseURL(). Loops through all new video ids displaying a confirm dialog and the video. 
 *                                        After all videos have been approved or denied it calls user API with a single list of the old and new video ids.
 * video.delete(videoID)                : Shows confirm delete dialog if user clicks yes it calls user API with the list of video ids minus the deleted video id
 * 
 **********/
!(function (balls){
balls.video = {
  init:function(){
    // YouTube iframe API Ready
    this.loadYTAPI(function(){/*Function required for YouTube API*/});
    
    // Load Events
    this.events();
  },
  events:function(){
    var self = this;
    // Gallery Events
    // Delete Open Video or Delete video from list
    $("body").on("click", "section.videoGallery .infoPane header .delete, section.videoGallery .infoPane ul li .delete", function(e){
      // get videoID of open video
      e.preventDefault();
      e.stopPropagation();
      var videoID = (e.target.parentNode.tagName === 'HEADER') ? 
                      e.target.parentNode.nextElementSibling.querySelector(".selected").getAttribute("data-ytid") :
                      e.target.parentNode.getAttribute("data-ytid");
      if(videoID != null && videoID !== ""){
        self.delete(videoID);
        if(e.target.parentNode.tagName === 'HEADER' || (e.target.parentNode.tagName === 'LI' && e.target.parentNode.className.search("selected") > -1)){
          $("section.videoGallery .infoPane ul li").first().click();
          document.querySelector("section.videoGallery .infoPane ul li.selected").scrollIntoView();
          self.player.pauseVideo();
        }
      }else{
				balls.error("Could not find correct video id, please try again.", "Video ID was "+videoID, {logObject:{'this':this, 'event':e}, suppressSystem:userSession.isLive});
      }
    });
    // Open Video
    $("body").on("click", "section.videoGallery .infoPane ul li", function(e){
      e.preventDefault();
      e.stopPropagation();
      var videoID = e.target.hasAttribute("data-ytid") ? e.target.getAttribute("data-ytid") : $(e.target).closest("li")[0].getAttribute("data-ytid");
      if(videoID != null && videoID !== ""){
        self.load(videoID);
      }else{
				balls.error("Could not find correct video id, please try again.", "Video ID was "+videoID, {logObject:{'this':this, 'event':e}, suppressSystem:userSession.isLive});
      }
    });
    // Clear ytPlayer on Gallery Close
    $(document).on("ballsModalClose", function(e){
      if(e.originalEvent.memo.modalClass && e.originalEvent.memo.modalClass.search("videoGallery") > -1){
        balls.video.deletePlayer();
      }
    });
  },
  // YouTube Things
  loadYTAPI:function(onReady){
    if(typeof YT === 'undefined'){
      require(['https://www.youtube.com/iframe_api'], function(){
        window.onYouTubeIframeAPIReady = onReady;
      });
    }else{ balls.error("", "YouTube API Already Loaded", {suppressSystem:userSession.isLive}); }
  },
  getYouTubeURL:function(videoID){
    return "https://www.youtube.com/embed/" +
            videoID +
            "?version=3&enablejsapi=1" + // enable version 3 JS API
            "&autoplay=1"       + // play video once loaded
            "&autohide=1"       + // hide controls, reappear on hover
            "&disablekb=1"      + // disable keyboard controls
            "&iv_load_policy=3" + // disable annotations
            "&rel=0"             + // don't show related videos
            "&origin="+window.location.protocol+"//"+window.location.hostname;
  },
  getYouTubeDataURL:function(videoID){
    return "https://gdata.youtube.com/feeds/api/videos/"+videoID+"?v=2&alt=json";
  },
  getVideoData:function(videoID){
    var self = this;
    if(videoID == null){
      try{
        videoID = self.player.getVideoData().video_id;
      }catch(e){
        return null;
      }
    }
    return (function(){
      var json = null;
      $.ajax({
        'async':false,
        'global':false,
        'url': self.getYouTubeDataURL(videoID),
        'dataType':"json",
        'success':function(data){ json = data; }
      });
      return json;
    })();
  },
  get player(){
    if(typeof YT === 'undefined'){
      this.loadYTAPI(function(){});
      return this.player;
    }
    if(this.ytPlayer){
      return this.ytPlayer;
    }else{
      this.ytPlayer = new YT.Player('yt-player');
      return this.ytPlayer;
    }
  },
  get playerExists(){
    return !(typeof this.ytPlayer === 'undefined');
  },
  deletePlayer:function(){
    return delete this.ytPlayer;
  },
  stopVideo:function(){
    try{
      if(this.playerExists){
        this.player.stopVideo();
      }
    }catch(e){
      balls.error("", "Exception caught on video.stopVideo()", {logObject:{'exception':e}, suppressSystem:userSession.isLive});
    }
  },
  // END YouTube Things
  get videoIDs(){
    var i, ids = [],
        lis = (document.querySelector("section.videoGallery")) ?
          document.querySelectorAll(".videoGallery .infoPane ul li") :
          document.querySelectorAll(".explorer.artist .portlet.videos .explode .wrapper ul li.video");

    if(lis.length === 0){
      return ids;
    }
    for(i=0; i < lis.length; i++){
      ids.push(lis[i].getAttribute("data-ytid"));
    }

    ids = ids.filter(function(v){ return !(v === ""); });
    return ids;
  },
  openGallery:function(userID, videoID){
    var html = "";
    if(userID == null){
			balls.error("Could not open video gallery for unknown artist", "video.openGallery was not passed the right information", {logObject:{'userID':userID, 'videoID':videoID}, suppressSystem:userSession.isLive});
      return false;
    }
    $.ajax({url:balls.api, data:{action:"template", "post-type":"video", "template":"gallery", content:userID}, type:"post"})
      .done(function(response){
        response = $.parseJSON(response);
        if(response && response.html){
          if(response.html.search("<li") === -1){
            balls.modal.alert("There are no videos for this artist! :(");
            return false;
          }
          if(videoID == null || typeof videoID !== 'string'){
            videoID = document.querySelector("section.main explorer.artist .portlet.videos .explode .wrapper ul li.video").getAttribute("data-ytid");
          }
          balls.modal.html(response.html);
          balls.video.deletePlayer();
          balls.video.load(videoID);
          $(".videoGallery .infoPane ul li.selected")[0].scrollIntoView();
        }
      })
      .fail(function(response){
				balls.error("Failed to open Video Gallery, please try again.", "Fail on api call to get video gallery template", {logObject:{'response':response, 'userID':userID, 'videoID':videoID}, suppressSystem:userSession.isLive});
      });
  },
  load:function(videoID){
    var li, info = $(".videoGallery .infoPane header"), self = this;
    if(info == null){
			balls.error("I'm sorry but you can only watch videos in the video gallery, and I can't find it :(", "Couldn't find info pane on video load", {logObject:{'videoID':videoID, 'info':info}, suppressSystem:userSession.isLive});
      return false;
    }
    if(videoID == null){
      videoID = document.querySelector(".videoGallery .infoPane ul li").getAttribute("data-ytid");
    }

    // Deselect Selected LI
    $(".videoGallery .infoPane ul li.selected").removeClass("selected");
    // Select new LI 
    li = $(".videoGallery .infoPane ul li[data-ytid="+videoID+"]").addClass("selected");

    // Load meta data, title, description into header
    info.find("h2").html(li.find(".title").html());
    info.find("p").html(li.find(".description").html());

    try{
      if(self.playerExists){
        self.player.loadVideoById(videoID);
      }else{
        // set src and create ytPlayer
        document.querySelector(".videoGallery .videoPane iframe").setAttribute("src", self.getYouTubeURL(videoID));
        self.player;
      }
    }catch(e){
			balls.error("Something broke. :/ Video module failure.....rebootering...", "Exception caught on video.load with id:"+videoID, {logObject:{'exception':e, 'videoID':videoID, 'li':li, 'info':info, 'ytPlayer':self.ytPlayer}, suppressSystem:userSession.isLive});
      self.deletePlayer();
    }
  },
  parseURL:function(input){
    var rtn = [];
    if(input && typeof input === 'string'){
      input.split(/ |,/).forEach(function(s){
        if(s.length === 11 && (/[A-Za-z\d-_]{11}/).test(s)){
          rtn.push(s);
        }else if((/youtube|youtu\.be/).test(s)){
          var rgx = /((\?v=|&v=|\/v\/|embed\/|\/e\/)|(youtu\.be\/)){1}([A-Za-z\d\-_]{11})/ig;
          s.match(rgx).forEach(function(v){
            rtn.push(v.slice(-11));
          });
        }
      });
    }/*else{ return null; }*/
    return rtn;
  },
  add:function(input){
    var self = this, first = true, loopConfirm,
        vid, newCount, newIDs = self.parseURL(input),
        currIDs = self.videoIDs;

    if(newIDs == null || !(newIDs instanceof Array) || newIDs.length === 0){
			balls.error("I'm sorry, I couldn't find any video IDs from what you sent me :-/ please try again.", "List of new IDs was null, not array or length 0", {logObject:{'newIDs':newIDs, 'currIDs':currIDs, 'input':input}, suppressSystem:userSession.isLive});
      return false;
    }

    newCount = newIDs.length;
    loopConfirm = function(arr){
        vid = arr.shift();
        if(first){
          self.deletePlayer();
          balls.modal.html('<iframe id="yt-player" type="text/html" width="640px" height="360px" src="'+self.getYouTubeURL(vid)+'" frameborder="0" style="margin:5px;"></iframe>').style.marginTop = "-390px";
          self.player;
          first = false;
        }else{
          self.player.loadVideoById(vid);
        }
        balls.modal.confirm("Is this a video you want to add to your profile?", function(yes){
          if(yes){
            currIDs.push(vid);
          }else{
            // don't add id
          }
          if(arr.length !== 0){
            loopConfirm(arr);
          }else{
            self.stopVideo();
            self.deletePlayer();
            balls.modal.close();
						currIDs = currIDs.toString();
						$.ajax({url:"/wp-content/plugins/user/api.php", data:{'action':'set_videos', 'data':currIDs, 'id':userSession.id}, type:"post"})
              .done(function(response){
                response = $.parseJSON(response);
                if(response && response.data){
                  var div = document.createElement("div");
                  div.innerHTML = response.html;
                  document.querySelector(".main .explorer.artist .portlet.videos").innerHTML = div.querySelector(".portlet.videos").innerHTML;
                }
                try{
                  toast.success("Successfully added "+newCount+" "+(newCount === 1 ? 'video' : 'videos'));
                }catch(e){
									balls.error("Successfully added "+newCount+" "+(newCount === 1 ? 'video' : 'videos')+"", "Exception on call to toast.success", {logObject:{'exception':e, 'response':response}, suppressSystem:userSession.isLive});
                }
              })
              .fail(function(response){
								balls.error("Error sending new "+(newCount === 1 ? 'video' : 'videos')+" to the server, please try again later.", "Failed API Call in video.add()", {logObject:{'response':response}, suppressSystem:userSession.isLive});
              })
          }
        }).style.marginTop = "-10px";
    };
    loopConfirm(newIDs);
  },
  'delete':function(videoID){
    var self = this, ids = this.videoIDs;
    if(videoID == null){
			balls.error("Didn't find video id, please try again. If the problem persists, try refreshing the page.", "video.delete was called but not passed a video ID", {logObject:{'videoID':videoID, 'ids':ids}, suppressSystem:userSession.isLive});
      return false;
    }

    // Delete Dialog
    balls.modal.delete("Are you sure you want to delete this video?", function(yes){
      if(yes){
        // remove from video id list
        ids = ids.filter(function(v){
          return !(v === videoID);
        }).toString();
				$.ajax({url:"/wp-content/plugins/user/api.php", data:{'action':'set_videos', 'data':ids, 'id':userSession.id}, type:"post"})
          .done(function(response){
            var ul;
            response = $.parseJSON(response);
            if(response){
              // remove from explode
              if(document.querySelector(".explorer.artist")){
                ul = document.querySelector(".explorer.artist .portlet.videos .explode .wrapper ul");
                ul.removeChild(ul.querySelector('li.video[data-ytid="'+videoID+'"]'));
								$(".main .explorer.artist .portlet.videos .overlay .caption .number").html("("+ids.split(",").length+")");
              }
              // remove from gallery
              if(document.querySelector("section.videoGallery")){
                ul = document.querySelector("section.videoGallery .infoPane ul");
                ul.removeChild(ul.querySelector('li[data-ytid="'+videoID+'"]'));
              }
            }
            try{
              toast.success("Video successfully removed");
            }catch(e){
							balls.error("Video successfully removed", "Exception on call to toast.success", {logObject:{'exception':e, 'response':response}, suppressSystem:userSession.isLive});
            }
          })
          .fail(function(response){
						balls.error("There was a problem deleting that video. Please try again. If the problem persists try refreshing the page.", "Failed api call in video.delete", {logObject:{'response':response}, suppressSystem:userSession.isLive});
          });
      }else{
        try{
          toast.info("Keeping that video right where it was.");
        }catch(e){
					balls.error("Keeping that video right where it was.", "Exception caught on call to toast.info", {logObject:{'exception':e}, suppressSystem:userSession.isLive});
        }
      }
    });
  }
};
})(window.balls);
