<?php
/**
 * Perfmatters Code → Snippets list integration.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Integration;

use SnippetAdminProForPerfmatters\Perfmatters\LocationRegistry;
use SnippetAdminProForPerfmatters\Perfmatters\SnippetService;
use SnippetAdminProForPerfmatters\Plugin;

/**
 * Injects bulk location controls into the Perfmatters snippets list.
 */
final class PerfmattersSnippetsIntegration {

	/**
	 * Register integration hooks.
	 */
	public static function register(): void {
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
		add_action( 'admin_footer', [ self::class, 'render_post_form' ] );
		BulkLocationHandler::register();
	}

	/**
	 * Whether the current request is the Perfmatters snippets list (not single-snippet editor).
	 */
	public static function is_snippets_list_screen(): bool {
		if ( ! is_admin() || ! Plugin::is_perfmatters_active() ) {
			return false;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( (string) wp_unslash( $_GET['page'] ) ) : '';

		if ( $page !== 'perfmatters' ) {
			return false;
		}

		if ( ! empty( $_GET['snippet'] ) ) {
			return false;
		}

		return current_user_can( 'manage_options' );
	}

	/**
	 * Enqueue assets on the Perfmatters snippets list only.
	 *
	 * @param string $hook_suffix Current admin screen hook.
	 */
	public static function enqueue_assets( string $hook_suffix ): void {
		if ( $hook_suffix !== 'settings_page_perfmatters' || ! self::is_snippets_list_screen() ) {
			return;
		}

		wp_enqueue_style(
			'sapfp-perfmatters',
			SAPFP_URL . 'assets/admin/perfmatters-bulk-location.css',
			[],
			SAPFP_VERSION
		);

		wp_enqueue_script(
			'sapfp-perfmatters',
			SAPFP_URL . 'assets/admin/perfmatters-bulk-location.js',
			[],
			SAPFP_VERSION,
			true
		);

		wp_localize_script(
			'sapfp-perfmatters',
			'SAPFP_PMCS',
			[
				'locationCatalog'   => LocationRegistry::get_catalog_for_js(),
				'snippetLocations'  => SnippetService::get_location_labels_by_file(),
				'strings'           => [
					'changeLocationTo'   => __( 'Change location to', 'snippet-admin-pro-for-perfmatters' ),
					'selectLocation'     => __( 'Select location…', 'snippet-admin-pro-for-perfmatters' ),
					'selectSnippetsFirst' => __( 'Select snippets to see locations', 'snippet-admin-pro-for-perfmatters' ),
					'noCommonLocations'  => __( 'No common locations for selected types', 'snippet-admin-pro-for-perfmatters' ),
					'apply'              => __( 'Apply to selected', 'snippet-admin-pro-for-perfmatters' ),
					'selectSnippets'     => __( 'Select at least one snippet in the list below.', 'snippet-admin-pro-for-perfmatters' ),
					'chooseLocation'     => __( 'Choose a location.', 'snippet-admin-pro-for-perfmatters' ),
					'locationColumn'     => __( 'Location', 'snippet-admin-pro-for-perfmatters' ),
				],
			]
		);
	}

	/**
	 * Hidden POST form for bulk location updates.
	 */
	public static function render_post_form(): void {
		if ( ! self::is_snippets_list_screen() ) {
			return;
		}

		$status = isset( $_GET['status'] ) ? sanitize_key( (string) wp_unslash( $_GET['status'] ) ) : '';
		$type   = isset( $_GET['type'] ) ? sanitize_key( (string) wp_unslash( $_GET['type'] ) ) : '';
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['s'] ) ) : '';
		$location_sort = BulkLocationHandler::sanitize_location_sort( $_GET['sapfp_location_sort'] ?? '' );
		?>
		<form id="sapfp-bulk-location-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" hidden>
			<input type="hidden" name="action" value="<?php echo esc_attr( BulkLocationHandler::ACTION ); ?>" />
			<?php wp_nonce_field( BulkLocationHandler::NONCE_ACTION ); ?>
			<input type="hidden" name="new_location" id="sapfp-new-location-input" value="" />
			<input type="hidden" name="redirect_status" value="<?php echo esc_attr( $status ); ?>" />
			<input type="hidden" name="redirect_type" value="<?php echo esc_attr( $type ); ?>" />
			<input type="hidden" name="redirect_search" value="<?php echo esc_attr( $search ); ?>" />
			<input type="hidden" name="redirect_location_sort" id="sapfp-redirect-location-sort" value="<?php echo esc_attr( $location_sort ); ?>" />
			<div id="sapfp-snippet-inputs"></div>
		</form>
		<?php
	}
}
