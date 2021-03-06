//Balls Edit In Place (eip)
//
//balls.eip                     :   Main Object Literal
//balls.eip.init                :   Initilize EIP for elements on the page with the 'data-eip' attribute
//balls.eip.edit                :   (postId, newValue, originalValue) Callback for editinPlace plugin [overrides default async request]  (sinlge EIP field)
//balls.eip.image               :   Set rules for EIP Image binding for selected (this)
//balls.eip.imagePopUp          :   Main Image Popup controller
//balls.eip.imagePopUp.build    :   Show the Image-EIP box for profile image.
//balls.eip.imagePopUp.confirm  :   Browse/Drop action follow through for submitting a profile image update with a local image (no remoate support yet)
//balls.eip.events              :   All events related to EIP actions.

!(function (balls){
balls.eip = {
  api:"/wp-content/plugins/edit-in-place/api.php",
  init: function () {
    $("[data-eip='text']").editInPlace({ element_id:"data-content", callback: balls.eip.edit });
    $("[data-eip='select']").each(function () {
      var self = $(this);
      self.editInPlace({
        element_id:"data-content",
        field_type: "select",
        select_options: self.attr("data-select"),
        callback: balls.eip.edit
      });
    });
    $("[data-eip='textarea']").each(function(){
      var self = $(this);
      self.editInPlace({
        element_id:"data-content",
        field_type:"textarea",
        textarea_cols:self.attr("data-cols"),
        textarea_rows:self.attr("data-rows"),
        callback: balls.eip.edit
      });
    });
    $("[data-eip='image']").each(function(){ balls.eip.image(this); });
    // Pass click from Edit button to image
    $("span.editPictureOverlay").on('click', function(){ $(this).prev().trigger('click'); });
  }, 
  edit: function (id, value, originalValue) {
    var self = $(this),
    type = self.attr("data-eip") || null,
    id = id? id : self.attr("data-content"),
    value = value && typeof value == "string"? value.replace(/\,/g, '') : null,
    field = self.attr("data-field"),
    action = ($("body>section[id^='/profile/']").length > 0)? "user" : (($("body>section[id^='/song/']").length > 0)? "song" : "post");

    $.ajax({type:"post", url:balls.eip.api, data:{"action":action, "id":id, "value":value, "field":field}})
          .done(function (response) {
            response = $.parseJSON(response);
            if(response != false){
              if(field=="City"){
                value = value+",";
              }else if(action==="song" && field==="genre"){
                value = self.attr("data-select").match(new RegExp("([^,]*):"+value))[1];
              }
              self.html(value);
            }else{
              self.html(originalValue);
            }

            switch(action){
              case "user":  toast.success("Profile info saved!"); break;
              case "song":  toast.success("Song info saved!");    break;
              case "post":  toast.success("Post info saved!");    break;
              default:      toast.success("New info saved!");     break;
            }
          })
          .fail(function(response){
						require(['balls.error'], function(){
							balls.error("Whops! Had some trouble saving that, please try again.", "Failed API request in eip.edit", {logObject:response, suppressSystem:userSession.isLive});
						});
          })
          .always();
    return (value==null)? false : true;
  },
  image: function () {
    var obj = (arguments[0])? arguments[0] : null;
    if(obj == null) return false;
    $(obj).on('click', balls.eip.imagePopup.build);
    balls.eip.imagePopup.events();
  },
  imagePopup:{
    build: function () {
      //populate and display pop-up
      $("body .profileImageUploadWashout").show();
    },
    confirm: function (files) {
      if(files.length > 0){
        require(['balls.file'], function () {
          for(var x=0;x<=files.length-1;x++){
                var file = files[x], ownerId = userSession.id;
                balls.state.fluffer("start");
                $("body .profileImageUpload .header .close").click();
                balls.file.upload(file, ownerId, "profile", function (response) {
                  if(!response) return false;
                  balls.state.fluffer("stop");
                  response = $.parseJSON(response);
                  if(response.data){
                    imgURL = response.data.resource.source;
                    // Change response to return url and image width and height
                    var fr = new FileReader;
                    fr.onload = function() {
                      var i = new Image;
                      i.onload = function () {
                        var h = this.height, w = this.width, 
                            ratio = this.height/this.width,
                            maxHeight = 250, maxWidth = 374;
                        if( h > maxHeight ){ ratio = maxHeight/h; h *= ratio; w *= ratio; }
                        if( w > maxWidth ){ ratio = maxWidth/w; h *= ratio; w *= ratio; }
                        $("body section section.header header.fan div.main div.mainImg > img").attr("src", imgURL);
                        $("body nav ul.user .userPic a img").attr("src", imgURL);// Navbar
                        $("body section.rail.right.user .widget .wrapper ul li a[data-content="+userSession.id+"] img").attr("src", imgURL); 
                        var uName = $("body nav ul.user .userPic a").text().trim();
                        $("body .main .wall ul.shouts img[alt="+uName+"]").attr("src", imgURL);
                      }
                      i.src = imgURL;
                    }
                    fr.readAsDataURL(file);
                  }else{
										require(['balls.error'], function(){
											balls.error("", "No Data on response in eip.confrim", {logObject:response, suppressSystem:userSession.isLive});
										});
                  }
                  return false; //This needs to be false to skip the 'save' step as this has a custom process
                });
          }
        });
      }else{
				require(['balls.error'], function(){
					balls.error("", "No Files in eip.confirm, log of 'files' to follow", {logObject:files, suppressSystem:userSession.isLive});
				});
      }
    },
    events:function (){
      $("body").on("click", ".profileImageUpload .header .close", function(e){
        $("body .profileImageUploadWashout").hide();
        $("body .profileImageUpload .confirm").hide();
        $("body .profileImageUpload .split").show();
        $("body .profileImageUpload .or").show();
      });
      $("body").on('dragover dragleave', ".profileImageUpload div.dropArea", function (e) {
        var self = $(e.target);
        if(e.type == "dragover"){
          self.addClass("hover");
        }else if(e.type == 'dragleave' || e.type== "drop"){
          self.removeClass("hover");
        }
      });
      //Allow for upload by browse     
      $("body").on('change',".profileImageUpload .wrapper input[type='file']" , function(e){
        var self = this, files = self.files;
        balls.eip.imagePopup.confirm(files);
      });
      $("body").on("click", ".profileImageUpload .wrapper .fakeBrowse", function () {
        $(this).next().click();
      });
      //Allow for upload by [drag/drop]
      $(document).on("dragover dragleave drop", function (e) {
        e.returnValue = false;
        e.preventDefault();
      });
      $("body .profileImageUpload").on('drop', ".dropArea", function(e){
        e.preventDefault();
        e.stopPropagation();
        //console.log(e);
        $("body .profileImageUpload div.dropArea").removeClass("hover");
        var data = e.originalEvent.dataTransfer, I=0, fileExt, self = $(this);
        balls.eip.imagePopup.confirm(data.files);
      });
      /*$("body .profileImageUpload").on("click", ".confirm .btn", function(){
        var self = $(this), imgURL = self.attr("data-url"),
        id = self.attr("data-content");
        $.ajax({url:balls.contribute.api, type:"POST", data:{action:"confirm", url:imgURL, id:id}})
          .done(function(response){
            $("body .header .main .mainImg img").attr("src", imgURL);

            // Update all profile images
            if(userSession.id == id){
              $("body nav ul.user .userPic a img").attr("src", imgURL);// Navbar
              $("body section.rail.right.user .widget .wrapper ul li a[data-content="+id+"] img").attr("src", imgURL); 

              var uName = $("body nav ul.user .userPic a").text().trim();
              $("body .main .wall ul.shouts img[alt="+uName+"]").attr("src", imgURL);
            }

          });
        self.attr("data-url", "");
        $("body .profileImageUpload .header .close").click();
      });*/
    }
  }
}
})(window.balls);
