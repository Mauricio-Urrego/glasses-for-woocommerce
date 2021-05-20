# woocommerce-colors

Drop-in primary color identification of WooCommerce products. This is thanks to the wonderful [algorithm](https://github.com/pixelogik/ColorCube) by Ole Krause-Sparman and my [port](https://github.com/Mauricio-Urrego/colorcube-php) of this algorithm from the original Python to the translated PHP.

## Installation
Simply place into wordpress plugins folder, run ```git submodule update --init --force --remote``` inside plugin folder and then activate the plugin.

## Note
As of right now, version 0.0.1, the functionality is minimal. It requires a settings page and most importantly it requires a save of the results to the product object or to the database (to avoid recalculating on every product page load). This is only a proof of concept that will add the identified color below the product on product loops. The next version will be a lot more customizable.

## Update May 20, 2021
There is a pull request open that successfully adds the identified dominant color of your products to the color attribute terms. This is then assigned to your product avoiding manual entry. Have a large product list in need of color attribute entry? Already have the product images uploaded? Let the plugin do the leg work for you.
