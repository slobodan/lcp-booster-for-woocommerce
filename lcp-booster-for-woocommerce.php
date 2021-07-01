<?php

/**
 * Plugin Name:       LCP Booster for WooCommerce
 * Plugin URI:        https://wordpress.org.com/plugins/lcp-booster-for-woocommerce/
 * Description:       Preloads main product image in the WooCommerce product page, helping lower LCP (Largest Contentful Paint) time. Responsive image preload for browsers that support it.
 * Version:           1.0.0
 * Requires at least: 5.5
 * Requires PHP:      7.0
 * Author:            Slobodan Manic
 * Author URI:        https://www.nohacksmarketing.com/
 * License:           GPL v3
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

function lcp_wc_activate() {
	// Do this once right after activation.
	if ( is_admin() ) {
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			add_action( 'admin_notices', 'lcp_wc_self_deactivate_notice' );

			// Deactivate our plugin.
			deactivate_plugins( plugin_basename( __FILE__ ) );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}
add_action( 'admin_init', 'lcp_wc_activate' );

/**
 * Display an error message when parent plugin is missing
 */
function lcp_wc_self_deactivate_notice() {
	?>
	<div class="notice notice-error">
		<p>LCP Booster for WooCommerce could not be activated. Please install and activate WooCommerce plugin before activating this plugin.</p>
	</div>
	<?php
}

/**
 * Preload main product image.
 */
function lcp_wc_preload_product_image() {
	if ( is_product() ) {
		// Global $product not available here, need to grab post object ID.
		$product_id = get_the_ID();
		$product = wc_get_product( $product_id );
		$post_thumbnail_id = $product->get_image_id();

		if ( $post_thumbnail_id ) {
			$image_size = 'woocommerce_single';
			$image      = wp_get_attachment_image_src( $post_thumbnail_id, $image_size );
			$image_meta = wp_get_attachment_metadata( $post_thumbnail_id );
			$size_array = array( absint( $image[1] ), absint( $image[2] ) );
			
			$image_srcset = wp_calculate_image_srcset( $size_array, $image[0], $image_meta, $post_thumbnail_id );
			$image_sizes  = wp_calculate_image_sizes( $size_array, $image[0], $image_meta, $post_thumbnail_id );
			?>
				<link rel="preload" as="image" href="<?php echo $image[0]; ?>" imagesrcset="<?php echo $image_srcset; ?>" imagesizes="<?php echo $image_sizes; ?>">
			<?php
		}
	}
}
add_action( 'wp_head', 'lcp_wc_preload_product_image', 0 );
