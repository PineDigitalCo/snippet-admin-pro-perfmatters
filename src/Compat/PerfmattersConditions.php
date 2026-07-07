<?php
/**
 * Perfmatters PMCS Conditions shim — adds sapfp OR/AND logic, delegates everything else.
 *
 * Loaded via spl_autoload_register before Perfmatters' composer autoload when possible.
 * Synced with Perfmatters 2.6.5 Conditions API surface.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace Perfmatters\PMCS;

use SnippetAdminProForPerfmatters\Compat\NativeConditions;
use SnippetAdminProForPerfmatters\Perfmatters\ConditionLogic;

/**
 * Drop-in replacement for Perfmatters\PMCS\Conditions.
 */
class Conditions {

	/**
	 * Evaluate snippet conditions (native or sapfp-enhanced).
	 *
	 * @param array<string, mixed>|mixed $conditions Snippet conditions.
	 */
	public static function evaluate( $conditions ): bool {
		if ( empty( $conditions ) || ! is_array( $conditions ) ) {
			return true;
		}

		$config = ConditionLogic::extract_config( $conditions );

		if ( $config !== null ) {
			return ConditionLogic::evaluate( $conditions, $config );
		}

		return NativeConditions::evaluate( $conditions );
	}

	/**
	 * @param mixed $condition Condition row.
	 * @param array<string, mixed> $location Current location.
	 */
	public static function check_condition_match( $condition, array $location ): bool {
		return NativeConditions::check_condition_match( $condition, $location );
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_current_location(): array {
		return NativeConditions::get_current_location();
	}

	/**
	 * @return list<string>
	 */
	public static function get_current_user(): array {
		return NativeConditions::get_current_user();
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_conditions(): array {
		return NativeConditions::get_conditions();
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_user_conditions(): array {
		return NativeConditions::get_user_conditions();
	}

	/**
	 * @param mixed $rule Condition rule id.
	 * @param array<string, mixed>|null $conditions Condition catalog.
	 * @return array<string, string>
	 */
	public static function get_rule_meta( $rule, $conditions = null ): array {
		return NativeConditions::get_rule_meta( $rule, $conditions );
	}

	/**
	 * @param string $type include|exclude|users.
	 * @param array<string, mixed> $conditions Condition catalog.
	 * @param int $row_count Row index.
	 * @param array<string, mixed>|mixed $value Row value.
	 * @param bool $hidden Whether row is hidden.
	 */
	public static function print_input_row( string $type, array $conditions, int $row_count = 0, $value = [], bool $hidden = false ): void {
		NativeConditions::print_input_row( $type, $conditions, $row_count, $value, $hidden );
	}
}
