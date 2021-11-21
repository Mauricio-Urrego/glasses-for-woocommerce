(function($) {
  $(document).ready(function() {
    $('[data-process]').click(() => {
      $.ajax({
        url: '/wp-content/plugins/woocommerce-colors/handlers/adminHandler.php', success: function(result){
          alert(result);
        }
      });
    });
  })
})(jQuery);