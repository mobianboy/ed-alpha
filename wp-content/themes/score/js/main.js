//window.balls || balls       :     The Main balls object;
//

//Util                        :     Utility Object
//Util.init                   :     Fire on page load, and template load util.exec for current post type, template type
//Util.exec                   :     Execute current post-type and template JS

require.config({
  baseUrl: "/wp-content/themes/score/js/",
  waitSeconds: 30,
  paths:{
    "jquery"              : "lib/jquery/jquery-1.8.1.min",
    "jquery_ui"           : "lib/jquery/plugins/ui/js/jquery-ui-1.8.21.custom.min", 
    "jplayer"             : "lib/jquery/plugins/jplayer/js/jquery.jplayer.min",
    "jplayerplaylist"     : "lib/jquery/plugins/jplayer/js/jplayer.playlist.min",
    "chosen"              : "lib/jquery/plugins/chosen/jquery.chosen.min",
    "editinplace"         : "lib/jquery/plugins/editinplace/jquery.editinplace.min",
    "motioncaptcha"       : "lib/jquery/plugins/motioncaptcha/jquery.motionCaptcha.0.2.min",
    "scrollThis"          : "lib/jquery/plugins/scrollThis/jquery.scrollThis.min",
    "toast"               : "lib/toastNotifications",
    "viewport"            : "lib/jquery/plugins/viewport/jquery.viewport.min",
    "less"                : "lib/less-1.4.1.min",
    "balls.account"       : "balls/balls.account.min",
    "balls.analytics"     : "balls/balls.analytics.min",
    "balls.archive"       : "balls/balls.archive.min",
    "balls.comment"       : "balls/balls.comment.min",
    "balls.contribute"    : "balls/balls.contribute.min",
    "balls.cpe"           : "balls/balls.cpe.min",
    "balls.css"           : "balls/balls.css.min",
    "balls.demoPlayer"    : "balls/balls.demoPlayer.min",
    "balls.dig"           : "balls/balls.dig.min",
    "balls.dw"            : "balls/balls.dw.min",
    "balls.easterEggs"    : "balls/balls.easterEggs.min",
    "balls.eip"           : "balls/balls.eip.min",
    "balls.ePlayer"       : "balls/balls.ePlayer.min",
    "balls.event"         : "balls/balls.event.min",
		"balls.error"					: "balls/balls.error.min",
    "balls.file"          : "balls/balls.file.min",
    "balls.follow"        : "balls/balls.follow.min",
    "balls.form"          : "balls/balls.form.min",
    "balls.message"       : "balls/balls.message.min",
    "balls.modal"         : "balls/balls.modal.min",
    "balls.notification"  : "balls/balls.notification.min",
    "balls.photo"					:	"balls/balls.photo.min",
		"balls.player"        : "balls/balls.player.min",
    "balls.post"          : "balls/balls.post.min",
    "balls.search"        : "balls/balls.search.min",
    "balls.single"        : "balls/balls.single.min",
    "balls.shout"         : "balls/balls.shout.min",
    "balls.song"          : "balls/balls.song.min",
    "balls.state"         : "balls/balls.state.min",
    "balls.ttl"           : "balls/balls.ttl.min",
    "balls.user"          : "balls/balls.user.min",
    "balls.userbar"       : "balls/balls.userbar.min",
    "balls.video"         : "balls/balls.video.min"
  },
  shim:{
    'jquery_ui'         : ['jquery'],
    'jplayer'           : ['jquery'],
    'jplayerplaylist'   : ['jquery'],
    'chosen'            : ['jquery'],
    'scrollThis'        : ['jquery'],
    'balls.account'     : ['jquery', 'chosen', 'motioncaptcha', 'balls.error', 'balls.state', 'toast'],
    'balls.archive'     : ['jquery', 'viewport', 'toast'],
    'balls.comment'     : ['jquery', 'toast'],
    'balls.contribute'  : ['jquery', 'jquery_ui', 'balls.file', 'balls.ePlayer', 'toast', 'balls.error'],
    'balls.cpe'         : ['jquery', 'jplayer'],
    'balls.demoPlayer'  : ['jquery', 'balls.player', 'balls.analytics', 'balls.error', 'balls.follow', 'toast'],
    'balls.player'      : ['jquery', 'jquery_ui', 'jplayer', 'jplayerplaylist', 'balls.analytics', 'chosen', 'balls.search', 'scrollThis', 'toast'],
    'balls.ePlayer'     : ['balls.event', 'toast'],
    'balls.easterEggs'  : ['toast'],
    'balls.eip'         : ['jquery', 'editinplace', 'chosen', 'toast'],
		'balls.error'				: ['toast'],
    'balls.file'        : ['toast'],
    'balls.follow'      : ['toast'],
    'balls.message'     : ['jquery', 'toast'],
    'balls.photo'				:	['jquery', 'balls.modal', 'balls.contribute'],
		'balls.search'      : ['jquery'],
    'balls.shout'       : ['jquery', 'toast'],
    'balls.single'      : ['balls.user', 'balls.song'],
    'balls.song'        : ['jquery', 'balls.cpe', 'balls.player', 'balls.ePlayer', 'toast'],
    'balls.state'       : ['jquery', 'balls.css', 'balls.user', 'balls.post', 'balls.song', 'balls.single', 'balls.archive', 'balls.ttl', 'toast'],
    'balls.ttl'         : ['jquery'],
    'balls.user'        : ['balls.comment', 'balls.photo', 'balls.player'],
    'balls.userbar'     : ['jquery', 'scrollThis', 'balls.demoPlayer', 'balls.player', 'balls.shout', 'balls.notification', 'balls.message', 'balls.account'],
    'balls.video'       : ['jquery', 'balls.error', 'balls.modal', 'toast']
  }
});



