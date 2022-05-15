<?php

/**
 * @file
 * Contains \MauricioUrrego\WooColorsForWooCommerce\Plugin.
 */

namespace Mauriciourrego\WooColorsForWooCommerce;

/**
 * Main front-end functionality.
 */
class Plugin {
  /**
   * Prefix for naming.
   *
   * @var string
   */
  const PREFIX = 'woo-colors-for-woocommerce';

  /**
   * Gettext localization domain.
   *
   * @var string
   */
  const L10N = self::PREFIX;

  /**
   * Loads the plugin textdomain.
   */
  public static function loadTextdomain() {
    load_plugin_textdomain(static::L10N, FALSE, static::L10N . '/languages/');
  }

  /**
   * The base URL path to this plugin's folder.
   *
   * Uses plugins_url() instead of plugin_dir_url() to avoid a trailing slash.
   */
  public static function getBaseUrl() {
    if (!isset(static::$baseUrl)) {
      static::$baseUrl = plugins_url('', static::getBasePath() . '/plugin.php');
    }
    return static::$baseUrl;
  }

  /**
   * The absolute filesystem base path of this plugin.
   *
   * @return string
   *   Plugin base directory name.
   */
  public static function getBasePath() {
    return dirname(__DIR__);
  }

}
