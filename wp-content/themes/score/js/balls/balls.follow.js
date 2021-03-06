// Balls Follow Module
// balls.follow             :   Main follow object litteral
// balls.follow.on          :   Array of on balls.follow modules
// balls.follow.init        :   Initilizer for balls follow (executes events)
// balls.follow.events      :   Event watchers for follow actions on body
// balls.follow.accept      :   Accept response action to a follow request from a fellow fan
// balls.follow.get         :   See if you're already following a user by id (id, callBack)
// balls.follow.ignore      :   Reject response action to a follow request from a fellow fan (object)
// balls.follow.remove      :   Stop following by id, takes in (object)
// balls.follow.request     :   Request to follow a user, takes in (object)
!(function (balls) {
  balls.follow = {
    on: [],
    init: function () {
      if(this.on['init'])return false;
      this.events();
      return this.on['init'] = true;
    },
    events: function () {
      var self = this;
      $("body").on("click", function (e) {
        var target = e.target;
        className = target.className? target.className.toString().replace(/\s+/g, "") : "";
        if(className == "follow" || className == 'friend') self.request(target);
        if(className == "unfollow" || className == "unfriend" || className == "requested") self.remove(target);
        if(className == "follow_accept") self.accept(target);
        if(className == "follow_deny") self.ignore(target);
      }); 
    },
    accept: function(target){
      id = target? target.getAttribute("data-content") : null;
      if(target && id){
        $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"post", data:{"action":"set", "post-type":"follow", type:"accept", "id":id}})
          .done(function () {
            //balls api request
            var notification = $(target).parentsUntil("[data-content]").parent().attr("data-content");
            toast.success($(".notification.widget .note[data-content='"+notification+"'] .content a.template")[0].innerHTML+" is now following you!");
            $(".notification.widget .note[data-content='"+notification+"']").remove(); 
          })
          .fail(function(response){
						require(['balls.error'], function(){
							balls.error("Sorry, we had some trouble accepting that follow request, please try again later.", "Failed API request in follow.accept", {logObject:response, suppressSystem:userSession.isLive});
						});
          });
      }
    },
    get: function (id, cb) {
      cb = cb && typeof cb == "function" ? cb : null;
      $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"post", data:{action:"get", "post-type":"follow", "id":id}}).done(function(response){
        if(response){
          if(cb)cb(response);
        }else{
          if(cb)cb(response);
        }
      });
    },
    ignore: function (obj) {
      id = obj? obj.getAttribute("data-content") : null;
      if(obj && id){
        $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"post", data:{"action":"set", "post-type":"follow", "type":"reject", "id":id}})
          .done(function(){
            var notification = $(obj).parentsUntil("[data-content]").parent().attr("data-content");
            toast.success("REJECTED! What a burn, "+$(".notification.widget .note[data-content='"+notification+"'] .content a.template")[0].innerHTML+" is blocked from following you");
            $(".notification.widget .note[data-content='"+notification+"']").remove();
            // get note 'friends'
          });
      }
    },
    remove: function (obj) {
      var profileType = obj? obj.getAttribute("data-profile") : null,
      id = obj.getAttribute("data-content") ? obj.getAttribute("data-content") : $("body>section[id^='/profile/']").attr("id") || null; 
      
      if(obj == null || id == null) return false;
      id = (id.search(/\//) >-1)? id.split("/")[2]: id;
      
      $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"post", data:{"action":"set", "post-type":"follow", "type":"remove", "id":id}})
        .done(function (response) {
          response = (response.toLowerCase() == "true");
          if(response){
            obj.className = (userSession.profileType == 'fan' && userSession.profileType == profileType) ? "friend" : "follow";
            $(".widget.messages .context .conversation .userSearch select option[value='"+id+"']").remove();
            $(".widget.messages .context .conversation .userSearch select").trigger("liszt:updated");//update chosen 
            toast.success("Unfollowed! I hope you two can kiss and make up someday...");
          }else{
						require(['balls.error'], function(){
							balls.error("Problem sending unfollow request, please try again later", "Response false on follow.remove", {logObject:response, suppressSystem:userSession.isLive});
						});
          }
        });
    },
    request: function (obj) {
      var profileType = obj? obj.getAttribute("data-profile") : null,
      id = obj.getAttribute("data-content")? obj.getAttribute("data-content") : $("body>section[id^='/profile/']").attr("id") || null;

      //reciever of request
      if(obj == null || id == null) return false;
      id = (id.search(/\//) >-1)? id.split("/")[2]: id;

      $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"post", data:{"action":"set","post-type":"follow", "type":"request", "id":id}})
        .done(function (response) {
          var check = (response.toLowerCase() != "false");
          if(check){
            response = response.replace(/\"/g, "");
            obj.className = (userSession.profileType == 'fan' && userSession.profileType == profileType) ? "requested" : "unfollow";
            $(".widget.messages .context .conversation .userSearch select").prepend("<option value='"+id+"'>"+response+"</option>");//add to messages select list 
            $(".widget.messages .context .conversation .userSearch select").trigger("liszt:updated");//update chosen 
            toast.success("Hey! You are now following "+response);
          }else{
						require(['balls.error'], function(){
							balls.error("Problem sending follow request, please try again later", "Response false on follow.request", {logObject:response, suppressSystem:userSession.isLive});
						});
          }
        });
    }
  };
})(window.balls);
