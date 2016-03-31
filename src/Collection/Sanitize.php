<?php namespace Lean\Endpoints\Collection;

/**
 * Class that provides some helper functions to Sanitize elements.
 *
 * @package Lean\Endpoints
 */
class Sanitize {

	/**
	 * Function that iterates over an array to Sanitize the value of the array
	 * in secure text values.
	 *
	 * @param array $data The data to be Sanitized.
	 * @return array The cleaned data.
	 */
	public static function text_array( array $data = [] ) {
		$map = function( $item ) {
			return sanitize_text_field( $item );
		};
		return array_map( $map, $data );
	}
}
