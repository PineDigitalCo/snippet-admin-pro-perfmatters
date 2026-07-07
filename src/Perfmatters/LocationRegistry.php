<?php
/**
 * Perfmatters snippet location definitions.
 *
 * Mirrors option values and data-code-type rules from Perfmatters inc/pmcs.php.
 * When Perfmatters adds locations, update this catalog to match.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Perfmatters;

/**
 * Maps Perfmatters PMCS location values to labels and snippet types.
 */
final class LocationRegistry {

	/**
	 * Catalog entries in Perfmatters editor order.
	 *
	 * @var list<array{value: string, label: string, code_types: list<string>}>
	 */
	private const CATALOG = [
		[
			'value'      => '',
			'label'      => 'Everywhere',
			'code_types' => [ 'php' ],
		],
		[
			'value'      => 'frontend',
			'label'      => 'Frontend Only',
			'code_types' => [ 'php' ],
		],
		[
			'value'      => 'admin',
			'label'      => 'Admin Only',
			'code_types' => [ 'php' ],
		],
		[
			'value'      => 'wp_head',
			'label'      => 'Frontend Header',
			'code_types' => [ 'js', 'css', 'html' ],
		],
		[
			'value'      => 'wp_footer',
			'label'      => 'Frontend Footer',
			'code_types' => [ 'js', 'css', 'html' ],
		],
		[
			'value'      => 'admin_head',
			'label'      => 'Admin Header',
			'code_types' => [ 'js', 'css' ],
		],
		[
			'value'      => 'admin_footer',
			'label'      => 'Admin Footer',
			'code_types' => [ 'js', 'css' ],
		],
		[
			'value'      => 'wp_body_open',
			'label'      => 'Frontend Body',
			'code_types' => [ 'html' ],
		],
		[
			'value'      => 'before_content',
			'label'      => 'Before Content',
			'code_types' => [ 'html' ],
		],
		[
			'value'      => 'after_content',
			'label'      => 'After Content',
			'code_types' => [ 'html' ],
		],
		[
			'value'      => 'shortcode',
			'label'      => 'Shortcode',
			'code_types' => [ 'html' ],
		],
	];

	/**
	 * Full catalog for JS (Perfmatters pmcs-location-options parity).
	 *
	 * @return list<array{value: string, label: string, codeTypes: list<string>}>
	 */
	public static function get_catalog_for_js(): array {
		$out = [];

		foreach ( self::CATALOG as $entry ) {
			$out[] = [
				'value'     => $entry['value'],
				'label'     => $entry['label'],
				'codeTypes' => $entry['code_types'],
			];
		}

		return $out;
	}

	/**
	 * Locations valid for all given snippet types (bulk apply intersection).
	 *
	 * @param list<string> $types Snippet types.
	 * @return array<string, string> value => label
	 */
	public static function get_options_for_types( array $types ): array {
		$types = self::normalize_types( $types );

		if ( $types === [] ) {
			return [];
		}

		$out = [];

		foreach ( self::CATALOG as $entry ) {
			if ( self::entry_supports_all_types( $entry['code_types'], $types ) ) {
				$out[ $entry['value'] ] = $entry['label'];
			}
		}

		return $out;
	}

	/**
	 * Human-readable label for a stored location value.
	 */
	public static function get_label( string $location ): string {
		foreach ( self::CATALOG as $entry ) {
			if ( $entry['value'] === $location ) {
				return $entry['label'];
			}
		}

		return $location;
	}

	/**
	 * Label for table display, accounting for empty stored values per snippet type.
	 */
	public static function get_display_label( string $location, string $type ): string {
		if ( $location === '' ) {
			return strtolower( $type ) === 'php' ? self::get_label( '' ) : '--';
		}

		$label = self::get_label( $location );

		return $label !== '' ? $label : '--';
	}

	/**
	 * Locations valid for a single snippet type.
	 *
	 * @return array<string, string> value => label
	 */
	public static function get_options_for_type( string $type ): array {
		return self::get_options_for_types( [ $type ] );
	}

	/**
	 * Whether a location is valid for the given snippet type.
	 */
	public static function is_valid_for_type( string $location, string $type ): bool {
		$type = strtolower( $type );

		foreach ( self::CATALOG as $entry ) {
			if ( $entry['value'] !== $location ) {
				continue;
			}

			return in_array( $type, $entry['code_types'], true );
		}

		return false;
	}

	/**
	 * @return list<array{value: string, label: string, code_types: list<string>}>
	 */
	public static function all(): array {
		return self::CATALOG;
	}

	/**
	 * @param list<string> $types Snippet types.
	 * @return list<string>
	 */
	private static function normalize_types( array $types ): array {
		$normalized = [];

		foreach ( $types as $type ) {
			$type = strtolower( trim( (string) $type ) );
			if ( $type !== '' && preg_match( '/^[a-z0-9_-]+$/', $type ) ) {
				$normalized[] = $type;
			}
		}

		return array_values( array_unique( $normalized ) );
	}

	/**
	 * @param list<string> $entry_types Types supported by a catalog entry.
	 * @param list<string> $selected    Selected snippet types.
	 */
	private static function entry_supports_all_types( array $entry_types, array $selected ): bool {
		foreach ( $selected as $type ) {
			if ( ! in_array( $type, $entry_types, true ) ) {
				return false;
			}
		}

		return true;
	}
}
