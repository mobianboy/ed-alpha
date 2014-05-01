// Balls Notification module
// balls.notification                     :   Main notifations object litteral
// balls.notification.timeout             :   TTL for notifications Long polling 
// balls.notification.init                :   Initilzation method to fire events and first notifications long poll request
// balls.notification.events              :   Event watchers for Notifications
// balls.notification.getNotification     :   Long poll for new notifications (callBack)
// balls.notification.markAsRead          :   Mark all visible as 'read' up to the latest id in the list
// balls.notifications.ttl                :   TTL functionality for Long Polling (time)
!(function (balls){
  balls.notification = {
        timeout: 10000,
        init: function () {
          this.ttl();
          this.events();
        },
        events: function () {
          var self = this;
          $(".buttons div#btn_notification").on("click", function () {
              self.markAsRead();
          });
          $(".drop_zone .widgets .notification .widgetClose").on("click", self.markAsRead);
        },
        getNotification: function (cb) {
          var id = ($("section.notification div.wrapper .note").length > 0)? $("section.notification div.wrapper .note").first().attr("data-content") : null;
          if(id){
            $.ajax({url:"/wp-content/plugins/notification/api.php", type:"post", data:{"id":id, "action":"push"}})
              .done(function (response) {
                try{
                  if(response) response = $.parseJSON(response);
                  else return false;
                }catch(e){
									require(['balls.error'], function(){
										balls.error("", "Exception in notification.getNotification", {logObject:e, suppressSystem:userSession.isLive});
									});
                  return false;
                }
                if(response.html && response.html.length > 0){
                  var html = '';
                  response.html.forEach(function(elem, index, arr){
                    html += response.html[index];
                  });
                  $(".drop_zone .buttons #btn_notification .new").html(response.html.length);
                  $(".notification .wrapper .context").prepend(html);
                  toast.info("New Notes! <a onclick='$(\".buttons div#btn_notification\").click();'>Click here to check 'em out!</a>");
                }
                if(cb)cb();
            });
          }
        },
        markAsRead:function (){
          var id = $(".widgets .notification .note").first().attr("data-content") || null;
          $.ajax({url:"/wp-content/plugins/notification/api.php", type:"post", data:{"id":id, "action":"mark"}})
            .done(function (response) {
              
            });
        },
        ttl: function (time){
          var self = this,
          time = time? time : self.timeout;
          setTimeout(function () {
            self.getNotification(function () {self.ttl()});
          }, time);
        }
  }
})(window.balls);
