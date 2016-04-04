<?php namespace Lean\Endpoints;

use Leean\AbstractEndpoint;
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
	 * data based on certain params.
	 *
	 * @return array The formated data from every item.
	 */
	protected function format_item() {
		$the_post = get_post();

		// Author.
		$the_author = get_userdata( $the_post->post_author );

		// Thumbnail.
		if ( has_post_thumbnail( $the_post->ID ) ) {
			$size = apply_filters( Filter::THUMBNAIL_SIZE, 'thumbnail', $the_post );

			$attachment_id = get_post_thumbnail_id( $the_post->ID );

			$src = wp_get_attachment_image_src( $attachment_id, $size );

			$thumbnail = [
				'src' 		=> $src[0],
				'width'		=> $src[1],
				'height'	=> $src[2],
				'alt'		=> get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			];
		} else {
			$thumbnail = false;
		}

		// Taxonomies.
		$taxonomies = get_object_taxonomies( $the_post );

		$post_terms = [];

		foreach ( $taxonomies as $taxonomy ) {
			$post_terms[ $taxonomy ] = [];

			$terms = wp_get_post_terms( $the_post->ID, $taxonomy );

			foreach ( $terms as $term ) {
				$post_terms[ $taxonomy ][] = [
					'id' => $term->term_id,
					'name' => $term->name,
					'slug' => $term->slug,
					'description' => $term->description,
				];
			}
		}

		// Put it all together.
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
				'posts_link' => str_replace( site_url(), '', get_author_posts_url( $the_author->ID ) ),
			],
			'date' => $the_post->post_date_gmt,
			'thumbnail' => $thumbnail,
			'terms' => $post_terms,
		];

		return apply_filters( Filter::ITEM_FORMAT, $item, $the_post, $this->args );
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
