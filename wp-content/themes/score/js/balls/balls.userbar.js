// Balls userbar module
// balls.userbar                :   Userbar main object litteral
// balls.userbar.init           :   Initilize userbar, and all widgets
// balls.userbar.reset          :   Reset the userbar and close all widgets
// balls.userbar.events         :   Event watchers for userbar
!(function (balls) {  
  balls.userbar = {
      init : function () {
        var self = this;
        Util.init("notification");
        Util.init("shout");
        Util.init("message");
        Util.init("contribute"); 
        //Util.init("dw"); 
        self.events();
      },
      reset : function () {
        var parent = $("section.contribute");
        parent.removeClass("open");
        parent.find("div.selectType").show();
        parent.find("section[id*='/form/']").removeClass("open");
      },
      events: function () {
        $("body").on('click', "nav ul.dropdown li.logout a", function (e) {
            e.preventDefault();
            balls.account.logout();
        }); 
        // ScrollThis
        $("#the_playlists .playlist_list>ul").scrollThis({
          scrollNum:2,
          hoverScrollNum:1,
          hoverScrollDelay:200,
          hoverStartDelay:1000,
          cssSelectors:{
            wrapper:"container",
            buttons:"btn",
            upBtn:"up",
            downBtn:"down"
          }
        });

        //open and close widgets
        $("body div.drop_zone").on("click", ".widgetClose", function() {
           var target = $(this).parent(),
           id = target.attr("id").replace(/the_/, "btn_");
           target.removeClass("open");
           $("body div.drop_zone .sortable>.buttons div.btn#"+id).removeClass("open");
           $("#the_playlists h2 input").removeClass("open");
        });
        $("body div.drop_zone").on("click", "div.buttons>.btn, #btn_playlists", function (e){
          var self = $(this), id= self.attr("id").replace(/btn_/, ""), target = (id.length > 0)? $("div#the_"+id) : null;
          if(id.indexOf("tuner") > -1 ||  id.indexOf("dw") > -1 /*||  id.indexOf("contribute") > -1*/) return false;
          e.preventDefault(); 
          if(target == null) return false;
          
          //Toggle Target open state
          if(target.hasClass("open")){
            target.removeClass("open");
            if(self.find(".new").length > 0) self.find(".new").html("");
            $("#the_playlists h2 input").removeClass("open");
          }else{
            target.addClass("open");
            if(id=="playlists")balls.demoPlayer.close();
          }
          
          //Toggle 'other' widgets open state
          $("body div.sortable div.widgets section.widget>div, body div.sortable div#the_playlists, body div.sortable div.demoPlayer").each(function () {
            if(this.id != target.attr('id')){
              $(this).removeClass("open");
            }
          });

          //Toggle button open state
          if(self.hasClass("open")) self.removeClass("open")
          else self.addClass("open");
          self.siblings().each(function(){
            if($(this).hasClass("open") && $(this).attr("id") != target.attr("id")){
              $(this).removeClass("open"); 
            }
          });
          
        });
      }    
  };
})(window.balls);
