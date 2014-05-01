// Balls Flaging (or reporting)
// balls.flag                 :   Main flagging Object Litteral
// balls.flag.init            :   Iniliize balls object
// balls.flag.events          :   Flagging event watchers
// balls.flag.confirmation    :   Callback for successful flagging
// balls.flag.error           :   Callback for error on flagging
// balls.flag.fire            :   Fire of a flagging on a specific object (obj)
!(function (balls) {
  balls.flag = {
    init: function (){
      this.events();
    },
    events: function () {
      $("body").on("click", function (e) {
        if(e.target.className.indexOf("flagMenu") > -1){
          var menu = $(this).next();
          if(menu.hasClass("open")) { menu.removeClass("open")  }
          else                      { menu.addClass("open")     }
        }
        if(e.target.className.indexOf("flagOption") > -1){
          self.fire(this);
        }
      });
    },
    confirmation: function () {
      //show confirmation message
    },
    error: function () {
      //show error message
    },
    fire: function (obj) {
      var self = this,
      obj = obj? $(obj) : null,
      id = obj.parentsUntil("[data-post-type]").parent()[0].getAttribute("data-content") || null,
      toggle = obj.attr("class"),
      reason = obj.attr("data-content"),
      description = obj.siblings("input[type='text']").val();

      if(obj == null || id == null || toggle == null || reason == null) return false;

      $.ajax({url:"/wp-content/plugins/social/api.php", type:"post", data:{"action":"flag", "id":id, "toggle":toggle, "reason":reason, "description":description}})
        .done(function (response) {
          self.confirmation(response);
          if(obj.hasClass("set")){  obj.removeClass("set");
          }else{                    obj.addClass("set"); }
        }).fail(function (response) {
          self.error(response);
        });
    }
  };
})(window.balls);
