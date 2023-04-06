<?php

/*
  Plugin Name: Glasses for WooCommerce
  Version: 2.0.3
  Text Domain: glasses-for-woocommerce
  Description: AI assisted store creation. Ever wanted a robot to open an e-commerce shop for you? This is what that would look like.
  Author: Mauricio Urrego
  License: GPL-2.0+
  License URI: http://www.gnu.org/licenses/gpl-2.0
*/

namespace Mauriciourrego\GlassesForWooCommerce;
require_once  __DIR__ . '/vendor/autoload.php';

if (!defined('GLASSES_PLUGIN_FILE')) {
  define('GLASSES_PLUGIN_FILE', __FILE__);
}

if (!defined('ABSPATH')) {
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

add_action('activated_plugin', __NAMESPACE__ . '\Schema::activated_plugin');
add_action('plugins_loaded', __NAMESPACE__ . '\Plugin::loadTextdomain');
add_action('init', __NAMESPACE__ . '\WooCommerce::init', 20);
add_action('admin_init', __NAMESPACE__ . '\Admin::admin_init');
add_action('admin_menu', __NAMESPACE__ . '\Admin::add_glasses_menu_page');
