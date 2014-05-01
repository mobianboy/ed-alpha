// Balls Account
// balls.account                            :   Balls Account Main Object litteral
// balls.account.on                         :   Already Fired balls.account Methods full pass
// balls.account.init                       :   If (VOID) fire first time events. If (username, pass, repass, and profType) Validate the users account setup/initilization
// balls.account.events                     :   Main events for Account Initilization on splash page
// balls.account.settings                   :   Account settings template events
// balls.account.changeSetting              :   Set new value for *setting* then execute a callback based on it's success  - (setting, value, cb)
// balls.account.requestForgotToken					:		Param: user email, request a password reset for given email address.
// balls.account.changePassword							:		Param: old password, new password, new password confirm, [email, cb], sets the users password
// balls.account.preReg                     :   Request registration token  with email submittal  - (email)
// balls.account.validateReg                :   Validate email and token from preRegistration for account Initilization - (email, token)
// balls.account.setPassword                :   Method to request setting of current password to somehting new - (old, newPass, newRePass)
//
// balls.account.keyAvail                   :   Main Object Litteral for key availablility
// balls.account.keyAvail.activelyChecking  :   List of currently being checked keys value availability
// balls.account.keyAvail.check             :   Active request to see if a key is available
//
// balls.account.login                      :   Login an user - (email, password)
// balls.account.logout                     :   Logout an user [currently logged in user]

