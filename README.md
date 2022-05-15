# woo-colors-for-woocommerce

Drop-in primary color identification of WooCommerce products. This is thanks to the wonderful [algorithm](https://github.com/pixelogik/ColorCube) by Ole Krause-Sparman and my [port](https://github.com/Mauricio-Urrego/colorcube-php) of this algorithm from the original Python to the translated PHP.

## Installation
Simply clone into wordpress plugins folder, run ```composer install``` inside newly created woo-colors-for-woocommerce folder and then activate the plugin.

## Note
As of right now, version 0.0.3, the functionality is minimal. It only includes one action that will loop through all (currently only published) products and assign them a color auto-magically. Please be aware that there is no progress bar as of right now, please only click the action once and be patient, an alert will come up when complete. If you see an error in this alert please let the author know. I am not responsible for any lost data, please backup your database before testing out software that is still in development.

## Who is this plugin for?
Have a large product list in need of color attribute entry? Already have the product images uploaded? Let the plugin do the leg work for you.
