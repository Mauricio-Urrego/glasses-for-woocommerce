# woocommerce-colors

Drop-in primary color identification of WooCommerce products. This is thanks to the wonderful [algorithm](https://github.com/pixelogik/ColorCube) by Ole Krause-Sparman and my [port](https://github.com/Mauricio-Urrego/colorcube-php) of this algorithm from the original Python to the translated PHP.

## Installation
Simply place into wordpress plugins folder and activate.

## Note
As of right now, version 0.0.1, the functionality is minimal. It requires a settings page and most importantly it requires a save of the results to the product object or to the database (to avoid recalculating on every product page load). This is only a proof of concept that will add the identified color below the product on product loops. The next version will be a lot more customizable.
