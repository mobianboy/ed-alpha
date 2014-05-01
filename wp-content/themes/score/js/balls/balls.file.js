// Balls file upload handler
// balls.file           :   Miain balls file handler object litteral
// balls.file.typeAuth  :   Verifies file is able to be up loaded and it's properties
// balls.file.upload    :   Async file uploader method
!(function (balls){ 
  balls.file = { 
    typeAuth: function (fileExt, mimeType) {
      var fileExt = fileExt? fileExt.toLowerCase() : null,
          mimeType = mimeType? mimeType.toLowerCase() : null,
          audioFileExts = ["mp3", "ogg", "oga", "wav", "mp4", "m4a", "mp4a", "aiff", "aif"],
          audioMimes = ["audio/flac", "audio/ogg", "audio/mp3", "audio/mp4", "audio/mpeg", "audio/wav", "audio/x-aiff", "audio/x-m4a"],
          imageFileExts = ["jpg", "jpeg", "png", "svg", "tiff", "gif", "bmp"],
          imageMimes = ["image/jpg", "image/jpeg", "image/png", "image/tiff", "image/gif", "image/bpm"],
         	fileType = "";


      if(!fileExt){ return "No File Extension"}
      if(!mimeType){ return "No Mime Type"}
      
      fileType = (audioFileExts.inArray(fileExt) && audioMimes.inArray(mimeType))? "audio" : fileType; 
      /*for(I=0; I<audioFileExts.length;I++){
        if(audioFileExts[I] == fileExt){
          for(I=0; I<audioMimes.length;I++){
            if(audioMimes[I] == mimeType){
              fileType = "audio";
              break;
            }
          } 
          break;
        }
      }*/
      fileType = (fileType !== "audio" && imageFileExts.inArray(fileExt) && imageMimes.inArray(mimeType))? "image" : fileType;
      /*if(fileType !=="audio"){
        for(I=0; I<imageFileExts.length;I++){
          if(imageFileExts[I] == fileExt){
            for(I=0; I<imageMimes.length;I++){
              if(imageMimes[I] == mimeType){
                fileType = "image";
                break;
              }
            }
            break;
          }
        }
      }*/
      if(fileType === ""){
        balls.file.fail("Wrong file extention or mime type.<br />------Your File------<br />File Extention: "+fileExt+"<br />File Mime type: "+mimeType);
      }

      return (fileType !== "")? fileType : false;
    },
    upload:function() {
      var file = arguments[0] ? arguments[0] : null,
      ownerOfFileId = arguments[1] ? arguments[1] : null,
      uploadType = arguments[2] && typeof arguments[2] != "function" ? arguments[2] : null,
      callBack = arguments[2] && typeof arguments[2] == "function" ? arguments[2] : null,
      callBack = arguments[3] && typeof arguments[3] == "function" ? arguments[3] : callBack,
      xhr = new XMLHttpRequest(),
      name = file.name.toString(),
      fileExt = name.substring(name.lastIndexOf(".")+1, name.length),
      fileType = this.typeAuth(fileExt, file.type);
      
      if(fileType === false){ return "Bad file extention or mime type";}
       
      // progress bar
      var progress = $("<div class='progress'><div class='bar'></div><span></span></div>");
      if($(".profileImageUploadWashout").css("display") == "block"){
        $(".profileImageUploadWashout").find(".profileImageUpload .wrapper").append(progress);
      }else{
        $("section.contribute .dropZone").append(progress);
        progress.find("span").append("Media: " + file.name);
      }
      xhr.upload.addEventListener("progress", function(e) {
        var pc = parseInt((e.loaded / e.total * 100), 10);
        if(pc < 90 || pc > 99){
          progress.find(".bar").css('width', pc + "%");
        }
      }, false);

      // file received/failed
      xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
          if(xhr.status == 200){
            progress.html("Upload complete!");
            setTimeout(function () {
              progress.remove();
              callBack(xhr.responseText);
            }, 1000);
          }else{
            balls.file.fail("Unknown Server Error. Please try again in a moment.", xhr);
            callBack(false);
          }
        }
      };  
      // start upload
      if (xhr.upload && file != null){
        var formData = new FormData();
        formData.append("mfp_upload", file);
        formData.append("data", JSON.stringify({
          format:fileType,
          type:uploadType,//'profile' || null,
          id:ownerOfFileId
        }));
        xhr.open("POST", "http://"+userSession.mfpServer+"/upload");
        //xhr.setRequestHeader("Content-Type", "multipart/form-data");
        xhr.send(formData);
      }else{
        balls.file.fail("file is null or xhr.upload isn't available");
      }
      return xhr;
    },  
    fail: function (text, xhr) {
      balls.state.fluffer("stop");
      balls.error(text+" - Please see console for more");
			console.log(xhr);
    }   
  };
})(window.balls);
