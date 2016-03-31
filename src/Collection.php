<?php namespace Lean\Endpoints;

use Leean\AbstractEndpoint;

use Lean\Endpoints\Collection\Sanitize;
use Lean\Endpoints\Collection\Filter;

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
	 *
	 * @since 0.1.0
	 * @var String
	 */
	protected $endpoint = '/collection';

	/**
	 * Array that holds all the shared arguments used for query the site.
	 *
	 * @since 0.1.0
	 * @var array
	 */
	protected $args = [];

	/**
	 * Object that holds the current queried object on the site.
	 *
	 * @since 0.1.0
	 * @var \WP_Query
	 */
	protected $query = null;

	/**
	 * Flag used to carry the value of the filter and avoid to call the function
	 * N times inside of the loop.
	 *
	 * @since 0.1.0
	 * @var bool
	 */
	protected $format_item = false;

	/**
	 * Function inherint from the parant Abstract class that is called once the
	 * endpoint has been initiated and the method that returns the data delivered
	 * to the endpoint.
	 *
	 * @Override
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_REST_Request $request The request object that mimics the request
	 *									made by the user.
	 * @return array The data to be delivered to the endpoint
	 */
	public function endpoint_callback( \WP_REST_Request $request ) {
		$this->args = $request->get_params();
		return $this->filter_data( $this->loop() );
	}

	/**
	 * WP_Query Loop that has been triggered from the endpoint.
	 *
	 * @return array An array with the data associated with the request.
	 */
	protected function loop() {
		$data = [];

		$this->query = new \WP_Query( $this->args );
		$this->format_item = apply_filters( Filter::ITEM_IS_ENABLED, false );

		while ( $this->query->have_posts() ) {
			$this->query->the_post();
			$data[] = $this->format_item( $this->query->post );
		}
		wp_reset_postdata();

		return [
			'data' => $data,
			'pagination' => $this->get_pagination(),
		];
	}

	/**
	 * This function allow to format every item that is returned to the endpoint
	 * the filter sends 3 params to the user so can be more easy to manipulate the
	 * data based on certaim params.
	 *
	 * @param \WP_Post $the_post Current post object.
	 * @return array The formated data from every item.
	 */
	protected function format_item( \WP_Post $the_post ) {
		$item = [ 'id' => $the_post->ID ];
		if ( $this->format_item ) {
			$item = apply_filters( Filter::ITEM_FORMAT, $item, $the_post, $this->args );
		}
		return $item;
	}

	/**
	 * Returns the data related with the pagination, useful to
	 * iterate over the data in the FE on a infinite scroll or load more
	 * buttons since we know if there are more pages ahead.
	 *
	 * @return array The array with the formated data.
	 */
	protected function get_pagination() {
		$total = absint( $this->query->found_posts );
		$meta = [
			'items' => $total,
			'pages' => 0,
		];
		if ( $total > 0 ) {
			$meta['pages'] = $this->query->max_num_pages;
		}
		return $meta;
	}

	/**
	 * Clean up and make sure we don't deliver post with password, privates and
	 * some other datat that might be sensible on the API. This medhod overrides
	 * the default mechanism inherint from the parent class.
	 *
	 * @Override
	 *
	 * @since 0.1.0
	 * @return array an array with the accepted arguments and options per each argument.
	 */
	public function endpoint_args() {
		return [
			'post_type' => [
				'default' => 'post',
				'sanitize_callback' => function( $post_type ) {
					if ( is_array( $post_type ) ) {
						return array_map( 'sanitize_text_field', $post_type );
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
			'has_password' => [ 'validate_callback' => '__return_false' ],
			'post_password' => [ 'validate_callback' => '__return_false' ],
		];
	}
}