window.balls = {};
require(['jquery'], function ($) {
  var balls = window.balls;
  balls.api = "/wp-content/plugins/balls/api.php";
  balls.global = {
    on:[],
    init: function () {
      var self = this;
      
      //Mess with Superglobal String and Object
      Array.prototype.inArray = function (value){
        var i=0;
        for (i; i<this.length; i++){
            if (this[i] == value){return true;}
        }
        return false;
      }
      String.prototype.trim=function(){return this.replace(/\s+|\s+/g, '');};
      String.prototype.ltrim=function(){return this.replace(/\s+/,'');};
      String.prototype.rtrim=function(){return this.replace(/\s+$/,'');};
      String.prototype.fulltrim=function(){return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');};
      String.prototype.removePeriods=function(){return this.replace(/\.|\s\./g,'');};
      String.prototype.isValidEmail=function(){ return (/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/).test(this);};
      String.prototype.isValidUsername=function(){return (/^[a-zA-Z0-9\s\.\_\-]{5,}$/).test(this);};
      String.prototype.isValidPassword=function(){return this.length >= 6 && (/[0-9]/).test(this) && (/[A-Z]/).test(this) && (/[^A-Za-z0-9]/).test(this)? true : false;};
      String.prototype.toHHMMSS = function () {
        var sec_num = parseInt(this, 10), // don't forget the second parm
            hours   = Math.floor(sec_num / 3600),
            minutes = Math.floor((sec_num - (hours * 3600)) / 60),
            seconds = sec_num - (hours * 3600) - (minutes * 60);

        if (hours   < 10) {hours  = "0"+hours;} 
        if (minutes < 10) {minutes = "0"+minutes;}
        if (seconds < 10) {seconds = "0"+seconds;}

        return (hours != "00")? hours+':'+minutes+':'+seconds : minutes+':'+seconds;
      };
      
      Util.init("analytics");
      //initilize Balls Plugins
      if(userSession && userSession.id){
        Util.init("player");
        Util.init("demoPlayer"); 
        Util.init("state");       
        Util.init("userbar");     
        Util.init("follow");
        Util.init("modal");
        Util.init("video");
        Util.init("easterEggs");
      }else{
        require(['balls.account'], function () {
          balls.account.events();
        });
      }
      this.nav();
      this.events();
      return true;
    },
    nav : function (){
      if(userSession.id && userSession.id != 0 && userSession.id != ""){
        //For dropdown menu in nav bar
        $("nav").on('click', "li.more span", function(e){
          e.stopPropagation();
          $(this).toggleClass('clicked');
          $("ul.dropdown").toggleClass('visible');
        });
        $("html").on("click", function() {
          $("nav li.more span").removeClass('clicked');
          $("nav ul.dropdown").removeClass('visible');
        });
        $(".options").on('click', ".button", function(){
          $(this).toggleClass('clicked');
          $(this).next(".dropdown").toggleClass('visible');
        });
      }
    },
    //----- Global Events -----
    events: function () {
      // TERMS OF SERVICE / PRIVACY POLICY
      $("body").on("click", ".bottomBar a, nav .dropdown a:not([href])", function(e){
        e.preventDefault();
        var t = $(this),
            tos = $("body .tosPopover");
        if( t.parent().hasClass("locked") )
          return false;

        if( t.hasClass("tos") )
          tos.removeClass("PP").addClass("TOS").removeClass("hidden");
        else if( t.hasClass("pp") )
          tos.removeClass("TOS").addClass("PP").removeClass("hidden");
        else
          return false;
      });
      $("body .tosPopover .close").on("click", function(e){
        e.preventDefault();
        $("body .tosPopover").addClass("hidden").removeClass("TOS").removeClass("PP");
        return false;
      });
      $("body .tosPopover").on("click", function(e){
        e.preventDefault();
        $("body .tosPopover ." + ($(this).hasClass("TOS") ? "terms" : "privacy") + " .close").click();
        return false;
      });
      $("body .tosPopover .terms, body .tosPopover .privacy").on("click", function(e){ e.preventDefault(); return false; });
      // END TERMS OF SERVICE / PRIVACY POLICY

      // Dig/Undig event watcher
      $("body").on("click", function (e) {
        if(e.target.className.indexOf("dig") > -1 || e.target.className.indexOf("undig") > -1){
          e.preventDefault();
          require(['balls.dig'], function(){
            balls.dig.fire(e.target);
          });
        }
      });
      $(window).on("unload", function(){
        //console.log("unload");
        require(["balls.player"],function(){
          balls.ePlayer.pause();
					balls.playlist.active.pause();
        });
      });
    },
    userMessage: function (msg) {
      if(window.toast){
        window.toast.message(arguments);
      }else{
        console.info("User Message:", msg);
      }
    }
  };
  Util = {
        exec: function (controller, action) {
            var self = this,
            ns = balls, action = (action === undefined) ? "init" : action;
            if (controller !== "" && ns[controller] && typeof ns[controller][action] === "function" && !balls.global.on[controller+"-"+action]) {
              ns[controller][action]();
              //console.log("controller:", controller, "action:", action);
              //ns.global.on[controller] = true;
            }
        },
        init: function () {
            var section = $('body>section[id^="/"]'),
            argsArr = section.attr("id") ? section.attr("id").split("/") : null,
            controller = (arguments[0] && typeof arguments[0] == "string")? arguments[0] : argsArr?argsArr[1]:null,
            action = (arguments[0] && typeof arguments[0] == "string")? null : argsArr && argsArr[2] == "form"?"form":null,
            trigger = (arguments[1] && typeof arguments[1] == "boolean")? arguments[1] : null, postType, I, J, dummy;
            
            try{
              if(controller){
                if(controller.search(/member|profile|users/)>-1){               postType = "user";      }
                else if(controller.search(/music|songs/)>-1){                   postType = "song";      }
                else if(controller.search(/news|article|posts/)>-1){            postType = "post";      } 
                else {                                                          postType = controller;  }
                
                action = !action && (/news|members|articles|music|songs|posts|users/).test(controller) ? 'archive' : action;
                action = !action && (/profile|article|post|song/).test(controller)? "single": action;
                action = (/account/).test(controller)? "settings" : action;
              }
              if(postType){
                require(['balls.'+postType], function () {
                  Util.exec(postType, 'init');
                  dummy = (action) ? Util.exec(postType, action) : '';
                });
              }
              if((/archive|single|form/).test(action)){
                require(['balls.'+action], function () {
                  Util.exec(action);
                });
              }
            } catch (err) {
              console.log(err);
              console.log("---Section---");
              console.log(section);
              console.log("---Action---");
              console.log(action);
            }
        }
   };
  Util.init();
  Util.exec("global");
});
