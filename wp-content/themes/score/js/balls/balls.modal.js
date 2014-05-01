/****
 * Balls.modal module
 *
 * balls.modal                            : modal module 
 *
 * **** INTERNAL MEMBERS ****
 * modal.wrapper                { get; }  : Gets the modal wrapper from the DOM, if it doesn't exist it will be created.
 * modal.closeBtn               { get; }  : String of the close button for dialogs
 * modal.OkBtn                  { get; }  : String of the OK button for dialogs
 * modal.YesNoBtns              { get; }  : String of the yes and no buttons for dialogs
 * modal.closeEventListener     { get; }  : The function used to capture modal close button clicks
 *
 * modal.init()                           : Creates the wrapper, calls this.events()
 * modal.events()                         : Adds startFetchTemplate watcher for closing the modal when a new template is fetched.
 * modal.fireCloseEvent(e, str)           : Leverages balls.event.dispatch to fire a 'ballsModalClose' event that has the memo:{modalClass:str, ogEvent:e}
 * 
 * modal.isOpen                 { get; }  : Returns true if either a modal or dialog is open and visible.
 * modal.hide()                           : Hides the modal wrapper if it is not already hidden, also calls hideModal()
 * modal.show()                           : Shows the modal wrapper, if the modal bucket is not empty it will call showModal()
 * modal.close(e)                         : Hides Modal, then hides Modal wrapper, then calls clearModal(), then calls clearDialog() then calls fireCloseEvent with argument e and the className of the first child inside the .modal wrapper.
 * modal.closeDialog()                    : If the modal is empty calls this.hide() then calls this.clearDialog() regardless
 * modal.hideModal()                      : Hides modal, will leave dialogs visible
 * modal.showModal()                      : Shows modal
 * modal.clearModal()                     : Hides and empties the modal div (not wrapper div)
 * modal.clearDialog()                    : Removes dialog element
 *
 * modal.dialog(msg, className, cb)       : Used by alert, confirm and delete, builds and displays dialog box
 *
 * **** PUBLIC MEMBERS **** 
 * Use these functions to make modals and dialogs
 *
 * modal.alert(msg [, cb])                : Creates an alert dialog with a single OK button, optional callback fired on user action.
 * modal.confirm([msg, cb])               : Creates a confirm dialog with yes and no buttons, optional callback fired on user action with true or false for yes or no/close.
 * modal.delete([msg, cb])                : Creates a delete dialog with yes and no buttons, optional callback fired on user action with true or false for yes or no/close.
 *        The above functions can take callback as the first or second parameter. Message can only be the first parameter.
 *
 * modal.html(el/str)                     : Takes in a string or element and inserts it into 'body > .modalWrapper > div.modal' will set the margins to align the modal to center.
 *
 *****/
