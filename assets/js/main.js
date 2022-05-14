(function($) {
  $(document).ready(function() {
    $('[data-process]').click(() => {
      let data = {
        'action': 'process_colors',
        'foobar_id': 123
      };

      $.post(ajaxurl, data, function(response) {
        if (window.confirm('All done! Check them out?')) {
          window.open('edit-tags.php?taxonomy=pa_color&post_type=product', '_self');
        }
      });
    });
  })
})(jQuery);