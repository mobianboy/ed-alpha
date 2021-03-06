// Balls messaging module
// balls.message                    :   Messages Object litteral
//
// balls.message.api								:		URL for messagse API
//
// balls.message.timeout            :   Timout object for fetching messages
// balls.message.timeout.obj        :   Timeout current messages api request object
// balls.message.timeout.time       :   TTL time limit for requests
//
// balls.message.init               :   Iniltilization method for balls message handling
// balls.message.events             :   Events watchers for messages widget
// balls.message.compose            :   Compse prep action. Builds the compose screen into the widget and activates the options
// balls.message.delete             :   Delete message or thread by postID (id)
// balls.message.read               :   Marks a thread as read
// balls.message.push               :   Long pull or request of 'new' messages (callBack)
// balls.message.getThread          :   Gets List element and thread data without the message by convo ID (id)
// balls.message.getConversation    :   Gets actual message by convo id (id)
// balls.message.send               :   Send new message with Convo Id, and data (id, data)
// balls.message.ttl                :   TTL timeout for fetching messages via long pull, takes in time in miliseconds (time)

!(function (balls){  
  balls.message = {
    api: "/wp-content/plugins/message/api.php",
		timeout:{
      obj:null,
      timeout:10000
    },
    init:function () {
      this.events();  
      //this.ttl();
    },
    events: function () {
      var self = this;
      //new conversation start/fetch conversation
      $("body").on("click", ".widget.message .context .threads ul li", function (e) {
        e.stopPropagation();
        $(this).hasClass("compose")? self.compose() : self.getConversation($(this).attr("data-content"));
      });
      //send message
      $("body").on("click", ".widget.message .context .reply div.button", function () { 
        self.send(); 
      });
      //delete message/conversation
      $("body").on("click", ".widget.message .delete", function (e){
        e.stopPropagation();
        self.delete($(this).parent().attr("data-content"));
      });
      $("body").on("click", ".widget.message .context .conversation .userSearch .close", function () {
        $(".widget.message .context .conversation .userSearch select").prop('selectedIndex',0);
        $(".widget.message .context .conversation .userSearch").hide();
        $(".widget.message .context .conversation>ul[data-content]").remove();
        $(".widget.message .context .conversation .default").show();
      });

      /* CHOSEN Stuffs */
      require(['chosen'], function(){
        $(".widget.message .context .conversation .userSearch select").chosen({
          max_selected_options: 1,
          no_results_text: "Nothing Found :("
        });
      }); 
      /* END CHOSEN Stuffs */

    },
    compose: function () {
        $(".widget.message .context .threads ul li.compose").addClass("open").siblings().removeClass("open");
        //$(".widget.message .context .threads ul li.active").removeClass("active");
        $(".widget.message div.context div.conversation div.default").hide();
        // Delete any previous selection
        $(".widget.message div.context div.conversation div.userSearch .chzn-container-multi .chzn-choices .search-choice a.search-choice-close").click();
        $(".widget.message div.context div.conversation div.userSearch").show();
        $(".widget.message div.context div.conversation>ul[data-content]").remove();
        $(".widget.message div.context div.conversation .userSearch .chzn-choices .search-field input").show();
      //if messages already exist load em in
    },
    delete: function (id) {
      var type = (id && id.indexOf(",") > -1)? "thread" : "message";
      if(id){
        $.ajax({url:balls.message.api, type:"POST", data:{"action":"hide", "type":type, "id":id}}).done(function (response) {
          if(response){
            $(".widget.message [data-content='"+id+"']").remove();
            try{
              toast.success("Message(s) removed");
            }catch(e){
							require(['balls.error'], function(){
								balls.error("", "Exception on call to toast.success in message.delete", {logObject:e, suppressSystem:userSession.isLive});
							});
            }
          }
          if($(".widget.message .context .conversation > ul li").length < 1){
            $(".widget.message .context .threads ul li[data-content='"+$(".widget.message .context .conversation>ul").attr("data-content")+"']").remove();
            $(".widget.message .context .conversation>ul[data-content]").remove();
          }
        });
      }
    },
    read: function (parent) {
      var parent = parent? parent : $(".widget.message .context .conversation>ul").attr("data-content") || null;
      if(parent){
        $.ajax({url:balls.message.api, type:"POST", data:{"action":"read","type":"thread", "id":parent}}).done(function (response) {
          if(response){
            $(".widget.message .threads ul li[data-content='"+parent+"'] span.unread").html("");
            $(".widget.message .threads ul li[data-content='"+parent+"']").removeClass("unread");
          }
        });
      }
    },
    push: function (cb) {
      cb = cb && typeof cb === "function"? cb : function() {};
      $.ajax({url:balls.message.api, type:"POST", data:{"action":"push"}}).done(function (response) {
        response = $.parseJSON(response);
        var total = 0;
        if(response){
          for(id in response){
            if(response.hasOwnProperty(id)){
              var html = response[id];
              html = $(html.replace(/\s\n/, ""));
              //Apply new count to the thread, if the thread doesn't exist go get it.
              if($(".widget.message .context .threads ul li[data-content='"+id+"']").length > 0){
                $(".widget.message .context .threads ul li[data-content='"+id+"'] span.unread").html(html.length);
                $(".widget.message .context .threads ul li[data-content='"+id+"']").addClass("unread");
                total = total+html.length
              }else{
                balls.message.getThread(id);
              }
              //Apply the new content to the conversation if it's open
              if($(".widget.message .context .conversation>ul[data-content='"+id+"']").length > 0){
                $(".widget.message .context .conversation>ul[data-content='"+id+"']").append(html);
                balls.message.read(id);
              }
              //Apply the new count to the userbar
              if(total>0) {
                $(".drop_zone .buttons .btn#btn_message div.new").html(total);
                $(".widget.message .wrapper h2").html("Messages <em>( " + total + " unread )</em>");
                if(total==1){
                  toast.info(html);
                }else{
                  toast.info("You have "+total+" new messages"); 
                }
              }
              else
                $(".widget.message .wrapper h2").html("Messages");
            }
          }
          //resort list
        }
        cb();
      }).fail().always(function(response){
        cb(); 
      });
    },
    getThread: function (id) {
      if(id){
        $.ajax({url:balls.message.api, type:"POST", data:{"action":"get", "id":id}}).done(function (response) {
          response = $.parseJSON(response);
          if(response && response.html){
            if($(".widget.message .context .threads ul li[data-content='"+id+"']").length > 0){
              $(".widget.message .context .threads ul li[data-content='"+id+"']").replaceWith(response.html);
            }else{
              $(".widget.message .context .threads ul li.compose").after(response.html);
            }
            $(".widget.message .context .conversation div.reply input").val("").stop().focus();
          }
        });
      }
    },
    getConversation: function (parent){
      parent = parent? parent : $(".widget.message .context .conversation>ul").attr("data-content") || null;
      if(parent){
        $.ajax({url:balls.api, type:"POST", data:{"post-type":"message", "template":"archive", "content":parent}}).done(function (response) {
          response = $.parseJSON(response);
          if(response && response.html){
            var convoBox = $(".widget.message .context .conversation");
            convoBox.find("ul[data-content]").remove();
            convoBox.find(".default").hide();
            convoBox.find(".userSearch").after(response.html);
            convoBox.find("ul").scrollTop(convoBox.find("ul")[0].scrollHeight).stop().focus();
            if($(".widget.message .context .threads ul li.compose").hasClass("open")) {
              $(".widget.message .context .conversation .userSearch .close").click();
            }
            $(".widget.message .context .threads ul li.thread[data-content='"+parent+"']").addClass("open").siblings().removeClass("open");
            balls.message.read(parent);
          }
        });
      }
    },
    send:function (parent, data) {
      parent = parent? parent : $(".widget.message .context .conversation>ul").attr("data-content") || null;
      data = data? data : $(".widget.message .context .conversation .reply input").val() || null;

      var userSearch = $(".widget.message .context .conversation .userSearch select");
      parent = (userSearch.prop("selectedIndex")>0)? userSearch.val()[0]+","+userSession.id : parent;
      parent = parent.split(",");
      parent = parent.sort(function(a,b){return a-b});
      parent = parent.join(",");
      
      if(parent && data){
        $.ajax({url:balls.message.api, type:"POST", data:{"action":"send", "parent":parent, "data":data}}).done(function (response) {
          response = $.parseJSON(response);
          if(response && response.html){
            //$(".widget.message .context .conversation>ul").attr("data-content", parent).append(response.html);
            $(".widget.message .context .conversation .userSearch").hide();
            userSearch.prop("selectedIndex", 0);
            if($(".widget.message .context .threads ul li[data-content='"+parent+"']").length < 1)
              balls.message.getThread(parent);
            balls.message.getConversation(parent);
          }else{
            $(".widget.message .context .conversation div.default").html("Failed to send").show(500);
            setTimeout(function () {
              $(".widget.message .context .conversation div.default").hide(500).html("No Conversation");
            }, 1000);
          }
          try{
            toast.info("Message sent");
          }catch(e){
						require(['balls.error'], function(){
							balls.error("", "Exception on call to toast.info in message.send", {logObject:e, suppressSystem:userSession.isLive});
						});
          }
          $(".widget.message .context .conversation div.reply input").val("").stop().focus();
        });
      }
    },
    ttl: function (time) {
      time = time ? time : balls.message.timeout.time;
      balls.message.timeout.obj = setTimeout(function () {
        balls.message.push(function () {balls.message.ttl()});
      }, time);
    }
  };
})(window.balls);
