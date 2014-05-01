!(function (balls) {
  balls.dw = {
    balance: 0,
    threshold: 50,
    timeout: 30000,
    init: function () {
      this.ttl();
      this.events();
      this.getTransactions(false, this.updateBalance);
    },
    events: function () {
      $("#bottom section.dw").on('click', ".return, .eligibility", function(){
        $(this).toggleClass('clicked');
      });
      // --Userbar Widgets-Bring context into focus on wrapper mouseover so the mouse focus what you're scrolling--
      $("div#bottom").on("mouseover", ".widget .wrapper", function(){
        $(this).find("div.context").click();
      });
    },
    updateBalance: function () {
      var oldBalance = this.balance;
      this.balance = (arguments[0] && (typeof arguments[0] == "number" || typeof arguments[0] == "string"))? (arguments[0]).toFixed(2) : null;
      this.balance = (arguments[0] && (typeof arguments[0] == "object"))? (arguments[0].balance.toFixed(2)) : this.balance;
      
      if(this.balance > oldBalance){
        $(".widgets .widget.dw div.context div.balance div.amount, .widgets .widget.dw div.context div.threshold div.progress").html("$"+this.balance);
        progress = $(".widgets .widget.dw div.context .threshold .progress");
        percentOfThreshold = Math.floor(this.balance/this.threshold*100);
        percentOfThreshold = (percentOfThreshold > 100)? 100 : percentOfThreshold;
        progress.css("left", percentOfThreshold+"%");
      }
    },
    getBalance: function () {
      $.ajax({url:"/wp-content/plugins/digital-wallet/api.php", type:"post", data:{"action":"getBalance"}})
        .done(function (response) {
          //response = $.parseJSON(response);
          //self.updateBalance(response.balance);
        });
    },
    getTransactions: function () {
      var startDate = (arguments[0] == true)? $(".dw .rewardList li").last().find("ul li.date").html() : null,
      endDate = (startDate == null)? $(".dw .rewardList li").first().find("ul li.date").html() : null,
      cb = (arguments[1])? arguments[1] : function() {},
      self = this;

      $.ajax({url:"/wp-content/plugins/digital-wallet/api.php", type:"post", data:{"action":"getTransactions", "startDate":startDate, "endDate":endDate}})
        .done(function (response) {
          try{
            /*response = $.parseJSON(response);
            if(response.indexOf("positions") < 0){
              if(startDate) { $(".dw .rewardList").append(response.html)}
              else          { $(".dw .rewardList").prepend(response.html)}
            }
            cb(response);*/
          }catch(e){
						require(['balls.error'], function(){
							balls.error("", "Exception on dw.getTransactions", {logObject:e, suppressSystem:userSession.isLive});
						});
          }
        }).fail(function(response){
					require(['balls.error'], function(){
						balls.error("", "Failed API call on dw.getTransactions", {logObject:response, suppressSystem:userSession.isLive});
					});
				});
    },
    getRankings: function () {
      //Fetch ranking percents
    },
    ttl: function () {
      var self = this;
      time = (arguments[0])? arguments[0] : self.timeout;
      setTimeout(function () {
        self.getTransactions(false, function () {self.ttl()});
      }, time);

    },
    cashout: function () {
      //ajax request to server to start cashout
      // On Success:
      //    Start paypal transaction, when the server gets confirmation the funds have been transfered it will remove the funds.
      // On Failure:
      //    Send Failure message to EndUser
    }
  }
})(window.balls);
