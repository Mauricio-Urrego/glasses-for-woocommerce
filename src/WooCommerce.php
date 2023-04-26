<?php

/**
 * @file
 * Contains \Mauriciourrego\GlassesForWooCommerce\WooCommerce.
 */

namespace Mauriciourrego\GlassesForWooCommerce;

use Exception;
use Mauriciourrego\ColorcubePhp\ColorCube;
use Phim\Color;
use Phim\Color\RgbColor;
use WC_Product_Attribute;
use WC_Product_Variable;

class WooCommerce {

	/**
	 * Initializes the WooCommerce hooks.
	 */
    public static function init(): void {
      add_action('wp_ajax_glassesLoading', __CLASS__ . '::glassesLoading');
      add_action('wp_ajax_checkProgress', __NAMESPACE__ . '\Progress::checkProgress');
    }

	/**
	 * Handles the glasses_loading AJAX request.
	 */
    public static function glassesLoading(): void {
		  $queryParams = $_POST['query_params'] ?? '';
		  parse_str($queryParams, $params);
		  $type = $params['type'] ?? '';

		  if (!empty($type)) {
			  $productIds = $params['ids'];
			  switch ($type) {
				  case 'color':
					  self::processColors($productIds);
					  break;
				  case 'description':
					  self::updateProductDescription($productIds);
					  break;
				  case 'short description':
					  self::updateProductDescription($productIds, true);
					  break;
				  case 'image':
					  self::generateImages($productIds);
					  break;
				  case 'price':
					  self::createPrices($productIds);
					  break;
			  }
		  } else {
			  $postParams = $_POST['post_params'] ?? '';
			  $categories = $postParams['categories'] ?? '';
			  $description = $postParams['description'] ?? '';

			  if (!empty($categories) && !empty($description)) {
				  $store = is_array($categories);
				  $productIds = self::createProducts($categories, $description, $store);

				  self::updateProductDescription($productIds);
				  self::updateProductDescription($productIds, true);
				  self::generateImages($productIds);
				  self::processColors($productIds);
				  self::createPrices($productIds);
			  }
		  }

		  wp_die();
    }

	/**
	 * Creates new products or stores based on the provided categories and description.
	 *
	 * @param string|array $categories The product categories.
	 * @param string $description The product description.
	 * @param bool $isStore Whether it's a multiple or a singular product creation. Stores are multiple.
	 *
	 * @return array The product IDs.
	 */
  public static function createProducts(string|array $categories, string $description, bool $isStore = false): array {
	  $numberOfProducts = $isStore ? 10 : 1;
	  if ($isStore) {
		  $option = get_option('glasses-how-many');
		  $numberOfProducts = $option ?: $numberOfProducts;
	  }

	  (new Progress)->startProgress($numberOfProducts, 'Creating Product(s)...');

	  try {
		  $response = (new Openai)->requestCompletions('Come up with ' . $numberOfProducts . ' product name(s) in the category(s) of ' . $categories . ' with a description of ' . $description . ' and make the response text comma separated with an oxford comma and no word and or numbers. Just the names. (example: name1, name2, name3, etc.):');
		  $response = json_decode($response)->choices[0]->text;
		  $response = trim(preg_replace('/\s\s+/', '', $response));
		  $productNames = explode(', ', $response);
		  $numberOfProductNames = count($productNames);

		  // Check if we received the expected number of product names
		  if ($numberOfProductNames !== $numberOfProducts) {
			  if ($numberOfProductNames < $numberOfProducts) {
				  $numberOfMissingProducts = $numberOfProducts - $numberOfProductNames;

				  for ($i = 0; $i < $numberOfMissingProducts; $i++) {
					  $productNames[] = 'error generating';
				  }
			  }
			  elseif ($numberOfProductNames > $numberOfProducts) {
				  $productNames = array_slice($productNames, 0, 1);
			  }
		  }

		  $productIds = [];
		  for ($i = 0; $i < $numberOfProducts; $i++) {
			  (new Progress)->updateProgress($i + 1, false);
			  preg_match('/\w.*\w/', $productNames[$i], $title);
			  $productIds[] = wp_insert_post([
				  'post_title' => $title[0],
				  'post_type' => 'product'
			  ]);
		  }

		  (new Progress)->completeProgress(count($productIds));

		  return $productIds;
	  }
	  catch(Exception $e) {
		  wp_die('Error: ' . $e);
	  }
  }

