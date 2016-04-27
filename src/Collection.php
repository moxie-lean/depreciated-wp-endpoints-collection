<?php namespace Lean\Endpoints;

use Lean\AbstractCollectionEndpoint;
use Lean\Endpoints\Collection;

/**
 * Class that returns a collection of posts using dynamic arguments.
 *
 * @package Lean\Endpoints;
 */
class Collection extends AbstractCollectionEndpoint {

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
	 * WP_Query Loop that has been triggered from the endpoint.
	 *
	 * @return array An array with the data associated with the request.
	 */
	protected function loop() {
		$data = [];
		$meta = [];

		$this->args = apply_filters( Collection\Filter::COLLECTION_ARGS, $this->args );
		$this->query = new \WP_Query( $this->args );
		while ( $this->query->have_posts() ) {
			$this->query->the_post();
			$data[] = $this->format_item( $this->query->post );

			if ( empty( $meta ) ) {
				// Take the metadata from the first post.
				$meta = \Lean\Utils\Meta\Collection::get_all_collection_meta( $this->query->post );
			}
		}

		wp_reset_postdata();

		$response = [
			'data' => $data,
			'meta' => $meta,
			'pagination' => $this->get_pagination(
				$this->query->found_posts,
				$this->query->max_num_pages
			),
		];

		return apply_filters( Collection\Filter::COLLECTION_DATA, $response );
	}

	/**
	 * This function allow to format every item that is returned to the endpoint
	 * the filter sends 3 params to the user so can be more easy to manipulate the
	 * data based on certain params.
	 *
	 * @param object $the_post The post object to format.
	 * @return array The formated data from every item.
	 */
	protected function format_item( $the_post ) {
		$the_author = get_userdata( $the_post->post_author );

		$item = [
			'id' => $the_post->ID,
			'title' => $the_post->post_title,
			'link' => get_permalink( $the_post->ID ),
			'slug' => $the_post->post_name,
			'excerpt' => get_the_excerpt(),
			'author' => [
				'id' => $the_author->ID,
				'first_name' => $the_author->first_name,
				'last_name' => $the_author->last_name,
				'posts_link' => str_replace( home_url(), '', get_author_posts_url( $the_author->ID ) ),
			],
			'date' => strtotime( $the_post->post_date_gmt ) * 1000,
			'thumbnail' => Collection\Post::get_thumbnail( $the_post, $this->args ),
			'terms' => Collection\Post::get_terms( $the_post ),
		];

		return apply_filters( Collection\Filter::ITEM_FORMAT, $item, $the_post, $this->args );
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
