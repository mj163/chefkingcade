<?php
/**
 * Genesis Framework.
 *
 * WARNING: This file is part of the core Genesis Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Genesis\Admin
 * @author  StudioPress
 * @license GPL-2.0+
 * @link    https://my.studiopress.com/themes/genesis/
 */

add_action( 'admin_menu', 'genesis_add_inpost_seo_box' );
/**
 * Register a new meta box to the post or page edit screen, so that the user can set SEO options on a per-post or
 * per-page basis.
 *
 * If the post type does not support genesis-seo, then the SEO meta box will not be added.
 *
 * @since 1.0.0
 *
 * @see genesis_inpost_seo_box() Generates the content in the meta box.
 */
function genesis_add_inpost_seo_box() {

	foreach ( (array) get_post_types(
		array(
			'public' => true,
		)
	) as $type ) {
		if ( post_type_supports( $type, 'genesis-seo' ) ) {
			add_meta_box( 'genesis_inpost_seo_box', __( 'Theme SEO Settings', 'genesis' ), 'genesis_inpost_seo_box', $type, 'normal', 'high' );
		}
	}

	add_action( 'load-post.php', 'genesis_seo_contextual_help' );
	add_action( 'load-post-new.php', 'genesis_seo_contextual_help' );

}

/**
 * Callback for in-post SEO meta box.
 *
 * @since 1.0.0
 */
function genesis_inpost_seo_box() {

	genesis_meta_boxes()->show_meta_box( 'genesis-inpost-seo-box' );

}

/**
 * Callback for in-post SEO meta box contextual help.
 *
 * @since 2.4.0
 */
function genesis_seo_contextual_help() {

	global $typenow;

	if ( post_type_supports( $typenow, 'genesis-seo' ) ) {
		genesis_meta_boxes()->add_help_tab( 'genesis-inpost-seo', __( 'Theme SEO Settings', 'genesis' ) );
	}

}

add_action( 'save_post', 'genesis_inpost_seo_save', 1, 2 );
/**
 * Save the SEO settings when we save a post or page.
 *
 * Some values get sanitized, the rest are pulled from identically named sub-keys in the $_POST['genesis_seo'] array.
 *
 * @since 1.0.0
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void Return early if `genesis_seo` is not a key in `POST` data.
 */
function genesis_inpost_seo_save( $post_id, $post ) {

	if ( ! isset( $_POST['genesis_seo'] ) ) {
		return;
	}

	// Merge user submitted options with fallback defaults.
	$data = wp_parse_args(
		$_POST['genesis_seo'],
		array(
			'_genesis_title'         => '',
			'_genesis_description'   => '',
			'_genesis_keywords'      => '',
			'_genesis_canonical_uri' => '',
			'redirect'               => '',
			'_genesis_noindex'       => 0,
			'_genesis_nofollow'      => 0,
			'_genesis_noarchive'     => 0,
		)
	);

	// Sanitize the title, description, and tags.
	foreach ( (array) $data as $key => $value ) {
		if ( in_array( $key, array( '_genesis_title', '_genesis_description', '_genesis_keywords' ) ) ) {
			$data[ $key ] = strip_tags( $value );
		}
	}

	genesis_save_custom_fields( $data, 'genesis_inpost_seo_save', 'genesis_inpost_seo_nonce', $post );

}

add_action( 'admin_menu', 'genesis_add_inpost_scripts_box' );
/**
 * Register a new meta box to the post or page edit screen, so that the user can apply scripts on a per-post or
 * per-page basis.
 *
 * The scripts field was previously part of the SEO meta box, and was therefore hidden when an SEO plugin was active.
 *
 * @since 2.0.0
 *
 * @see genesis_inpost_scripts_box() Generates the content in the meta box.
 */
function genesis_add_inpost_scripts_box() {

	// If user doesn't have unfiltered html capability, don't show this box.
	if ( ! current_user_can( 'unfiltered_html' ) ) {
		return;
	}

	foreach ( (array) get_post_types(
		array(
			'public' => true,
		)
	) as $type ) {
		if ( post_type_supports( $type, 'genesis-scripts' ) ) {
			add_meta_box( 'genesis_inpost_scripts_box', __( 'Scripts', 'genesis' ), 'genesis_inpost_scripts_box', $type, 'normal', 'low' );
		}
	}

}

/**
 * Callback for in-post Scripts meta box.
 *
 * @since 2.0.0
 */
function genesis_inpost_scripts_box() {

	genesis_meta_boxes()->show_meta_box( 'genesis-inpost-scripts-box' );

}

add_action( 'save_post', 'genesis_inpost_scripts_save', 1, 2 );
/**
 * Save the Scripts settings when we save a post or page.
 *
 * @since 2.0.0
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void Return early if `genesis_seo` is not a key in `POST` data, or current user doesn't
 *              have `unfiltered_html` capability.
 */
