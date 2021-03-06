// Balls Shout Module
// balls.shout                :   Main shout object litteral
// balls.shout.timeout        :   Timeout object litteral
// balls.shout.timeout.obj    :   Ajax reference object
// balls.shout.timeout.time   :   TTL timeout for Long Polling
// balls.shout.events         :   Shout widget related events
// balls.shout.suggest        :   Takes string message, populates and opens shout widget
// balls.shout.delete         :   Delete shout by (id)
// balls.shout.get            :   Get most up to date shouts Long polling style (callBack)
// balls.shout.send           :   Send a shout to your wall or someone elses (shout, to)
// balls.shout.ttl            :   Long Polling timeout fetch for updated shouts (time)
!(function (balls) {  
  balls.shout = {
    timeout: { obj:null, time:90000},
    init:function () {
      this.events();
      this.ttl();
    },
    events: function () {
      var self = this;
      $("body").on("click", ".shoutBtn", function(e){
        e.preventDefault();
        var ta = $(this).parent().find("textarea");
        self.send(ta.val());
        ta.val("");
      });
      $("body").on("click", ".dishPost .dishInput .post, .widget.shout .context .shoutBtn", function() {
        var textarea, text, to = $("body>section[id^='/profile/']").attr("id") || null;
        textarea = $(this).parent().find("textarea"),
        text = textarea? textarea.val() : null;
        if(textarea && text.length > 0){
          self.send(text, to);
          textarea.val("");
        }
      });
      $("body").on("click", ".followingTab, .activityTab", function() {
        $(this).siblings().removeClass("active");
        $(this).removeClass("active");
        var toggle = this.className.replace(/Tab/g, '');
        $(this).addClass("active");
        $(".wall .following, .wall .activity").hide();
        $("."+toggle).show();
        if(toggle == "activity"){
          $(".wall .dishPost").removeClass("hidden");
        }else{
          $(".wall .dishPost").addClass("hidden");
        }
      });


      $("body").on("click", ".widget.shout .confirm .close", function () {
				$(".widget.shout").find(".confirm").hide().parent().find(".context").show();
				$(".widget.shout").parent().parent().find(".buttons div#btn_shout").click();
      });
      $("body").on("click", ".widget.shout .confirm .new", function () {
        $(".widget.shout").find(".confirm").hide().parent().find(".context").show();
      });
      $("body").on("click", ".shout > .edit .gear", function(){
        var parent = $(this).parent();
        if( parent.hasClass("hidden") ){
          $(".edit:not(.hidden)").addClass("hidden");
          parent.removeClass("hidden");
        }else{
          parent.addClass("hidden");
        }
      });
      $("body").on("click", ".shout > .edit .delete", function () {
        self.delete($(this).closest("[data-content]").attr("data-content"));
      });
    },
    suggest:function (msg) {
      // set text area (.widgets .shout.widget .context .input textarea) = msg
      // click #btn_shout if not(.open)
      if(msg && typeof msg === 'string'){
        $(".widgets .shout.widget .context .input textarea").first().val(msg);
        $("#btn_shout:not(.open)").click();
      }
    },
    delete: function (id) {
      $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"POST", data:{"action":"delete", "post-type":"shout", "id":id}}).done(function (response) {
        //console.log(response);
        //console.log($.parseJSON(response));
        if(response){
          response = $.parseJSON(response);
          if(typeof response === 'string'){
						require(['balls.error'], function(){
							balls.error("Whops! There was a problem removing that shout, please try again.", "Bad response on shout.delete", {logObject:response, suppressSystem:userSession.isLive});
						});
          }else{
            $("li.shout[data-content='"+id+"']").hide(200, function(){
              $("li.shout[data-content='"+id+"']").remove();
            });
            toast.success("Shout deleted! I hope that embarissing post doesn't come back to haunt you!");
          }
        }
      });
    },
    get: function (cb) {
      var self = this,
      section = $("body>section[id^='/profile/']") || null,
      userId = section.attr("id") ? section.attr("id") : null,
      last = $(".wall .activity ul li").first().attr("data-content") || null,
      cb = cb && typeof cb == "function"? cb : function() { if(self.timeout.obj==null)self.ttl(); };
      if(userId){
        $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"POST", data:{"action":"push","post-type":"shout", "id":last, "parent":userId}})
          .done(function (response) {
            response = $.parseJSON(response);
            if(response && response.html){
              $(".wall .activity>ul").prepend(response.html);
              $(".wall .activity>ul li.hidden").removeClass("hidden");
              cb();
            }
          });
      }
    },
    send: function (shout, to) {
      var self = this;
      $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"POST", data:{"action":"set","post-type":"shout", "data":shout, "id":to}}).done(function (response) {
        response = $.parseJSON(response);
        if(response && response.html){
          $(".wall .activity>ul").prepend(response.html);
          $(".wall .activity>ul li.hidden").removeClass("hidden");
          toast.success("Your shout was successfully posted!");
        }else{
					require(['balls.error'], function(){
						balls.error("We were unable to post your shout, please try again.", "Bad Response on shout.send", {logObject:response, suppressSystem:userSession.isLive});
					});
        }
      }).fail(function(response){
				require(['balls.error'], function(){
					balls.error("We were unable to post your shout, please try again.", "Failed API call in shout.send", {logObject:response, suppressSystem:userSession.isLive});
				});
      });
    },
    ttl: function (time){
      var self = this;
      time = (time)? time: self.timeout.time;
      self.timeout.obj = setTimeout(function () {
        self.get(function () {self.ttl()});
      }, time);
    }
  };
})(window.balls);
