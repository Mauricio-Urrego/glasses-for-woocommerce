<?php

/**
 * @file
 * Contains \Mauriciourrego\GlassesForWooCommerce\WooCommerce.
 */

namespace Mauriciourrego\GlassesForWooCommerce;

use Mauriciourrego\ColorcubePhp\ColorCube;
use WP_Query;
use WC_Product_Attribute;
use Phim\Color;
use Phim\Color\RgbColor;

class WooCommerce {

  public static function init() {
    add_action('wp_ajax_process_colors', __CLASS__ . '::process_colors');
  }

  public static function process_colors() {
    Schema::ensure_color_tax();
    // loop through all products. TODO: Make customizable (drafts, etc.)
    $args = [
      'post_type' => 'product',
      'post_status' => 'publish'
    ];
    $loop = new WP_Query($args);

    while ($loop->have_posts()) {
      $loop->the_post();
      $product_id = get_the_id();

      $product = wc_get_product($product_id);

      // Declare containers for color identification.
      $attachment_ids = [];
      $image_ids = [];

      // Fill containers.
      $attachment_ids[] = $product->get_gallery_image_ids();
      $image_ids[] = $product->get_image_id();

      // Collect image URLs.
      $image_urls = [];
      if ($image_ids) {
        foreach ($image_ids as $image_id) {
          $image_urls[] = wp_get_original_image_path($image_id);
        }
      }

      foreach ($attachment_ids as $attachment_id) {
        $image_urls[] = wp_get_attachment_url($attachment_id);
      }

      $cc = new ColorCube();

      foreach ($image_urls as $image_url) {
        if (empty($image_url)) {
          break;
        }
        $image = imagecreatefromjpeg($image_url);
        $colors = $cc->get_colors($image);
        if (empty($colors)) {
          break;
        }
        $rgbColor = new RgbColor($colors[0][0], $colors[0][1], $colors[0][3]);

        // Color is identified so assign color to product attribute term.
        $taxonomy = 'pa_glasses_color';
        $term_name = Color::toName($rgbColor);
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

    // @see: https://codex.wordpress.org/AJAX_in_Plugins
    wp_die();
  }
}
