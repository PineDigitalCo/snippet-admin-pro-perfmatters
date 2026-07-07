<?php
/**
 * Plugin bootstrap.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters;

use SnippetAdminProForPerfmatters\Integration\ConditionsSaveHandler;
use SnippetAdminProForPerfmatters\Integration\PerfmattersConditionsIntegration;
use SnippetAdminProForPerfmatters\Integration\PerfmattersSnippetsIntegration;

/**
 * Main plugin class.
 */
final class Plugin {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		if ( ! self::is_perfmatters_active() ) {
			return;
		}

		if ( is_admin() ) {
			PerfmattersSnippetsIntegration::register();
			PerfmattersConditionsIntegration::register();
			ConditionsSaveHandler::register();
		}
	}

	/**
	 * Whether Perfmatters is installed and active.
	 */
	public static function is_perfmatters_active(): bool {
		return defined( 'PERFMATTERS_VERSION' ) || class_exists( '\Perfmatters\PMCS\PMCS' );
	}
}
