!(function(window, $){
  window.listTTL = [];
    $.ttl = function(){
      var self = (arguments[0] && typeof arguments[0] == "object") ? $(arguments[0]) : null,
      url = (arguments[1]) ? arguments[1] : null,
      data = (arguments[2]) ? arguments[2] : null;
      if(self == null && arguments[0] && arguments[0]=="reset"){
        for(I in window.listTTL){
          if(window.listTTL.hasOwnProperty(I))
            clearTimeout(window.listTTL[I]);
        }
        window.listTTL = [];
        return;
      }else if(self == null){return false;}

      
      var timeout = self.attr("data-ttl"), I, ajaxCall,
      getTimeout = function(timeout){
        switch(timeout){
          case "fast"   : timeout = 440000;               break;
          case "medium" : timeout = 1000000;              break;
          case "long"   : timeout = 5400000;                break;
          default       : timeout = parseInt(timeout, 10);  break;
        }
        return timeout;
      
      timeout = getTimeout(timeout);
      dataArray = (typeof data != "string")? data : data.replace(/\s/g, '').replace(/\_/g, '-').split(",");
      data = {};
      for(I in dataArray){data[dataArray[I]] = self.attr("data-"+dataArray[I]);}
      if(timeout > 999 && data.length > 0){
        if(window.listTTL[self.attr('id')])
          clearTimeout(window.listTTL[self.attr('id')]);
         
        window.listTTL[self.attr('id')] = setTimeout(function(){
                              ajaxCall = $.ajax({
                                      cache:false,
                                      global:false,
                                      url:url,
                                      data:data,
                                      type:"post",
                                      headers:{
                                        'Connection' : 'close'
                                      }
                                  }).done(function (response) {
                                    var data = $.parseJSON(response);
                                    self.children("[data-ttl]").each(function () { clearInterval($(this).attr("id"))});
                                    self.html(data.html);
                                    (new $.ttl(self, url, dataArray));
                                    self.children("[data-ttl]").each( function () {
                                      if(getTimeout($(this).attr("data-ttl")) < timeout){
                                        (new $.ttl(this, url, dataArray));
                                    }});
                                  }).fail(function () {
                                    console.log(arguments);
                                  }).always(function () {
                                    $(document).trigger('TTL-Done', data);
                                  });
                                }, timeout);
                                if(ajaxCall)
                                  setTimeout(ajaxCall.abort, 5000);
      }
    };

    $.fn.ttl = function(url, data) {
      return this.each(function(i){
        if ((typeof(options)).match('object|undefined')){ (new $.ttl(this, url, data)); }
      });
    };
})(window, jQuery);
