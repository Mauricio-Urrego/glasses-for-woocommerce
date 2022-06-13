(function($) {
  $(document).ready(function() {
    $('[data-process]').click(() => {
      let data = {
        'action': 'process_colors',
        'foobar_id': 123
      };

      $('.glasses__process-data').text('Processing...');
      $.post(ajaxurl, data, function(response) {
        if (response) {
          window.confirm(response);
          $('.glasses__process-data').text('Process Colors');
        }
        else {
          if (window.confirm('All done! Check them out?')) {
            $('.glasses__process-data').text('Process Colors');
            window.open('edit-tags.php?taxonomy=pa_glasses_color&post_type=product', '_self');
          }
        }
      });
    });
  })
})(jQuery);