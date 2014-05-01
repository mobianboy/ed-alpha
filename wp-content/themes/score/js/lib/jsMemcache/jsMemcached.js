/*
 * Copyright (c) 2009, Rakesh Pai
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the <organization> nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY <copyright holder> ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <copyright holder> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * Phew.
 */

JsMemcached = (function() {
	var host = "localhost";
	var port = 11211;
	
	var isConnectionOpen = false;
	var socket = new Jaxer.Socket();
	var throwErrors = true;
	
	var openConnection  = function() {
		if(!isConnectionOpen) {
			socket.open(host, port);
			isConnectionOpen = true;
		}
	};
	
	var issueCommand = function(command) {
		openConnection();
		try {
			socket.writeString(command);
		} catch(e) {
			socket.close();
			openConnection();
			socket.writeString(command);
		}
	};
	
	var store = function(commandType, key, value, time) {
		time = time || 0;
		issueCommand(commandType + " " + key + " 0 " + time + " " + value.length + "\r\n" + value + "\r\n");
		var response = socket.readLine();
		if(throwErrors && response.indexOf("STORED") !== 0) {
			throw new Error("Couldn't store data - " + response);
		}
	};
	
	var retrieve = function(commandType, key) {
		var startDate = new Date();
		var command = commandType + " " + key + "\r\n";
		issueCommand(command);
		
		var firstLineSplit = socket.readLine().split(" "), returnValue = "", currentLine;
		if(firstLineSplit[0] === "VALUE") {
			currentLine = socket.readLine();
			while(currentLine.indexOf("END")!==0) {
				returnValue += currentLine;
				currentLine = socket.readLine();
			}
		}
		return returnValue;
	};
	
	var deleteKey = function(key, time) {
		issueCommand("delete " + key + "\r\n");
	};
	
	var returnObj = {
		close: function(){
			socket.close();
		},
		get: function(key) {
			return retrieve("get", key);
		},
		deleteKey: deleteKey,
		config: function(sHost, nPort) {
			host = sHost;
			port = nPort;
		}
	};
	
	["add", "set", "replace"].forEach(function(commandName) {
		returnObj[commandName] = function(key, value, time) {
			store(commandName, key, value, time);
		};
	});
	
	return returnObj;
})();