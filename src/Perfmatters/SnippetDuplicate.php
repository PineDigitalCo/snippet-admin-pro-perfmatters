<?php
/**
 * Snippet duplication helpers (display names and file title slugs).
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Perfmatters;

/**
 * Pure helpers for duplicate snippet naming.
 */
final class SnippetDuplicate {

	/**
	 * Build a duplicate snippet display name from the source name.
	 */
	public static function display_name_for_copy( string $source_name ): string {
		$source_name = trim( $source_name );

		if ( $source_name === '' ) {
			return __( 'Snippet Copy', 'snippet-admin-pro-for-perfmatters' );
		}

		if ( preg_match( '/^(.+)\sCopy(?:\s(\d+))?$/', $source_name, $matches ) === 1 ) {
			$base = $matches[1];
			$num  = isset( $matches[2] ) ? ( (int) $matches[2] ) + 1 : 2;

			return $base . ' Copy ' . $num;
		}

		return $source_name . ' Copy';
	}

	/**
	 * Convert a display name into a Perfmatters-style file title slug (max four words).
	 */
	public static function file_title_from_display_name( string $display_name ): string {
		$file_title = trim( $display_name );

		if ( $file_title === '' ) {
			$file_title = 'snippet';
		}

		$name_words = preg_split( '/\s+/', $file_title ) ?: [];

		if ( count( $name_words ) > 4 ) {
			$name_words = array_slice( $name_words, 0, 4 );
			$file_title = implode( ' ', $name_words );
		}

		return sanitize_title( $file_title, 'snippet' );
	}
}
