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
	private const API_ENDPOINT_COMPLETIONS = 'https://api.openai.com/v1/completions';
	private const API_ENDPOINT_GENERATE_IMAGES = 'https://api.openai.com/v1/images/generations';
	private const MODEL_TEXT_DAVINCI_003 = 'text-davinci-003';
	private const IMAGE_SIZE_512 = '512x512';

	private function getApiKey(): string {
		$api_key = get_option('open-ai-api-key');
		if (!$api_key) {
			wp_die('For this feature an Open AI API Key must be added in the plugin settings.');
		}
		return $api_key;
	}

	/**
	 * @throws \Exception
	 */
	private function makeApiRequest(string $endpoint, array $request_body): string {
		// Initialize curl object
		$ch = curl_init();
		$post_fields = json_encode($request_body);

		// Set curl options
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => 1, // Return information from server
			CURLOPT_URL => $endpoint,
			CURLOPT_POST => 1, // Normal HTTP post
			CURLOPT_POSTFIELDS => $post_fields,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: Bearer ' . $this->getApiKey()
			),
		));

		// Execute curl and return result to $response
		$response = curl_exec($ch);

		// Close request
		curl_close($ch);

		// Check for errors.
		if (json_decode($response)->error) {
			wp_die(json_decode($response)->error->message);
		}

		if (curl_errno($ch)) {
			throw new \Exception('OpenAI API request error: ' . curl_error($ch));
		}

		return $response;
	}

	/**
	 * @throws \Exception
	 */
	public function requestCompletions(string $prompt): string {
		$request_body = [
			'prompt' => $prompt,
			'max_tokens' => 2049,
			'model' => self::MODEL_TEXT_DAVINCI_003,
			'temperature' => 0.6,
			'top_p' => 1,
			'presence_penalty' => 1,
			'frequency_penalty' => 1,
			'best_of' => 1,
			'stream' => false,
		];

		return $this->makeApiRequest(self::API_ENDPOINT_COMPLETIONS, $request_body);
	}

	/**
	 * @throws \Exception
	 */
	public function requestImages(string $prompt): string {
		$request_body = [
			"prompt" => $prompt,
			"n" => 1,
			"size" => self::IMAGE_SIZE_512
		];

		return $this->makeApiRequest(self::API_ENDPOINT_GENERATE_IMAGES, $request_body);
	}
}
