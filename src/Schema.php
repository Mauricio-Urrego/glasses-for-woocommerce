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
	 * @var $wpdb
	 */
	private \wpdb $wpdb;

	/**
	 * Progress constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	const COLOR_TAXONOMY_SLUG = 'pa_glasses_color';

	/**
	 * register_activation_hook() callback.
	 */
	public static function activate(): void {
	  if(!class_exists('WooCommerce')){
	    trigger_error(__('Please enable WooCommerce Plugin before using Glasses for WooCommerce.', 'glasses-for-woocommerce'), E_USER_ERROR);
	  }
	  ( new Schema )->ensureColorTax();
	  ( new Schema )->createProgressTable();
	}

    /**
     * register_deactivation_hook() callback.
     */
    public static function deactivate(): void {
		  ( new Schema )->deleteProgressTable();
    }

    /**
     * register_uninstall_hook() callback.
     */
    public static function uninstall(): void {
		  ( new Schema )->deleteProgressTable();
    }

	public function ensureColorTax(): void {
		$attributes = wc_get_attribute_taxonomies();
		$color_taxonomy = get_terms(self::COLOR_TAXONOMY_SLUG, $attributes);
		if (is_wp_error($color_taxonomy)) {
			$args = [
				'slug' => self::COLOR_TAXONOMY_SLUG,
				'name' => __('Color', Plugin::L10N),
				'type' => 'text',
				'orderby' => 'menu_order',
				'has_archives' => false,
			];
			wc_create_attribute($args);
		}
	}

	private function createProgressTable(): void {
		$this->wpdb->query('CREATE TABLE wp_glasses_progress (ProgressID int, TotalFound int, CurrentIndex int, ProductName varchar(255), Task varchar(100))');
		$this->wpdb->replace('wp_glasses_progress', ['ProgressID' => 1]);
	}

	private function deleteProgressTable(): void {
		$this->wpdb->query('DROP TABLE wp_glasses_progress');
	}

	public static function activated_plugin(string $plugin): void {
		if ($plugin === 'glasses-for-woocommerce/glasses-for-woocommerce.php') {
			if (!get_option('open-ai-api-key')) {
				exit(wp_safe_redirect(admin_url('admin.php?page=glasses-settings')));
			}
		}
	}
}
