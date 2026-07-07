<?php
/**
 * Merge sapfp condition logic into Perfmatters snippet save requests.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Integration;

use SnippetAdminProForPerfmatters\Perfmatters\ConditionLogic;

/**
 * Injects sapfp:logic sentinel into $_POST before Perfmatters saves a snippet.
 */
final class ConditionsSaveHandler {

	/**
	 * Register save handler (runs before Perfmatters PMCS::action_handler).
	 */
	public static function register(): void {
		add_action( 'admin_init', [ self::class, 'maybe_merge_logic_into_post' ], 5 );
	}

	/**
	 * Merge sapfp logic config from POST into conditions array.
	 */
	public static function maybe_merge_logic_into_post(): void {
		if ( empty( $_POST['save_snippet'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $_POST['sapfp_condition_logic'] ) ) {
			return;
		}

		$raw = wp_unslash( (string) $_POST['sapfp_condition_logic'] );
		$decoded = json_decode( $raw, true );

		if ( ! is_array( $decoded ) ) {
			return;
		}

		$config     = ConditionLogic::normalize_config( $decoded );
		$conditions = isset( $_POST['conditions'] ) && is_array( $_POST['conditions'] )
			? wp_unslash( $_POST['conditions'] )
			: [];

		$_POST['conditions'] = self::apply_config_to_conditions( $conditions, $config );
	}

	/**
	 * @param array<string, mixed> $conditions Snippet conditions from POST.
	 * @param array<string, mixed> $config     sapfp logic config.
	 * @return array<string, mixed>
	 */
	public static function apply_config_to_conditions( array $conditions, array $config ): array {
		$conditions = ConditionLogic::strip_sentinel( $conditions );

		if ( self::is_default_config( $config ) ) {
			return $conditions;
		}

		if ( ! isset( $conditions['exclude'] ) || ! is_array( $conditions['exclude'] ) ) {
			$conditions['exclude'] = [];
		}

		$conditions['exclude'][] = ConditionLogic::build_sentinel_row( $config );

		return $conditions;
	}

	/**
	 * @param array<string, mixed> $config sapfp logic config.
	 */
	public static function is_default_config( array $config ): bool {
		$defaults = ConditionLogic::default_config();

		foreach ( $defaults as $key => $value ) {
			if ( ( $config[ $key ] ?? $value ) !== $value ) {
				return false;
			}
		}

		return empty( $config['groups'] );
	}
}
