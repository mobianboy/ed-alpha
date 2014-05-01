// Main balls Contribute Module
//
// balls.contribute                   :   Main Object for contribute
// balls.conritbute.api               :   Relative API URL
// balls.contribute.dropZone          :   jQuery selector for the contribute file drop element
// balls.contribute.init              :   contribute initilization method
// balls.contribute.error             :   error handler for conrtibute related events (message)
// balls.contribute.success           :   contribute success handler, optional reset (false/true) Default:true 
// balls.contribute.reset             :   clear values, dump html, clear images, hide drop zone, cancel actions, and options close. (true/false) Default:false
// balls.contribute.cancel            :   cancel contribute handler, required ID of the resource being canceled (ID)
// balls.contribute.remove            :   remove conributed postID (ID)
// balls.contribute.upload            :   Contribute upload file handler (file, type, id) All agruments are required
// balls.contribute.complete          :   The action to send to the server to tell it to publish or delete the new asset song/demo (postId, newStatus, close) postId, newStatus required
// balls.contribute.audio             :   The functionality to pass an audio track into the balls.file.upload (file, Id)
// balls.contribute.albumArt          :   Album art attached to a specific resource/postId & takes in a file object and the resource ID it's related to (file, id) 
// balls.contribute.setDemoSelector   :   Set the visual representation and litteral values for the cutting of the demo from the full track with a wave form visual aid (id, timeLength[60/90])
// balls.contribute.setStartTime      :   Leveraged Math for the start time by setDemoSelector uses a pre-defined elements dropped location.
// balls.contribute.events            :   All user based event watchers for contribute actions

