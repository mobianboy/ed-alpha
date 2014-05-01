// Balls User module
// balls.user               :   User module
// balls.user.on            :   On user methods
// balls.user.init          :   Initilize user module, and dependancies
// balls.user.single        :   Event watchers for user single
// balls.user.archive       :   Event watchers for user archive
// balls.user.eipToggle     :   turn on/off EIP.
// balls.user.form          :   Event watchers for user form

!(function (balls) {  
  balls.user = {
    on:[],
    init: function init () {
      if(balls.user.on['init'])return true;
      
      Util.init("follow");
      
      return this.on['init'] = true;
    },
    single: function () {
      //=================================
      //This needs a new home... move it.
      //=================================
        // EIP-Visual-Toggle Click
        Util.init("eip");
        $("body").on("click", "#eipToggleCB", balls.user.eipToggle);
      //=================================
      //=================================
       
      Util.init("comment");
      
      if(balls.user.on['single'])return true;
      //--- Handle password initial set, reset, or re-key by user ---
     
      // set events for 'commit'
      $("body").on("click", ".setPassword button.commit", function () {
        var wrapper = $(this.parentNode.parentNode),
        action = wrapper.hasClass('reset')? "resetPass" : "setPass";
        oldpass = wrapper.find("input.reset").val(),
        passOne = wrapper.find(".inputs input+label+input")[0].value,
        passTwo = wrapper.find(".inputs input+label+input+label+input")[0].value;
        try{
          require(['balls.account'], function () {
            balls.account.reSetPassword(oldpass, passOne, passTwo);
          }); 
        }catch(e){
					require(['balls.error'], function(){
						balls.error("", "Unknown error in password reset. Exception follows", {logObject:e, suppressSystem:userSession.isLive});
					});
        }
      });
      $("body").on("click", ".setPassword span.close", function (){
        this.parentNode.parentNode.className += " hidden";
      });
      
      //--- Shout wall box take focus ---
      $("body").on("click", ".main .buttons .shout", function () {
        $("body .main .wall .dishPost .dishInput textarea").focus();
        if($("section[id*='/profile/']").length > 0){
          var y = $(window).scrollTop();
          $(window).scrollTop(y+100);
        }
      });
      //--- Artist Explorer explode ---
	    $("body").on("click", ".main .explorer.artist .portlet", function(){
        if($(this).hasClass("rewards") ||
            $(this).hasClass("playlists"))return false;
   		  $(this).addClass("open");    
      });


			//--- Atist/Fan Photo Explode ---
			$("body").on("click", ".explorer .portlet .explode .wrapper ul li.photo .delete",function(){
				balls.photo.delete($(this).parent().attr("id"));
			});


      //--- Artist Video Explode ---
      // Click video
      $("body").on("click", ".explorer.artist .portlet.videos .explode .wrapper ul li.video", function(e){
        var me = this,
						userID  = document.URL.split("/").pop(), 
            videoID = $(e.target).closest("li.video").attr("data-ytid");
        if(userID === "") userID = userSession.id;
				if(videoID != null && videoID !== ""){
					require(['balls.video'], function(){
						balls.video.openGallery(userID, videoID);
					});
				}else{
					require(['balls.error'], function(){
						balls.error("Could not find correct video id, please try again.", "Video ID was "+videoID, {logObject:{'this':me, 'event':e}});
					});
				}
      });
      // Add Video button 
      $("body").on("click", ".main .explorer.artist .portlet.videos .explode > button.add", function(){
        var h = this.parentNode.querySelector("div.addURL");
        if( h.className.search(/hidden|hide/) > -1 ){
          $(h).removeClass("hidden");
        }else{
          h.className = h.className+" hidden";
        }
      });
      // Add URL Submit
      $("body").on("click", ".explorer.artist .portlet.videos .explode div.addURL button.submit", function(){
        var input = this.previousElementSibling.value;
        require(['balls.video'], function(){
          balls.video.add(input);
        });
        this.previousElementSibling.value = "";
      });
      $("body").on("keyup", ".explorer.artist .portlet.videos .explode div.addURL input", function(e){
        if(e.which === 13){
          $(this.nextElementSibling).click();
        }
      });
      // In Explode Delete button
      $("body").on("click", ".explorer.artist .portlet.videos .explode .wrapper ul li.video .delete", function(e){
        e.stopPropagation();
        var videoID = this.parentNode.getAttribute("data-ytid");
        if(videoID != null){
          require(['balls.video'], function(){
            balls.video.delete(videoID);
          });
        }else{
					require(['balls.error'], function(){
						balls.error("Could not find correct video id, please try again.", "Video ID was "+videoID, {logObject:e, suppressSystem:userSession.isLive});
					});
        }
      });
      //--- END Artist Video Explode ---


      $("body").on("click", ".explorer .songs ul>li>ul>li.options>ul>li>a.delete", function(){
        balls.contribute.remove($(this).parent().parent().parent().parent().parent().attr("id"));
      });

      $("body").on("click", ".main .explorer.artist .portlet-close", function(e) {
        e.stopPropagation();            
        $(".explorer .portlet").removeClass("open");
      });
      
      $("body").on("click", ".main .explorer .portlet.songs .explode ul li > ul li.options ul li a.queue", function(){
          balls.player.dialog.add(this.parentNode.parentNode.parentNode.parentNode.parentNode);
      });

      //--- Fan Single Header explode ---
      $("body").on("click", ".explorer.mini .portlet", function() {
        if($(this).hasClass("rewards") || $(this).hasClass("playlists"))return false;
        $(this).addClass('open');
      });
      $("body").on("click", ".explorer.mini .portlet-close", function(e) {
        e.stopPropagation();
        $(".explorer.mini .portlet").removeClass('open');
      });
      
      //--- Fan Single Explorer Explode ---
      $("body").on("click", "section.main .explorer.fan .portlet", function(){
	      $(this).addClass("open");
	      $(".explorer.fan").addClass("open"); 
      });
      $("body").on("click", "section.main .explorer.fan .portlet-close", function(e) {
        e.stopPropagation();
        $(this).parent().parent().removeClass("open").parent().removeClass("open");
      });
      
      this.on['single'] = true;
    },
    archive: function () {
      if(balls.user.on['archive'])return true;

      this.on['archive'] = true;
    },
    eipToggle: function() { // Edit-in-Place Toggle class
      var eip = $("[data-eip], .topBar .eipToggle label[for=eipToggleCB]");
      if( $("#eipToggleCB").is(":checked") ){
        eip.addClass("showEIP");
      }else{
        eip.removeClass("showEIP");
      }
    },
    form: function() {
      if(balls.user.on['form'])return true;
      this.on['form'] = true;
    }
  };
})(window.balls);
/*for(var I in balls.user){
  if(balls.user.hasOwnProperty(I)){
    balls.user.prototype = {
      constructor:balls.user,
      get single(){
        console.log("getValue");
        if(this._on) return false;
        this._on = true;       
      }
    };
  }
}*/
