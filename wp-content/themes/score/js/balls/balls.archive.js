// Balls Archive Controller
//
// balls.archive            :   Main archive controller object litteral
// balls.archive.on         :   Previously executed balls.archive methods
// balls.archive.response   :   Current template archive actively updating flag
// balls.archive.currentSet :   Current template available postID set
// balls.archive.init       :   Initilize archive controls and events
// balls.archive.events     :   Events for archive templates
// balls.archive.update     :   Either replace, or paginate the result set for current template archive ([true/false]) //true == paginate


!(function (balls){
balls.archive = {
  on:[],
  response: null,
  currentSet: "",
  init: function () {
    if($("section.archivePagination").length < 1){
      $.ajax({url:"/wp-content/themes/score/tpl/global/archiveControls.php"}).done(function(response){
        if(response){
          $("body>section[id*='/archive/']>section, body>section[id^='/music/']>section, body>section[id^='/news/']>section, body>section[id^='/members/']>section").last().after(response);
    			balls.archive.updatePagination();
        }
      });
    } else { return false; }
		this.events();
  },
  events: function () {
    var self = this;
    //-------------- Chosen -----------------
    require(['chosen'], function() {
      $('.topBar .selectBox.genre').chosen({
        no_results_text: "No Genre: ",
        allow_single_deselect: true
      });
      $('.topBar .selectBox.profType').chosen({
        disable_search: true,
        allow_single_deselect: true
      });
      $('.topBar .selectBox.location').chosen({
        no_results_text: "Local Only",
        allow_single_deselect: true
      });
      $('.topBar .selectBox.radius').chosen({
        no_results_text: "Local Only",
        allow_single_deselect: true,
        disable_search:true
      });
      $('.topBar .selectBox.orderBy').chosen({disable_search: true});
      $('.topBar .selectBox.order').chosen({disable_search: true});
    });

    //--------------- Pagination -------------
    $("body").on("click", ".archivePagination ul li a", function (e) {
      var list = $(this).parent().parent();
      return true;
    });
    
    $(window).on("scroll", function () {
      if($("section.explorer>img.showMore:in-viewport").length > 0){
        self.update(true);
      	balls.archive.updatePagination();
      }
			return false;
    });
   	$("body").on("click", ".archivePagination", function (e){
			mouseX = e.clientX
			mouseY = e.clientY
			//---
			containerLeft = $("body section.archivePagination")[0].offsetLeft;
			containerRight = $("body section.archivePagination")[0].offsetLeft +  $("body section.archivePagination")[0].offsetWidth;
			withinX = (containerLeft < mouseX && containerRight > mouseX);
			//---
			beforeTop = $("body section.archivePagination")[0].offsetTop;
			beforeBottom = $("body section.archivePagination")[0].offsetTop + $("body section.archivePagination ul li")[0].offsetHeight;
			withinBeforeY = (beforeTop < mouseY && beforeBottom > mouseY);
			//---
			afterTop = $("body section.archivePagination")[0].offsetTop + $("body section.archivePagination")[0].offsetHeight - $("body section.archivePagination ul li")[0].offsetHeight;
			afterBottom = $("body section.archivePagination")[0].offsetTop + $("body section.archivePagination")[0].offsetHeight;
			withinAfterY = (afterTop < mouseY && afterBottom > mouseY);
			//---
			
			if($(this).hasClass("afterOpen") && withinX && withinAfterY){
				$("body section.archivePagination ul li:last a[href^='#']")[0].click(); 
			} else if ($(this).hasClass("beforeOpen") && withinX && withinBeforeY){
				$("body section.archivePagination ul li:first a[href^='#']")[0].click();
			}
		}); 
    $("body").on("click", ".archivePagination ul li a[href^='#']", function(e){
      e.stopPropagation();
    });

    if(self.on['events'])return true;
    //------ Start Body Events --------
    $("body").on("click", "section.header>section.topBar .advSearch .btn", function (e) {
      e.preventDefault();
      $("body section.header>section.topBar").toggleClass("adv");
      if( $("body section.header>section.topBar").hasClass("adv") ){
         $("body section.header>section.topBar .advSearch .btn").html("Standard Search");
      }else{
        $("body section.header>section.topBar .advSearch .btn").html("Advanced Search");
      }
    });
    $("body").on("click", "section.header>section.topBar .find", function(e){
      e.preventDefault();
      self.update();
    });
    $("body").on("keyup", "section.header>section.topBar .search input", function(e){
      e.preventDefault();
      if(e.keyCode == 13)
        self.update();
    });
    
    //---------Click on Image in members gallery fix---------------
		$("body").on("click", "section[id^='/members/'] > section.explorer > ul > li.user", function(e){
			var that = this,
					target = e.target;
			e.preventDefault();
			if(target && target.tagName === 'A' && target.className.search("template") > -1){
				// do nothing - let filter to be caught by balls.state
			}else{
				e.stopPropagation();
				require(['balls.state'], function(){
					balls.state.fetchTemplate(that.querySelector(".metaBox .meta a.template").getAttribute("href"));
				});
			}
		});

    //---------Dish Picks Tabs---------------
    $("body").on("click", "section.header .dishPicks .topBar .tabs .tab", function(e){
        var elem = $(this),
            ul = $(".dishPicks > ul");
        if(elem.hasClass("selected")){
          elem.children(".userName a").trigger('click');
        }else{
          elem.siblings(".tab.selected").removeClass("selected");
          elem.addClass("selected");

          ul[0].className = ul[0].className.replace(/showSet-./g, "showSet-"+(elem.index()+1));
          return false;
        }
    });
    $("body").on("click", "section.header .dishPicks .topBar .tabs .tab a", function(e){
      var elem = $(this),
          parentTab = $(this).parent().parent();
      if(!parentTab.hasClass("selected")){
        parentTab.trigger('click');
        e.preventDefault();
        return false;
      }
    });
    $("body").on("error", "section.header .dishPicks .topBar .tabs .tab img", function(e){
      $(this).css("display", "none");
    });
    return self.on['events'] = true;
  },
  update: function (append) {//(true == paginate)
    var self = this, append = append? append : false,
    id = $("body>section[id^='/']").attr("id"),
    page = (append == false)? 1 : parseInt($("section.archivePagination ul li").length, 10)+1,
    tax = {"genre":$("section.header .topBar .selectBox.genre").val()},
    locZip = $("section.header .topBar .selectBox.location").val(), // or any other zip
    locRad = $("section.header .topBar .selectBox.radius").val(), // miles for search radius
    search = $("section.header .topBar .search input").val(),
    order = $("section.header .topBar .selectBox.order").val() || "desc", // asc/(desc)
    orderBy = $("section.header .topBar .selectBox.orderBy").val() || "date"; // (date)/title/rand

    if($("section.header .topBar .selectBox.profType") && $("section.header .topBar .selectBox.profType").val() != "");
      tax.profileType = $("section.header .topBar .selectBox.profType").val();

/*
    console.log("===================");
    console.log("Append: ", append);
    console.log("Page: ", page);
    console.log("Tax:", tax);
    console.log("Search:", search);
    console.log("Id:", id);
    console.log("loc-zip:",locZip);
    console.log("loc-rad:",locRad);
    console.log("order:", order);
    console.log("orderBy:", orderBy);
    if(tax.profileType)
      console.log("profileType:",profType);
    console.log("===================");
*/
    if(self.response && self.response != null || 
      ($("body>section[id^='/news/']").length < 1 && $("body>section>section.grid ul, body>section>section.list ul").last().children().length < 15 && append) ||
      window.templateFetch != null ||
      $("section.archivePagination ul li").length < 1){
        //console.log("Archive Pagination DOM elements not found!");
        return false;
    }

    if(!append){
      self.currentSet = "";
      if(self.response != null){
        self.response.abort();
        self.response = null;
      }
      $("section.explorer > ul, section.explorer a.paginate").remove();
    }
    
    $("section.explorer > img.showMore").removeClass("hidden");
    self.response = $.ajax({
      url:balls.api,
      type:"post",
      data:{
        "href":id,
        "template":"archiveLoop",
        "page":page,
        "orderby":orderBy,
        "order":order,
        "tax":tax,
        "search":search,
        "loc-zip":locZip,
        "loc-rad":locRad,
        exclude:self.currentSet
      }
    }).done(function (response) {
        response = $.parseJSON(response);
        if(response.html != " " && response.html != null){
          if(append){
            $("section.explorer>img").before("<a class='paginate' name='"+page+"'><span></span></a>").before("<ul>"+response.html+"</ul>");
            $("section.archivePagination ul").append("<li><a href='#"+page+"'>"+page+"</a></li>");
          }else{
            $("section.explorer").prepend("<a name='1' class='paginate'><span></span></a>");
            $("section.explorer a.paginate[name='1']").after("<ul>"+response.html+"</ul>");
            $("section.archivePagination>ul").replaceWith("<ul><li><a href='#1'>1</a></li></ul>");
          }
					$("section.archivePagination").attr("data-pages", $(".archivePagination ul li").length);
					balls.archive.updatePagination();	
          $("section.explorer > img.showMore").addClass("hidden");
        }else{
					require(['balls.error'], function(){
						balls.error("Failed to paginate or you found the end of page!", "pagination fail response to follow", {logObject:response, suppressSystem:userSession.isLive});
					});
        }
        self.currentSet = response.exclude ? response.exclude : "";
        self.response.abort();
        self.response = null;
      })
      .fail(function (response) {
				require(['balls.error'], function(){
					balls.error("Page request failed, please try again. If the problem persists refresh the page.", "failed response to follow", {logObject:response, suppressSystem:userSession.isLive});
				});
        $("section.explorer img").addClass("hidden");
      });
  },
	updatePagination: function () {
		var firstInView = function(){ 
			var elem = $(".archivePagination ul li:first"),
					wrapper = elem.parent(),
					container = wrapper.parent();
			return !container.hasClass("beforeOpen")? (wrapper.offset().top >= container.offset().top-elem.height()) : (wrapper.offset().top >= container.offset().top);
		},
		lastInView = function () {
			var elem = $(".archivePagination ul li:last"),
          wrapper = elem.parent(),
          container = wrapper.parent(), result;

			if( container.hasClass("afterOpen") && container.hasClass("beforeOpen")){ 
				result = (container.offset().top+container.height()-elem.height() > wrapper.offset().top+wrapper.height()-(elem.height()*3));
			}else if(!container.hasClass("afterOpen") && container.hasClass("beforeOpen")){
				result = (container.offset().top+container.height() > wrapper.offset().top+wrapper.height()-(elem.height()*2));
			}else if(!container.hasClass("afterOpen")){
				 result = (container.offset().top+container.height() > wrapper.offset().top+wrapper.height()-elem.height());
			}else{
				result = (container.offset().top+container.height()-elem.height() > wrapper.offset().top+wrapper.height()-(elem.height()*2));
			}
			return result;
		},
		btmPercHid = Math.round((($("body>section>section.explorer")[0].offsetHeight-(window.scrollY+window.innerHeight))+$("body>section>section.explorer")[0].offsetTop)/$("body>section>section.explorer")[0].offsetHeight*10000)/100;
		pixelHid = Math.floor( (btmPercHid/100) * ($(".archivePagination ul li")[0].offsetHeight*($(".archivePagination ul li").length) + ($(".archivePagination ul li").length > 1 ? 1 : 0)) );
		$("section.archivePagination ul").css("margin-bottom", "-"+pixelHid+"px");
		
		if( !firstInView() && !$("section.archivePagination").hasClass("beforeOpen")){
			$("section.archivePagination").addClass("beforeOpen");
		} else if( firstInView() && $("section.archivePagination").hasClass("beforeOpen")){
			$("section.archivePagination").removeClass("beforeOpen");
		}
		if( !lastInView() && !$("section.archivePagination").hasClass("afterOpen")){
			$("section.archivePagination").addClass("afterOpen");
		}else if ( lastInView() && $("section.archivePagination").hasClass("afterOpen")){
			 $("section.archivePagination").removeClass("afterOpen");
		}
		return true;	
	}
}
})(window.balls);
