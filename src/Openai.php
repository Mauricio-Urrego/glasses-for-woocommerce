<?php
/**
 * @file
 * Contains \MauricioUrrego\GlassesForWooCommerce\Openai.
 */

namespace Mauriciourrego\GlassesForWooCommerce;

/**
 * API for OpenAI.
 */
class Openai {
	private function secret_key(){
		if (!get_option('open-ai-api-key')) {
			wp_die();
		}
		return get_option('open-ai-api-key');
	}

	public static function request($prompt): string {
		$max_tokens = 2049;
		$request_body = [
			"prompt" => $prompt,
			"max_tokens" => $max_tokens,
			"model" => "text-davinci-003",
			"temperature" => 0.6,
			"top_p" => 1,
			"presence_penalty" => 1,
			"frequency_penalty"=> 1,
			"best_of"=> 1,
			"stream" => false,
		];

		// Initialize curl object
		$ch = curl_init();
		$post_fields = json_encode($request_body);

		// Set curl options
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => 1, // Return information from server
			CURLOPT_URL => 'https://api.openai.com/v1/completions',
			CURLOPT_POST => 1, // Normal HTTP post
			CURLOPT_POSTFIELDS => $post_fields,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: Bearer ' . (new Openai)->secret_key()
			),
		));

		// Execute curl and return result to $response
		$response = curl_exec($ch);

		// Close request
		curl_close($ch);

		if (curl_errno($ch)) {
			return 'Error:' . curl_error($ch);
		}

		return $response;
	}

	public static function requestGenerateImages($prompt): string {
		$request_body = [
			"prompt" => $prompt,
			"n" => 1,
			"size" => "512x512"
		];

		// Initialize curl object
		$ch = curl_init();
		$post_fields = json_encode($request_body);

		// Set curl options
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => 1, // Return information from server
			CURLOPT_URL => 'https://api.openai.com/v1/images/generations',
			CURLOPT_POST => 1, // Normal HTTP post
			CURLOPT_POSTFIELDS => $post_fields,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: ' . (new Openai)->secret_key()
			),
		));

		// Execute curl and return result to $response
		$response = curl_exec($ch);

		// Close request
		curl_close($ch);

		if (curl_errno($ch)) {
			return 'Error:' . curl_error($ch);
		}

		return $response;
	}
}
