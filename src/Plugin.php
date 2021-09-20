<?php

/**
 * @file
 * Contains \MauricioUrrego\WoocommerceColors\Plugin.
 */

namespace Mauriciourrego\WoocommerceColors;

/**
 * Main front-end functionality.
 */
class Plugin {
  /**
   * Prefix for naming.
   *
   * @var string
   */
  const PREFIX = 'colors';

  /**
   * Gettext localization domain.
   *
   * @var string
   */
  const L10N = self::PREFIX;

  public static function init() {
    echo '<button class="btn" onclick="onrequest();">Auto-populate</button>';
    //WooCommerce::init();
  }

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
