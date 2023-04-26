<?php
/**
 * @file
 * Contains \MauricioUrrego\GlassesForWooCommerce\Progress.
 */

namespace Mauriciourrego\GlassesForWooCommerce;

use JetBrains\PhpStorm\NoReturn;

/**
 * Progress for progress bar.
 */
class Progress {
	/**
	 * @var $wpdb
	 */
	private $wpdb;

	/**
	 * Progress constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function startProgress($count, $task): void {
		$this->wpdb->update(
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

	public function updateProgress($current_index, $product = true): void {
		$this->wpdb->update(
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

	public function completeProgress($current_index): void {
		$this->wpdb->update(
			'wp_glasses_progress',
			[
				'CurrentIndex' => $current_index,
			],
			[
				'ProgressID' => 1
			]
		);
	}

	public static function checkProgress(): void {
		global $wpdb;
		$result = [];
		$result[] = $wpdb->get_var("SELECT TotalFound FROM wp_glasses_progress WHERE ProgressID=1");
		$result[] = $wpdb->get_var("SELECT CurrentIndex FROM wp_glasses_progress WHERE ProgressID=1");
		$result[] = $wpdb->get_var("SELECT ProductName FROM wp_glasses_progress WHERE ProgressID=1");
		$result[] = $wpdb->get_var("SELECT Task FROM wp_glasses_progress WHERE ProgressID=1");
		echo json_encode($result);

		wp_die();
	}
}