<?php
/**
 * Contravenção — bootstrap do tema.
 */

defined( 'ABSPATH' ) || exit;

define( 'CV_VERSION', '0.1.0' );
define( 'CV_DIR', get_template_directory() );
define( 'CV_URI', get_template_directory_uri() );

require CV_DIR . '/inc/helpers.php';
require CV_DIR . '/inc/setup.php';
require CV_DIR . '/inc/enqueue.php';
require CV_DIR . '/inc/menus.php';
require CV_DIR . '/inc/acf.php';

if ( class_exists( 'WooCommerce' ) ) {
	require CV_DIR . '/inc/woocommerce.php';
	require CV_DIR . '/inc/wishlist.php';
}
