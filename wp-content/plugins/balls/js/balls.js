(function($) {
  $(document).ready(function() {

    // Toggle visibility on Position placement code sample
    $('#positions').on("click", ".position_name", function() {
      $(this).parent().parent().find("pre").toggle();
    });

    // Confirm a delete click
    $("#positions").on("click", "a", function(e) {
      e.preventDefault();
      e.stopPropagation();
      if(confirm("Are you SURE?")) {
        window.location = $(this).attr("href");
      }
    });

  }); // end document ready function
})(jQuery); // end unobtusive jquery

