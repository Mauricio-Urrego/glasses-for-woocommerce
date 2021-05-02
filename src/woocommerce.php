<?php

namespace Ellipsis\Colors;

class WooCommerce {

  public static function init() {
    // Get product object.
    $product = wc_get_product();

    // Declare containers.
    $attachment_ids = [];
    $image_ids = [];

    // Fill containers.
    $attachment_ids[] = $product->get_gallery_image_ids();
    $image_ids[] = $product->get_image_id();

    // Collect image URLs.
    $image_urls = [];
    if ( $image_ids ) {
      foreach ($image_ids as $image_id) {
        $image_urls[] = wp_get_attachment_image_url( $image_id, 'full' );
      }
    }

    foreach ( $attachment_ids as $attachment_id ) {
      $image_urls[] = wp_get_attachment_url( $attachment_id );
    }

    $cc = new ColorCube();

    foreach ( $image_urls as $image_url ) {
      if (empty($image_url)) {
        return;
      }
      $image = imagecreatefromjpeg($image_url);
      $colors = $cc->get_colors($image);
      if (empty($colors)) {
        echo '<div style="background-color: rgb(255,255,255); height:50px; width:100%; text-align:center; overflow:hidden;">Color not found :(</div>';
        return;
      }
      preg_match('/\[(.*)\]/', json_encode($colors[0]), $matches);
      echo '<div style="background-color: rgb(' . $matches[1] . '); height:50px; width: 100%;"></div>';
    }

  }
}
