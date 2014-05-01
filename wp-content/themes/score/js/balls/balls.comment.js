// Balls comment Post Type
// balls.comment          :   Main Comment controller Object LItteral
// balls.comment.init     :   Initilize comment base
// balls.comment.events   :   Comment based events
// balls.comment.get      :   get comments on parent PostId (parentId, lastCommentId, callBack)
// balls.comment.submit   :   post a comment fromt eh logged in user to a specific postId (targetPost)
// balls.comment.remove   :   If you're the comment owner/parent post owner you can remove the comment - (commentId)

!(function (balls){  
  balls.comment = {
    init: function () {
      this.events();
    },
    events: function () {
      var self = this;
      $("body").on("click", ".comments .submitComment, .comments .delete", function (e) {
        if(this.className.indexOf("submitComment") > -1){ self.submit(this); }
        if(this.className.indexOf("delete") > -1){ 
          self.remove(this); 
          $(this).parents("div.edit").removeClass("hidden");
        }
      });
      $("body").on("keyup", ".comments textarea", function(e){
        if(e.keyCode == 13){
          $(this).parent().next().click();
				}
      });

      // show gear menu
      $("body").on("click", ".comment > .edit .gear", function(e){
        var parent = $(this).parent();
        if( parent.hasClass("hidden") ){
          $(".edit:not(.hidden)").addClass("hidden");
          parent.removeClass("hidden");
        }else{
          parent.addClass("hidden");
        }
      });

    },
    get: function (parent, last, cb) {
      var self = this,
      cb = cb && typeof cb == "function" ? cb : function() { /*if(self.timeout.obj==null)self.ttl()*/ };
      if(parent){
        $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"POST", data:{"action":"push","post-type":"comment", "id":last, "parent":parent}}).done(cb);
      }
    },
    submit: function (target) {
      var self = this,
      parent = $(target).parent().parent() || null,
      comment = $(target).prev().find("textarea").val() || null,
      last = parent.find("ul li").last().attr("data-content");

      if(parent && comment){
        parent.find("textarea").val("");
        $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"post", data:{"post-type":"comment", "action":"set", "parent":parent.attr("data-content"), "comment":comment}})
          .done(function (response) {
            if(response){
              self.get(parent.attr("data-content"), last, function(response){
                response = $.parseJSON(response);
                if(response && response.html){
                  parent.children("ul").append(response.html[response.html.length - 1]);
                  toast.success("Nice comment! You got a real good point there.");
                }else{
									require(['balls.error'], function(){
										balls.error("There was an issue posting that comment, please try again.", "Failed in comment.submit response to follow", {logObject:response, suppressSystem:userSession.isLive});
									});
                }
              });
            }   
          })
          .fail(function(response){
						require(['balls.error'], function(){
							balls.error("Whops, that comment didn't go through, it must be lost in the internet somewhere.", "Failed api call in comment.submit response to follow", {logObject:response, suppressSystem:userSession.isLive});
						});
          });
      }
    },
    remove: function (target) {
      var parent = $(target).closest("li.comment[data-content]");
      $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"post", data:{"post-type":"comment", "action":"delete", "id":parent.attr("data-content")} })
        .done(function (response) {
          if(response){
            toast.success("Comment removed, maybe don't type every little thing that pops into your head next time.");
            parent.fadeOut("fast", function () { $(parent).remove() }  );  
          }
        })
        .fail(function(response){
					balls.error("Whops, we made an uh-oh, try deleting that comment again.", "Error removing comment, response to follow", {logObject:response, suppressSystem:userSession.isLive});
        });
    }//,report: function() {}
  };
})(window.balls);
