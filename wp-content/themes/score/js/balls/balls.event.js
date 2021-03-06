var balls = window.balls;
balls.event = {
  dispatch: function (e, memo) {
    var event;
    if (document.createEvent) {
      event = document.createEvent("HTMLEvents");
      event.initEvent(e, true, true);
    } else {
      event = document.createEventObject();
      event.eventType = e;
    }

    event.eventName = e;
    event.memo = memo || { };

    if (document.createEvent) {
      document.dispatchEvent(event);
    } else {
      document.fireEvent("on" + event.eventType, event);
    } 
  }
}
