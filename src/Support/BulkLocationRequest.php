<?php
/**
 * Decode and encode bulk location form values.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Support;

/**
 * Helpers for bulk location POST payloads.
 */
final class BulkLocationRequest {

	/**
	 * Encode stored location for HTML option value (empty string = everywhere).
	 */
	public static function location_option_value( string $location ): string {
		return $location === '' ? '__everywhere__' : $location;
	}

	/**
	 * Decode submitted location option value.
	 */
	public static function normalize_submitted_location( string $value ): string {
		if ( $value === '__everywhere__' ) {
			return '';
		}

		return sanitize_key( $value );
	}

	/**
	 * Sanitize snippet file names from a request array.
	 *
	 * @param mixed $raw Raw request value.
	 * @return list<string>
	 */
	public static function sanitize_snippet_files( $raw ): array {
		if ( ! is_array( $raw ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map( 'sanitize_file_name', array_map( 'strval', $raw ) )
			)
		);
	}
}
