(function($) {
  $(document).ready(function() {

    // Edit In Place Hover State
    $('.eip').hover(
    	function() {
        	$(this).css({'background-color':'yellow','cursor':'pointer','color':'black'});
      	},
      	function() {
        	$(this).css({'background-color':'transparent','color':'inherit'});
      	}
    ); // end hover state

    // Edit in place click handler
    $('.eip').click(function() {

      // Setup vars 
      var eip = this;
      var user = $(eip).attr('data-user');
      var field = $(eip).attr('data-field');
      var value = $(eip).html();
      value = value.replace(/\<p\>/, '');
      value = value.replace(/\<\/p\>/, '');
      value = value.replace(/^\s*/, '');
      value = value.replace(/\s*$/, '');

      // Switch on field type
      var input;
      switch(field) {
        case '15':
        case '37':
          input = '<textarea rows="4" cols="16">' + value + '</textarea>';
          break;
        default:
          input = '<input type="text" value="' + value + '" />';
      }

      // Activate
      $(eip).parent().append('<div class="edit-in-place field-' + field + '">' + input + '<button class="cancel">X</button><button class="save">Save</button></div>');
      $(eip).hide();

      // Cancel Button Click handler
      $('.edit-in-place button.cancel').click(function() {
          $('.edit-in-place').remove();
          $(eip).show();
      });

      // Save button click handler
      $('.edit-in-place button.save').click(function() {
        var user = $(eip).attr('data-user');
        var field = $(eip).attr('data-field');
        var value;
        switch(field) {
          case '15':
          case '37':
            value = $('.edit-in-place textarea').val();
            break;
          default:
            value = $('.edit-in-place input').val();
        }
        $.post('/wp-content/plugins/edit-in-place/set_value.php', {
          'user' : user,
          'field' : field,
          'value' : value
        }, function(str) {
          $('.edit-in-place').remove();
          $(eip).html(value);
          $(eip).fadeIn(100).fadeOut(300).fadeIn(100).fadeOut(300).fadeIn(100);
				});
      }); // end save button click handler

    }); // end click handler

  }); // end document ready handler
})(jQuery);

