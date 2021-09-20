<?php

/*
  Plugin Name: Woocommerce Colors
  Version: 0.0.1
  Text Domain: woocommerce-colors
  Description: Recommend related products based on dominant colors.
  Author: Mauricio Urrego
  License: GPL-2.0+
  License URI: http://www.gnu.org/licenses/gpl-2.0
*/

namespace Mauriciourrego\WoocommerceColors;

if (!defined('ABSPATH')) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
  exit;
}

/**
 * Loads PSR-4-style plugin classes.
 */
function classloader($class) {
  static $ns_offset;
  if (strpos($class, __NAMESPACE__ . '\\') === 0) {
    if ($ns_offset === NULL) {
      $ns_offset = strlen(__NAMESPACE__) + 1;
    }
    include __DIR__ . '/src/' . strtr(substr($class, $ns_offset), '\\', '/') . '.php';
  }
}
spl_autoload_register(__NAMESPACE__ . '\classloader');

register_activation_hook(__FILE__, __NAMESPACE__ . '\Schema::activate');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\Schema::deactivate');
register_uninstall_hook(__FILE__, __NAMESPACE__ . '\Schema::uninstall');

add_action('plugins_loaded', __NAMESPACE__ . '\Plugin::loadTextdomain');

function edit_product_column( $columns ) {
    //add column
    $columns['Terms'] = __( 'Terms', 'woocommerce' );
    var_dump($columns);

    return $columns;
}
add_filter( 'manage_edit-product_columns', 'add_product_column', 10, 1 );

add_action('woocommerce_after_product_attribute_settings', __NAMESPACE__ . '\Plugin::init', 20); // TODO: load only on settings trigger.
// 'woocommerce_after_product_attribute_settings' for single product updates.
