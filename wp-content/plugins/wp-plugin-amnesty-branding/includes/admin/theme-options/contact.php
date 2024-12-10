<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Admin\Theme_Options;

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_register_theme_options_contact' ) ) {
	/**
	 * Register theme options for contact information
	 *
	 * @return void
	 */
	function amnesty_register_theme_options_contact(): void {
		$settings = new_cmb2_box(
			[
				'id'           => 'amnesty_contact_options_page',
				'option_key'   => 'amnesty_contact_options_page',
				/* translators: [admin] */
				'title'        => __( 'Contact', 'aibrand' ),
				'object_types' => [ 'options-page' ],
				'tab_group'    => 'amnesty_theme_options',
				/* translators: [admin] */
				'tab_title'    => __( 'Contact Info', 'aibrand' ),
				'parent_slug'  => 'amnesty_theme_options_page',
				'display_cb'   => 'amnesty_options_display_with_tabs',
			]
		);

		$group = $settings->add_field(
			[
				'id'         => 'amnesty_is_contact',
				/* translators: [admin] */
				'name'       => __( 'IS Office', 'aibrand' ),
				'type'       => 'group',
				'repeatable' => false,
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'      => 'info',
				'type'    => 'message',
				/* translators: [admin] */
				'message' => __( 'Contact info for the International Secretariat office', 'aibrand' ),
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'   => 'name',
				/* translators: [admin] */
				'name' => __( 'Name', 'aibrand' ),
				'type' => 'text',
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'   => 'address',
				/* translators: [admin] */
				'name' => __( 'Address', 'aibrand' ),
				'type' => 'textarea',
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'   => 'phone',
				/* translators: [admin] */
				'name' => __( 'Phone', 'aibrand' ),
				'type' => 'text',
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'   => 'fax',
				/* translators: [admin] */
				'name' => __( 'Fax', 'aibrand' ),
				'type' => 'text',
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'   => 'email',
				/* translators: [admin] */
				'name' => __( 'Email', 'aibrand' ),
				'type' => 'text_email',
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'      => 'contact_page',
				/* translators: [admin] */
				'name'    => __( 'Link to contact page', 'aibrand' ),
				/* translators: [admin] */
				'desc'    => __( 'Please select only one item', 'aibrand' ),
				'type'    => 'custom_attached_posts',
				'options' => [
					'show_thumbnails' => false,
					'filter_boxes'    => true,
					'query_args'      => [
						'post_type' => 'page',
					],
				],
			]
		);
	}
}

add_action( 'amnesty_register_theme_options', __NAMESPACE__ . '\\amnesty_register_theme_options_contact' );