!(function (balls){
balls.account = {
  on:[],
  init: function (username, pass, repass, profType) {
    if(arguments.length == 0){
      this.events();
    }else if(arguments.length == 4){
      if(username && pass && repass && profType && userSession.email){
        if(!username.isValidUsername()){
           balls.error("Sorry but your username does not fit the requirements.");
           return false;
        }
        if(!pass.isValidPassword()){
          balls.error("Your password does not fit the requirements.");
          return false;
        }
        if(pass != repass){
          balls.error("The two passwords did not match. Please try again.");
          return false;
        }
        
        $.ajax({url:"wp-content/plugins/user/api.php",type:"post", data:{action:'register', password:pass, confpass:repass, username:username, profile:profType, email:userSession.email}})
          .done(function (response) {
            userSession.profileType = profType;
            response = $.parseJSON(response);
            if(response && response.data){
              //remove:
              //  splashOverride, credentials, logo, joinTheEvolution, content, submitEmail, bottomBar, popupWrap, 
              //add:
              //  userbar/remote/remote.php
              //  <nav>userbar/menu/menu.php</nav>
              //  <section class="ttl long" id=""></section>
              //  ballsFluffer
              //  tpl/campaign/single.php
              toast.success("Account created! Welcome to Eardish!");
              $.ajax({url:"/profile/"}).done(function(html){
                $("body").html($(html).find("body").html());
              });
              //balls.state.fetchTemplate(response.slug);
            }else{
							balls.error("There was a problem initilizing your account. "+response, "Response object to follow:", {logObject:response, suppressSystem:userSession.isLive});
            }
          });
      }else{
        balls.error("Sorry but you seem to be missing some information, please try again.", "See object for information passed from user",
					{
						logObject:{
								'Username':username,
								'Password':pass,
								'RePass'  :repass,
								'ProfType':profType,
								'Email'		:userSession.email
						},
						suppressSystem:userSession.isLive
				});
				/*
        console.log("Username:", username);
        console.log("Password:", pass);
        console.log("RePass:", repass);
        console.log("ProfType:", profType);
        console.log("Email:", userSession.email);
				*/
        return false;
      }
    }
  },
  events: function () {
    var account = this;
    $("body .credentials input").on("keyup", function (e) {
      if(e.keyCode == "13"){
        $("body .credentials div.button").click();
			}
    });
    $("body .credentials div.button").on("click", function(e){
      e.preventDefault();
      balls.account.login($("body .credentials>input[type='text']").val(),$("body .credentials>input[type='password']").val(),$("body .credentials input[name='rememberMe']:checked").val()||false);
    }).on("keypress", function(e) {
      if(e.which === 32 || e.which === 13){ $(this).click(); }
    });
    // ACCOUNT INIT EVENTS 
    $("body").on("click", "div.popupWrap>div.accInitPopup>button.submit", function (e) {
      e.preventDefault();
      var wrapper = $(this.parentNode.parentNode),
      username = wrapper.find("input.username").val(),
      pass = wrapper.find("input.password").val(),
      repass = wrapper.find("input.passwordConfirm").val(),
      profType = wrapper.find("input[name='accType']:checked").val();
      
      account.init(username, pass, repass, profType);
    });
    $("body").on("keyup", "div.popupWrap div.accInitPopup>input.username", function (e) {
      e.preventDefault();
      account.keyAvail.check('checkusername', 'username', $(this).val());
    });
    $("body").on("click", "div.popupWrap>div.accInitPopup>span.close", function (e){
      e.preventDefault();
      $(this.parentNode.parentNode).addClass("hidden");
      //cleanUP
    });

    //Show Token Registraion field
    $("body").on("click", "div.submitEmail>div.continue>a.showTokenField", function () {
      $("body div.submitEmail input").removeClass("hidden").addClass("two");
      $("body div.submitEmail div.getStarted img").attr("src", "http://"+userSession.cdn+"/images/helloCircle.png");
      $("body div.submitEmail div.button").removeClass("button").addClass("button2");
      $("body div.submitEmail div.button2 img").attr("src", "http://"+userSession.cdn+"/images/goButtonB.png");
      $("body div.submitEmail div.continue").addClass("hidden");
      $("body div.submitEmail div.noToken").removeClass("hidden");
    });
    $("body").on("click", "div.submitEmail>div.noToken>a.backToPreReg", function () {
      $("body div.submitEmail input").removeClass("two");
      $("body div.submitEmail input+input").addClass("hidden");
      $("body div.submitEmail div.getStarted img").attr("src", "http://"+userSession.cdn+"/images/getStarted.png");
      $("body div.submitEmail div.button2").removeClass("button2").addClass("button");
      $("body div.submitEmail div.button img").attr("src", "http://"+userSession.cdn+"/images/goButtonA.png");
      $("body div.submitEmail div.continue").removeClass("hidden");
      $("body div.submitEmail div.noToken").addClass("hidden");
    });

    //registration box events and setup
    //balls.css("/wp-content/themes/score/js/lib/jquery/plugins/motioncaptcha/jquery.motionCaptcha.0.2.css")
    /*$("#captcha").motionCaptcha({
      onSuccess:function () {
        //submit email.
      },
      onError: function (form, canvas, ctx) {
        console.log(ctx);
      }
    });*/
    
    $("body").on("click", "div.submitEmail>div.button", function(e) {
      e.preventDefault();
      account.preReg($(this.parentNode).find("input").first().val());
    });
    $("body").on("click", "div.submitEmail>div.button2", function (e) {
      e.preventDefault();
      var wrapper = $(this.parentNode);
      //validate captcha
      account.validateReg(wrapper.find("input[name='email']").val(), wrapper.find("input[name='token']").val());
    });
    $("body").on("keyup", "div.submitEmail input[type='text']", function(e){
      e.preventDefault();
      if(e.keyCode == "13"){
        $(".submitEmail div.button, submitEmail, div.button2").click();
      }
    });
    $("body .submitEmail .button, body .submitEmail .button2").on("mouseover mouseout", function(e){
      var img = $(this).find("img"),
          src = img.attr("src");
      if(src.search("sent") < 0){
        e.preventDefault();
        img.attr("src", (e.type == "mouseover")? src.replace(/\.png/g, '-hover.png') : src.replace(/\-hover/g, ''));
      }
    });
		
		// Splash Page Forgot Pass Stuff
		if(userSession.forgotToken && userSession.forgotToken !== ""){
			try{
				account.forgotPassword();
			}catch (e){
				balls.error("", "Error in account.forgotPassword", {logObject:{'e':e}, suppressSystem:userSession.isLive});
			}
		}
			// open popup
		$("body").on("click", ".credentials .forgotPass a", function(e){
			try{
				account.forgotPassword();
			}catch (e){
				balls.error("", "Error in account.forgotPassword", {logObject:{'e':e}, suppressSystem:userSession.isLive});
			}
		});
		// Tooltip
		$("body").on("mouseenter mouseleave", ".credentials .forgotPass", function(e){
			if(e.type === 'mouseenter'){
				$(this).find(".tooltip").removeClass("hidden");
			}else if(e.type === 'mouseleave'){
				$(this).find(".tooltip").addClass("hidden");
			}
		});
		$("body").on("click", ".credentials .forgotPass .tooltip .close", function(){
			$(this.parentNode).addClass("hidden");
		});	
	},
	forgotPassword: function(){
		var account = this,
				validToken = function(str){
					return (typeof str === 'string') ? str.length === 32 : false;
				},
				autoPopulateModal = function(){
					var email = document.querySelector(".modalWrapper .modal .forgotPass div input#fpEmail"),
							token = document.querySelector(".modalWrapper .modal .forgotPass div input#fpToken"),
							userName = document.querySelector("body .credentials input#username");

					if(email && userSession.email.isValidEmail()){
						email.value = userSession.email;
						email.setAttribute("disabled", "disabled");
						email.previousElementSibling.innerHTML = "You will receive a token at this address:";
					}else if(userName && email && userName.value && userName.value.isValidEmail()){
						email.value = userName.value;
					}
					if(token && validToken(userSession.forgotToken)){
						token.value = userSession.forgotToken;
						$(".modalWrapper .modal .forgotPass div a.next.token").click();
						return true;
					}
					if(email.value === "" && token.value === ""){
						$(email).focus();
					}
				};
		// events
		if(!account.on['forgotPassword']){
			// keyup
			$("body").on("keyup", ".modalWrapper .modal .forgotPass div input", function(e){
				var target = e.target,
						send = $(".modalWrapper .modal .forgotPass div button.send"),
						link = $(".modalWrapper .modal .forgotPass div a.next"),
						email = $(".modalWrapper .modal .forgotPass div input#fpEmail"),
						token = $(".modalWrapper .modal .forgotPass div input#fpToken");

				// remove spaces from email and token
				if((/fpEmail|fpToken/).test(target.id) && this.value && (/ /).test(this.value)){
					this.value = this.value.trim();
				}
				// if enter on email send request 
				// else if email and token valid show paswords
				if(e.which === 13 && target.id === "fpEmail" && target.value.isValidEmail()){
					send.click();
				}else if( (/fpEmail|fpToken/).test(target.id) && email[0].value.isValidEmail() && validToken(token[0].value) && link.hasClass("pass")){
					link.click();
					target.parentNode.previousElementSibling.innerHTML = "Please fill in the password fields to finish and set your password.";
				}
			});
			// cancel
			$("body").on("click", ".modalWrapper .modal .forgotPass div .cancel", function(){
				$(this.parentNode.parentNode).find("button.close").click();
			});
			// link
			$("body").on("click", ".modalWrapper .modal .forgotPass div a.next", function(){
				var self = $(this),
						selector = ".modalWrapper .modal .forgotPass div label[for=ID]";

				if(self.hasClass("token")){
					$(selector.replace("ID", "fpToken")).removeClass("hidden").next().removeClass("hidden");
					self.removeClass("token").addClass("pass");
					self[0].innerHTML = "Show Password Fields";
				}else if(self.hasClass("pass")){
					$(selector.replace("ID", "fpNewPass")).removeClass("hidden").next().removeClass("hidden").next().removeClass("hidden").next().removeClass("hidden");
					self.removeClass("pass").addClass("reset");
					self[0].innerHTML = "Reset Form";
				}else if(self.hasClass("reset")){
					$(selector.replace("ID", "fpEmail")).siblings("label, input:not([id=fpEmail])").addClass("hidden");
					$(selector.replace("ID", "fpEmail")).siblings("input:not([disabled])").val("");
					self.removeClass("reset").addClass("token");
					self[0].innerHTML = "Already have a token?";
				}
			});
			// auto focus on transition end
			$("body").on("transitionend webkitTransitionEnd oTransitionEnd", ".modalWrapper .modal .forgotPass div", function(e){
				$(this).find("input").each(function(){
					if(this.value === ""){
						$(this).focus();
						return false;
					}
				});
			});
			// send
			$("body").on("click", ".modalWrapper .modal .forgotPass div .send", function(e){
				var self = this,
						link = $(self).siblings("a.next"),
						parent = $(e.target.parentNode),
						email = parent.find("#fpEmail")[0].value,
						token = parent.find("#fpToken")[0].value,
						newPass = parent.find("#fpNewPass")[0].value,
						confPass = parent.find("#fpConfPass")[0].value;

				if(email && token && newPass && confPass && email.isValidEmail() && validToken(token) && newPass.isValidPassword() && confPass.isValidPassword() && newPass === confPass){
					// forgotReset
					account.changePassword(token, newPass, confPass, email, function(response){});
					return true;
				}
				if(email && email.isValidEmail() && (token === "" && newPass === "" && confPass === "")){
					// request token
					account.requestForgotToken(email);
					if(userSession.id !== 0){
						return true;
					}
					if(link.hasClass("token")){
						link.click();
					}
					self.parentNode.previousElementSibling.innerHTML = "Now Paste the token from the email you received to continue.";
					return true;
				}
				// error mesgs
				if(!email || !email.isValidEmail()){
					// invalid email
					balls.error("I'm sorry but that doesn't look like the correct email address", "Email did not pass valid email test", {logObject:{'email':email}, suppressSystem:userSession.isLive})
				}else if(!token || !validToken(token)){
					// incorrect token length
					balls.error("The token doesn't appear to be the correct length, did you add any extra spaces by accident?", "Token did not pass valid token test", {logObject:{'token':token}, suppressSystem:userSession.isLive})
				}else if(!newPass || !newPass.isValidPassword()){
					// newPass invalid
					balls.error("The first password doesn't appear to match the requirements, please try again", "NewPass did not pass valid password test", {logObject:{'newPass':newPass}, suppressSystem:userSession.isLive})
				}else if(!confPass || !confPass.isValidPassword()){
					// confPass invalid
					balls.error("The second password doesn't appear to match the requirements, please try again", "ConfPass did not pass valid password test", {logObject:{'confPass':newPass}, suppressSystem:userSession.isLive})
				}else if(newPass && confPass && newPass !== confPass){
					// passwords don't match
					balls.error("It appears the password fields don't match, please try again.", "NewPass !== ConfPass", {logObject:{'newPass':newPass, 'confPass':confPass}, suppressSystem:userSession.isLive})
				}
				return false;
			});
			account.on['forgotPassword'] = true;
		}

		// get template, open modal
		if(userSession && userSession.id === 0 && document.querySelector("body .credentials")){
			var html = document.querySelector("body .credentials .forgotPass .hidden .forgotPass").cloneNode(true);
			require(['balls.modal'], function(){
				balls.modal.html(html).style.marginTop = '-400px';
				autoPopulateModal();
			});
		}else{
			$.ajax({url:balls.api, data:{action:"template", "post-type":"account", "template":"forgotPassword", content:""}, type:"post"})
				.done(function(res){
					if(res){
						try{ res = $.parseJSON(res); }
						catch(e){ balls.error("", "boo json", {logObject:{'e':e, 'res':res}, suppressSystem:userSession.isLive}); }
						if(res.html){
							require(['balls.modal'], function(){
								balls.modal.html(res.html).style.marginTop = '-400px';
								autoPopulateModal();
							});
						}
					}
				})
				.fail(function(res){
					balls.error("Unable to obtain forgot password dialog, please try again", "Failed API call on account.forgotPassword", {logObject:{'response':res}, suppressSystem:userSession.isLive});
				});
		}
	},
	settings: function() {
		var account = this,
				// Helper function for opening account settings page popups.
				openPopup = function(className){
					if(className && typeof className !== "string"){ return false; }
					var popups = document.querySelector("body section div.popups"),
							toOpen = popups.querySelector("."+className);
					if(popups && toOpen){
						$(toOpen).removeClass("hidden").siblings().addClass("hidden");
						$(popups).removeClass("hidden");
					}
				},
				closePopups = function(){
					$("body > section > div.popups").addClass("hidden").children().addClass("hidden");
				};

		if(userSession.forgotToken.length === 32){
			account.forgotPassword();
		}

    // CHOSEN STYLES
    // Profile Visibility
    require(['chosen'], function() {
      $("body section[id*='settings'] .profVisibility .options select").chosen({
        // chosen options
        disable_search: true,
        allow_single_deselect: false
      });
      // Language
      $("body section section.language .options select").chosen({
        disable_search:true,
        allow_single_deselect:false
      });
      // Block list Popup
      $("body section .popups .privacyList .blockList").chosen({
        no_results_text: "No Members Found :("
      });
    });
    // END CHOSEN

    if(account.on['settings'])return true;
    // .helpBtn Buttons
    $("body").on("click", 'section[id*="settings"] > section .help .helpBtn', function(e){
      e.preventDefault(); 
      var parent = $(this).parent();
      if( parent.hasClass("hidden") ){
        parent.parent().parent().find("div.help:not(.hidden)").addClass("hidden");
        parent.removeClass("hidden");
      }else{ parent.addClass("hidden"); }
      return false;
    });

    // Profile Visibility Actions 
    $("body").on('change', ".profVisibility .options select", function(){
      var option = $(this).find(":selected").val(),
          self = this;
      account.changeSetting("profile_visibility", option, function(ok, setTo, fail){
        if(ok){
          switch(setTo){
            case "public"   : setTo = "Everyone";         break;
            case "network"  : setTo = "My Network";       break;
            case "extended" : setTo = "Extended Network"; break;
            case "private"  : setTo = "Private";          break;
            default         : setTo = ":-/";              break;
          }
          toast.success("Profile Visibility changed to: " + setTo);
        }else{
          $(self).find(":selected")[0].selected = false;
          $(self).find("[value="+setTo+"]")[0].selected = true;
          $(self).trigger("liszt:updated"); // update chosen
          if(!fail){
            balls.error("There was an error, Profile Visiblity could not be saved. Please try again.");
          }
        }
      });
    });

    // Block List
    // Open Block List
    $("body").on("click", ".blockList .options p .openBlockList", function(e){
      e.preventDefault();
			e.stopPropagation();
			try{ 
				openPopup("privacyList"); 
			}catch(e){
				balls.error("Woha, what happened? How did you do that?", "Exception on openPopup in open block list", {logObject:e, suppressSystem:userSession.isLive}); 
			}
    });
    // Close Block List
    $("body").on("click", "section .popups .privacyList .close, section .popups .privacyList .buttons .cancel", function(){
			closePopups();
			// Update changes
      var i, popup = $(this).closest(".privacyList"),
          oldList = (popup.find("var").html() !== "")? popup.find("var").html().split(',') : null;
      popup.find("select option:selected").removeAttr('selected');
      if(oldList !== null){
        for(i=0; i < oldList.length; i++){
          popup.find("select option[value="+oldList[i]+"]")[0].selected = true;
        }
      }
      popup.find("select").trigger("liszt:updated"); // update chosen
    });
    // Save Block List
    $("body").on("click", ".popups .privacyList .buttons .save", function(){
      var list = "";
      $(this).parent().parent().find(":selected").each(function(){
        list += ($(this).val())+",";
      });
      list = list.substring(0, list.length-1);
      account.changeSetting("block_list", list, function(ok, setTo, fail){
        if( ok == null || setTo == null ){ return false; }
        if(ok){
          toast.success("Block list updated!");
          $(".popups .privacyList var").html(setTo.toString());
        }else{
          var i, selected = $(".popups .privacyList select :selected");
          for(i=0; i < selected.length; i++){
            if(setTo.search(selected[i].getAttribute("value")) == -1){
              selected[i].removeAttribute("selected");
						}
          }
          if(!fail){
            if( list.search(setTo) != -1 ){
              toast.info("There were no changes to block list.");
            }else{
              balls.error("Block list was not updated, please try again.");
            }
          }
        }
      });
			$("body section .popups, body section .popups .privacyList").addClass("hidden");
    });

    // Allow Comments on
    $("body").on("click", ".allowComments .options input[type=checkbox]", function(){
      var self = this,
          setting = self.value;
      account.changeSetting("comments_"+setting, $(self).is(":checked"), function(ok, setTo, fail){
        setTo = (typeof setTo === 'string') ? setTo === 'true' : setTo;
        if(ok){
          toast.success("Comments are now"+(setTo?"":" not")+" allowed on "+((setting=="song") ? "music" : setting+"s"));
        }else{
          self.checked = setTo;
          if(!fail){
            balls.error("Couldn't save comments setting, please try again");
          }
        }
      });
    });

    // Email Notifications
    $("body").on("click", ".emailNotifications .options input[type=checkbox]", function(){
      var self = this,
          setting = self.value;
      account.changeSetting("email_"+setting, $(self).is(":checked"), function(ok, setTo, fail){
        setTo = (typeof setTo === 'string') ? setTo === 'true' : setTo;
        if(ok){
          switch(setting){
            case "note" : setting = "notes";    break;
            case "msg"  : setting = "messages"; break;
            default     : setting = "that thing you clicked";      break;
          }
          toast.success("You will "+(setTo?"now":"no longer")+" receive email notifications for "+setting);
        }else{
          self.checked = setTo;
          if(!fail){
            balls.error("Couldn't save email notification setting, please try again.");
          }
        }
      });
    });

    // Forgot Pass
    $("body").on("click", ".changePassword .options a.forgotPass", function(e){
			try{
				balls.modal.confirm("This action will sign you out, are you sure you want to proceed?", function(ok){
					if(ok){
						account.forgotPassword();
					}
				});
			}catch(e){
				balls.error("", "Error in account.forgotPassword", {logObject:{'e':e}, suppressSystem:userSession.isLive});
			}
    });
		// Tooltip
		$("body").on("mouseenter mouseleave", ".changePassword .options a.forgotPass", function(e){
			var tt = $(this).prev();
			if(e.type === 'mouseenter'){
				tt.removeClass("hidden");
			}else if(e.type === 'mouseleave'){
				tt.addClass("hidden");
			}
		});
		$("body").on("click", ".changePassword .options .tooltip .close", function(){
			$(this.parentNode).addClass("hidden");
		});

    // Change Password
    // Submit
    $("body").on("click", ".changePassword .options .submitPass", function(){
      var self = $(this),
          currPass = self.parent().find(".currPass")[0].value,
          newPass  = self.parent().find(".newPass").val(),
          confirmPass = self.parent().find(".confirmNewPass").val();
      if(currPass === "" || newPass === "" || confirmPass === ""){
        toast.info("C'mon! You didn't even fill all the boxes in!");
        switch(""){
          case currPass:    self.parent().find(".currPass").focus();       return;
          case newPass:     self.parent().find(".newPass").focus();        return;
          case confirmPass: self.parent().find(".confirmNewPass").focus(); return;
          default: return;
        }
      }
      account.changePassword(currPass, newPass, confirmPass, function(response) {
        if(response.data === true){
          self.parent().find("input[type=password], input[type=text]").val("");
				}
      });
    });
      // Submit when enter on confirm box
    $("body").on("keypress", ".changePassword .options input[type=password]", function(e) {
      if(e.which === 13){
        // if all have value submit
        // else move to next
        var submit = $(e.target).siblings("button.submitPass"), 
            next = $(e.target).next(),
            check=true;
        $(e.target.parentNode).children("input[type=password]").each(function(){
          if(check && this.value && this.value.length > 0){
            // continue
          }else{ check = false; }
        });
        if(check || next.is("button")){ submit.click(); }
        else                          { next.focus();   }
      }
    });
    
    /******** Coming Soon
    // Change Email *** Unfinished TODO
    // Submit
    $("body").on("click", ".changeEmail .options .submitEmail", function(e){
      var newEmail = $(this).siblings(".newEmail").val(),
          newConfirm = $(this).siblings(".confirmNewEmail").val();
      if( newEmail && newConfirm && typeof newEmail == 'string' && typeof newConfirm == 'string' && newEmail == newConfirm){
        console.log("emails match");
        if(/[^\s@]+@[^\s@]+\.[^\s@]+/.test(newEmail)){
          toast.success("Is an email address! Yay");
        }else{
          balls.error("Not an actual Email address");
        }
      }else{
        console.log("emails not matching");
        balls.error("Emails do not match!");
      }
      console.log("submit email", this);
    });

    // Language 
    $("body").on("change", ".language .options .languageSelect", function(e){
      console.log("change language", this);
    });

    // Explicit Content
    $("body").on("click", ".explicit .options input[type=checkbox]", function(e){
      console.log("checkbox:", this);
    });
    **** END Coming Soon */
    return this.on['settings'] = true;
  },
  changeSetting: function(setting, value, cb){
    if( setting == null || value == null ){ return false; }
    $.ajax({url:"/wp-content/plugins/user/api.php", data:{action:"update", "setting":setting, data:value}, type:"post"})
      .done(function(response){
        response = $.parseJSON(response);
        //console.log("response in changeSetting:", response);
        if( response && response.data.result === true){
          if( cb && typeof cb === 'function'){
            cb(response.data.result, response.data.value);
          }else{
            toast.success("Setting Saved!");
          }
        }else{
          if( cb && typeof cb === 'function'){
            cb(response.data.result, response.data.value);
          }else{
            toast.success("Setting could not be saved, please try again.");
          }
        }
      })
      .fail(function(response){
        balls.error("Bad Request, please try again later.", "Response object to follow", {logObject:response, suppressSystem:userSession.isLive});
        if( cb && typeof cb === 'function' ){
          cb(false, value, true);
        }
      });
  },
	requestForgotToken: function(email){
		if(typeof email !== 'string' || email === "" || !email.isValidEmail()){ return false; }
		$.ajax({url:"/wp-content/plugins/user/api.php", data:{action:'forgotpass', 'email':email}, type:"post"})
			.done(function(response){
				try{
					response = $.parseJSON(response);
				}catch(e){
					balls.error("", "Exception on JSON parse in account.requestForgotToken", {logObject:{'response':response, 'e':e}, suppressSystem:userSession.isLive});
				}
				if(response && response.data === true){
					toast.success("Password reset email sent! Go check your inbox.");
					if(userSession.id !== 0){
						balls.account.logout();
					}
				}else{
					balls.error("We had some trouble requesting a token for "+email+". Are you sure this is the correct email address?", "Bad response in request token.", {logObject:response, suppressSystem:userSession.isLive});
				}
			})
			.fail(function(response){
				balls.error("Trouble requesting password reset, please try again.", "Response object to follow", {logObject:{'response':response}, suppressSystem:userSession.isLive});
			});
	},
  changePassword: function(oldPass, newPass, confPass, email, cb) {
		if(typeof email === 'function' && cb == null){
			cb = email;
			email = null;
		}
    if( oldPass == null || newPass == null || confPass == null ){
      if(cb && typeof cb === 'function') cb(false);
      return false;
    }
    if(newPass !== confPass){
      balls.error("New password and confirmation don't match!", "Incorrect inputs in change password", {suppressSystem:userSession.isLive});
      if(cb && typeof cb === 'function') cb(false);
      return false;
    }
    if( !newPass.isValidPassword() ){
      balls.error("New Password does not conform to the password requirements!", "Incorrect inputs in change password", {suppressSystem:userSession.isLive});
      if(cb && typeof cb === 'function') cb(false);
      return false;
    }
		var data = {
					action:'resetpass',
					oldpass:oldPass,
					password:newPass,
					confpass:confPass
				};
		if(email && email.isValidEmail()){
			data.email = email;
		}
    $.ajax({url:"/wp-content/plugins/user/api.php", 'data':data, type:"post"})
      .done(function(response) {
        response = $.parseJSON(response);
        if(response && response.data === true){
          toast.success("Password successfully changed! We will now log you back in with your new password...", {longToast:true});
          // Log back in with new info
					if(email){
						balls.account.login(email, newPass, true, '/account/settings/');
					}else{
						balls.account.login(userSession.email, newPass, true, true);
					}
        }else{
          balls.error("Password was not updated. Please try again");
        }
        if(cb && typeof cb === 'function') cb(response);
      })
      .fail(function(response) {
        balls.error("Password could not be changed, please try again later", "Response to follow", {logObject:response, suppressSystem:userSession.isLive});
        if(cb && typeof cb === 'function') cb(response);
      });
  },
  preReg: function (email) {
    if(email == null || email == "" || email.isValidEmail() == false){return false;}
    $.ajax({url:"/wp-content/plugins/user/api.php", data:{"email":email, action:"prereg"}, type:"post"})
      .done(function (response) {
        response = $.parseJSON(response);
        if(response && response.data){
          if(response.data == "exists"){
            toast.info("The email you provided is already in the system, did you forget your password?");
          }else{
            $("body .submitEmail .button img").attr("src", "http://"+userSession.cdn+"/images/sentA.png");
            $("body .submitEmail input[type='text']").val("").attr("disabled", "disabled");
            $("body .submitEmail input").attr("placeholder", "Thank you for joining the evolution!");
            $("body .submitEmail .getStarted img").attr("src", "http://"+userSession.cdn+"/images/thankYou.png");
            toast.success("Thank your for your intrest in Eardish. We hope to send you an invite token shortly!");
          }
        }else{
          balls.error("The email you provided did not pass the requirements, please try again.");
        }
      })
      .fail(function (response) {
				balls.error("Something went wrong :( Please try again.", "Failed API call in account.preReg, response to follow", {logObject:response, suppressSystem:userSession.isLive});
      });
  },
  validateReg: function (email, token) {
    if(email && email.isValidEmail() && token){
    $.ajax({url:"/wp-content/plugins/user/api.php",type:"post", data:{action:'validatereg', token:token, email:email}})
      .done(function (response) {
        if(response){
          userSession.email = email;
          toast.success("Valid token! Welcome to Eardish!");
          $("body>div.popupWrap").removeClass("hidden");
        }
      });
    }
  },
  setPassword: function () {
    var old = arguments.length == 3 && arguments[0]? arguments[0] : null,
    pass = arguments[1]? arguments[1] : null,
    repass = arguments[2]? arguments[2] : null,
    cb = arguments[3]? arguments[3] : null,
    newPass = pass == repass ? pass : null;

    if(newPass == null || !newPass.isValidPassword()){
			balls.error("Error: Passwords don't match the requirements or they differ!", "Pass: "+pass+" - RePass: "+repass, {suppressSystem:userSession.isLive});
      return false;
    }
    //later
    $.ajax({url:"/wp-content/plugins/user/api.php",type:"post", data:{action:'resetpass'||'setpass',oldpass:old, password:newPass, confpass:repass}})
      .done(function (response) {
        var response = arguments[0]? $.parseJSON(arguments[0]) : null;
        try{
          if(response.error){
            balls.error(response.error.msg);
          }else{
            $(".setPassword").addClass("hidden");
            toast.success("Password set!");
            if(!old)
              window.location = window.location.href;
          }
        }catch(e){
					balls.error("", "Exception caught in account.setPassword", {logObject:{'e':e, 'stackTrace':e.stackTrace}, suppressSystem:userSession.isLive});
        } 
      }).fail(function(response){
				balls.error("Failed to set password, please try again.", "Failed API call:", {logObject:response, suppressSystem:userSession.isLive});
    });
    return true;
  },
  keyAvail:{ 
    activelyChecking: [],
    check : function (action, key, val) {
      if(action == null || key == null || val == null) return false;
      var data = {};
      data.action = action;
      data[key] = val;
      if(balls.account.keyAvail.activelyChecking[key] != null) balls.account.keyAvail.activelyChecking[key].abort();

      balls.account.keyAvail.activelyChecking[key] = $.ajax({url:"/wp-content/plugins/user/api.php",type:"post", data:data})
        .done(function (response) {
          if(response){
            response = $.parseJSON(response);
            $("body>div.popupWrap>div.accInitPopup>input."+key).removeClass("red").removeClass("green").addClass(response.data==true || val == "" || val == null?"red":"green");
            if(response.data==true || val == "" || val == null){
              balls.error("We're sorry, "+key+" is already in use."); 
            }
          }
          balls.account.keyAvail.activelyChecking[key] = null;
        });
    }
  },
  login:function (email, password, remember, navigateTo) {
    if(email && password){
      //console.log("Remember: ", remember);
      $.ajax({url:"/wp-content/plugins/user/api.php",type:"post", data:{action:"login", email:email, password:password, remember:remember}})
        .done(function (response) {
          response = $.parseJSON(response);
          if(response && response.data && response.data.ID){
            if(navigateTo  === true){
							toast.success("Login Successful! Now go about your business.", {className:"login"}); // TODO
              return true;
            }else if(navigateTo && typeof navigateTo === 'string'){
							window.location.replace(navigateTo);
							return true;
            }else{
							toast.success("Login Successful! Hang on while we load the awesome...", {persist:true, className:"login"});
              window.location.href = "/profile/";
							return true;
            }
          }else{
						balls.error("Login uncuccessful. "+response.data, "Unsuccessful login attempt.", {logObject:response, suppressSystem:userSession.isLive});
						if(userSession.loginAttempts){
							userSession.loginAttempts += 1;
							if(userSession.loginAttempts > 2){
								$("div.forgotPass.tooltip.hidden").removeClass("hidden");
							}
						}else{
							userSession.loginAttempts = 1;
						}
          }
        });
    }else return false;
  },
  logout:function () {
    toast.info("Logging out...");
    $.ajax({url:"/wp-content/plugins/user/api.php",type:"post", data:{action:"logout"}})
      .done(function (response) {
        if(response){
          window.location.href = window.location.origin || window.location.protocol+'//'+window.location.host;
        }
      });
  }
}
})(window.balls);