function genesis_inpost_scripts_save( $post_id, $post ) {

	if ( ! isset( $_POST['genesis_seo'] ) ) {
		return;
	}

	// If user doesn't have unfiltered html capability, don't try to save.
	if ( ! current_user_can( 'unfiltered_html' ) ) {
		return;
	}

	// Merge user submitted options with fallback defaults.
	$data = wp_parse_args(
		$_POST['genesis_seo'],
		array(
			'_genesis_scripts'               => '',
			'_genesis_scripts_body'          => '',
			'_genesis_scripts_body_position' => '',
		)
	);

	genesis_save_custom_fields( $data, 'genesis_inpost_scripts_save', 'genesis_inpost_scripts_nonce', $post );

}

add_action( 'admin_menu', 'genesis_add_inpost_layout_box' );
/**
 * Register a new meta box to the post or page edit screen, so that the user can set layout options on a per-post or
 * per-page basis.
 *
 * @since 1.0.0
 *
 * @see genesis_inpost_layout_box() Generates the content in the boxes
 *
 * @return void Return early if Genesis layouts are not supported.
 */
function genesis_add_inpost_layout_box() {

	if ( ! current_theme_supports( 'genesis-inpost-layouts' ) ) {
		return;
	}

	foreach ( (array) get_post_types(
		array(
			'public' => true,
		)
	) as $type ) {
		if ( post_type_supports( $type, 'genesis-layouts' ) ) {
			add_meta_box( 'genesis_inpost_layout_box', __( 'Layout Settings', 'genesis' ), 'genesis_inpost_layout_box', $type, 'normal', 'high' );
		}
	}

}

/**
 * Callback for in-post layout meta box.
 *
 * @since 1.0.0
 */
function genesis_inpost_layout_box() {

	genesis_meta_boxes()->show_meta_box( 'genesis-inpost-layout-box' );

}

add_action( 'save_post', 'genesis_inpost_layout_save', 1, 2 );
/**
 * Save the layout options when we save a post or page.
 *
 * Since there's no sanitizing of data, the values are pulled from identically named keys in $_POST.
 *
 * @since 1.0.0
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void Return early if `genesis_layout` is not a key in `POST` data.
 */
function genesis_inpost_layout_save( $post_id, $post ) {

	if ( ! isset( $_POST['genesis_layout'] ) ) {
		return;
	}

	$data = wp_parse_args(
		$_POST['genesis_layout'],
		array(
			'_genesis_layout'            => '',
			'_genesis_custom_body_class' => '',
			'_genesis_post_class'        => '',
		)
	);

	$data = array_map( 'genesis_sanitize_html_classes', $data );

	genesis_save_custom_fields( $data, 'genesis_inpost_layout_save', 'genesis_inpost_layout_nonce', $post );

}

add_action( 'admin_menu', 'genesis_add_inpost_adsense_box' );
/**
 * Register a new meta box to the post or page edit screen, so that the user can disable adsense output.
 *
 * @since 2.6.0
 *
 * @see genesis_inpost_adsense_box() Generates the content in the boxes
 *
 * @return void Return early if adsense disabled.
 */
function genesis_add_inpost_adsense_box() {

	if ( ! genesis_get_option( 'adsense_id' ) ) {
		return;
	}

	foreach ( (array) get_post_types(
		array(
			'public' => true,
		)
	) as $type ) {
		add_meta_box( 'genesis_inpost_adsense_box', __( 'Google Adsense', 'genesis' ), 'genesis_inpost_adsense_box', $type, 'normal', 'high' );
	}

}

/**
 * Callback for in-post adsense meta box.
 *
 * @since 2.6.0
 */
function genesis_inpost_adsense_box() {

	genesis_meta_boxes()->show_meta_box( 'genesis-inpost-adsense-box' );

}

add_action( 'save_post', 'genesis_inpost_adsense_save', 1, 2 );
/**
 * Save the adsense option when we save a post or page.
 *
 * Since there's no sanitizing of data, the values are pulled from identically named keys in $_POST.
 *
 * @since 2.6.0
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void Return early if `genesis_adsense` is not a key in `POST` data.
 */
function genesis_inpost_adsense_save( $post_id, $post ) {

	if ( ! isset( $_POST['genesis_adsense'] ) ) {
		return;
	}

	$data = wp_parse_args(
		$_POST['genesis_adsense'],
		array(
			'_disable_adsense' => 0,
		)
	);

	$data['_disable_adsense'] = Genesis_Sanitizer::one_zero( $data['_disable_adsense'] );

	// unset the key
	unset( $data['key'] );

	genesis_save_custom_fields( $data, 'genesis_inpost_adsense_save', 'genesis_inpost_adsense_nonce', $post );

}