<?php

/**
 * @file
 * Contains \MauricioUrrego\GlassesForWooCommerce\Plugin.
 */

namespace Mauriciourrego\GlassesForWooCommerce;

/**
 * Main front-end functionality.
 */
class Plugin {
  private static string $baseUrl;

  /**
   * Prefix for naming.
   *
   * @var string
   */
  const PREFIX = 'glasses-for-woocommerce';

  /**
   * Gettext localization domain.
   *
   * @var string
   */
  const L10N = self::PREFIX;

	/**
   * Loads the plugin text domain.
   *
   * @return void
   */
  public static function loadPluginTextDomain(): void {
    load_plugin_textdomain(static::L10N, FALSE, static::L10N . '/languages/');
  }

  /**
   * The base URL path to this plugin's folder.
   *
   * Uses plugins_url() instead of plugin_dir_url() to avoid a trailing slash.
   *
   * @return string
   *   The base URL path to this plugin's folder.
   */
  public static function getPluginBaseUrl(): string {
    if (!isset(static::$baseUrl)) {
      static::$baseUrl = plugins_url('', static::getPluginBasePath() . '/plugin.php');
    }
    return static::$baseUrl;
  }

  /**
   * Returns the absolute filesystem base path of this plugin.
   *
   * @return string
   *   The absolute filesystem base path of this plugin.
   */
  public static function getPluginBasePath(): string {
    return dirname(__DIR__);
  }

}