  public static function processColors(array $productIds): void {
	  // Ensure color taxonomy exists.
	  ( new Schema )->ensureColorTax();

	  // Start progress tracking.
	  (new Progress)->startProgress(count($productIds), 'Identifying Colors');

	  foreach ($productIds as $index => $productId) {
		// Get product and check if it exists.
	    $product = wc_get_product($productId);
		if (!$product) {
			continue;
		}

		// Update progress tracker.
	    (new Progress)->updateProgress($index, $product);

	    // Declare container for color identification.
        $imageIds = [];

        // Fill containers.
        $imageIds[] = $product->get_image_id();

        // Collect image URLs.
        $imageUrls = [];
        if ($imageIds) {
          foreach ($imageIds as $imageId) {
            $imageUrls[] = wp_get_attachment_image_url($imageId);
          }
        }

	  $productVariations = new WC_Product_Variable($productId);
	  $productVariations = $productVariations->get_available_variations();

	  foreach ($productVariations as $productVariation) {
		$imageUrls[] = $productVariation['image']['thumb_src'];
	  }

	  $cc = new ColorCube();

      foreach ($imageUrls as $imageUrl) {
        if (empty($imageUrl)) {
          continue;
        }
        $image = imagecreatefromjpeg($imageUrl);
		if (!$image) {
			$image = imagecreatefrompng($imageUrl);
		}
        $colors = $cc->get_colors($image);
        if (empty($colors)) {
          continue;
        }
        $rgbColor = new RgbColor($colors[0][0], $colors[0][1], $colors[0][3]);

        // Color is identified so assign color to product attribute term.
        $taxonomy = 'pa_glasses_color';
        $term_name = Color::toHexString($rgbColor);
        $term_slug = sanitize_title($term_name);

        // Check if the term exists and if not then create it (and get the term ID).
        if (!term_exists($term_name, $taxonomy)) {
          $term_data = wp_insert_term($term_name, $taxonomy);
          if (is_wp_error($term_data)) {
              wp_die("Oopsies! Looks like you deleted your colors attribute by mistake. We'll handle it. Let's try that one more time. Click on 'Process Colors' again please :)");
          }
          $term_id = $term_data['term_id'];
        }
        else {
          $term_id = get_term_by('name', $term_name, $taxonomy)->term_id;
        }

        $attributes = $product->get_attributes();

        // If the product attribute is set for the product.
        if (array_key_exists($taxonomy, $attributes)) {
          foreach($attributes as $key => $attribute){
            if($key === $taxonomy){
              $options = $attribute->get_options();
              $options[] = $term_id;
              $attribute->set_options($options);
              $attributes[$key] = $attribute;
              break;
            }
          }
          $product->set_attributes($attributes);
        }
        // If the product attribute is not set for the product.
        else {
          $attribute = new WC_Product_Attribute();

          $attribute->set_id(sizeof($attributes) + 1);
          $attribute->set_name($taxonomy);
          $attribute->set_options([$term_id]);
          $attribute->set_position(sizeof($attributes) + 1);
          $attribute->set_visible(true);
          $attribute->set_variation(false);
          $attributes[] = $attribute;

          $product->set_attributes($attributes);
        }

        $product->save();

        // Add the new term in the product.
        if (!has_term($term_name, $taxonomy, $productId)) {
          wp_set_object_terms($productId, $term_slug, $taxonomy, true);
        }
      }
    }

	// Complete progress tracking.
	(new Progress)->completeProgress(count($productIds));

	wp_reset_postdata();
  }

