(function($) {
  $(document).ready(function() {
    const process_button = $('[data-process]');
    process_button.click(() => {
      let data = {
        'action': 'process_colors',
        'foobar_id': 123
      };

      let pollingData = {
        'action': 'check_progress',
        'foobar_id': 1234
      }

      process_button.prop("disabled",true);
      $('.button__text').text('In Progress...');
      $.post(ajaxurl, data, function(response) {
        if (response) {
          process_button.prop("disabled",false);
          $('.button__text').text('Try Again?');
          window.confirm(response);
        }
        else {
          process_button.prop("disabled",false);
          $('.button__text').text('Done! ...Again?');
          if (window.confirm('All done! Check them out?')) {
            window.open('edit-tags.php?taxonomy=pa_glasses_color&post_type=product', '_self');
          }
        }
        window.clearInterval(pollInterval);
      });

      let pollInterval = window.setInterval(function() {
        jQuery.get(ajaxurl, pollingData, function(data){
          let parsedData = JSON.parse(data);
          let totalItems = parseInt(parsedData[0]);
          let currentIndex = parseInt(parsedData[1]) + 1;
          let productName = parsedData[2];
          $('.button__progress').width(`${currentIndex/totalItems * 100}%`);
          jQuery('#progress').empty().append(currentIndex + '/' + totalItems + ' ' + productName);
        });
      }, 500);
    });
  })
})(jQuery);
