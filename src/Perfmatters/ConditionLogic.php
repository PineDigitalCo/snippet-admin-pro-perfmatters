<?php
/**
 * Extended Perfmatters snippet condition evaluation (OR / AND logic).
 *
 * Config is stored in snippet conditions as a sentinel row:
 *   rule: sapfp:logic, object: JSON
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Perfmatters;

use SnippetAdminProForPerfmatters\Compat\NativeConditions;

/**
 * Parses sapfp:logic metadata and evaluates conditions with configurable operators.
 */
final class ConditionLogic {

	public const SENTINEL_RULE = 'sapfp:logic';

	public const LOGIC_OR  = 'or';
	public const LOGIC_AND = 'and';

	/**
	 * Default config when no sentinel is stored (native Perfmatters behavior).
	 *
	 * @return array<string, string>
	 */
	public static function default_config(): array {
		return [
			'include_logic'  => self::LOGIC_OR,
			'exclude_logic'  => self::LOGIC_OR,
			'section_logic'  => self::LOGIC_AND,
		];
	}

	/**
	 * Extract sapfp logic config from conditions, if present.
	 *
	 * @param array<string, mixed> $conditions Snippet conditions.
	 * @return array<string, mixed>|null
	 */
	public static function extract_config( array $conditions ): ?array {
		foreach ( [ 'exclude', 'include' ] as $type ) {
			if ( empty( $conditions[ $type ] ) || ! is_array( $conditions[ $type ] ) ) {
				continue;
			}

			foreach ( $conditions[ $type ] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}

				if ( ( $row['rule'] ?? '' ) !== self::SENTINEL_RULE ) {
					continue;
				}

				$decoded = json_decode( (string) ( $row['object'] ?? '' ), true );

				return is_array( $decoded ) ? self::normalize_config( $decoded ) : null;
			}
		}

