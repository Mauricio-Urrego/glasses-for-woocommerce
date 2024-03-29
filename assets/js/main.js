(function($) {
  $(document).ready(function() {
      let data = {
        'action': 'glassesLoading',
        'query_params': glasses.query_params,
        'post_params': glasses.post_params
      };

      let pollingData = {
        'action': 'checkProgress',
      }

      $.post(ajaxurl, data, function(response) {
        if (response) {
          if (window.confirm(response)) {
              window.open('admin.php?page=glasses-settings', '_self');
          }
        }
        else {
          let queries = new URLSearchParams(glasses.query_params);
          window.open('edit.php?post_type=product' + '&count=' + queries.get('count') + '&type=' + queries.get('type'), '_self');
        }
        window.clearInterval(pollInterval);
      });

      let pollInterval = window.setInterval(function() {
        jQuery.get(ajaxurl, pollingData, function(data){
          let parsedData = JSON.parse(data);
          let totalItems = parseInt(parsedData[0]);
          let currentIndex = parseInt(parsedData[1]);
          let productName = parsedData[2];
          let task = parsedData[3];
          $('.button__progress').width(`${currentIndex/totalItems * 100}%`);
          jQuery('#progress').empty().append(task + ' ' + currentIndex + '/' + totalItems + ' ' + (productName ? productName : ''));
        });
      }, 500);
  })
})(jQuery);
