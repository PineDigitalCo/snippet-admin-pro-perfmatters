<?php
/**
 * Perfmatters snippet editor — OR/AND condition logic UI.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Integration;

use SnippetAdminProForPerfmatters\Perfmatters\ConditionLogic;
use SnippetAdminProForPerfmatters\Plugin;

/**
 * Injects condition logic controls on the single-snippet editor screen.
 */
final class PerfmattersConditionsIntegration {

	/**
	 * Register integration hooks.
	 */
	public static function register(): void {
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
	}

	/**
	 * Whether the current request is the Perfmatters single-snippet editor.
	 */
	public static function is_snippet_editor_screen(): bool {
		if ( ! is_admin() || ! Plugin::is_perfmatters_active() ) {
			return false;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( (string) wp_unslash( $_GET['page'] ) ) : '';

		if ( $page !== 'perfmatters' ) {
			return false;
		}

		$snippet = isset( $_GET['snippet'] ) ? sanitize_file_name( (string) wp_unslash( $_GET['snippet'] ) ) : '';

		return $snippet !== '' && $snippet !== 'create' && current_user_can( 'manage_options' );
	}

	/**
	 * Enqueue assets on the snippet editor only.
	 *
	 * @param string $hook_suffix Current admin screen hook.
	 */
	public static function enqueue_assets( string $hook_suffix ): void {
		if ( $hook_suffix !== 'settings_page_perfmatters' || ! self::is_snippet_editor_screen() ) {
			return;
		}

		$config = self::get_editor_config();

		wp_enqueue_style(
			'sapfp-perfmatters-conditions',
			SAPFP_URL . 'assets/admin/perfmatters-conditions.css',
			[],
			SAPFP_VERSION
		);

		wp_enqueue_script(
			'sapfp-perfmatters-conditions',
			SAPFP_URL . 'assets/admin/perfmatters-conditions.js',
			[ 'jquery' ],
			SAPFP_VERSION,
			true
		);

		wp_localize_script(
			'sapfp-perfmatters-conditions',
			'SAPFP_CONDITIONS',
			[
				'config'   => $config,
				'sentinel' => ConditionLogic::SENTINEL_RULE,
				'strings'  => [
					'panelTitle'       => __( 'Condition logic', 'snippet-admin-pro-for-perfmatters' ),
					'panelDescription' => __( 'OR = any match · AND = all must match. Saved with the snippet.', 'snippet-admin-pro-for-perfmatters' ),
					'includeLogic'     => __( 'Include rules', 'snippet-admin-pro-for-perfmatters' ),
					'includeOrShort'   => __( 'Any include', 'snippet-admin-pro-for-perfmatters' ),
					'includeAndShort'  => __( 'All includes', 'snippet-admin-pro-for-perfmatters' ),
					'excludeLogic'     => __( 'Exclude rules', 'snippet-admin-pro-for-perfmatters' ),
					'excludeOrShort'   => __( 'Any match', 'snippet-admin-pro-for-perfmatters' ),
					'excludeAndShort'  => __( 'All match', 'snippet-admin-pro-for-perfmatters' ),
					'sectionLogic'     => __( 'Include + users', 'snippet-admin-pro-for-perfmatters' ),
					'sectionHint'      => __( 'How location and user rules combine', 'snippet-admin-pro-for-perfmatters' ),
					'sectionAndShort'  => __( 'Both required', 'snippet-admin-pro-for-perfmatters' ),
					'sectionOrShort'   => __( 'Either enables', 'snippet-admin-pro-for-perfmatters' ),
					'orBadge'          => __( 'OR', 'snippet-admin-pro-for-perfmatters' ),
					'andBadge'         => __( 'AND', 'snippet-admin-pro-for-perfmatters' ),
				],
			]
		);
	}

	/**
	 * Load sapfp logic config for the open snippet.
	 *
	 * @return array<string, string>
	 */
	private static function get_editor_config(): array {
		$file_name = isset( $_GET['snippet'] ) ? sanitize_file_name( (string) wp_unslash( $_GET['snippet'] ) ) : '';

		if ( $file_name === '' || ! class_exists( '\Perfmatters\PMCS\Snippet' ) ) {
			return ConditionLogic::default_config();
		}

		$snippet = \Perfmatters\PMCS\Snippet::get( $file_name );

		if ( ! is_array( $snippet ) ) {
			return ConditionLogic::default_config();
		}

		$conditions = $snippet['meta']['conditions'] ?? $snippet['conditions'] ?? [];

		if ( ! is_array( $conditions ) ) {
			return ConditionLogic::default_config();
		}

		$config = ConditionLogic::extract_config( $conditions );

		return $config !== null ? $config : ConditionLogic::default_config();
	}
}
