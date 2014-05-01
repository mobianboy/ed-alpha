/** 
* @description Wraps a thing in a div, puts a div before and after said thing.
* @description Makes the thing scroll via the buttons or scroll wheel.
*
* @version 0.0.8
* @author Ryan Bogle
* @license Attribution 3.0 Unported (CC BY 3.0)
* @license http://creativecommons.org/licenses/by/3.0/legalcode
* 
* @requires jQuery
*
* @class scrollThis
* @memberOf jQuery.fn
*
* @example
*
* $(".thingToMakeScroll").scrollThis();
* $(".withOptions").scrollThis({
* 	scrollNum: 1, 				// number of items to scroll (any multiple, could be 0.5!)
* 	hoverScrollNum:1,			// number of items to scroll on hover over button
* 	hoverScrollDelay:200,		// delay between scrolls during hover
* 	hoverStartDelay:1000,		// time to hover over button before scrolling
* 	cssSelectors:{				// a collection of CSS classes that will be applied to new elements
* 		wrapper:"container",	// wrapper class
* 		buttons:"btn",			// button class
* 		upBtn:"up",				// up button class
* 		downBtn:"down"			// down button class
* 	}
* });
*/


!(function(window, $){
	$.scrollThis = function(){
		var base = this, list = (arguments[0] && typeof arguments[0] == "object") ? $(arguments[0]) : null,
				options  = (arguments[1]) ? $.extend($.scrollThis.defaults,arguments[1], false) : $.scrollThis.defaults,
				wrapper  = list.wrap("<div class='"+options.cssSelectors.wrapper+"' />").parent(),
				upBtn    = wrapper.prepend("<div class='"+options.cssSelectors.buttons+" "+options.cssSelectors.upBtn+"' />").find("."+options.cssSelectors.upBtn),
				downBtn  = wrapper.append("<div class='"+options.cssSelectors.buttons+" "+options.cssSelectors.downBtn+"' />").find("."+options.cssSelectors.downBtn),
				wheelEvents = function(e){
					e.stopPropagation();
					e = e.originalEvent;
					var delta = Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail)));
					base.scroll(delta);
				};

		base.scroll = function(n){
			var off = list[0].offsetTop, 
					top = upBtn[0].offsetHeight,
					bottom = top - list[0].offsetHeight + (wrapper[0].offsetHeight - (2*upBtn[0].offsetHeight));
			off += n*(list.children()[0].offsetHeight);
			off =  off > top    ? top    : off;
			off =  off < bottom ? bottom : off;
			list.css({top: off});
		};

		base.timeoutID = null;
		base.start = function(dir){
			if( base.timeoutID != null ){
				if(dir == options.cssSelectors.upBtn) {base.scroll(options.hoverScrollNum);}
				else                                  {base.scroll(0-options.hoverScrollNum);}
				clearTimeout(base.timeoutID);
				base.timeoutID = setTimeout(base.start, options.hoverScrollDelay, dir);
			}else{
				base.timeoutID = setTimeout(base.start, options.hoverStartDelay, dir);
			}
		};
		base.stop = function(){ 
			if( base.timeoutID != null )
				clearTimeout(base.timeoutID);
			base.timeoutID = null;
		};

		// events, click mouseover mouseout
		wrapper.on({
			click: function(){
				if($(this).hasClass(options.cssSelectors.upBtn))         base.scroll(options.scrollNum);
				else if($(this).hasClass(options.cssSelectors.downBtn))  base.scroll(-options.scrollNum);
				base.stop();
			},
			mouseover:function(){
				if($(this).is('.'+options.cssSelectors.upBtn+', .'+options.cssSelectors.downBtn))
					var dummy = $(this).hasClass(options.cssSelectors.upBtn) ? base.start(options.cssSelectors.upBtn) : base.start(options.cssSelectors.downBtn);
			},
			mouseout:function(){
				if($(this).is('.'+options.cssSelectors.upBtn+', .'+options.cssSelectors.downBtn))
					base.stop();
			}
		}, 'div');
		// mouse wheel events
		wrapper.on({
			mousewheel:wheelEvents,    // webkit, IE
			DOMMouseScroll:wheelEvents // get with it Firefox
		});
	}; // end main plugin def
	$.scrollThis.defaults = {
		scrollNum: 1,
		hoverScrollNum:1,
		hoverScrollDelay:200,
		hoverStartDelay:1000,
		cssSelectors:{
			wrapper:"container",
			buttons:"btn",
			upBtn:"up",
			downBtn:"down"
		}
	};
	$.fn.scrollThis = function(options) {
		return this.each(function(i){
			if ((typeof(options)).match('object|undefined')){ (new $.scrollThis(this, options));}
		});
	};
})(window, jQuery);
