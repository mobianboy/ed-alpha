// Main balls Error Module
// balls.error(userMsg[, sysMsg, ..., options])
//
// This module is a single function, it takes a message for the user, any number of optional system messages and a single optional options object;
// Parameters:
// 		Required
// 			- One (1) string message to be shown to the user using a toast, if toast is not available falls back to a standard JS alert which falls back to a console.log output
// 				NOTE: If you don't want to show a user message but sill show a system message pass in an empty string for the first parameter.
// 		Optional
// 			- Zero or more [0, infinity) string messages to be passed to console.error
// 			- One (1) object containing any extra options for balls.error
//
// Available Options
//		suppressSystem		(bool)	:	if true no system messages will be output to the console
//		logObject									: This will be dumped to console.log if system messages are output.
//
//

!(function (window, balls){  
balls.error = function(){
	var self = this, l = arguments.length,
			defaultOptions = {
				// Default options set
				suppressSystem:false,
				logObject:null
			},
			userMsg	= (arguments[0] 	&& typeof arguments[0] 	 === 'string') ? arguments[0] : null,
			sysMsgs = (arguments.length >= 2) ? ((typeof arguments[l-1] === 'string') ? Array.prototype.slice.call(arguments, 1) : Array.prototype.slice.call(arguments, 1, l-1)) : null,
			options = (arguments.length >= 2 && typeof arguments[l-1] === 'object') ? arguments[l-1] : defaultOptions;
	
	// set sysMsgs to null if empty array
	//sysMsgs = (sysMsgs != null && sysMsgs.length === 0) ? null : sysMsgs;
	
	//console.log({'args':arguments, 'uMsg':userMsg, 'sysMsgs':sysMsgs, 'opt':options});
	
	// Do work
	// Inject Default Options
	for(var prop in defaultOptions){
		if(defaultOptions.hasOwnProperty(prop) && (typeof options[prop] === 'undefined' || options[prop] === null)){
			options[prop] = defaultOptions[prop];
		}
	}
	
	// Output Messages and Such
	try{
		// Display User Message
		if(userMsg && userMsg.length > 0){
			if(window.toast){
				toast.error(userMsg);
			}else if(window.alert){
				window.alert(userMsg);
			}else{
				console.log(userMsg);
			}
		}

		// Display System message
		if(sysMsgs != null && sysMsgs.length > 0 && options.suppressSystem === false){
			var i, curr;
			for(i=0, curr=sysMsgs[i]; i < sysMsgs.length; curr=sysMsgs[++i]){
				console.error(curr);
			}
			if(options && options.logObject && typeof options.logObject !== 'undefined'){
				console.log(options.logObject);
			}
		}
	}catch(e){
		console.error("Whoa, an error in the error module.... exception: ", e);
		return false;
	}

	// To return true or to return self that is the question.
	return true;
};
})(window, window.balls);
