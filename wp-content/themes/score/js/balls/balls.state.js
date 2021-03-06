// BALLS State
// balls.state                :     Main State Object Litteral
// balls.state.init           :     Initilization method for balls statefullness
// balls.state.events         :     Events for balls states
// balls.state.enableScroll   :     Enables default scrolling
// balls.state.disableScroll  :     Disables default scrolling
// balls.state.keyDown        :     balls.state.[enable/disable]Scroll Helper
// balls.state.preventDefault :     balls.state.[enable/disable]Scroll Helper
// balls.state.wheel          :     balls.state.[enable/disable]Scroll Helper
// balls.state.fluffer        :     balls.state all action 'wait' overlay and interupt to main template
// balls.state.reinit         :     ReIntilize the HTML Template and dump into main resource <section />, JavaScript calls and events based on the new template ({id:'templateId', html:'associatedHTML'}) 
// balls.state.html5Histroy   :     User browser 'popstate' to fetch previous template and call balls.state.fetchTemplate
// balls.state.fetchTemplate  :     Fetch new page template and call balls.state.reinit
!(function (balls){
  balls.state = {
    init: function () {
      try{
        balls.ttl.set($(".ttl"), function(){
          id = $("body>section[id^='/']").attr("id");
          
          template = (id.search(/members|news|music|songs|posts|articles|users|profiles/) > -1)? "archive" : "single";
          if(id.search(/member|profile|user/)>-1){         postType = "user"; }
          if(id.search(/music|song/)>-1){                  postType = "song"; }
          if(id.search(/news|article|post/)>-1){           postType = "post"; }
     
          if(postType){
            balls.css([postType,template]);
            //balls.less([postType,template]);
            balls[postType].init();
            if(template){
              if(balls[template]){
                balls[template].init();
              }
              balls[postType][template]();
            }
          }
        });
      }catch(e){
				require(['balls.error'], function(){
					balls.error("", "Balls.ttl probably not available. Error below:", {logObject:e, suppressSystem:userSession.isLive});
				});
      }
      this.events();
    },
    events: function () {
      var self = this;
      $(window).bind("popstate", function(data) {
        if(data){
          self.html5History(data);   
        }else return false;
      });
      $("body").on("click", function (e) {
        var target = e.target;
        if(target.parentNode && target.parentNode.nodeName == "A" && target.parentNode.className.indexOf("template") > -1){
          e.preventDefault();
          balls.state.fetchTemplate(target.parentNode.getAttribute("href")); 
        }
        if(target && target.nodeName == "A" && target.className.indexOf("template") > -1){
          e.preventDefault();
          balls.state.fetchTemplate(target.getAttribute("href"));
        }
      });
      require(['balls.event'], function(){
        $(document).on("startTemplateFetch", function () {
          balls.state.fluffer("start");
        });
        $(document).on("endTemplateFetch", function () {
          balls.state.fluffer("stop");
        });
      });
    },
    enableScroll: function () {
      if (window.removeEventListener) {
          window.removeEventListener('DOMMouseScroll', balls.state.wheel, false);
      }
      window.onmousewheel = document.onmousewheel = document.onkeydown = null;
    },
    disableScroll: function () {
      if (window.addEventListener) {
        window.addEventListener('DOMMouseScroll', balls.state.wheel, false);
      }
      window.onmousewheel = document.onmousewheel = balls.state.wheel;
      document.onkeydown = balls.state.keydown;
    },
    keyDown: function (e) {
      // spacebar: 32, pageup: 33, pagedown: 34, end: 35, home: 36
      // left: 37, up: 38, right: 39, down: 40,
      var keys = [32, 33, 34, 35, 36, 37, 38, 39, 40];
      for (var i = keys.length; i--;) {
          if (e.keyCode === keys[i]) {
              preventDefault(e);
              return;
          }
      } 
    },
    preventDefault: function (e) {
      e = e || window.event;
      if (e.preventDefault){
        e.preventDefault();
      }
      e.returnValue = false;  
    },
    wheel: function (e){
      balls.state.preventDefault(e);
    },
    fluffer: function () {
        var action = arguments[0]? arguments[0] : null,
        bucket = arguments[1]? arguments[1] : "template",
        templateFluffer = $("div.ballsFluffer");
        if(action == null) return false;
        
        switch (action) {
          case "start" :(function (bucket) {
            window.scrollTo(0, 0);
            balls.state.disableScroll();
            switch (bucket){
              case "template" : (function (){
                templateFluffer.removeClass("hidden");
              })(); break;
              case "widget" : (function () {})();break;
              default:  break;
            }
          })(bucket);break;
          case "stop" : (function (bucket) {
            balls.state.enableScroll();
            if(!templateFluffer.hasClass("hidden")){
              templateFluffer.addClass("hidden");
            }
          })(bucket);break;
          default: break;
        }
        return true;
    },
    reinit: function(){
      var data = arguments[0] && typeof arguments[0] == "object"? arguments[0] : null,
          wrapper = $("body>section[id^='/']"), postType, template;
      if(data == null) return false;
      
      //set id and new HTML
      wrapper.attr("id", data.id).html(data.html);
      /* Nav down state Set */
      $("body nav .global li").removeClass("open");
      $("body nav .global li a").each(function(){
        if(wrapper[0].id == this.getAttribute("href")){
          this.parentNode.className = "open";
        }
      });
      $("body nav .user .userPic").removeClass("active");
      if($("body nav .user .userPic").find("a").attr("href") == $("body>section[id^='/profile/']").attr("id")){
        $("body nav .user .userPic").addClass("active");
      }
      //Get postType and Template from data.id
      /* Re-init post type js if it needs */
      template = ((/members|news|music|songs|posts|articles|users|profiles/).test(data.id) || data.id === '/')? "archive" : "single";
      if((/member|profile|user/).test(data.id)){                          postType = "user"; }
      else if((/music|song/).test(data.id)){                              postType = "song"; }
      else if((/news|article|post/).test(data.id) || data.id === '/'){    postType = "post"; }
      else if((/account/).test(data.id)){                                 postType = "account";template="settings";}

      if(postType){
        balls.css([postType,template]);
        //balls.less([postType,template]);
        balls[postType].init();
        if(template){
          if(balls[template]){
            balls[template].init();
          }
          balls[postType][template]();
        }
      }
      window.templateFetch.abort();
      window.templateFetch = null;
      
      balls.ttl.reset();
      balls.ttl.set($(".ttl"), function(response){
        if(response){
          if(postType){
            balls.css([postType,template]);
            //balls.less([postType,template]);
            balls[postType].init();
            if(template){
              if(balls[template]){
                balls[template].init();
              }
              balls[postType][template]();
            }
          }
        }
      });
    },
    html5History: function () {
      var data = arguments[0]? arguments[0] : null,
      state = data? data.originalEvent.state : null;
      if(state){
        balls.state.fetchTemplate(state.id);
      }
    },
    fetchTemplate: function () {
      var newId = arguments[0] && typeof arguments[0] == "string"? arguments[0] : null,
      oldId = $("body>section[id^='/']").attr("id") || null;

      newId = newId ==='/' ? "/news/" : newId;
      
      if(self == null || newId == null)
        return false;
      
      if(window.templateFetch)
        window.templateFetch.abort();
     
      require(['balls.event'], function(){
        balls.event.dispatch("startTemplateFetch");
      });
      window.templateFetch = $.ajax({url:balls.api, data:{href:newId}, type:"post"})
          .done(function (response) {
            response = $.parseJSON(response);
            if(response && response.html){
              if(oldId){ //-------only do this if its a new state.
                stateObj = {id:newId};
                history.pushState(stateObj, "Eardish", newId);
              }
              balls.state.reinit({id:newId, html:response.html});
            }else{
							require(['balls.error'], function(){
                balls.error("Failed Balls API request. Please refresh your browser.", "Failed Template Fetch response to follow", {logObject:response, suppressSystem:userSession.isLive});
							});
            }
            require(['balls.event'], function(){
              balls.event.dispatch("endTemplateFetch");
            });
          }); 
    }
  };
})(window.balls);
