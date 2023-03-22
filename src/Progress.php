<?php
/**
 * @file
 * Contains \MauricioUrrego\GlassesForWooCommerce\Progress.
 */

namespace Mauriciourrego\GlassesForWooCommerce;

/**
 * Progress for progress bar.
 */
class Progress {
	public static function startProgress($count, $task) {
		global $wpdb;
		$wpdb->update(
			'wp_glasses_progress',
			[
				'TotalFound' => $count,
				'ProductName' => '',
				'CurrentIndex' => 0,
				'Task' => $task
			],
			[
				'ProgressID' => 1
			]
		);
	}

	public static function updateProgress($current_index, $product = true) {
		global $wpdb;
		$wpdb->update(
			'wp_glasses_progress',
			[
				'CurrentIndex' => $current_index,
				'ProductName' => $product ? $product->get_title() : 'Creating product titles...'
			],
			[
				'ProgressID' => 1
			]
		);
	}

	public static function completeProgress($current_index) {
		global $wpdb;
		$wpdb->update(
			'wp_glasses_progress',
			[
				'CurrentIndex' => $current_index,
			],
			[
				'ProgressID' => 1
			]
		);
	}
}