// Balls Time To Live Module
// balls.ttl            :   Main TTL object litteral
// balls.ttl.openTTLs   :   Array of Current open TTL requests
// balls.ttl.getTimeout :   Get timeout based on a wrapper class
// balls.ttl.reset      :   Reset all TTLs and clear the timeouts
// balls.ttl.set        :   Set time to live on a specific element (obj, cb);
!(function () {  
  balls.ttl = {
    openTTLs:[],
    getTimeout: function(wrapper) {
      var timeout = (wrapper.attr("class"))? wrapper.attr('class').match(/test10|test20|short|medium|long/)[0] : 1000 ;
      switch(timeout){
            case "test10" : timeout = 10000;                  break;
            case "test20" : timeout = 30000;                  break;
            case "short"  : timeout = 440000;                 break;
            case "medium" : timeout = 1000000;                break;
            case "long"   : timeout = 5400000;                break;
            default       : timeout = parseInt(timeout, 10);  break;
      }
      return timeout;
    },
    reset: function (){
      var I;
      for(I in balls.ttl.openTTLs){
        if(balls.ttl.openTTLs.hasOwnProperty(I)){
          clearTimeout(balls.ttl.openTTLs[I]);
        }
      }
      balls.ttl.openTTLs = [];
      return true;
    },
    set: function(self, cb){
        var self = self && typeof self == "object" ? $(self) : $(".ttl"),
        cb = cb && typeof cb == "function"? cb : function () {}; 

        if(!self || self.length < 1){return false;}

        var timeout = balls.ttl.getTimeout(self), I, ajaxCall,
        id = self.attr("id");
        
        if(timeout > 999 && id){
          if(balls.ttl.openTTLs[self.attr('id')])
            clearTimeout(balls.ttl.openTTLs[self.attr('id')]);

          balls.ttl.openTTLs[self.attr('id')] = setTimeout(function(){
                                ajaxCall = $.ajax({
                                        cache:false,
                                        global:false,
                                        url:balls.api,
                                        data:{href:id},
                                        type:"post",
                                        headers:{
                                          'Connection' : 'close'
                                        }
                                    }).done(function (response) {
                                      var data = $.parseJSON(response);
                                      self.find(".ttl").each(function () { clearTimeout($(this).attr("id"))});
                                      self.html(data.html);
                                      balls.ttl.set(self, cb);
                                      self.find(".ttl").each( function () {
                                        if(balls.ttl.getTimeout($(this)) < timeout){
                                          balls.ttl.set(this, cb);
                                      }});
                                    }).fail(function (response) {
																			require(['balls.error'], function(){
																				balls.error("", "Failed API request in ttl.set", {logObject:response, suppressSystem:userSession.isLive});
																			});
                                    }).always(function (response) {
                                      cb(response);
                                    });
                                  }, timeout);
                                if(ajaxCall)
                                  setTimeout(ajaxCall.abort, 5000);
        }
      }
  }; 
})(window.balls);
