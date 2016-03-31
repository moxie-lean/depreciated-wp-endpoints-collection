<?php namespace Lean\Endpoints;

use Leean\AbstractEndpoint;
use Lean\Endpoints\Sanitize;

/**
 * Class that returns a collection of posts using dynamic arguments.
 *
 * @package Lean\Endpoints;
 */
class Collection extends AbstractEndpoint {

	/**
	 * Path of the new endpoint.
	 *
	 * @Override
	 * @var String
	 */
	protected $endpoint = '/collection';
	protected $args = [];

	public function endpoint_callback( \WP_REST_Request $request ) {
		$this->args = $request->get_params();
		return $this->filter_data( $this->loop() );
	}

	protected function loop() {
		$data = [];
		$custom_query = new \WP_Query( $this->args );
		while ( $custom_query->have_posts() ) {
			$custom_query->the_post();
			$data[] = [
				'id' => $custom_query->post->ID,
				'title' => $custom_query->post->post_title,
			];
		}
		wp_reset_postdata();
		return $data;
	}

	public function endpoint_args() {
		return [
			'post_type' => [
				'default' => 'post',
				'sanitize_callback' => function( $post_type ) {
					if ( is_array( $post_type ) ) {
						return Sanitize::text_array( $post_type );
					} else {
						return sanitize_text_field( $post_type );
					}
				},
			],
			'post_status' => [
				'default' => 'publish',
				'validate_callback' => function( $post_status ) {
					return 'publish' === $post_status;
				},
			],
			'has_password' => [
				'validate_callback' => '__return_false',
			],
			'post_password' => [
				'validate_callback' => '__return_false',
			],
		];
	}
}
