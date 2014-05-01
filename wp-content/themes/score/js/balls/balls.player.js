//Require jQuery, then the jPlayer
//Find jPlayer elments

// Build balls extentions
// balls.playlists                      :   Empty Array
// balls.player                         :   Empty Object
// balls.player.init                    :   Get player and playlist data and load up.

// balls.player.state                   :   Player State Object
//                    .get              :   Return current player/playlist state as an object {}
//                    .save             :   Save state to DB
//                    .load             :   Pull last Saved state of player from DB

// balls.player.dialog                  :   Dialog object
//                    .elem             :   Jquery object
//                    .cleanUp          :   Destroy children of *.elem
//                    .add              :   Display add-song dialog
//                    .createPlaylist   :   Display create-playlist dialog
//                    .renamePlaylist   :   Rename the playlist

// balls.playlist                       :   Empty Object
//                .init                 :   Initilize Playlist
//                .events               :   Playlist events
//                .active               :   Set the active playlist object for the player
//                .order                :   Order object for Playlist
//                  .save               :   Save playlist order to DB
//                .format               :   Format data into jPlayer recognizable object

// balls.playlist.load                  :   Load playlist by ID from server (state/ID, callBack)

// balls.playlist.build                 :   Build loaded playlist into the DOM/HTML with all it's data

// balls.playlist.song                  :   Main playlist Song manipulation object
//                    .add              :   Add song to playlist(mediaObj, playNow, ID)
//                    .remove           :   Removes element and song (elem)

