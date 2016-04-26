<?php namespace Lean\Endpoints\Collection;

/**
 * Get parts of posts.
 *
 * @package Endpoints\Collection;
 * @since 0.1.0
 */
class Post {
	/**
	 * Get the thumbnail
	 *
	 * @param \WP_Post $the_post The post.
	 * @param array    $args	 The query args.
	 * @return array
	 */
	public static function get_thumbnail( $the_post, $args ) {
		$thumbnail = [
			'src' 		=> '',
			'width'		=> '',
			'height'	=> '',
			'alt'		=> '',
		];

		if ( has_post_thumbnail( $the_post->ID ) ) {
			$size = apply_filters( Filter::THUMBNAIL_SIZE, 'thumbnail', $the_post, $args );

			$attachment_id = get_post_thumbnail_id( $the_post->ID );

			$src = wp_get_attachment_image_src( $attachment_id, $size );

			$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

			$thumbnail = [
				'src' 		=> $src[0],
				'width'		=> $src[1],
				'height'	=> $src[2],
				'alt'		=> $alt ? $alt : $the_post->post_title,
			];
		}

		return $thumbnail;
	}

	/**
	 * Get all taxonomies and terms for this post.
	 *
	 * @param \WP_Post $the_post The post.
	 * @return array
	 */
	public static function get_terms( $the_post ) {
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

		return $post_terms;
	}
}
