<?php
/**
 * Bulk location POST handler and admin notices.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Integration;

use SnippetAdminProForPerfmatters\Perfmatters\SnippetService;
use SnippetAdminProForPerfmatters\Plugin;
use SnippetAdminProForPerfmatters\Support\BulkLocationRequest;

/**
 * Processes bulk location changes and redirects back to Perfmatters.
 */
final class BulkLocationHandler {

	public const ACTION       = 'sapfp_bulk_location';
	public const NONCE_ACTION = 'sapfp_bulk_location';

	/**
	 * Register handler hooks.
	 */
	public static function register(): void {
		add_action( 'admin_post_' . self::ACTION, [ self::class, 'handle' ] );
		add_action( 'pmcs_admin_notice', [ self::class, 'render_redirect_notice' ] );
	}

	/**
	 * Handle bulk location POST (admin-post.php).
	 */
	public static function handle(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'snippet-admin-pro-for-perfmatters' ) );
		}

		check_admin_referer( self::NONCE_ACTION );

		$redirect = self::get_redirect_url();

		if ( ! Plugin::is_perfmatters_active() || ! SnippetService::is_available() ) {
			self::redirect_with_notice( $redirect, 'error', __( 'Perfmatters is not available.', 'snippet-admin-pro-for-perfmatters' ) );
		}

		$file_names   = BulkLocationRequest::sanitize_snippet_files( $_POST['snippets'] ?? [] );
		$location_raw = isset( $_POST['new_location'] ) ? (string) wp_unslash( $_POST['new_location'] ) : '__none__';

		if ( $file_names === [] ) {
			self::redirect_with_notice( $redirect, 'warning', __( 'Select at least one snippet.', 'snippet-admin-pro-for-perfmatters' ) );
		}

		if ( $location_raw === '__none__' ) {
			self::redirect_with_notice( $redirect, 'warning', __( 'Choose a location.', 'snippet-admin-pro-for-perfmatters' ) );
		}

		$location = BulkLocationRequest::normalize_submitted_location( $location_raw );
		$result   = SnippetService::bulk_update_location( $file_names, $location );

		if ( $result['updated'] === 0 ) {
			$message = __( 'No snippets were updated.', 'snippet-admin-pro-for-perfmatters' );
			if ( $result['errors'] !== [] ) {
				$message .= ' ' . implode( ' ', array_unique( $result['errors'] ) );
			}

			self::redirect_with_notice( $redirect, 'error', $message );
		}

		$message = sprintf(
			/* translators: %d: number of snippets updated */
			_n( 'Updated location for %d snippet.', 'Updated location for %d snippets.', $result['updated'], 'snippet-admin-pro-for-perfmatters' ),
			$result['updated']
		);

		if ( $result['skipped'] > 0 ) {
			$message .= ' ' . sprintf(
				/* translators: %d: number of snippets skipped */
				_n( '%d snippet was skipped.', '%d snippets were skipped.', $result['skipped'], 'snippet-admin-pro-for-perfmatters' ),
				$result['skipped']
			);
		}

		if ( $result['errors'] !== [] ) {
			$message .= ' ' . implode( ' ', array_unique( $result['errors'] ) );
		}

		self::redirect_with_notice( $redirect, 'success', $message );
	}

	/**
	 * Show notice after redirect on the Perfmatters snippets screen.
	 */
	public static function render_redirect_notice(): void {
		if ( ! PerfmattersSnippetsIntegration::is_snippets_list_screen() ) {
			return;
		}

		$type = isset( $_GET['sapfp_notice'] ) ? sanitize_key( (string) wp_unslash( $_GET['sapfp_notice'] ) ) : '';
		$text = isset( $_GET['sapfp_message'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['sapfp_message'] ) ) : '';

		if ( $type === '' || $text === '' || ! in_array( $type, [ 'success', 'warning', 'error' ], true ) ) {
			return;
		}

		wp_admin_notice(
			esc_html( $text ),
			[
				'type'        => $type,
				'dismissible' => true,
			]
		);
	}

	/**
	 * Build redirect URL back to Perfmatters snippets list.
	 */
	public static function get_redirect_url(): string {
		$args = [
			'page' => 'perfmatters',
		];

		if ( ! empty( $_POST['redirect_status'] ) ) {
			$args['status'] = sanitize_key( (string) wp_unslash( $_POST['redirect_status'] ) );
		}

		if ( ! empty( $_POST['redirect_type'] ) ) {
			$args['type'] = sanitize_key( (string) wp_unslash( $_POST['redirect_type'] ) );
		}

		if ( ! empty( $_POST['redirect_search'] ) ) {
			$args['s'] = sanitize_text_field( (string) wp_unslash( $_POST['redirect_search'] ) );
		}

		$location_sort = self::sanitize_location_sort( $_POST['redirect_location_sort'] ?? '' );
		if ( $location_sort !== '' ) {
			$args['sapfp_location_sort'] = $location_sort;
		}

		return add_query_arg( $args, admin_url( 'admin.php' ) ) . '#code';
	}

	/**
	 * @param mixed $value Raw sort direction.
	 */
	public static function sanitize_location_sort( $value ): string {
		$sort = sanitize_key( (string) $value );

		return in_array( $sort, [ 'asc', 'desc' ], true ) ? $sort : '';
	}

	/**
	 * Redirect with flash query args (stripped on display).
	 *
	 * @param string $redirect Target URL without notice args.
	 */
	public static function redirect_with_notice( string $redirect, string $type, string $message ): void {
		$url = add_query_arg(
			[
				'sapfp_notice'  => $type,
				'sapfp_message' => $message,
			],
			$redirect
		);

		wp_safe_redirect( $url );
		exit;
	}
}