!(function (balls){  
  balls.contribute = {
    get api() {         return  "/wp-content/plugins/contribute/api.php";},
    get dropZone(){     return  $("section.contribute .context .dropZone");},
    init: function () {
      window.contributeXHR = [];
      this.events();
    },
    error: function (message) {
      var contribute = this;
      if(message && typeof message == "string"){
        try{
         toast.error(message);
         setTimeout(function () {
            contribute.reset(true)
          }, toast.animation.getDelay());
        }catch(e){
          console.log(e);
          console.log(message);
        }
      }
    },
    success: function (close) {
      var contribute = this,
      close = close? close : true;
      toast.success("Hey... cool, you've just made music happen!");
			setTimeout(function () { contribute.reset(close);}, 1500);
    },
    reset: function (close) {
      close = close? close : false;
      $("section.contribute .dropZone .cancel").addClass("hidden");
      $("section.contribute .context .songConfirm").remove();
			$("section.contribute .context .photoSave").remove();
      $("section.contribute .context").children().removeClass("hidden").show();
      $("section.contribute .dropZone .progress").remove();
      $("section.contribute .dropZone").removeClass("hover").removeClass("uploading");
      if(close){
        $("body>.drop_zone>.sortable .widgets .widget.contribute .wrapper").removeClass("open");
        $("body>.drop_zone>.sortable #btn_contribute").removeClass("open");
      }
      balls.ePlayer.stop();
    },
    cancel: function (id) {
      if(id){
        this.reset(true);
        if(window.contributeXHR.hasOwnProperty(id)){
          window.contributeXHR[id].abort();
          window.contributeXHR[id] = null;
        }
      }
    },
    remove: function (id) {
      if(id){
        $.ajax({url:balls.song.api, type:"post", data:{id:id, "action":"delete"}})
          .done(function (response) {
            if(response){
              $("[data-content='"+id+"']").remove();
            }
          });
      }
    },
    upload: function (files, id) {
      id = id? id : userSession.id;
			if(files.length > 0){
        var I=0;
				for(I; I<files.length; I++){
					var name = files[I].name.toString(),
					fileExt = name.substring(name.lastIndexOf(".")+1, name.length),
					fileType = balls.file.typeAuth(fileExt, files[I].type);
          if(!fileType)continue;
					this[fileType](files[I], id);
        }
      }else{
        return false;
      }
      return true;
    },
		image: function (file, id){
      var fileName = file.name.trim().replace(/\.[^.]*$/g, '');
			window.contributeXHR[fileName] = balls.file.upload(file, id, "photo", function (response){
				var response = response ? $.parseJSON(response) : null,
            resId = response && response.data? response.data.resource.source.resourceId : null,
						imageSrc = response && response.data? response.data.resource.source.url : null;
				if(resId){
					$.ajax("/wp-content/themes/score/tpl/photo/contribute.php").done(function(response){
						$("section.contribute .dropZone, section.contribute .selectType, section.contribute p").addClass("hidden");
						// if there is a response and it's not false
						if(response){
							// populate the html with imageSrc in the img, and populate resId in the save button
							$("section.contribute .context").prepend(response);
							$("section.contribute .context .photoSave .meta .image img").attr("src", imageSrc);
							$("section.contribute .context .photoSave .commit .save").attr("data-resId", resId);
						}
					});
				}else{
					balls.error("","No resource ID");
				}
			});
		},
		//complete photo (send resId to wordPress image)
		completeImage: function (resId, caption){
			if(resId == null)return false;
			window.contributeXHR[resId] = $.ajax({url:balls.contribute.api, type:"POST", data:{action:"setphoto", id:resId, caption:caption}})
				.done(function(data){
					var src = data?data:null;
					if(!src)return false;
					if(toast)toast.success("image uploaded");
					balls.contribute.reset(true);
					// reset html in explode, and modal if already open
				});
		},
    completeSong: function (postId, newStatus/*["publish"|"delete"]*/, close) {
      var contribute = this;
      if(postId && newStatus){
        //show action happening
        newStatus = newStatus.split("+")[0];
        $.ajax({url:balls.contribute.api, type:"POST", data:{action:"confirm", typeid:postId, status:newStatus}})
          .done(function (response) {
            if(response){
              contribute.success(close);
              toast.success("Your upload: "+newStatus+"~ing!");
              balls.playlist.song.add({origin:$("section.contribute .context .songConfirm[data-content='"+postId+"'] .commit input[type='button'][name='save']")});
              //add to songs explode if artist;
              //add to other songs if on song single page, and not already @ max: 10;
            }
          });
      }
    },
    // Upload audio extension
    audio: function (file, id) {
      var contribute = this,
      fileName = file.name.trim().replace(/\.[^.]*$/g, '');
      $("section.contribute .dropZone .cancel").attr("data-src", fileName).removeClass("hidden");
      window.contributeXHR[fileName] = balls.file.upload(file, id, "song", function (response) {
              if(response){
                var response = response ? $.parseJSON(response) : null,
                duration = response && response.data? response.data.resource.source.duration : null,
                resId = response && response.data? response.data.resource.source.songid : null,
                wfid = response && response.data? response.data.resource.source.wfid : null;
              	
								if(response.status && response.status.code > "29"){
                  balls.contribute.error("Error:"+response.status.code+" - "+response.status.message);
                  console.log("Error: "+response.status.code+" - "+response.status.message);
                  return false;
                }
								if(response === false || response == null || wfid == null || resId == null){
                	return false;
              	}
								if(response.data.resource.type == "data" && resId != null && !window.contributeXHR[resId]){
									window.contributeXHR[resId] = $.ajax({url:balls.api, type:"POST", data:{template:"contribute", "post-type":"song", id:resId, duration:duration}})
										.done(function(data){
											//hide cancel button.
											$("section.contribute .dropZone .cancel").attr("data-src", '').addClass("hidden");
											//continue
											var data = data ? $.parseJSON(data) : null,
											html = data ? data.html : null;
											if(html && html != "" && html != '\n'){
												$("section.contribute .context").children().hide();
												//append contribute context HTML with response
												$("section.contribute .context").prepend(html);
												$("section.contribute .context .songConfirm[data-content='"+resId+"'] .theClip .slideContainer img").attr("src", response.data.resource.source.png).attr("data-wfid", wfid);
												$("section.contribute .context .songConfirm[data-content='"+resId+"'] .commit input[type='button'][name='save']")
													.attr("data-resource", resId)
													.attr("data-start", contribute.setStartTime(resId))
													.attr("data-length", contribute.setDemoSelector(resId));
												//set next step 'full' for complete step
												$("section.contribute .context .songConfirm[data-content='"+resId+"'] .complete .full img").attr("src", response.data.resource.source.png);
												$("section.contribute .context .songConfirm[data-content='"+resId+"'] .playFull").attr("data-audio", response.data.resource.source.song);
												$("section.contribute .context .songConfirm[data-content='"+resId+"'] .playDemo").attr("data-audio", response.data.resource.source.song);
												
												/* Drop Down CHOSEN */
												$("section.contribute .context .songConfirm[data-content='"+resId+"'] .meta select").chosen({
													disable_search: true
												});

												/* End Drop Down CHOSEN */
                      /* Set Events */
                      $("section.contribute .context .songConfirm[data-content='"+resId+"'] .slideContainer .selector").draggable({
                        axis:"x",
                        revert:false,
                        scroll:false,
                        containment:"parent"
                      });
                      // slider drop
                      $("section.contribute .context .songConfirm[data-content='"+resId+"'] .slideContainer").droppable().on("drop", function () {
                        var start = contribute.setStartTime($(this).find(".selector")[0]);
                        $(this).parent().parent().find(".commit").find("input[name='save']").attr("data-start", start);
                      });
                    }else{
                      balls.error("There seems to be a problem with the conribute widget... ut ohh.");
                    }
                  })
                  .fail(function(response){
                    if(response){
                      balls.error("",response);
                    }
                  });
              }
					}
         	return false;
      });
      //set cancel button to handle file.name.trim();
    },
    // Upload albumArt extension
    albumArt: function (file, id) {
      var contribute = this;
      contribute.dropZone.show();
      console.log(file.name.trim());
      window.contributeXHR[file.name.trim()] = balls.file.upload(file, id, "song", function (response) {
        contribute.dropZone.hide();
        var complete, response = response? $.parseJSON(response) : null;
        if(response){
          complete = $("section.contribute .context .songConfirm[data-content='"+id+"']").find(".complete");
          complete.removeClass("hidden")
                  .siblings().addClass("hidden");
          complete.children("img").attr("src", response.data.resource.source);
        }
        return false;//stop save step;
      });
    },
    //song clipping and submit with meta
    setDemoSelector: function (id, time) {
      var container = $("section.contribute .context .songConfirm[data-content='"+id+"'] .slideContainer"), selector = container.find(".selector"),
          time = time ? parseInt(time, 10) : 60,
          songLength = selector.attr("data-length").split(":"), ratio, width;
      if(id && time){
        songLength = songLength[1] ? (parseInt(songLength[0], 10)*60)+parseInt(songLength[1], 10) : parseInt(songLength[0], 10);
        ratio = time/songLength;
        width = Math.floor(parseInt(container[0].offsetWidth,10)*ratio)+"px";
        selector.css("width", width);
        container.parent().parent().parent().find(".commit input[name='save']").attr("data-length", time);      
      
        return time;
      }
    },
    setStartTime: function (){
      //number of seconds from start 00:00
			var songLength, leftOffset, startTime, width,
			selector = arguments[0] && typeof arguments[0] != "string" && typeof arguments[0] != "number"? $(arguments[0]) : null;
			selector = (selector == null && (typeof arguments[0] == "string" || typeof arguments[0] == "number"))? $("section.contribute .context .songConfirm[data-content='"+arguments[0]+"'] .slideContainer .selector") : selector;
      
			if(!selector[0])return false;
			songLength = selector.attr("data-length") && selector.attr("data-length") != "" ? selector.attr("data-length").split(":") : 0;
			songLength = songLength[1] ? (parseInt(songLength[0], 10)*60)+parseInt(songLength[1], 10) : parseInt(songLength[0], 10);
			leftOffset = selector[0].style.left.split("px")[0];
			width = selector.parent()[0].offsetWidth;
			startTime = (songLength)? Math.floor((songLength * leftOffset)/width) : 0; 

      return startTime;
    },
    events: function () {
          var contribute = this;
          //--- Contribute Start ---
          $(document).on("dragover dragleave drop", function (e) {
            e.returnValue = false;
            e.preventDefault();
            var self = $("body").find("#the_contribute"),
                btn = self.parent().parent().next().find("#btn_contribute");
            if(e.type === "dragover"){
              self.addClass("open");
              self.find(".dropZone").addClass(self.find(".dropZone").hasClass("hover") ? "" : "hover");
              if(btn.hasClass("open") === false){
                btn.addClass("open");
              }
            }else if(e.type === 'dragleave'){
              self.removeClass("open");
              self.find(".dropZone").removeClass("hover");
              if(e.type!=="drop" && btn.hasClass("open")){
                btn.removeClass("open");
              }
            }
          });
          $("section.contribute").on('drop', ".dropZone", function(e){
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass("hover").addClass("uploading");
            contribute.upload(e.originalEvent.dataTransfer.files);
          });
          $("section.contribute .dropZone .cancel").on("click", function () {
            var self = $(this);
            contribute.cancel(self.attr("data-src"));
          });
					$("section.contribute").on('click', '.dropZone', function(){
						 $("section.contribute .song input[type='file']")[0].click();
					});
          $("section.contribute").on("change", ".song input[type='file']", function () {
						contribute.upload(this.files);
						$("section.contribute .song input[type='file']").val("");
          });
					//* --- Image Sepcifics ---- */
					$("section.contribute").on("click", ".photoSave .commit .save", function (e) {
						e.stopPropagation();
						contribute.completeImage($(this).attr("data-resId"), $(this).parent().parent().find("[name='caption']").val())
					});	
					$("section.contribute").on("click", ".photoSave .commit .cancel", function (e) {
						e.stopPropagation();
						contribute.reset(true);
					});
					//* --End Image Sepcifics -- */
          $("section.contribute").on("click", "div.btn#btn_contribute, div.widgetClose", function () {
            contribute.reset(true);
            balls.ePlayer.stop();
          });
          $("section.contribute").on("click", ".context .timeSelect input[type='radio']", function () {
            var id = $(this).parent().parent().parent().attr("data-content");
            switch($(this).attr("class")){
              case "sixtySec" : contribute.setDemoSelector(id,60);break;
              case "nintySec" : contribute.setDemoSelector(id,90);break;
              default : break;
            }
          });
          
          //genre on change
          $("section.contribute").on("change", ".context .meta select.genre", function () {
            var self = $(this);
            self.parent().next().find("input[name='save']").attr("data-genre", self.val());
          });
          
          //input onkeyup
          $("section.contribute").on("keyup", ".context .meta input", function (e) {
            var self = $(this);
            if(e.keyValue === "13"){
              self.parent().next().find("[name='save']").click();
            }else{
              self.parent().next().find("[name='save']").attr("data-title", self.val());
            }
          });

          
          /* == Save song in system in to post DB and create draft version == */
          $("section.contribute").on("click", ".context .songConfirm .commit input[type='button'][name='save']", function(){
            var self = $(this);
            //validate all fields that need a value have it.
            //
            $("section.contribute").find(".dropZone").show();
            // Send Meta + clipping info to Server NOT MFP
						$.ajax({url:balls.contribute.api,
										type:"POST", 
										data:{
											action:"set",
											title:self.attr("data-title"), 
											genre:self.attr("data-genre"),
											demoStart:self.attr("data-start"),
											demoLength:self.attr("data-length"),
											mfpResourceId:self.attr("data-resource"),
											wfid:self.parent().parent().find(".theClip .slideContainer img").attr("data-wfid")}})
							.done(function(response){
								$("section.contribute").find(".dropZone").hide();
								var response = response? $.parseJSON(response) : null,
										id = response.id, wrapper = self.parent().parent(), complete = wrapper.find(".complete"), mfp = response.mfp;
								if(id == null) return false;
								toast.success("Song demo clipping complete.");
								
								// change data-content from the ResourceId to the postId
								wrapper.attr("data-content", id).children().each(function(){
									var child = $(this);
									if(child.hasClass("albumArt")){
										child.removeClass("hidden");
									}else{
										child.addClass("hidden");
									}
								});
								
								// Set song complete fields
								complete.find(".artist").html(userSession.name);
								complete.find(".title").html(self.attr("data-title"));
								complete.find(".genre").append(response.genre);
								complete.find(".length").append(self.parent().parent().find(".theClip .slideContainer .selector").attr("data-length").toHHMMSS());
								
								//set demo PNG
								complete.find(".demo img").attr("src", mfp.data.resource.source.png); 
								
								//setDemo data-audio
								wrapper.find(".playDemo").each(function(){
									$(this).attr("data-audio", mfp.data.resource.source.song);
								});
								//setDemo no points;
								self.attr("data-start", 0);
								self.attr("data-length",0);
						})
						.fail(function (response) {
							balls.error("", response);
						});
      });

			/* == Handling the Album Art == */
			$("section.contribute").on("dragover dragleave drop",".context .albumArt .dropArea", function (e) {
				var self = $(this);
				if(e.type === "dragover" || e.type==="drop")
					self.addClass("highlight");
				else
					self.removeClass("highlight");
			});
			$("section.contribute").on("drop", ".context .albumArt .dropArea", function (e) {
				e.stopPropagation();
				e.preventDefault();
				var id = $(this).parent().parent().attr("data-content") || null;
				if(!id || id === "")return false;
				contribute.upload(e.originalEvent.dataTransfer.files, "albumArt", id);
			});
			$("section.contribute").on("click", ".context .albumArt input[name='skip']", function () {
				$(this).parent().parent().find(".complete").removeClass("hidden")
					.siblings().addClass("hidden");  
			});

			/* == Demo and Full song Playing in Contribute Widget == */ 
			$("section.contribute .song .confirm .preview").on("click", "div", function() {
				//var time = contribute.setStartTime 
				//get length of timeSelect
				//play song from startTime
				//setTimeout(timeSelect, player.stop);
			});

			/* == Publish or Delete Step == */
			$("section.contribute").on("click", ".context .songConfirm .complete .publish", function () {
				var id = $(this).parent().parent().attr("data-content");
				contribute.completeSong(id, "publish");
				balls.ePlayer.stop();
			});
			$("section.contribute").on("click", ".context .songConfirm .complete .publishPlus", function () {
				var id = $(this).parent().parent().attr("data-content");
				contribute.completeSong(id, "publish", false);
				balls.ePlayer.stop();
			});
			$("section.contribute").on("click", ".context .songConfirm .complete .delete", function () {
				var id = $(this).parent().parent().attr("data-content");
				contribute.completeSong(id, "delete");
				balls.ePlayer.stop();
			});
			$("section.contribute").on("click", ".context .songConfirm .complete .startOver", function () {
				var id = $(this).parent().parent().attr("data-content");
				contribute.completeSong(id, "delete", false);
				balls.ePlayer.stop();
			});

      
      /* == ePlayer Events == */
      $("section.contribute").on("click", ".songConfirm .playFull, .songConfirm .playDemo", function (){
        var self = $(this),
        startTime = (self.hasClass("playDemo"))? self.parent().parent().parent().find(".commit input[name='save']").attr("data-start") : 0,
        playTime = (self.hasClass("playDemo"))? self.parent().parent().parent().find(".commit input[name='save']").attr("data-length") : 0;
        balls.ePlayer.play(self.attr("data-audio"), startTime, playTime);
        self.addClass("hidden");
        self.siblings(".stop").removeClass("hidden");
      });
      $("section.contribute").on("click", ".songConfirm .previewUpload .stop", function () {
        var self = $(this);
        self.addClass("hidden");
        self.siblings(".playDemo, .playFull").removeClass("hidden");
        balls.ePlayer.stop();
      });
      $("section.contribute").on("click", ".songConfirm .complete .stop", function () {
        balls.ePlayer.stop();
        $(this).addClass("hidden");
        $(this).prev().removeClass("hidden");
      });
      
      balls.ePlayer.player.addEventListener('ended', function () {
         $("section.contribute .playDemo, section.contribute .playFull").removeClass("hidden").next(".stop").addClass("hidden");
      }, false); 

    }
  }
})(window.balls);
