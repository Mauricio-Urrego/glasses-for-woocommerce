<?php

/**
 * @file
 * Contains \Mauriciourrego\GlassesForWooCommerce\Schema.
 */

namespace Mauriciourrego\GlassesForWooCommerce;

/**
 * Generic plugin lifetime and maintenance functionality.
 */
class Schema {

  /**
   * register_activation_hook() callback.
   */
  public static function activate() {
    if(!class_exists('WooCommerce')){
      trigger_error(__('Please enable WooCommerce Plugin before using Glasses for WooCommerce.', 'glasses-for-woocommerce'), E_USER_ERROR);
    }
    Schema::ensure_color_tax();
  }

  /**
   * register_deactivation_hook() callback.
   */
  public static function deactivate() {
  }

  /**
   * register_uninstall_hook() callback.
   */
  public static function uninstall() {
  }

  public static function ensure_color_tax() {
    $attributes = wc_get_attribute_taxonomies();
    $color_taxonomy = get_terms('pa_glasses_color', $attributes);
    if (is_wp_error($color_taxonomy)) {
      $args = [
        'slug'    => 'pa_glasses_color',
        'name'   => __('Color', Plugin::L10N),
        'type'    => 'text',
        'orderby' => 'menu_order',
        'has_archives'  => false,
      ];
      wc_create_attribute($args);
    }
  }

}
