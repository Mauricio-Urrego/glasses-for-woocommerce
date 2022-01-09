<?php

/**
 * @file
 * Contains \MauricioUrrego\WoocommerceColors\Admin.
 */

namespace Mauriciourrego\WoocommerceColors;

/**
 * Administrative back-end functionality.
 */
class Admin {

  public static function admin_init() {
    add_action('admin_enqueue_scripts', __CLASS__ . '::enqueue_admin_assets');

  }

  public static function add_wc_colors_menu_page() {
    add_menu_page(
      'WooColors',
      'WooColors',
      'manage_options',
      'wc-colors',
      __CLASS__ . '::wc_colors_settings_page',
      'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjMzNi4xMDhweCIgaGVpZ2h0PSIzMzYuMTA4cHgiIHZpZXdCb3g9IjAgMCAzMzYuMTA4IDMzNi4xMDgiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDMzNi4xMDggMzM2LjEwODsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPHBvbHlnb24gcG9pbnRzPSI3Ny41Nyw0NS4zNDIgNzcuNTcsMS41ODEgMTQ0LjgyOCwxOS4xNDcgCSIvPg0KCTxyZWN0IHg9IjE3Ni41NjMiIHdpZHRoPSIyMi44NDUiIGhlaWdodD0iMzYuMjgzIi8+DQoJPHBhdGggZD0iTTE1OC4yOTIsNzAuMTI2aDU5LjM4NWMxNS41MTcsMCwyOC40NDEsMTEuMywzMC45OTcsMjYuMDk4Yy0wLjI5OS0wLjAxNi0wLjU5My0wLjAyMS0wLjg4OC0wLjAyMUgxMjguMTg1DQoJCWMtMC4yOTcsMC0wLjU5MSwwLjAwNS0wLjg4OCwwLjAyMUMxMjkuODQ2LDgxLjQyNywxNDIuNzcyLDcwLjEyNiwxNTguMjkyLDcwLjEyNnoiLz4NCgk8cGF0aCBkPSJNMjU4LjUzOCwzMjUuMzU5YzAsNS45MzEtNC44MiwxMC43NDktMTAuNzUxLDEwLjc0OUgxMjguMTg1Yy01LjkzLDAtMTAuNzUxLTQuODE4LTEwLjc1MS0xMC43NDlWMTE3LjcxMQ0KCQljMC00LjE4MiwyLjQ4My04LjAxNyw2LjMyNi05Ljc1N2MxLjQ1MS0wLjY2MSwyLjkzNy0wLjk5NSw0LjQyNS0wLjk5NWgxMTkuNjAyYzEuNDksMCwyLjk4MSwwLjMzMyw0LjQwOSwwLjk5DQoJCWMwLjAxMSwwLDAuMDExLDAsMC4wMTEsMC4wMDVjMy44NDgsMS43NDUsNi4zMjUsNS41OCw2LjMyNSw5Ljc1N3YyMDcuNjQ4SDI1OC41Mzh6Ii8+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg==',
      66
    );
  }

  public static function wc_colors_settings_page() {
    echo '
      <h1>Woocommerce Colors</h1>
      <p class="wc-colors__description">Clicking the button below will loop through all of your published products and assign a color attribute to them auto-magically. Review the colors <a href="edit-tags.php?taxonomy=pa_color&post_type=product">here</a> to change them to more friendly names.</p>
      <div class="wc-colors__process-data" data-process>Process Colors</div>
    ';
  }

  public static function enqueue_admin_assets() {
    wp_enqueue_style( 'wc-colors', '/wp-content/plugins/woocommerce-colors/assets/css/style.css');
    wp_enqueue_script( 'wc-colors', '/wp-content/plugins/woocommerce-colors/assets/js/main.js', array('jquery'));
  }
}
