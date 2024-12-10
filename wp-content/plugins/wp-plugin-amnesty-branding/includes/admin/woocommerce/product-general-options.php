<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Admin\WooCommerce;

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_wc_add_donation_field' ) ) {
	/**
	 * Add checkbox to product edit to declare product as donation
	 *
	 * @package Amnesty\Plugins\WooCommerce
	 *
	 * @return void
	 */
	function amnesty_wc_add_donation_field() {
		echo '<div class="options-group show_if_variable">';
		woocommerce_wp_checkbox(
			[
				'id'      => 'amnesty_wc_product_type',
				'name'    => 'amnesty_wc_product_type',
				/* translators: [admin] */
				'label'   => __( 'Is Donation', 'amnesty' ),
				'cbvalue' => 'donation',
			]
		);
		echo '</div>';
	}
}

add_action( 'woocommerce_product_options_general_product_data', __NAMESPACE__ . '\\amnesty_wc_add_donation_field' );
