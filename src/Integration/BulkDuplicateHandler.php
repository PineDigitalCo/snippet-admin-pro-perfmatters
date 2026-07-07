<?php
/**
 * Bulk duplicate POST handler and admin notices.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Integration;

use SnippetAdminProForPerfmatters\Perfmatters\SnippetService;
use SnippetAdminProForPerfmatters\Plugin;
use SnippetAdminProForPerfmatters\Support\BulkLocationRequest;

/**
 * Processes bulk snippet duplication and redirects back to Perfmatters.
 */
final class BulkDuplicateHandler {

	public const ACTION       = 'sapfp_bulk_duplicate';
	public const NONCE_ACTION = 'sapfp_bulk_duplicate';

	/**
	 * Register handler hooks.
	 */
	public static function register(): void {
		add_action( 'admin_post_' . self::ACTION, [ self::class, 'handle' ] );
	}

	/**
	 * Handle bulk duplicate POST (admin-post.php).
	 */
	public static function handle(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'snippet-admin-pro-for-perfmatters' ) );
		}

		check_admin_referer( self::NONCE_ACTION );

		$redirect = BulkLocationHandler::get_redirect_url();

		if ( ! Plugin::is_perfmatters_active() || ! SnippetService::is_available() ) {
			BulkLocationHandler::redirect_with_notice( $redirect, 'error', __( 'Perfmatters is not available.', 'snippet-admin-pro-for-perfmatters' ) );
		}

		$file_names = BulkLocationRequest::sanitize_snippet_files( $_POST['snippets'] ?? [] );

		if ( $file_names === [] ) {
			BulkLocationHandler::redirect_with_notice( $redirect, 'warning', __( 'Select at least one snippet.', 'snippet-admin-pro-for-perfmatters' ) );
		}

		$result = SnippetService::bulk_duplicate( $file_names );

		if ( $result['duplicated'] === 0 ) {
			$message = __( 'No snippets were duplicated.', 'snippet-admin-pro-for-perfmatters' );
			if ( $result['errors'] !== [] ) {
				$message .= ' ' . implode( ' ', array_unique( $result['errors'] ) );
			}

			BulkLocationHandler::redirect_with_notice( $redirect, 'error', $message );
		}

		$message = sprintf(
			/* translators: %d: number of snippets duplicated */
			_n( 'Duplicated %d snippet.', 'Duplicated %d snippets.', $result['duplicated'], 'snippet-admin-pro-for-perfmatters' ),
			$result['duplicated']
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

		BulkLocationHandler::redirect_with_notice( $redirect, 'success', $message );
	}
}
