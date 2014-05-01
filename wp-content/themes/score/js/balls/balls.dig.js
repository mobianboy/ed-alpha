// Balls Dig Object (Diggable by postId: song, video, photo, article, playlist, comment, shout)
// balls.dig        :   Main dig object
// balls.dig.fire   :   Fire off a dig/undig api call based on a specific parent element of the passed in dig button that fired the event (obj)
!(function (balls){
balls.dig = {
  fire: function (obj) {
    var parent = $(obj).closest("[data-content], body>section[id]") || null,
    id = ( parent.is("[data-content]") ? parent[0].getAttribute("data-content") : parent[0].getAttribute("id").split("/").pop() ),
    toggle = obj.className || null,
    ownerID = parseInt(obj.getAttribute("data-dig-owner"));

    if(obj == null || id == null || toggle == null) return false;
    if(!obj.hasAttribute("data-dig-owner") || ownerID == userSession.id) return false;
    // response returns new count 
    $.ajax({url:"/wp-content/plugins/socialnetwork/api.php", type:"POST", data:{"post-type":"dig", "id":id, "action":(toggle.search(/undig/) == -1) ? "set" : "delete" }})
      .done(function (response) {
        if(response || response == 0){
          var count = response.replace(/\"/g, '').trim(); 
          obj.setAttribute("data-dig-count",count);

          if(toggle.search(/undig/) === -1){
            obj.className = obj.className.replace(/dig/, "undig").trim();
            obj.innerHTML = "<span></span>Undig";
            toast.success("Thanks for the digs!");
          }else{
            obj.className = obj.className.replace(/undig/, "dig").trim();
            obj.innerHTML = "<span></span> Dig it!";
            toast.success("OH! I see how it is! Maybe next time I'll say <a href='http://no-backsies.urbanup.com/1644448' target='_blank'>\"no backsies\"</a>");
          }
        }
      });
  }
}
})(window.balls);
