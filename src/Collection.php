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

	public function endpoint_callback( \WP_REST_Request $request ) {
		$args = wp_parse_args( $request->get_query_params(), $request->get_default_params() );
		$custom_query = new \WP_Query( $args );
		while ( $custom_query->have_posts() ) {
			$custom_query->the_post();
			$data[] = $custom_query->post;
		}
		wp_reset_postdata();
		return $this->filter_data( $data );
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
		];
	}
}
