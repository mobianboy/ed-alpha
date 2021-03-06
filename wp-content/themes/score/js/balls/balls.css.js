// Balls CSS module
// balls.css  :   Modules for loading in new CSS style sheets on the fly based on the WP architecture
!(function (balls){
  balls.css = function (names) {
      var I, link, path, exists = false;
      if(typeof names == "object"){
        for(I in names){
          balls.css(names[I]);
        }
      } else if(typeof names == "string" && names != ""){
        path = (/\//).test(names)? names : "/wp-content/themes/score/css/"+names+"/desktop.min.css";
        styleSheets = document.getElementsByTagName("head")[0].childNodes;
        styleSheets = Array.prototype.slice.call(styleSheets).reverse();
        for(I in styleSheets){
          if(styleSheets.hasOwnProperty(I)){
            if(styleSheets[I].getAttribute && styleSheets[I].href){
              exists = (styleSheets[I].href.indexOf(path) > -1) ? true : false;
              if(exists)break;
            }
          }
        }
        if(exists == false){
          link = document.createElement("link");
          link.type = "text/css";
          link.rel = "stylesheet";
          link.href = path;
          document.getElementsByTagName("head")[0].appendChild(link);
        }
      }
  };
  balls.less = function (names) {
      require(['less'], function(){
        var I, link, path, exists = false;
        if(typeof names == "object"){
          for(I in names){
            balls.less(names[I]);
          }
        } else if(typeof names == "string" && names != ""){
          path = (/\//).test(names)? names : "/wp-content/themes/score/less/"+names+"/desktop.less";
          styleSheets = document.getElementsByTagName("head")[0].childNodes;
          styleSheets = Array.prototype.slice.call(styleSheets).reverse();
          for(I in styleSheets){
            if(styleSheets.hasOwnProperty(I)){
              if(styleSheets[I].getAttribute && styleSheets[I].href){
                exists = (styleSheets[I].href.indexOf(path) > -1) ? true : false;
                if(exists)break;
              }
            }
          }
          if(exists == false){
            //document.getElementsByTagName("head")[0].appendChild(link);
            var link  = document.createElement('link');
            link.rel  = "stylesheet";
            link.type = "text/less";
            link.href = path;
            less.sheets.push(link); 
          }
          less.refreshStyles();
        }
      });
  };  
})(window.balls);
