(function($) {
  $(document).ready(function() {
    $('[data-process]').click(() => {
      let data = {
        'action': 'process_colors',
        'foobar_id': 123
      };

      $.post(ajaxurl, data, function(response) {
        alert('Got this from the server: ' + response);
      });
    });
  })
})(jQuery);