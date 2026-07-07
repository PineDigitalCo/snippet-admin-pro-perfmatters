<?php
/**
 * Autoload Perfmatters\PMCS\Conditions from this plugin.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Support;

/**
 * Registers a prepended autoloader for the Perfmatters Conditions class.
 */
final class ConditionsAutoload {

	/**
	 * Register autoloader if Perfmatters Conditions is not already loaded.
	 */
	public static function register(): void {
		if ( class_exists( '\Perfmatters\PMCS\Conditions', false ) ) {
			return;
		}

		spl_autoload_register( [ self::class, 'autoload' ], true, true );
	}

	/**
	 * @param class-string $class_name Class being autoloaded.
	 */
	public static function autoload( string $class_name ): void {
		if ( $class_name !== 'Perfmatters\PMCS\Conditions' ) {
			return;
		}

		if ( class_exists( $class_name, false ) ) {
			return;
		}

		$file = dirname( __DIR__ ) . '/Compat/PerfmattersConditions.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
