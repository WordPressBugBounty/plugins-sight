<?php
/**
 * Elementor Integration Help.
 *
 * @package Sight
 */

namespace Sight_Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Control Point
 *
 * @since 1.0.0
 */
class Sight_Elementor_Helper {

	/**
	 * Initialize
	 */
	public function __construct() {
		add_action( 'wp_ajax_handler_custom_posts', array( $this, 'handler_custom_posts' ) );
		add_action( 'wp_ajax_nopriv_handler_custom_posts', array( $this, 'handler_custom_posts' ) );
		add_action( 'wp_ajax_handler_post_title', array( $this, 'handler_post_title' ) );
		add_action( 'wp_ajax_nopriv_handler_post_title', array( $this, 'handler_post_title' ) );
	}

	/**
	 * Get custom posts.
	 */
	public function handler_custom_posts() {

		if ( ! check_ajax_referer( 'sight_custom_post', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce.' );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'You do not have permission to perform this action.' );
		}

		$posts = array();

		$more = false;

		$search         = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : '';
		$paged          = isset( $_REQUEST['paged'] ) ? absint( wp_unslash( $_REQUEST['paged'] ) ) : 1;
		$posts_per_page = isset( $_REQUEST['posts_per_page'] ) ? absint( wp_unslash( $_REQUEST['posts_per_page'] ) ) : 10;

		$search_results = new \WP_Query(
			array(
				'post_status'         => 'publish',
				'post_type'           => 'post',
				'ignore_sticky_posts' => 1,
				's'                   => $search,
				'paged'               => $paged,
				'posts_per_page'      => $posts_per_page,
			)
		);

		if ( $search_results->have_posts() ) {
			while ( $search_results->have_posts() ) {
				$search_results->the_post();

				$posts[] = array(
					'id'   => get_the_ID(),
					'text' => get_the_title(),
				);

				$more = true;
			}
		}

		wp_send_json( array(
			'results' => $posts,
			'more'    => $more,
		) );
	}

	/**
	 * Get post title.
	 */
	public function handler_post_title() {
		if ( ! check_ajax_referer( 'sight_custom_post', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce.' );
		}

		$post_id = isset( $_REQUEST['post_id'] ) ? absint( wp_unslash( $_REQUEST['post_id'] ) ) : 0;

		if ( $post_id && get_post_status( $post_id ) ) {
			if ( current_user_can( 'read_post', $post_id ) ) {
				echo esc_html( get_the_title( $post_id ) );
			} else {
				wp_send_json_error( 'You do not have permission to view this post.' );
			}
		} else {
			wp_send_json_error( 'Post not found.' );
		}

		die();
	}
}

new Sight_Elementor_Helper();