!(function (balls){
balls.modal = {
  // pseudo static members
  get wrapper(){
    var e = document.querySelector("body > .modalWrapper");
    if(e == null){
      e = document.createElement("div");
      e.className = "modalWrapper hidden";
      e.innerHTML = '<div class="modal hidden"></div>';
      e.addEventListener("click", this.closeEventListener);
      document.body.appendChild(e);
      return e;
    }else{ return e; }
  },
  get closeBtn() { return '<button class="close">Close</button>'; },
  get OkBtn()    { return '<button class="ok">Ok</button>'; },
  get YesNoBtns(){ return '<button class="yes">Yes</button><button class="no">No</button>'; },
  get closeEventListener(){
    return function(e){
      var target = e.target;
      if(target.className && target.className !== ""){
        if(target.className.search("close") > -1 || (target.parentNode === document.body && target.className.search("modalWrapper") > -1)){
          e.preventDefault();
          e.stopPropagation();
          balls.modal.close(e);
        }
      }
    };
  },
  init:function(){
    // create wrapper
    var dummy = this.wrapper;
    this.events();
  },
  events:function(){
    document.addEventListener("startTemplateFetch", function(e){
      if(balls.modal.isOpen){
        balls.modal.close(e);
      }
    });
  },
  fireCloseEvent:function(e, modalChildClass){
    require(['balls.event'], function(){
      balls.event.dispatch("ballsModalClose", {modalClass:modalChildClass, ogEvent:e});
    });
    /*if(window.CustomEvent){
      var ev = new CustomEvent("ballsModalClose", {
        detail:{
          message:"modal closed",
          'modalChildClass':(modalChildClass)?modalChildClass:null,
          ogEvent:(e)?e:null
        },
        bubbles:true,
        cancelable:true
      });
      this.wrapper.dispatchEvent(ev);
    }*/
    return null;
  },
  // Helper Functions
  get isOpen(){
    return !(this.wrapper.className.search(/hidden|hide/) > -1);
  },
  hide:function(){
    var e = this.wrapper;
    if(e.className.search(/hidden|hide/g) > -1){
      return false;
    }else{ e.className += " hidden";  }
    this.hideModal();
    return true;
  },
  show:function(){
    var e = this.wrapper;
    if(e.className.search(/hidden|hide/g) > -1){
      e.className = e.className.split(" ").filter(function(v){
        return !(v === '' || v === ' ' || v === 'hidden' || v === 'hide');
      }).join(" ");
    }else{ return false; }
    if(e.querySelector(".modal").innerHTML !== "")
      this.showModal();
    return true;
  },
  close:function(e){
    var modalChildClass;
    try{ modalChildClass = this.wrapper.querySelector(".modal").firstElementChild.className; }
    catch(e){ modalChildClass = null; }
    this.hide();
    this.hideModal();
    this.clearModal();
    this.clearDialog();
    this.fireCloseEvent(e, modalChildClass);
  },
  closeDialog:function(){
    var m = this.wrapper.querySelector(".modal");
    if(m != null && m.className.search(/hidden|hide/g) > -1){
      this.hide();
    }
    this.clearDialog();
  },
  hideModal:function(){
    var m = this.wrapper.querySelector(".modal");
    if(m && m.className.search(/hidden|hide/g) > -1){
      return false;
    }else{ m.className += " hidden"; }
    return true;
  },
  showModal:function(){
    var m = this.wrapper.querySelector(".modal");
    if(m && m.className.search(/hidden|hide/g) > -1){
      m.className = m.className.split(" ").filter(function(v){
        return !(v === '' || v === ' ' || v === 'hidden' || v === 'hide');
      }).join(" ");
    }else{ return false; }
    return true;
  },
  clearModal:function(){
    this.hideModal();
    var m = this.wrapper.querySelector(".modal");
    if(m != null){
      m.innerHTML = "";
    }
  },
  clearDialog:function(){
    var d = this.wrapper.querySelector(".dialog");
    if(d != null)
      this.wrapper.removeChild(d);
  },
  // end Helpers
  // Dialog functions
  dialog:function(msg, className, cb){
    if(msg == null || typeof msg !== 'string' || typeof className !== 'string'){
      if(cb && typeof cb === 'function')cb(false);
      return false;
    }
    var self = this, htm = "",
        d = document.createElement("div");

    htm = this.closeBtn;
    htm += '<div class="msg">'+msg+'</div>';
    switch(className){
      case "delete":
      case "confirm":
        htm += this.YesNoBtns;
        break;
      default:
        htm += this.OkBtn;
        break;
    }

    d.className = "dialog "+className;
    d.innerHTML = htm;
    d.addEventListener("click", function(e){
        e.preventDefault();
        e.stopPropagation();
        var ev = e, bool = false;
        e = ev.target;
        if(e.tagName && e.className && e.tagName === 'BUTTON'){
          switch(e.className){
            case "yes":
            case "ok":
              bool = true;
              break;
            case "no":
            case "close":
            default:
              bool = false;
              break;
          }
          if(cb && typeof cb === 'function') cb(bool);
          self.closeDialog();
        }
    });

    this.wrapper.appendChild(d);
    this.show();
    return d;
  },
  alert:function(){
    var msg = (typeof arguments[0] === 'string') ? arguments[0] : null,
        cb  = (typeof arguments[0] === 'function') ? arguments[0] : (arguments[1] ? arguments[1] : null);
    if(msg == null){ msg = "An alert was called! But without a message! :("; }
    return this.dialog(msg, "alert", cb);
  },
  confirm:function(){
    var msg = (typeof arguments[0] === 'string') ? arguments[0] : null,
        cb  = (typeof arguments[0] === 'function') ? arguments[0] : (arguments[1] ? arguments[1] : null);
    if(msg == null){
      msg = "Are you sure you want to do this?";
    }
    return this.dialog(msg, "confirm", cb);
  },
  set deleteConf(cb) { return cb?balls.modal.delete(cb):null },
	delete:function(){
    var msg = (typeof arguments[0] === 'string') ? arguments[0] : null,
        cb  = (typeof arguments[0] === 'function') ? arguments[0] : (arguments[1] ? arguments[1] : null);
    if(msg == null){
      msg = "Are you sure you want to delete this item?";
    }
    return this.dialog(msg, "delete", cb);
  },
  // End Dialog functions
  // Create Modal
  html:function(strHTML){
    var m = this.wrapper.querySelector(".modal");

    this.hideModal();
    this.clearModal();

    if(strHTML && typeof strHTML === 'string'){
      m.innerHTML = strHTML;
    }else{
      try{
        m.appendChild(strHTML);
      }catch(e){
				require(['balls.error'], function(){
					balls.error("", "Could not add child to modal bucket", {logObject:e, suppressSystem:userSession.isLive});
				});
        return false;
      }
    }
    
    // Show so it can be repositioned
    this.showModal();
    this.show();

    // Reposition to center
    m.style.marginTop  = (m.offsetHeight/2 * -1)+"px";
    m.style.marginLeft = (m.offsetWidth/2 * -1)+"px";
    return m;
  },
}
})(window.balls);
