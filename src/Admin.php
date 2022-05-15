<?php

/**
 * @file
 * Contains \MauricioUrrego\WooColorsForWooCommerce\Admin.
 */

namespace Mauriciourrego\WooColorsForWooCommerce;

/**
 * Administrative back-end functionality.
 */
class Admin {

  public static function admin_init() {
    add_action('admin_enqueue_scripts', __CLASS__ . '::enqueue_admin_assets');
    if (!class_exists('WooCommerce')) {
      add_action('admin_notices', __NAMESPACE__ . '\Admin::enable_woocommerce');
    }
  }

  /**
   * Get the plugin url.
   *
   * @return string
   */
  public static function plugin_url() {
    return untrailingslashit( plugins_url( '/', WOOCOLORS_PLUGIN_FILE ) );
  }

  public static function add_woo_colors_menu_page() {
    add_menu_page(
      'Woo Colors!',
      'Woo Colors!',
      'manage_options',
      'woo-colors',
      __CLASS__ . '::woo_colors_settings_page',
      'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjMzNi4xMDhweCIgaGVpZ2h0PSIzMzYuMTA4cHgiIHZpZXdCb3g9IjAgMCAzMzYuMTA4IDMzNi4xMDgiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDMzNi4xMDggMzM2LjEwODsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPHBvbHlnb24gcG9pbnRzPSI3Ny41Nyw0NS4zNDIgNzcuNTcsMS41ODEgMTQ0LjgyOCwxOS4xNDcgCSIvPg0KCTxyZWN0IHg9IjE3Ni41NjMiIHdpZHRoPSIyMi44NDUiIGhlaWdodD0iMzYuMjgzIi8+DQoJPHBhdGggZD0iTTE1OC4yOTIsNzAuMTI2aDU5LjM4NWMxNS41MTcsMCwyOC40NDEsMTEuMywzMC45OTcsMjYuMDk4Yy0wLjI5OS0wLjAxNi0wLjU5My0wLjAyMS0wLjg4OC0wLjAyMUgxMjguMTg1DQoJCWMtMC4yOTcsMC0wLjU5MSwwLjAwNS0wLjg4OCwwLjAyMUMxMjkuODQ2LDgxLjQyNywxNDIuNzcyLDcwLjEyNiwxNTguMjkyLDcwLjEyNnoiLz4NCgk8cGF0aCBkPSJNMjU4LjUzOCwzMjUuMzU5YzAsNS45MzEtNC44MiwxMC43NDktMTAuNzUxLDEwLjc0OUgxMjguMTg1Yy01LjkzLDAtMTAuNzUxLTQuODE4LTEwLjc1MS0xMC43NDlWMTE3LjcxMQ0KCQljMC00LjE4MiwyLjQ4My04LjAxNyw2LjMyNi05Ljc1N2MxLjQ1MS0wLjY2MSwyLjkzNy0wLjk5NSw0LjQyNS0wLjk5NWgxMTkuNjAyYzEuNDksMCwyLjk4MSwwLjMzMyw0LjQwOSwwLjk5DQoJCWMwLjAxMSwwLDAuMDExLDAsMC4wMTEsMC4wMDVjMy44NDgsMS43NDUsNi4zMjUsNS41OCw2LjMyNSw5Ljc1N3YyMDcuNjQ4SDI1OC41Mzh6Ii8+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg==',
      66
    );
  }

  public static function woo_colors_settings_page() {
    echo '<h1>Woo Colors! for WooCommerce</h1>';
    echo '<p class="woo-colors__description">
            Clicking the button below will loop through all of your published products and assign a color attribute to 
            them auto-magically. Review the colors <a href="edit-tags.php?taxonomy=pa_color&post_type=product">here</a> 
            to change them to more friendly names.
          </p>';

    if (!class_exists('WooCommerce')) {
      echo '<div class="woo-colors__process-data" data-process style="filter: grayscale(1)">Process Colors</div>';
      return;
    }

    echo '<div class="woo-colors__process-data" data-process>Process Colors</div>';
  }

  public static function enqueue_admin_assets() {
    $plugin_url = self::plugin_url();
    wp_enqueue_style('woo-colors', $plugin_url . '/assets/css/style.css');
    wp_enqueue_script('woo-colors', $plugin_url . '/assets/js/main.js', ['jquery']);
  }

  public static function enable_woocommerce() {
    $class = 'notice notice-error';
    $message = __('Oops! Enable WooCommerce plugin to use Woo Colors! for WooCommerce.', 'woo-colors-for-woocommerce');
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html($message));
  }
}