		return null;
	}

	/**
	 * Whether enhanced logic is configured (non-default sentinel).
	 *
	 * @param array<string, mixed> $conditions Snippet conditions.
	 */
	public static function has_enhanced_logic( array $conditions ): bool {
		return self::extract_config( $conditions ) !== null;
	}

	/**
	 * Remove sapfp sentinel rows from conditions.
	 *
	 * @param array<string, mixed> $conditions Snippet conditions.
	 * @return array<string, mixed>
	 */
	public static function strip_sentinel( array $conditions ): array {
		foreach ( [ 'exclude', 'include' ] as $type ) {
			if ( empty( $conditions[ $type ] ) || ! is_array( $conditions[ $type ] ) ) {
				continue;
			}

			$conditions[ $type ] = array_values(
				array_filter(
					$conditions[ $type ],
					static function ( $row ): bool {
						return ! is_array( $row ) || ( $row['rule'] ?? '' ) !== self::SENTINEL_RULE;
					}
				)
			);
		}

		return $conditions;
	}

	/**
	 * Build a sentinel condition row for persistence in snippet meta.
	 *
	 * @param array<string, string> $config Logic config.
	 * @return array{rule: string, object: string}
	 */
	public static function build_sentinel_row( array $config ): array {
		return [
			'rule'   => self::SENTINEL_RULE,
			'object' => (string) json_encode( self::normalize_config( $config ), JSON_THROW_ON_ERROR ),
		];
	}

	/**
	 * Evaluate conditions using sapfp logic config.
	 *
	 * @param array<string, mixed> $conditions Raw snippet conditions (may include sentinel).
	 * @param array<string, mixed> $config     Parsed sapfp logic config.
	 */
	public static function evaluate( array $conditions, array $config ): bool {
		$conditions = self::strip_sentinel( $conditions );
		$config     = self::normalize_config( $config );

		if ( ! empty( $config['groups'] ) && is_array( $config['groups'] ) ) {
			return self::evaluate_groups( $conditions, $config );
		}

		$include_logic = (string) ( $config['include_logic'] ?? self::LOGIC_OR );
		$exclude_logic = (string) ( $config['exclude_logic'] ?? self::LOGIC_OR );
		$section_logic = (string) ( $config['section_logic'] ?? self::LOGIC_AND );

		if ( $section_logic === self::LOGIC_OR ) {
			return self::evaluate_sections_or( $conditions, $include_logic, $exclude_logic );
		}

		return self::evaluate_sections_and( $conditions, $include_logic, $exclude_logic );
	}

	/**
	 * @param array<string, mixed> $config Raw config.
	 * @return array<string, mixed>
	 */
	public static function normalize_config( array $config ): array {
		$normalized = self::default_config();

		foreach ( [ 'include_logic', 'exclude_logic', 'section_logic' ] as $key ) {
			if ( ! isset( $config[ $key ] ) ) {
				continue;
			}

			$value = strtolower( (string) $config[ $key ] );
			if ( $value === self::LOGIC_OR || $value === self::LOGIC_AND ) {
				$normalized[ $key ] = $value;
			}
		}

		if ( ! empty( $config['groups'] ) && is_array( $config['groups'] ) ) {
			$normalized['groups']          = $config['groups'];
			$normalized['group_operator']    = self::LOGIC_OR;
			$group_operator                  = strtolower( (string) ( $config['group_operator'] ?? self::LOGIC_OR ) );
			$normalized['group_operator']    = $group_operator === self::LOGIC_AND ? self::LOGIC_AND : self::LOGIC_OR;
		}

		return $normalized;
	}

	/**
	 * @param array<string, mixed> $conditions Snippet conditions.
	 * @param array<string, mixed> $config     Config with groups.
	 */
	private static function evaluate_groups( array $conditions, array $config ): bool {
		$groups          = $config['groups'];
		$group_operator  = (string) ( $config['group_operator'] ?? self::LOGIC_OR );
		$group_results   = [];

		foreach ( $groups as $group ) {
			if ( ! is_array( $group ) ) {
				continue;
			}

			$merged = $conditions;

			foreach ( [ 'include', 'exclude', 'users' ] as $type ) {
				if ( ! empty( $group[ $type ] ) && is_array( $group[ $type ] ) ) {
					$merged[ $type ] = $group[ $type ];
				}
			}

			$group_config = array_merge(
				self::default_config(),
				array_intersect_key( $group, array_flip( [ 'include_logic', 'exclude_logic', 'section_logic' ] ) )
			);

			$group_results[] = self::evaluate_sections_and(
				$merged,
				(string) ( $group_config['include_logic'] ?? self::LOGIC_OR ),
				(string) ( $group_config['exclude_logic'] ?? self::LOGIC_OR )
			);
		}

		if ( $group_results === [] ) {
			return NativeConditions::evaluate( $conditions );
		}

		if ( $group_operator === self::LOGIC_AND ) {
			return ! in_array( false, $group_results, true );
		}

		return in_array( true, $group_results, true );
	}

	/**
	 * Native-style: all sections must pass (with configurable row operators).
	 *
	 * @param array<string, mixed> $conditions Snippet conditions.
	 */
	private static function evaluate_sections_and( array $conditions, string $include_logic, string $exclude_logic ): bool {
		if ( ! empty( $conditions['include'] ) && is_array( $conditions['include'] ) ) {
			if ( ! self::match_include_rows( $conditions['include'], $include_logic ) ) {
				return false;
			}
		}

		if ( ! empty( $conditions['exclude'] ) && is_array( $conditions['exclude'] ) ) {
			if ( self::should_exclude( $conditions['exclude'], $exclude_logic ) ) {
				return false;
			}
		}

		if ( ! empty( $conditions['users'] ) && is_array( $conditions['users'] ) ) {
			if ( ! self::evaluate_users( $conditions['users'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * OR between Include and Users sections; excludes always gate the result.
	 *
	 * @param array<string, mixed> $conditions Snippet conditions.
	 */
	private static function evaluate_sections_or( array $conditions, string $include_logic, string $exclude_logic ): bool {
		if ( ! empty( $conditions['exclude'] ) && is_array( $conditions['exclude'] ) ) {
			if ( self::should_exclude( $conditions['exclude'], $exclude_logic ) ) {
				return false;
			}
		}

		$has_include = ! empty( $conditions['include'] ) && is_array( $conditions['include'] );
		$has_users   = ! empty( $conditions['users'] ) && is_array( $conditions['users'] );

		if ( ! $has_include && ! $has_users ) {
			return true;
		}

		$include_pass = $has_include && self::match_include_rows( $conditions['include'], $include_logic );
		$users_pass   = $has_users && self::evaluate_users( $conditions['users'] );

		return $include_pass || $users_pass;
	}

	/**
	 * @param list<array<string, mixed>> $rows Include rows.
	 */
	private static function match_include_rows( array $rows, string $logic ): bool {
		$location = NativeConditions::get_current_location();

		if ( $logic === self::LOGIC_AND ) {
			foreach ( $rows as $condition ) {
				if ( ! is_array( $condition ) || ! NativeConditions::check_condition_match( $condition, $location ) ) {
					return false;
				}
			}

			return $rows !== [];
		}

		foreach ( $rows as $condition ) {
			if ( is_array( $condition ) && NativeConditions::check_condition_match( $condition, $location ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param list<array<string, mixed>> $rows Exclude rows.
	 */
	private static function should_exclude( array $rows, string $logic ): bool {
		$location = NativeConditions::get_current_location();

		if ( $logic === self::LOGIC_AND ) {
			$matched = 0;

			foreach ( $rows as $condition ) {
				if ( is_array( $condition ) && NativeConditions::check_condition_match( $condition, $location ) ) {
					++$matched;
				}
			}

			return $matched > 0 && $matched === count( $rows );
		}

		foreach ( $rows as $condition ) {
			if ( is_array( $condition ) && NativeConditions::check_condition_match( $condition, $location ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param list<array<string, mixed>> $users User condition rows.
	 */
	private static function evaluate_users( array $users ): bool {
		$user_info = NativeConditions::get_current_user();
		$roles     = array_column( $users, 'rule' );

		return ! empty( array_intersect( $roles, $user_info ) );
	}
}
