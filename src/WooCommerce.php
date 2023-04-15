<?php

/**
 * @file
 * Contains \Mauriciourrego\GlassesForWooCommerce\WooCommerce.
 */

namespace Mauriciourrego\GlassesForWooCommerce;

use Mauriciourrego\ColorcubePhp\ColorCube;
use WC_Product_Variable;
use WC_Product_Attribute;
use Phim\Color;
use Phim\Color\RgbColor;

class WooCommerce {

  public static function init() {
    add_action('wp_ajax_glasses_loading', __CLASS__ . '::glasses_loading');
    add_action('wp_ajax_check_progress', __CLASS__ . '::check_progress');
  }

  public static function glasses_loading(): void {
	  parse_str($_POST['query_params'], $output);
	  if (isset($output['type'])) {
		  $product_ids = $output['ids'];
		  $type = $output['type'];

		  if ($type === 'color') {
			  self::process_colors($product_ids);
		  }
		  if ($type === 'description') {
			  self::update_product_description($product_ids);
		  }
		  if ($type === 'short description') {
			  self::update_product_description($product_ids, true);
		  }
		  if ($type === 'image') {
			  self::generate_images($product_ids);
		  }
		  if ($type === 'price') {
			  self::create_prices($product_ids);
		  }

		  wp_die();
	  }

	  if (empty($output['type']) && !empty($_POST['post_params']['categories']) && !empty($_POST['post_params']['description'])) {
		  $categories = $_POST['post_params']['categories'];
		  $description = $_POST['post_params']['description'];
		  $store = false;

		  if (is_array($categories)) {
			  $store = true;
			  $categories = implode(", ", $categories);
		  }

		  $product_ids = self::new_product_or_store($categories, $description, $store);
		  self::update_product_description($product_ids);
		  self::update_product_description($product_ids, true);
		  self::generate_images($product_ids);
		  self::process_colors($product_ids);
		  self::create_prices($product_ids);

		  wp_die();
	  }
  }

  public static function new_product_or_store($categories, $description, $store = false) {
	  $number = 1;
	  if ($store) {
		  $number = 10;
		  if (get_option('glasses-how-many')) {
			  $number = get_option('glasses-how-many');
		  }
	  }

	  Progress::startProgress($number, 'Creating Product(s)...');

	  $response = Openai::request('Come up with ' . $number . ' product name(s) in the category(s) of ' . $categories . ' with a description of ' . $description . ' and make the response text a comma separated array with an oxford comma and no and:');
	  $response = json_decode($response);
	  $response = $response->choices[0]->text;
	  $response = explode(', ', $response);
	  if (count($response) != $number) {
		  $titleCount = count($response);
		  $howManyLeft = $number - $titleCount;
		  for ($i = 0; $i < $howManyLeft; $i++) {
			  $response[] = 'error';
		  }
	  }

	  $product_ids = [];
	  for ($i = 0; $i < $number; $i++) {
		  progress::updateProgress($i + 1, false);
		  preg_match('/\w.*\w/', $response[$i], $title);
		  $product_ids[] = wp_insert_post([
			  'post_title' => $title[0],
			  'post_type' => 'product'
		  ]);
	  }

	  Progress::completeProgress(count($product_ids));

	  return $product_ids;
  }

  public static function process_colors($product_ids): void {
	Schema::ensure_color_tax();

	Progress::startProgress(count($product_ids), 'Identifying Colors');

    foreach ($product_ids as $current_index => $product_id) {
	  $product = wc_get_product($product_id);
	  progress::updateProgress($current_index, $product);

	  // Declare container for color identification.
      $image_ids = [];

      // Fill containers.
      $image_ids[] = $product->get_image_id();

      // Collect image URLs.
      $image_urls = [];
      if ($image_ids) {
        foreach ($image_ids as $image_id) {
          $image_urls[] = wp_get_attachment_image_url($image_id);
        }
      }

	  $product_variations = new WC_Product_Variable($product_id);
	  $product_variations = $product_variations->get_available_variations();

	  foreach ($product_variations as $product_variation) {
		$image_urls[] = $product_variation['image']['thumb_src'];
	  }

	  $cc = new ColorCube();

      foreach ($image_urls as $image_url) {
        if (empty($image_url)) {
          break;
        }
        $image = imagecreatefromjpeg($image_url);
		if (!$image) {
			$image = imagecreatefrompng($image_url);
		}
        $colors = $cc->get_colors($image);
        if (empty($colors)) {
          break;
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
        if (!has_term($term_name, $taxonomy, $product_id)) {
          wp_set_object_terms($product_id, $term_slug, $taxonomy, true);
        }
      }
    }

	Progress::completeProgress(count($product_ids));

	wp_reset_postdata();
  }

  public static function update_product_description($product_ids, $short = false): void {
	  Progress::startProgress(count($product_ids), 'Updating Product ' . ($short ? 'Short ' : '') . 'Description');

	  foreach ($product_ids as $current_index => $product_id) {
		  $product = wc_get_product($product_id);

		  progress::updateProgress($current_index, $product);

		  $title = $product->get_title();
		  $response = Openai::request('Write a product description for a' . $title . ($short ? ' in 1-2 sentences' : '') . ':');
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

	  Progress::completeProgress(count($product_ids));

	  wp_reset_postdata();
  }

  public static function generate_images($product_ids): void {
	  Progress::startProgress(count($product_ids), 'Generating Images');

	  foreach ($product_ids as $current_index => $product_id) {
		  $product = wc_get_product($product_id);

		  progress::updateProgress($current_index, $product);

		  $title = $product->get_title();
		  $response = Openai::requestGenerateImages('Studio image of a product with the title of: ' . $title);
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

	  Progress::completeProgress(count($product_ids));

	  wp_reset_postdata();
  }

  public static function create_prices($product_ids) {
	  Progress::startProgress(count($product_ids), 'Updating Product Price');

	  foreach ($product_ids as $current_index => $product_id) {
		  $product = wc_get_product($product_id);

		  progress::updateProgress($current_index, $product);

		  $title = $product->get_title();
		  $response = Openai::request('Perfect price for a ' . $title . ', only name one price, if you must name them in an array.');
		  $response = json_decode($response);
		  $response = $response->choices[0]->text;
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

	  Progress::completeProgress(count($product_ids));

	  wp_reset_postdata();
  }

  public static function check_progress() {
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
