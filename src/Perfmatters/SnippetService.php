<?php
/**
 * Read and update Perfmatters PMCS snippets.
 *
 * Persistence: updates go directly into Perfmatters snippet files via Snippet::update().
 * No plugin-owned options or tables — safe to deactivate or uninstall.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Perfmatters;

use Perfmatters\PMCS\PMCS;
use Perfmatters\PMCS\Snippet;

/**
 * Thin wrapper around Perfmatters PMCS snippet APIs.
 */
final class SnippetService {

	/**
	 * Whether Perfmatters PMCS APIs are available.
	 */
	public static function is_available(): bool {
		return class_exists( PMCS::class ) && class_exists( Snippet::class );
	}

	/**
	 * All snippets from the Perfmatters config (active + inactive).
	 *
	 * @return list<array<string, mixed>>
	 */
	public static function get_all_snippets(): array {
		if ( ! self::is_available() ) {
			return [];
		}

		$config = PMCS::get_snippet_config();

		if ( empty( $config ) ) {
			return [];
		}

		$snippets = array_merge( $config['active'] ?? [], $config['inactive'] ?? [] );

		return array_values( $snippets );
	}

	/**
	 * Update the location meta for one snippet file.
	 *
	 * @return true|\WP_Error
	 */
	public static function update_location( string $file_name, string $location ) {
		if ( ! self::is_available() ) {
			return new \WP_Error( 'sapfp_unavailable', 'Perfmatters PMCS is not available.' );
		}

		$snippet = Snippet::get( $file_name );

		if ( ! is_array( $snippet ) || ! array_key_exists( 'code', $snippet ) || ! is_array( $snippet['meta'] ?? null ) ) {
			return new \WP_Error( 'sapfp_not_found', sprintf( 'Snippet not found: %s', $file_name ) );
		}

		$type = strtolower( (string) ( $snippet['meta']['type'] ?? 'php' ) );

		if ( ! LocationRegistry::is_valid_for_type( $location, $type ) ) {
			return new \WP_Error(
				'sapfp_invalid_location',
				sprintf(
					'Location "%s" is not valid for %s snippets.',
					LocationRegistry::get_label( $location ),
					strtoupper( $type )
				)
			);
		}

		$meta             = $snippet['meta'];
		$meta['location'] = $location;

		$updated = Snippet::update( $file_name, $snippet['code'], $meta );

		if ( ! $updated ) {
			return new \WP_Error( 'sapfp_update_failed', sprintf( 'Could not update snippet: %s', $file_name ) );
		}

		return true;
	}

	/**
	 * Bulk-update location for multiple snippet files.
	 *
	 * @param list<string> $file_names Snippet file names.
	 * @return array{updated: int, skipped: int, errors: list<string>}
	 */
	public static function bulk_update_location( array $file_names, string $location ): array {
		$result = [
			'updated' => 0,
			'skipped' => 0,
			'errors'  => [],
		];

		foreach ( $file_names as $file_name ) {
			$file_name = sanitize_file_name( (string) $file_name );

			if ( $file_name === '' ) {
				++$result['skipped'];
				continue;
			}

			$update = self::update_location( $file_name, $location );

			if ( is_wp_error( $update ) ) {
				$result['errors'][] = $update->get_error_message();
				++$result['skipped'];
				continue;
			}

			++$result['updated'];
		}

		return $result;
	}

	/**
	 * Human-readable location labels keyed by snippet file name (for list table UI).
	 *
	 * @return array<string, string>
	 */
	public static function get_location_labels_by_file(): array {
		$labels = [];

		foreach ( self::get_all_snippets() as $snippet ) {
			$file_name = (string) ( $snippet['file_name'] ?? '' );
			if ( $file_name === '' ) {
				continue;
			}

			$labels[ $file_name ] = LocationRegistry::get_display_label(
				(string) ( $snippet['location'] ?? '' ),
				(string) ( $snippet['type'] ?? 'php' )
			);
		}

		return $labels;
	}

	/**
	 * Duplicate one snippet file (inactive copy with copied code and meta).
	 *
	 * @return string|\WP_Error New snippet file name on success.
	 */
	public static function duplicate( string $source_file_name ) {
		if ( ! self::is_available() ) {
			return new \WP_Error( 'sapfp_unavailable', 'Perfmatters PMCS is not available.' );
		}

		$source_file_name = PMCS::normalize_snippet_file_name( $source_file_name );

		if ( $source_file_name === '' ) {
			return new \WP_Error( 'sapfp_invalid_source', __( 'Invalid snippet file name.', 'snippet-admin-pro-for-perfmatters' ) );
		}

		$snippet = Snippet::get( $source_file_name );

		if ( ! is_array( $snippet ) || ! array_key_exists( 'code', $snippet ) || ! is_array( $snippet['meta'] ?? null ) ) {
			return new \WP_Error( 'sapfp_not_found', sprintf( 'Snippet not found: %s', $source_file_name ) );
		}

		$meta = $snippet['meta'];
		unset( $meta['file_name'] );

		$meta['name']    = SnippetDuplicate::display_name_for_copy( (string) ( $meta['name'] ?? '' ) );
		$meta['active']  = '';
		$meta['created'] = '';

		$new_file_name = self::generate_unique_file_name( $meta['name'] );
		$updated       = Snippet::update( $new_file_name, $snippet['code'], $meta );

		if ( ! $updated ) {
			return new \WP_Error( 'sapfp_duplicate_failed', sprintf( 'Could not duplicate snippet: %s', $source_file_name ) );
		}

		return $new_file_name;
	}

	/**
	 * Duplicate multiple snippet files.
	 *
	 * @param list<string> $file_names Snippet file names.
	 * @return array{duplicated: int, skipped: int, errors: list<string>, new_files: list<string>}
	 */
	public static function bulk_duplicate( array $file_names ): array {
		$result = [
			'duplicated' => 0,
			'skipped'    => 0,
			'errors'     => [],
			'new_files'  => [],
		];

		foreach ( $file_names as $file_name ) {
			$file_name = sanitize_file_name( (string) $file_name );

			if ( $file_name === '' ) {
				++$result['skipped'];
				continue;
			}

			$duplicate = self::duplicate( $file_name );

			if ( is_wp_error( $duplicate ) ) {
				$result['errors'][] = $duplicate->get_error_message();
				++$result['skipped'];
				continue;
			}

			$result['new_files'][] = (string) $duplicate;
			++$result['duplicated'];
		}

		return $result;
	}

	/**
	 * Generate a unique snippet file name in Perfmatters storage.
	 */
	public static function generate_unique_file_name( string $display_name ): string {
		$storage_dir = PMCS::get_storage_dir();
		$file_count  = count( glob( $storage_dir . '/*.php' ) ?: [] );

		if ( $file_count === 0 ) {
			PMCS::build_snippet_config();
			$file_count = count( glob( $storage_dir . '/*.php' ) ?: [] );
		}

		if ( $file_count === 0 ) {
			$file_count = 1;
		}

		$file_title = SnippetDuplicate::file_title_from_display_name( $display_name );

		for ( $offset = 0; $offset < 100; ++$offset ) {
			$candidate = sanitize_file_name( ( $file_count + $offset ) . '-' . $file_title . '.php' );

			if ( $candidate !== '' && ! is_file( $storage_dir . '/' . $candidate ) ) {
				return $candidate;
			}
		}

		return sanitize_file_name( uniqid( (string) $file_count . '-', false ) . '.php' );
	}
}