  public static function updateProductDescription(array $productIds, bool $short = false): void {
	  (new Progress)->startProgress(count($productIds), 'Updating Product ' . ($short ? 'Short ' : '') . 'Description');

	  foreach ($productIds as $current_index => $productId) {
		  $product = wc_get_product($productId);

		  (new Progress)->updateProgress($current_index, $product);

		  $title = $product->get_title();
		  $response = (new Openai)->requestCompletions('Write a product description for a' . $title . ($short ? ' in 1-2 sentences' : '') . ':');
		  $response = json_decode($response);
		  $response = $response->choices[0]->text;
		  if ($short) {
			  $product->set_short_description($response);
		  }
		  else {
			  $product->set_description($response);
		  }
		  $product->save();
	  }

	  (new Progress)->completeProgress(count($productIds));

	  wp_reset_postdata();
  }

	/**
	 * @throws Exception
	 */
	public static function generateImages($productIds): void {
	  (new Progress)->startProgress(count($productIds), 'Generating Images');

	  foreach ($productIds as $current_index => $productId) {
		  $product = wc_get_product($productId);

		  (new Progress)->updateProgress($current_index, $product);

		  $title = $product->get_title();
		  $response = ( new Openai )->requestImages( 'Studio image of a product with the title of: ' . $title);
		  $response = json_decode($response);

		  $image_url = $response->data[0]->url;

		  $tmp = download_url($image_url);
		  if (is_wp_error($tmp)) continue;

		  // Get the filename and extension ("photo.png" => "photo", "png")
		  $filename = pathinfo($image_url, PATHINFO_FILENAME);
		  $extension = pathinfo($image_url, PATHINFO_EXTENSION);

		  // An extension is required or else WordPress will reject the upload
		  if (!$extension) {
			  // Look up mime type, example: "/photo.png" -> "image/png"
			  $mime = mime_content_type( $tmp );
			  $mime = is_string($mime) ? sanitize_mime_type( $mime ) : false;

			  // Only allow certain mime types because mime types do not always end in a valid extension (see the .doc example below)
			  $mime_extensions = array(
				  // mime_type         => extension (no period)
				  'text/plain'         => 'txt',
				  'text/csv'           => 'csv',
				  'application/msword' => 'doc',
				  'image/jpg'          => 'jpg',
				  'image/jpeg'         => 'jpeg',
				  'image/gif'          => 'gif',
				  'image/png'          => 'png',
				  'video/mp4'          => 'mp4',
			  );

			  if (isset($mime_extensions[$mime])) {
				  // Use the mapped extension
				  $extension = $mime_extensions[$mime];
			  }
			  else {
				  // Could not identify extension
				  @unlink($tmp);
				  continue;
			  }
		  }

		  // Shorten filename.
		  $filename = substr($filename, -50);

		  $args = [
			  'name' => "$filename.$extension",
			  'tmp_name' => $tmp,
		  ];

		  $media_id = media_handle_sideload($args, 0, $title);

		  // Cleanup temp file
		  @unlink($tmp);

		  // Error uploading
		  if (is_wp_error($media_id)) continue;

		  $product->set_image_id($media_id);
		  $product->save();
	  }

	  ( new Progress )->completeProgress(count($productIds));

	  wp_reset_postdata();
  }

  public static function createPrices($productIds): void {
	  ( new Progress )->startProgress(count($productIds), 'Updating Product Price');

	  foreach ($productIds as $current_index => $productId) {
		  $product = wc_get_product($productId);

		  ( new Progress )->updateProgress($current_index, $product);

		  $title = $product->get_title();
		  $response = ( new Openai )->requestCompletions( 'Perfect price for a ' . $title . ', only name one price, if you must name them in an array.');
		  $response = json_decode($response)->choices[0]->text;
		  preg_match('/\[(.*)\]/', $response, $response);
		  $response = explode(', ', $response[1]);
		  $response = end($response);
		  $response = preg_match('/[\d.]+/', $response, $match);
		  if ($match[0]) {
			  $product->set_regular_price($match[0]);
			  $product->set_price($match[0]);
			  $product->save();
		  }
	  }

	  ( new Progress )->completeProgress(count($productIds));

	  wp_reset_postdata();
  }
}