// Everything else is global event manipulation or player event manipulation.
    var balls = window.balls;
    $('#jquery_jplayer_N').jPlayer({
          swfPath: '/wp-content/themes/score/js/lib/jquery/plugins/jplayer/js/',
          solution: 'html',
          supplied: 'mp3, oga',
          //preload: 'metadata',
          muted: false,
          verticalVolume:true,
          cssSelectorAncestor: '.jp-controls-holder',
          ready: function () {
            $("div#the_player .jp-jplayer").css("top", "100px");   
            //set volume
            $(this).jPlayer("option", "volume", ".8");
            $(this).find(".jp-volume-bar-value").css("height", "80%");
            balls.playlist.init();
          },
          ended: function(){
            //console.log($(this).data("jPlayer").status.media.id);
            balls.analytics.track($(this).data("jPlayer").status.media.id, 10, function(){
              //console.log("Played: "+$(this).data("jPlayer").status.media.title+" by: "+$(this).data("jPlayer").status.media.artist) 
            });
            return true;
          },
          errorAlerts: false,
          warningAlerts: false
    });

    balls.playlists = [];
    balls.player = {};

    balls.player.init = function () {
      $.ajax({url:"/wp-content/plugins/playlist/api.php", data:{"action":"get"}, type:"post"}).done(function(results){
        try{
          results = $.parseJSON(results);
        }catch(e){
          console.log(e);
          return false;
        }
        var active, I;
        for(I in results){
          if(results.hasOwnProperty(I)){
              songsArray = balls.playlist.format(results[I].songs);
              balls.playlists["_"+results[I].ID] = { songs: ((songsArray.length > 0)? songsArray : []),id:results[I].ID, title:results[I].post_title, reload:false };
          }
        }
        balls.player.state.load();
        return;
      });
    }; 
    balls.player.state = {
      current:{ 
        activeList : null,
        lastPlayedSong : null,
        sortOrder : null,
        shuffle : 0,
        repeat : 0,
        timeStamp : 0,
        volume : ".3",
        index  : 0
      },
      set: function () {
        var active = $(".active_playlist ul.active");
        this.current.activeList = parseInt(active.attr("id").split("_")[1], 10);
        this.current.lastPlayedSong = (balls.playlist.active.playlist[balls.playlist.active.current]) ? balls.playlist.active.playlist[balls.playlist.active.current].id : 0;
        this.current.sortOrder = active.attr("data-sort").toString(); //"artists/title/album && ASC DESC"
        this.current.shuffle = balls.playlist.active.shuffled || false;
        this.current.repeat = balls.playlist.active.loop || false;
        this.current.timeStamp = $("#jquery_jplayer_N").data("jPlayer").status.currentTime;
        this.current.volume = $("#jquery_jplayer_N").data("jPlayer").options.volume;
        this.current.index  = balls.playlist.active.current;
      },
      save: function () {
        var data = JSON.stringify(balls.player.state.current);
        $.ajax({url:"/wp-content/plugins/playlist/api.php", type:"post", data:{"action":"set_state", "data":data }})
          .done(function (response) {
          
          });
      },
      load: function () {
        //pull state of player on init
        $.ajax({url:"/wp-content/plugins/playlist/api.php", type:"post", data:{"action": "get_state"} })
          .done(function (response) {
            response = $.parseJSON($.parseJSON(response));
            balls.player.state.current = response;
            if(response && $(".playlist_list li a#"+response.activeList)){
              balls.playlist.load(response);
              $("#jquery_jplayer_N").data("jPlayer").options.volume = response.volume;
            }
          });
      }
    };

    balls.player.dialog = {
      elem: $("<div class='dialog' id='options'></div>"),
      cleanUp: function (target) {
        var self = balls.player.dialog.elem;
        if($(".contextMenu").length > 0)
          $(".contextMenu").remove();
        self.html(""); 
        console.log(self);
        if(self.dialog)
          self.dialog('close');
        
        self.off("click");
        self.off('keyup');
        $(window).off('keyup');
        self.children().remove();
        $(".ui-dialog-titlebar-close").off("click", "span");
      },
      add: function (selected) {
         var self = balls.player.dialog.elem;
         self.addClass("createPlaylist");
         self.append("<span>Add to Playlist: </span><select id='playlistSelect' data-placeholder='Select a Playlist'><option></option></select>");
         var select = self.find("select") || null;
         if(select == null || selected == null){
          balls.player.dialog.cleanUp();
          return false;
         }
         for(I in balls.playlists){
           select.append("<option value='"+balls.playlists[I].id+"'>"+balls.playlists[I].title+"</option>")
         }
         self.append("<input type='button' name='save' class='save' value='save' />");
         self.on("click", ".save", function () {
                              playlistId = $(this).parent().find("#playlistSelect").val() || null;
                              balls.playlist.song.add({origin:selected}, true, playlistId);
                              balls.player.dialog.cleanUp();
                            });
        $(window).on('keyup', function (e) {
          if(e.keyCode == 27){
            balls.player.dialog.cleanUp();
          }
        });
        self.dialog({dialogClass:"", modal:true, autoOpen:true});
        $(".ui-dialog-titlebar-close").on("click", balls.player.dialog.cleanUp);
        //Chosen
        select.chosen();
      },
      createPlaylist: function () {
        var self = balls.player.dialog.elem;
        self.addClass("createPlaylist");
        self.append("<span>Add Playlist:</span><input type='text' name='playlistName' id='playlistName' size='25' value=''/><input type='button' name='playlistNewSubmit' id='playlistNewSubmit' value='commit' />"); 
        self.on("click", "input#playlistNewSubmit", function () {
          var name = self.find("input#playlistName").val();
          balls.playlist.create(name);
        });
        self.on("keyup", "input#playlistName", function (e){
          if(e.keyCode == 13){
            var name = self.find("input#playlistName").val();
            balls.playlist.create(name);
          }
        });
        self.dialog({dialogClass:"", modal:true, autoOpen:true, open:function(){ $(".createPlaylist input[type='text']").focus();}});
        $(".ui-dialog-titlebar-close").on("click",balls.player.dialog.cleanUp);
      },
      renamePlaylist: function (id){
        require(["editinplace"], function () {
          $(".playlist_list ul li a#"+id).editInPlace({
            url:"/wp-content/plugins/playlist/api.php",
            params:"action=update&id="+id,
            update_value:"name",
            value_required:true,
            postclose:function(){
              setTimeout(function(){
                $(".playlist_list ul li a#"+id).unbind('.editInPlace');
                $(".playlist_list ul li a#"+id).attr("style", '');
                //rebind the rename event...?
                try{
                  toast.success("Playlist renamed to: "+$(".playlist_list ul li a#"+id).html());
                }catch(e){
                  console.log(e);
                }
              }, 1000);
            }
          });
          $(".playlist_list ul li a#"+id).click();
        });
      },
      contextMenu: function (theCase, target) {
        balls.player.dialog.cleanUp();
        var self = balls.player.dialog.elem;
        switch(theCase){
          case "playlist": 
            self.html("<div class='rename'>rename</div><div class='remove'>remove</div>");
            self.addClass("contextMenu")
            $("body").append(self);
            self.show();
            $(".ui-dialog-titlebar-close").on("click","span", balls.player.dialog.cleanUp);
  
            self.on("click", "div.remove", function () {
              self.off("click", "div");
              self.remove();
              balls.player.dialog.cleanUp();
              balls.playlist.remove(target.attr("id"));
            });

            self.on("click", "div.rename", function () {
              balls.player.dialog.renamePlaylist(target.attr("id"));
              self.off("click", "div");
              self.remove();
              balls.player.dialog.cleanUp();
            });
            break;
          default:break;
        }
      }
    };  
    
    
    balls.playlist = {
      onMethods:[],
      init: function () {
        if(balls.playlist.active){
          (balls.playlist.active.current < 0)? balls.playlist.active.select(0) : '';
          $(".active_playlist ul li").removeClass("active");
          if(balls.playlist.active.playlist[balls.playlist.active.current]){
            $(".active_playlist ul li").parent().find("#"+balls.playlist.active.playlist[balls.playlist.active.current].id).parent().addClass("active");
            var track = balls.playlist.active.playlist[balls.playlist.active.current];
          
            // Change out poster
            $('div.jp-interface div.jp-now-playing >img').attr('src', track.poster);
            $('div.jp-now-playing>div>.nowPlayingTitle').html(track.title);
            $('div.jp-now-playing>div>.nowPlayingArtist').html(track.artist);
          }
        }
        this.events();
      },
      events: function () {
        if(this.onMethods['events'])return false;
        //Search songs open up ^^
        $("h2 .showSearch").on("click", function() {
          $(this).parent().find("input").toggleClass("open");
        });
        //playlist drag and drop songs
        $(".active_playlist ul.active").sortable({
          helper:/*function () {
            return "<div style='border:thin solid black; width:200px;height:20px;'></div>";
          },*/"clone", 
          zIndex:11,
          items:">li.song",
          revert: true,
          start: function (e, ui){
            $(this).attr("data-last-index", $(ui.item[0]).index());
          },
          stop:function (e, ui) {
            if($(".active_playlist ul.active[data-sort='myOrder']").length > 0 && $(".active_playlist ul.active").attr("id").split("_")[1] != $(".playlist_list ul li a.library").attr("id")){
              var self = $(this);
              balls.playlist.order.save($(ui.item[0]).index(), self.attr("data-last-index"));
              self.removeAttr("data-last-index");
            }
          }
        }).disableSelection();
        //$(".active_playlist ul.active").draggable();
        $("body .playlist_list ul li").droppable({
          accept: "li.song",
          hoverClass:"hover",
          addClasses:false,
          greedy:true,
          tolerance:"pointer",
          drop: function (e, ui){
            selected = $(ui.draggable[0]).find("div");
            playlistId = $(e.target).children()[0].id;
            balls.playlist.song.add({origin:selected}, false, playlistId);
          }
        });
        /*$(".playlist_list ul li").on("contextmenu", function(e){
          e.preventDefault();
          balls.player.dialog.contextMenu("playlist", $(this));
        });*/
        $(".playlist_list").on("click", "ul li .delete", function (e) {
          e.stopPropagation();
          balls.playlist.remove($(this).parent().find(".playlist").attr('id'));
        });
        $(".playlist_list").on("click", "ul li div.edit", function (e) {
          e.stopPropagation();
          console.log("clicked");
          balls.player.dialog.renamePlaylist($(this).siblings("a").attr("id"));
        });
        $(".active_playlist").on("click mouseenter mouseleave", "ul.active li>div", function (e){
          e.preventDefault();
          //e.stopPropagation();
          var self = $(this), deleteButton;
          if($(".playlist_list ul li a.library").attr("id") != self.attr("data-template")){
            deleteButton = self.find("span.delete");
            if(e.type == "mouseenter"){
              deleteButton.show();
            }else if(e.type == "mouseleave"){
              deleteButton.hide();
            }
          }
          if(e.type == "click"){
            balls.playlist.active.select($(this).parent().index()); 
            balls.playlist.active.play(); 
          }
        });
        $(".active_playlist").on("click", "ul.active li div.delete", function (e) {
          e.preventDefault();e.stopPropagation();
          balls.playlist.song.remove($(this));
        });
        return this.onMethods['events'] = true;
      },
      active: new jPlayerPlaylist({
        jPlayer: "#jquery_jplayer_N",
        cssSelectorAncestor: "#the_playlists",
        cssSelector:{ playlist:".active_playlist ul"}
      }, {
        playlistOptions: {
          autoPlay: false,
          enableRemoveControls: false
        },
        supplied: "mp3, oga"
      }),
      create: function (name){
        $.ajax({url:"/wp-content/plugins/playlist/api.php", data:{"action":"set", "name":name}, type:"post"}).done(function (response) {
          balls.playlists["_"+response] = {songs:[],id:response,title:name,reload:false};
          $(".playlist_list ul").append("<li><a id='"+response+"' class='playlist'>"+name+"</a><div class='edit'></div><div class='delete'>X</div></li>");
          //re-sort playlist list alphabetically
          try{
            toast.success("Playlist '"+name+"' created.");
          }catch(e){
            console.log(e);
          }
          balls.player.dialog.cleanUp(); 
          $("body .playlist_list ul li:last-child").droppable({
            accept: "li.song",
            hoverClass:"hover",
            addClasses:false,
            greedy:true,
            tolerance:"pointer",
            drop: function (e, ui){
                selected = $(ui.draggable[0]).find("div");
                playlistId = $(e.target).children()[0].id;
                balls.playlist.song.add({origin:selected}, false, playlistId);
            }
          });
        });
      },
      remove: function (id){
        if(id){
          $.ajax({url:"/wp-content/plugins/playlist/api.php", type:"post", data:{"action":"delete", "id":id}}).done(function (response) { 
            $(".playlist_list ul li a#"+id).parent().remove();
            $(".active_playlist ul#_"+id).remove();
            try{
              var name = "name";
              toast.success("Playlist '"+name+"' removed");
            }catch(e){
              console.log(e);
            }
            balls.player.state.set();
            balls.player.state.save();
          });
        }
      },
      order: {
        save : function () {
          var newIndex = (arguments[0] || arguments[0] == 0)? parseInt(arguments[0], 10) : null,
          oldIndex = (arguments[1] || arguments[1] == 0)? parseInt(arguments[1], 10) : null;
          console.log("NewIndex: ", newIndex, "OldIndex: ", oldIndex);

          if((newIndex || newIndex ==0) && (oldIndex || oldIndex ==0) && (newIndex != oldIndex)){
            var listId = $(".active_playlist ul.active").attr("id").split("_")[1], ids = [], song;
            if(listId != $(".playlist_list ul li a.library").attr("id")){
              $(".active_playlist ul.active li").each( function () {
                ids.push('"'+$(this).find("div").attr("data-content")+'"');
              });
              if(newIndex == -1){
                balls.playlists["_"+listId].songs.splice(oldIndex, 1);
              }else {
                song =  balls.playlists["_"+listId].songs[oldIndex];
                balls.playlists["_"+listId].songs.splice(oldIndex, 1);
                balls.playlists["_"+listId].songs.splice(newIndex, 0, song);
              }
            
              $.ajax({url:"/wp-content/plugins/playlist/api.php", type:"post", data:{"action":"update", "data":"["+ids.join(",")+"]", "id":listId}})
                .done(function (response) {
                  //console.log(balls.playlists["_"+listId].songs);
                  balls.playlist.active.setPlaylist(balls.playlists["_"+listId].songs);
                });
            }
          }
          balls.player.state.set();
          balls.player.state.save();
        }
      },
      format: function (list) {
        list = list? list : [];
        var I, songs = [];
        for(I in list){
          if(list[I]){
            songs[I] = {
              id:list[I].ID,
              title:list[I].post_title,
              artist:list[I].meta.artist.data.display_name,
              mp3:list[I].meta.song_file[0]+".mp3",
              oga:list[I].meta.song_file[0]+".ogg",
              poster:list[I].meta.song_thumbnail[0]
            }
          }
        }
        return songs;
      } 
    };

    balls.playlist.load = function () {
      var state = typeof arguments[0] == "object"? arguments[0] : {},
      id = typeof arguments[0] == "string"? arguments[0] : state.activeList,
      cb = arguments[1] && typeof arguments[1] == "function"? arguments[1] : function(){},
      reload = (balls.playlists["_"+id] && balls.playlists["_"+id].reload)? true : false;
      
      if(id != 0 && balls.playlists["_"+id]){  
        self = $(".active_playlist ul.playlist#_"+id);
        if(self.length > 0  && self.hasClass("active") != true && reload != true){
          $(".active_playlist .playlist.active").removeClass("active");
          self.addClass("active");
          $(".playlist_list ul li").removeClass("active");
          $(".playlist_list ul li a.playlist#"+id).parent().addClass("active");
          balls.playlist.active.setPlaylist(balls.playlists["_"+id].songs);
          balls.playlist.active.select(0);
          $(".active_playlist ul.active").sortable({
                helper:"clone",
                zIndex:11,
                items:">li.song",
                revert: true,
                start: function (e, ui){
                  $(this).attr("data-last-index", $(ui.item[0]).index());
                },
                stop:function (e, ui) {
                  if($(".active_playlist ul.active[data-sort='myOrder']").length > 0 && $(".active_playlist ul.active").attr("id").split("_")[1] != $(".playlist_list ul li a.library").attr("id")){
                    var self = $(this);
                    balls.playlist.order.save($(ui.item[0]).index(), self.attr("data-last-index"));
                    self.removeAttr("data-last-index");
                  }
                }
          }).disableSelection(); 
          balls.search(".searchActiveList", ".active_playlist ul li");
        }else{
          var obj = {url:balls.api, data:{"action":"template", "post-type":"playlist", "template":"single", "content":id}, type:"post"};
          $.ajax(obj).done(function (response) {
            response = $.parseJSON(response);
            $(".active_playlist ul.playlist.active").removeClass("active");
            $(".active_playlist ul.playlist#_"+id).addClass("active").attr("data-sort", state.sortOrder);
            $(".active_playlist ul.playlist.active li div[data-content='"+state.lastPlayedSong+"']").parent().addClass("active");
            $(".playlist_list ul li").removeClass("active");
            $(".playlist_list ul li a.playlist#"+id).parent().addClass("active");
            
            balls.playlist.build(id, response);
            balls.playlist.active.setPlaylist(balls.playlists["_"+id].songs);
            balls.playlist.active.select(state.index);

            $(".active_playlist ul.active").sortable({
              helper:"clone",
              zIndex:11,
              items:">li.song",
              revert: true,
              start: function (e, ui){
                $(this).attr("data-last-index", $(ui.item[0]).index());
              },
              stop:function (e, ui) {
                if($(".active_playlist ul.active[data-sort='myOrder']").length > 0 && $(".active_playlist ul.active").attr("id").split("_")[1] != $(".playlist_list ul li a.library").attr("id")){
                  var self = $(this);
                  balls.playlist.order.save($(ui.item[0]).index(), self.attr("data-last-index"));
                  self.removeAttr("data-last-index");
                }
              }
            }).disableSelection();  
            balls.search(".searchActiveList", ".active_playlist ul li");
          });
        }
        balls.playlists["_"+id].reload = false;
      }
    };
    balls.playlist.build = function (id, data) {
      if(id && data){
        if($(".active_playlist ul.playlist#_"+id).length < 1){
          $(".active_playlist ul.active").removeClass("active");
          $(".active_playlist").append(data.html);
          $(".active_playlist ul.playlist#_"+id).addClass("active");
        }else{
          $(".active_playlist ul.active").removeClass("active");
          $(".active_playlist ul.playlist#_"+id).replaceWith(data.html);
          $(".active_playlist ul.playlist#_"+id).addClass("active");
        }
      }else{
        throw("Not enough arguments");
      }
    };
    
    balls.playlist.song = {
    // jPlayerPlaylist Add Media overloader
      add: function (media, playNow, id) {
        media = media? media : {};
        playNow = playNow? playNow : false;
        id = id? id : $(".playlist_list ul li a.library").attr("id");
        var libId = $(".playlist_list ul li a.library").attr("id"),
        self = (media && media.origin != null)? $(media.origin) : null, existsInLib, dataId;
     
        if(self){
            dataId = self.attr("id") || self.attr("data-content");
            media = {id:parseInt(dataId, 10), oga:self.attr('data-src')+".ogg",title:self.attr('data-title'), artist: self.attr('data-artist'), mp3:self.attr('data-src')+".mp3", poster:self.attr('data-img')};
            
            
            $.ajax({url:"/wp-content/plugins/playlist/api.php", data:{"action":"append",  "id":id,  "data":dataId}, type:"post"}).done(function(response) {
              balls.playlists["_"+id].songs.push(media); 
              balls.playlists["_"+id].reload = true;
              existsInLib = function (dataId) {
                var I;
                for(I in balls.playlists["_"+libId].songs){
                  if(balls.playlists["_"+libId].songs[I].id == dataId)
                    return true;
                }
                return false;
              }
              if(id != libId && existsInLib(dataId) != true ){
                balls.playlists["_"+libId].songs.push(media);
                balls.playlists["_"+libId].reload = true;
              }

              if($(".active_playlist ul.active").attr("id").split("_")[1] == id || $(".active_playlist ul.active").attr("id").split("_")[1] == $(".playlist_list ul li a.library").attr("id")){
                //console.log("loadingPlaylist");
                balls.playlist.load($(".active_playlist ul.active").attr("id").split("_")[1], function () {
                  if($.jPlayer.prototype.status.paused && playNow){
                    balls.playlist.active.select($("ul.playlist#"+$(".active_playlist ul.active").attr("id")+" li:last").index());
                    balls.playlist.active.play();
                  }else{
                    balls.playlist.active.select($("ul.playlist#"+$(".active_playlist ul.active").attr("id")+" li:last").index());
                  }
                });
              }
              try{
                toast.info("Song "+media.title+" added to playlist "+balls.playlists["_"+id].title);
              }catch(e){
                console.log(e);
              }
            });
          return true;
        }else{
          return false;
        }
      },
      remove: function (elem) {
        if(elem){
          var currIndex = elem.parent().parent().index();
          elem.parent().parent().remove();
          balls.playlist.order.save(-1,currIndex);
          try{
            toast.success("Song removed");
          }catch(e){
            console.log(e)
          }
        }
      }
    };


    // --- Element event manipulation ---
    $(".jp-gui").on("click", ".jp-play, .jp-pause, .jp-next, .jp-previous", function (e) { 
      console.log(e);
      e.preventDefault();
      balls.playlist.active[$(this).attr("class").split("jp-")[1]]();
      if($(this).hasClass("jp-pause")){
        try{     toast.info("Main player paused"); }
        catch(e){ console.log(e); }
      }
    });

    $(".playlist_list").on("click", "ul li", function (e){
      e.preventDefault();e.stopPropagation();
      var self = $(this).find("a");
      if(e.type == "click"){
        if(self.hasClass("playlist_new")){
          balls.player.dialog.createPlaylist();
        }else{
          balls.playlist.load(self.attr("id"));
        }
      }
    });

    $("div.jp-gui div.jp-volume-bar").on("click", function(e){
      var percent = ( (e.currentTarget.offsetHeight-e.originalEvent.layerY)/e.currentTarget.offsetHeight);
      percent = Math.floor(percent*100)/100;
      $("#jquery_jplayer_N").jPlayer("volume", percent);
      if($(".demoPlayer .player .object").data("jPlayer")){
        $('.demoPlayer .player .object').jPlayer("volume", percent);
      }
      require(["balls.ePlayer"], function () {
        balls.ePlayer.volumeSet();
      });
    });
    $("div.jp-gui").on("click", ".jp-mute, .jp-unmute", function (e) {
      var self = $(this), toastMsg;
      if(self.hasClass("jp-mute")){
        self.hide().siblings(".jp-unmute").show();
        $("#jquery_jplayer_N").jPlayer("mute", true)
        $('.demoPlayer .player .object').jPlayer("mute", true);
        toastMsg = "Player muted.&nbsp;<a onclick='$(\"div.jp-gui .jp-unmute\").click()'>Unmute player</a>";
      }else{
        self.hide().siblings(".jp-mute").show();
        $("#jquery_jplayer_N").jPlayer("unmute", true);
        $('.demoPlayer .player .object').jPlayer("unmute", true);
        toastMsg = "Player unmuted.";
      }
      try{
        $(".toastNotificationCenter .toast.mute span.close").click();
        toast.makeToast(toastMsg, {persist:(!self.hasClass("jp-unmute")), className:"info mute"});
      }catch(e){
        console.log(e);
      }
    }); 
    $("div.jp-gui div.jp-seek-bar").on("click", function (e){
      //console.log(e.currentTarget.offsetWidth);
      //console.log(e.originalEvent.clientX-276);
      var percent = Math.floor((e.originalEvent.clientX-276)/e.currentTarget.offsetWidth*100);
      $("#jquery_jplayer_N").jPlayer("playHead", percent);
    });
      
    //player event callbacks
    $("#jquery_jplayer_N").on($.jPlayer.event.error, balls.error);

    //time update response
    $("#jquery_jplayer_N").on($.jPlayer.event.timeupdate, function (event) {
      var percentComplete = Math.floor(event.jPlayer.status.currentTime/event.jPlayer.status.duration*100);
      percentComplete = percentComplete <= 100 ? percentComplete : 100;
      $(".jp-gui .jp-play-bar").css("width", percentComplete+"%");
      $("div.jp-now-playing .jp-current-time").html($.jPlayer.convertTime(Math.floor(event.jPlayer.status.currentTime)));
      if($("div.jp-now-playing .jp-duration").html() != event.jPlayer.status.duration.toString()){
        $("div.jp-now-playing .jp-duration").html($.jPlayer.convertTime(Math.floor(event.jPlayer.status.duration)));
      }
      //update time in state for next save state event.
    });
    
    //Volume update response
    $("#jquery_jplayer_N").on($.jPlayer.event.volumechange, function(event){
      $(".jp-gui .jp-volume-bar-value").css("height", (event.jPlayer.options.volume*100)+"%");
    });
    
    $("#jquery_jplayer_N").on($.jPlayer.event.pause, function(){
        $(".jp-gui .jp-pause").hide();
        $(".jp-gui .jp-play").show();
        $.jPlayer.prototype.status.paused = true;
        try{
          // should only go if pause button clicked?
          //toast.info("Main player paused");
        }catch(e){
          console.log(e);
        }
        balls.player.state.set();
        balls.player.state.save();
    });
    $("#jquery_jplayer_N").on($.jPlayer.event.play+" "+$.jPlayer.event.loadedmetadata, function(event) {
      var track = balls.playlist.active.playlist[balls.playlist.active.current];
      // Get currently playing track
      $(".active_playlist ul li").removeClass("active")
        .parent().find("#"+balls.playlist.active.playlist[balls.playlist.active.current].id).parent().addClass("active");  
        
      // Change out poster
      $('div.jp-interface div.jp-now-playing >img').attr('src', track.poster);
      $('div.jp-now-playing>div>.nowPlayingTitle').html(track.title);
      $('div.jp-now-playing>div>.nowPlayingArtist').html(track.artist);
       
      if(event.type != $.jPlayer.event.loadedmetadata){
        $(".jp-gui .jp-play").hide();
        $(".jp-gui .jp-pause").show();
        $.jPlayer.prototype.status.paused = false;
        balls.player.state.set();
        balls.player.state.save();
        balls.analytics.track({content:balls.playlist.active.playlist[balls.playlist.active.current].id, action:9}); 
        require(["balls.ePlayer"], function () {
          balls.ePlayer.stop();
        }); 
        try{
          toast.info("Now playing "+track.title+" by "+track.artist);
        }catch(e){
          console.log(e);
        }
      }else{
        // set current time from state
        console.log("State:", balls.player.state);
        console.log("timeStamp:", balls.player.state.current.timeStamp);
        console.log("duration:", $("#jquery_jplayer_N").data("jPlayer").status.duration);
        console.log("time:", Math.round(balls.player.state.current.timeStamp/$("#jquery_jplayer_N").data("jPlayer").status.duration*100));
        $("#jquery_jplayer_N").jPlayer("playHead", Math.round(balls.player.state.current.timeStamp/$("#jquery_jplayer_N").data("jPlayer").status.duration*100));
      }
    });

