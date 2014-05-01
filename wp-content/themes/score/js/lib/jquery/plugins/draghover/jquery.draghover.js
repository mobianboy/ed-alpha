$.fn.draghover = function(options) {
  return this.each(function() {

    var collection = $(),
        self = $(this);

    self.on('dragenter', function(e) {
      if (collection.size() === 0) {
        self.trigger('draghoverstart');
      }
      collection = collection.add(e.target);
    });

    self.on('dragleave', function(e) {
      // timeout is needed because Firefox 3.6 fires the dragleave event on
      // the previous element before firing dragenter on the next one
      setTimeout( function() {
        collection = collection.not(e.target);
        if (collection.size() === 0) {
          self.trigger('draghoverend');
        }          
      }, 1);
    });
  });
};
