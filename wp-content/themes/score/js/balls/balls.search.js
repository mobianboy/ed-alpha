// Balls DOM text search Module
// balls.search     :   Search through a list of elements for the value of the search box (searchBox, searchElems)  (both should be strings)
!(function (balls) {
  balls.search = function (searchBox, searchElems) {
    searchBox = searchBox && typeof searchBox == "string" ? $(searchBox) : null;
    searchElems = searchElems && typeof searchElems == "string"  ? $(searchElems) : null;
    if(searchBox == null || searchElems == null) return false;
    $(searchBox).on('keyup', function() {
      var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
      searchElems.show().filter(function() {
        var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
        return !~text.indexOf(val);
      }).hide();
    });
  }
})(window.balls);
